<?php

namespace Tests\Feature;

use App\Models\Achievement;
use App\Models\Category;
use App\Models\Game;
use App\Models\GameReport;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ZipArchive;

class GamePortalAndE2ETest extends TestCase
{
    use RefreshDatabase;

    protected function createValidGameZip(string $version = '1.0.0'): string
    {
        $tempZip = storage_path('app/e2e_game_' . uniqid() . '.zip');
        $zip = new ZipArchive();
        $zip->open($tempZip, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString(
            'index.html',
            '<!DOCTYPE html><html><head><meta name="viewport" content="width=device-width, initial-scale=1.0"></head><body><canvas id="game"></canvas><script src="main.js"></script></body></html>'
        );
        $zip->addFromString('main.js', 'console.log("Game running v' . $version . '");');
        $zip->addFromString('game.json', json_encode([
            'name' => 'Siêu Phẩm E2E',
            'description' => 'Mô tả tự động từ tệp game.json',
            'version' => $version,
            'author' => 'GameHub QA',
            'orientation' => 'landscape',
            'mobile_support' => true,
        ]));
        $zip->close();

        return $tempZip;
    }

    public function test_full_e2e_admin_upload_version_rollback_and_player_interaction_flow(): void
    {
        $adminRole = Role::create(['name' => 'Super Admin', 'slug' => 'super-admin']);
        $userRole = Role::create(['name' => 'Người chơi', 'slug' => 'user']);

        $admin = User::factory()->create(['name' => 'Admin QA']);
        $admin->roles()->attach($adminRole->id);

        $player = User::factory()->create(['name' => 'Player QA', 'total_points' => 10]);
        $player->roles()->attach($userRole->id);

        $category = Category::factory()->create(['name' => 'Hành Động', 'slug' => 'hanh-dong']);

        Achievement::create([
            'title' => 'Tân Binh',
            'slug' => 'first-play',
            'description' => 'Chơi ván đầu tiên',
            'points' => 25,
            'condition_type' => 'games_played',
            'condition_value' => 1,
        ]);

        // 1. Admin creates game + uploads v1.0.0 ZIP
        $zipPathV1 = $this->createValidGameZip('1.0.0');
        $uploadedZipV1 = new UploadedFile($zipPathV1, 'game_v1.zip', 'application/zip', null, true);

        $createResp = $this->actingAs($admin)->post(route('admin.games.store'), [
            'title' => 'Siêu Phẩm E2E',
            'category_id' => $category->id,
            'orientation' => 'landscape',
            'status' => 'published',
            'mobile_support' => 1,
            'is_featured' => 1,
            'version_number' => '1.0.0',
            'auto_publish' => 1,
            'game_zip' => $uploadedZipV1,
        ]);

        $game = Game::where('slug', 'sieu-pham-e2e')->firstOrFail();
        $createResp->assertRedirect(route('admin.games.edit', $game));
        $this->assertNotNull($game->current_version_id);

        $version1 = $game->currentVersion;
        $this->assertEquals('1.0.0', $version1->version_number);
        $this->assertTrue($version1->is_active);

        // 2. Admin uploads v1.1.0 in preview mode, publishes it, then rolls back to v1.0.0
        $zipPathV2 = $this->createValidGameZip('1.1.0');
        $uploadedZipV2 = new UploadedFile($zipPathV2, 'game_v2.zip', 'application/zip', null, true);

        $this->actingAs($admin)->post(route('admin.games.versions.upload', $game), [
            'game_zip' => $uploadedZipV2,
            'version_number' => '1.1.0',
            'changelog' => 'Bản cập nhật 1.1.0',
        ]);

        $version2 = $game->versions()->where('version_number', '1.1.0')->firstOrFail();
        $this->assertFalse($version2->is_active);

        // Publish v1.1.0
        $this->actingAs($admin)->post(route('admin.games.versions.publish', [$game, $version2]));
        $game->refresh();
        $this->assertEquals($version2->id, $game->current_version_id);

        // Rollback to v1.0.0 in 1 click
        $this->actingAs($admin)->post(route('admin.games.versions.rollback', [$game, $version1]));
        $game->refresh();
        $this->assertEquals($version1->id, $game->current_version_id);

        // 3. Public & Player E2E flow: Home, Explore, Show, Play, Session Score, Favorite, Review, Report
        $this->get(route('home'))->assertStatus(200)->assertSee('Siêu Phẩm E2E');
        $this->get(route('games.explore'))->assertStatus(200)->assertSee('Siêu Phẩm E2E');
        $this->get(route('games.show', $game->slug))->assertStatus(200)->assertSee('Siêu Phẩm E2E');
        $this->get(route('sitemap'))->assertStatus(200)->assertSee($game->slug);
        $this->get(route('robots'))->assertStatus(200)->assertSee('Sitemap:');

        // Player plays game
        $playResp = $this->actingAs($player)->get(route('games.play', $game->slug));
        $playResp->assertStatus(200);
        $playResp->assertSee('allow-scripts allow-same-origin', false);

        // Player records session & score -> unlocks achievement
        $sessionResp = $this->actingAs($player)->postJson(route('games.session.record', $game), [
            'session_token' => 'test-token-12345',
            'duration_seconds' => 120,
            'score' => 450,
        ]);
        $sessionResp->assertStatus(200)->assertJson(['status' => 'success']);

        $player->refresh();
        $this->assertEquals(35, $player->total_points); // 10 starter + 25 achievement

        // Player favorites game
        $this->actingAs($player)->post(route('games.favorite.toggle', $game));
        $this->assertEquals(1, $game->fresh()->favorite_count);

        // Player writes 5-star review
        $this->actingAs($player)->post(route('games.reviews.store', $game), [
            'rating' => 5,
            'comment' => 'Game chạy cực mượt trên trình duyệt!',
        ]);
        $game->refresh();
        $this->assertEquals(5.0, $game->avg_rating);
        $this->assertEquals(1, $game->review_count);

        // Player submits bug report
        $this->actingAs($player)->post(route('games.report.store', $game), [
            'type' => 'bug',
            'description' => 'Cần thêm hiệu ứng âm thanh khi chiến thắng.',
        ]);

        $report = GameReport::firstOrFail();
        $this->assertEquals('pending', $report->status);

        // 4. Admin resolves the report
        $this->actingAs($admin)->put(route('admin.reports.resolve', $report), [
            'status' => 'resolved',
            'admin_note' => 'Đã ghi nhận và bổ sung âm thanh.',
        ]);
        $this->assertEquals('resolved', $report->fresh()->status);

        File::delete([$zipPathV1, $zipPathV2]);
        File::deleteDirectory(storage_path('app/public/games/sieu-pham-e2e'));
    }
}

