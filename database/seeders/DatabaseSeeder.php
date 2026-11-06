<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\Announcement;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Game;
use App\Models\GameReview;
use App\Models\GameScore;
use App\Models\HomepageSection;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\User;
use App\Services\Audio\AudioSynthesizerService;
use App\Services\GameUpload\GameUploadService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use ZipArchive;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles & Permissions
        $superAdminRole = Role::updateOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Admin', 'description' => 'Toàn quyền quản trị hệ thống GameHub']
        );
        $adminRole = Role::updateOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Quản trị viên', 'description' => 'Quản lý trò chơi, nội dung và người dùng']
        );
        $userRole = Role::updateOrCreate(
            ['slug' => 'user'],
            ['name' => 'Người chơi', 'description' => 'Thành viên tham gia chơi game và đánh giá']
        );

        $permissionsData = [
            ['name' => 'Quản lý Game & ZIP', 'slug' => 'games.manage', 'group' => 'games'],
            ['name' => 'Quản lý Danh mục & Tags', 'slug' => 'catalog.manage', 'group' => 'catalog'],
            ['name' => 'Quản lý Người dùng', 'slug' => 'users.manage', 'group' => 'users'],
            ['name' => 'Kiểm duyệt Đánh giá & Báo cáo', 'slug' => 'moderation.manage', 'group' => 'moderation'],
            ['name' => 'Quản lý CMS & Cài đặt', 'slug' => 'cms.manage', 'group' => 'cms'],
        ];

        $permissionIds = [];
        foreach ($permissionsData as $perm) {
            $p = Permission::updateOrCreate(['slug' => $perm['slug']], $perm);
            $permissionIds[] = $p->id;
        }
        $superAdminRole->permissions()->sync($permissionIds);
        $adminRole->permissions()->sync($permissionIds);

        // 2. Seed Accounts (Strict .env configuration or random cryptographic password fallback)
        $superAdminPassword = (string) config('gamehub.seed_accounts.super_admin.password', '');
        $generatedSuperAdmin = false;
        if ($superAdminPassword === '') {
            $superAdminPassword = Str::random(24);
            $generatedSuperAdmin = true;
        }

        $superAdmin = User::updateOrCreate(
            ['email' => config('gamehub.seed_accounts.super_admin.email', 'admin@gamehub.local')],
            [
                'name' => 'Tổng Quản Trị GameHub',
                'password' => Hash::make($superAdminPassword),
                'bio' => 'Tài khoản Super Admin quản lý toàn diện hệ thống cổng game HTML5 GameHub.',
                'is_active' => true,
                'total_points' => 1000,
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->roles()->sync([$superAdminRole->id]);

        $adminPassword = (string) config('gamehub.seed_accounts.admin.password', '');
        $generatedAdmin = false;
        if ($adminPassword === '') {
            $adminPassword = Str::random(24);
            $generatedAdmin = true;
        }

        $adminUser = User::updateOrCreate(
            ['email' => config('gamehub.seed_accounts.admin.email', 'moderator@gamehub.local')],
            [
                'name' => 'Biên Tập Viên GameHub',
                'password' => Hash::make($adminPassword),
                'bio' => 'Quản trị viên phụ trách kiểm duyệt và cập nhật kho game HTML5.',
                'is_active' => true,
                'total_points' => 500,
                'email_verified_at' => now(),
            ]
        );
        $adminUser->roles()->sync([$adminRole->id]);

        $playerPassword = (string) config('gamehub.seed_accounts.user.password', '');
        $generatedPlayer = false;
        if ($playerPassword === '') {
            $playerPassword = Str::random(24);
            $generatedPlayer = true;
        }

        $playerUser = User::updateOrCreate(
            ['email' => config('gamehub.seed_accounts.user.email', 'player@gamehub.local')],
            [
                'name' => 'Minh Hoàng Gamer',
                'password' => Hash::make($playerPassword),
                'bio' => 'Đam mê chinh phục điểm số cao trong các tựa game Arcade & Giải đố HTML5.',
                'is_active' => true,
                'total_points' => 150,
                'email_verified_at' => now(),
            ]
        );
        $playerUser->roles()->sync([$userRole->id]);

        if ($this->command && ($generatedSuperAdmin || $generatedAdmin || $generatedPlayer)) {
            $this->command->warn('Một số mật khẩu SEED_* chưa được khai báo trong .env. Hệ thống đã sinh mật khẩu ngẫu nhiên an toàn:');
            if ($generatedSuperAdmin) {
                $this->command->line("  - Super Admin ({$superAdmin->email}): {$superAdminPassword}");
            }
            if ($generatedAdmin) {
                $this->command->line("  - Admin ({$adminUser->email}): {$adminPassword}");
            }
            if ($generatedPlayer) {
                $this->command->line("  - Player ({$playerUser->email}): {$playerPassword}");
            }
        }

        // 3. Categories & Tags
        $categoriesMap = [
            'hanh-dong' => ['name' => 'Hành Động', 'icon' => 'Zap', 'position' => 1, 'description' => 'Các tựa game hành động nhịp độ nhanh đòi hỏi kỹ năng phản xạ nhạy bén.'],
            'khong-gian-ban-sung' => ['name' => 'Không Gian & Bắn Súng', 'icon' => 'Rocket', 'position' => 2, 'description' => 'Điều khiển chiến cơ ngân hà tiêu diệt các đợt tấn công ngoài hành tinh.'],
            'giai-do-tri-tue' => ['name' => 'Giải Đố Trí Tuệ', 'icon' => 'Puzzle', 'position' => 3, 'description' => 'Rèn luyện tư duy logic với các trò chơi xếp hình và giải đố thông minh.'],
            'arcade-co-dien' => ['name' => 'Arcade Cổ Điển', 'icon' => 'Gamepad2', 'position' => 4, 'description' => 'Những trò chơi điện tử thùng kinh điển gắn liền với tuổi thơ.'],
            'dua-toc-do' => ['name' => 'Đua Tốc Độ', 'icon' => 'Flame', 'position' => 5, 'description' => 'Chinh phục những đường đua kịch tính và vượt qua mọi giới hạn tốc độ.'],
            'ky-nang-phan-xa' => ['name' => 'Kỹ Năng Phản Xạ', 'icon' => 'Target', 'position' => 6, 'description' => 'Thử thách sự khéo léo và khả năng căn thời gian chuẩn xác.'],
        ];

        $categories = [];
        foreach ($categoriesMap as $slug => $data) {
            $categories[$slug] = Category::updateOrCreate(['slug' => $slug], array_merge($data, ['is_active' => true]));
        }

        $tagsData = [
            'html5' => 'HTML5 Canvas',
            '2d' => 'Đồ họa 2D',
            'neon' => 'Phong cách Neon',
            'endless-runner' => 'Chạy vô tận',
            'space' => 'Vũ trụ',
            'puzzle' => 'Xếp khối',
            'mobile-touch' => 'Hỗ trợ Cảm ứng',
            '60fps' => 'Mượt 60FPS',
        ];

        $tags = [];
        foreach ($tagsData as $slug => $name) {
            $tags[$slug] = Tag::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'description' => "Trò chơi thuộc chủ đề {$name}", 'is_active' => true]
            );
        }

        // 4. Homepage Sections, Settings, Announcements, Achievements
        $sectionsData = [
            ['slug' => 'featured', 'title' => 'Game Nổi Bật Đề Cử', 'subtitle' => 'Những siêu phẩm HTML5 được biên tập viên chọn lọc', 'position' => 1, 'item_limit' => 8],
            ['slug' => 'popular', 'title' => 'Game Phổ Biến Nhất', 'subtitle' => 'Cộng đồng game thủ đang chơi nhiều nhất tuần qua', 'position' => 2, 'item_limit' => 8],
            ['slug' => 'newest', 'title' => 'Game Mới Cập Nhật', 'subtitle' => 'Trải nghiệm những phiên bản trò chơi vừa ra mắt', 'position' => 3, 'item_limit' => 8],
        ];
        foreach ($sectionsData as $sec) {
            HomepageSection::updateOrCreate(['slug' => $sec['slug']], array_merge($sec, ['is_active' => true]));
        }

        Setting::setValue('site_name', 'GameHub', 'general', 'Tên cổng game');
        Setting::setValue('site_tagline', 'Cổng Game HTML5 Trực Tuyến Miễn Phí', 'general', 'Khẩu hiệu');
        Setting::setValue('site_description', 'Chơi hàng trăm tựa game HTML5 2D miễn phí tốc độ cao ngay trên trình duyệt PC và Mobile tại GameHub.', 'general', 'Mô tả SEO');
        Setting::setValue('contact_email', 'support@gamehub.local', 'general', 'Email hỗ trợ');
        Setting::setValue('footer_copyright', '© 2026 GameHub Portal. Bảo lưu mọi quyền.', 'general', 'Bản quyền chân trang');

        Announcement::updateOrCreate(
            ['title' => 'Chào mừng đến với GameHub HTML5!'],
            [
                'content' => 'Hệ thống đã cập nhật 3 siêu phẩm HTML5 mới hỗ trợ cả PC & Cảm ứng Mobile. Chơi và đua Top ngay!',
                'type' => 'info',
                'is_active' => true,
            ]
        );

        $achievementsData = [
            ['slug' => 'first-play', 'title' => 'Tân Binh Khởi Hành', 'description' => 'Hoàn thành phiên chơi game đầu tiên trên GameHub', 'points' => 20, 'condition_type' => 'games_played', 'condition_value' => 1],
            ['slug' => 'explorer-3', 'title' => 'Nhà Thám Hiểm GameHub', 'description' => 'Trải nghiệm đủ 3 tựa game khác nhau trên hệ thống', 'points' => 50, 'condition_type' => 'games_played', 'condition_value' => 3],
            ['slug' => 'score-master', 'title' => 'Cao Thủ Điểm Số', 'description' => 'Đạt từ 200 điểm trở lên trong một ván chơi bất kỳ', 'points' => 100, 'condition_type' => 'high_score', 'condition_value' => 200],
        ];
        foreach ($achievementsData as $ach) {
            Achievement::updateOrCreate(['slug' => $ach['slug']], $ach);
        }

        // 5. Package & Import 3 Sample HTML5 Games via GameUploadService
        /** @var GameUploadService $uploadService */
        $uploadService = app(GameUploadService::class);

        $sampleGames = [
            [
                'slug' => 'pixel-runner',
                'title' => 'Pixel Runner Siêu Tốc',
                'short_description' => 'Chạy nước rút qua thành phố Neon, nhảy đôi vượt chướng ngại vật và thu thập tinh thể ánh sáng!',
                'category_id' => $categories['hanh-dong']->id,
                'tag_ids' => [$tags['html5']->id, $tags['neon']->id, $tags['endless-runner']->id, $tags['mobile-touch']->id],
                'play_count' => 1420,
                'is_featured' => true,
                'is_hot' => true,
                'is_new' => true,
            ],
            [
                'slug' => 'space-shooter',
                'title' => 'Chiến Cơ Ngân Hà',
                'short_description' => 'Điều khiển phi thuyền chiến đấu xuyên qua vành đai thiên thạch và tiêu diệt hạm đội xâm lược!',
                'category_id' => $categories['khong-gian-ban-sung']->id,
                'tag_ids' => [$tags['html5']->id, $tags['space']->id, $tags['60fps']->id, $tags['mobile-touch']->id],
                'play_count' => 1185,
                'is_featured' => true,
                'is_hot' => true,
                'is_new' => false,
            ],
            [
                'slug' => 'puzzle-blocks',
                'title' => 'Xếp Khối Tinh Thể',
                'short_description' => 'Ghép các khối tinh thể đa sắc màu vào bảng lưới 8x8 để tạo chuỗi Combo điểm số kỷ lục!',
                'category_id' => $categories['giai-do-tri-tue']->id,
                'tag_ids' => [$tags['html5']->id, $tags['puzzle']->id, $tags['2d']->id, $tags['mobile-touch']->id],
                'play_count' => 960,
                'is_featured' => true,
                'is_hot' => false,
                'is_new' => true,
            ],
        ];

        $audioSynthesizer = app(AudioSynthesizerService::class);
        $audioSynthesizer->provisionAllDefaultAudio($superAdmin->id);

        foreach ($sampleGames as $sg) {
            $sourceDir = base_path('games/sample-games/' . $sg['slug']);
            $zipPath = base_path('games/sample-games/' . $sg['slug'] . '.zip');

            $this->buildSampleZip($sourceDir, $zipPath);

            $game = Game::withTrashed()->updateOrCreate(
                ['slug' => $sg['slug']],
                [
                    'title' => $sg['title'],
                    'short_description' => $sg['short_description'],
                    'category_id' => $sg['category_id'],
                    'status' => 'published',
                    'play_count' => $sg['play_count'],
                    'is_featured' => $sg['is_featured'],
                    'is_hot' => $sg['is_hot'],
                    'is_new' => $sg['is_new'],
                    'uploaded_by' => $superAdmin->id,
                    'published_at' => now(),
                ]
            );

            if ($game->trashed()) {
                $game->restore();
            }

            $game->tags()->sync($sg['tag_ids']);

            $uploadService->processUpload(
                game: $game,
                zipFilePath: $zipPath,
                versionNumber: '1.0.0',
                changelog: 'Phiên bản chính thức 1.0.0 phát hành trên GameHub.',
                autoPublish: true,
                uploadedBy: $superAdmin->id
            );

            // Seed initial review & high score
            GameReview::updateOrCreate(
                ['user_id' => $playerUser->id, 'game_id' => $game->id],
                [
                    'rating' => 5,
                    'comment' => "Trò chơi {$game->title} cực kỳ mượt mà, điều khiển nhạy trên cả máy tính và điện thoại!",
                    'status' => 'approved',
                ]
            );
            $game->recalculateRating();

            GameScore::updateOrCreate(
                ['user_id' => $playerUser->id, 'game_id' => $game->id],
                ['score' => rand(350, 950), 'recorded_at' => now()]
            );
        }

        // Gắn kết toàn bộ Audio Assets với các Game đã tạo
        $audioSynthesizer->provisionAllDefaultAudio($superAdmin->id);

        $firstGame = Game::where('slug', 'pixel-runner')->first();
        if ($firstGame) {
            Banner::updateOrCreate(
                ['title' => 'Pixel Runner Siêu Tốc - Chinh Phục Thành Phố Neon'],
                [
                    'subtitle' => 'Thử thách phản xạ nhảy đôi cực đỉnh ở tốc độ 60FPS ngay trên trình duyệt của bạn!',
                    'badge_text' => 'SIÊU PHẨM ĐỀ CỬ',
                    'button_text' => 'Chơi Ngay Lập Tức',
                    'game_id' => $firstGame->id,
                    'position' => 1,
                    'is_active' => true,
                ]
            );
        }
    }

    protected function buildSampleZip(string $sourceDir, string $zipFilePath): void
    {
        if (!File::isDirectory($sourceDir)) {
            return;
        }

        if (File::exists($zipFilePath)) {
            File::delete($zipFilePath);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return;
        }

        foreach (File::allFiles($sourceDir) as $file) {
            $relativeName = str_replace('\\', '/', $file->getRelativePathname());
            $zip->addFile($file->getRealPath(), $relativeName);
        }

        $zip->close();
    }
}
