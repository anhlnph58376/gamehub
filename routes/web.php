<?php

use App\Http\Controllers\Admin\AdminAudioController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminCmsController;
use App\Http\Controllers\Admin\AdminGameController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Admin\AdminTagController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Game\AudioSettingController;
use App\Http\Controllers\Game\GameController;
use App\Http\Controllers\Game\HomeController;
use App\Http\Controllers\Game\InteractionController;
use App\Http\Controllers\Game\PageController;
use App\Http\Controllers\Game\SeoController;
use App\Http\Controllers\User\ProfileController;
use Illuminate\Support\Facades\Route;

// Public & SEO Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/api/instant-search', [GameController::class, 'instantSearch'])
    ->middleware('throttle:60,1')
    ->name('games.instant-search');
Route::get('/api/audio-settings', [AudioSettingController::class, 'show'])
    ->middleware('throttle:120,1')
    ->name('audio.settings.show');
Route::post('/api/audio-settings', [AudioSettingController::class, 'update'])
    ->middleware('throttle:60,1')
    ->name('audio.settings.update');
Route::get('/api/games/{slug}/audio-manifest', [AudioSettingController::class, 'gameManifest'])
    ->middleware('throttle:120,1')
    ->name('games.audio-manifest');
Route::get('/ngau-nhien', [GameController::class, 'random'])->name('games.random');
Route::get('/kham-pha', [GameController::class, 'explore'])->name('games.explore');
Route::get('/games', [GameController::class, 'explore']);
Route::get('/danh-muc/{slug}', [GameController::class, 'category'])->name('games.category');
Route::get('/game/{slug}', [GameController::class, 'show'])->name('games.show');
Route::get('/games/{slug}', [GameController::class, 'show']);
Route::get('/choi-game/{slug}', [GameController::class, 'play'])->name('games.play');
Route::get('/games/{slug}/play', [GameController::class, 'play']);

// Static Pages
Route::get('/gioi-thieu', [PageController::class, 'about'])->name('pages.about');
Route::get('/lien-he', [PageController::class, 'contact'])->name('pages.contact');
Route::post('/lien-he', [PageController::class, 'submitContact'])
    ->middleware('throttle:10,1')
    ->name('pages.contact.submit');
Route::get('/hoi-dap-faq', [PageController::class, 'faq'])->name('pages.faq');
Route::get('/chinh-sach-bao-mat', [PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/dieu-khoan-su-dung', [PageController::class, 'terms'])->name('pages.terms');

// SEO
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

// Public Game Session & Report Endpoints
Route::post('/games/{game}/session', [InteractionController::class, 'recordSession'])
    ->middleware('throttle:60,1')
    ->name('games.session.record');
Route::post('/games/{game}/report', [InteractionController::class, 'storeReport'])
    ->middleware('throttle:10,1')
    ->name('games.report.store');

// Guest Auth Routes (Protected with Brute-Force Rate Limiting)
Route::middleware('guest')->group(function () {
    Route::get('/dang-nhap', [AuthController::class, 'showLogin'])->name('login');
    Route::redirect('/login', '/dang-nhap');
    Route::post('/dang-nhap', [AuthController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('login.submit');

    // Đăng nhập bằng mã xác thực gửi về Email (Email OTP Login)
    Route::post('/dang-nhap/gui-ma-email', [AuthController::class, 'sendEmailLoginCode'])
        ->middleware('throttle:5,1')
        ->name('login.email.send');
    Route::get('/dang-nhap/xac-thuc-email', [AuthController::class, 'showVerifyEmailLogin'])
        ->name('login.email.verify.form');
    Route::post('/dang-nhap/xac-thuc-email', [AuthController::class, 'verifyEmailLogin'])
        ->middleware('throttle:6,1')
        ->name('login.email.verify.submit');

    Route::get('/dang-ky', [AuthController::class, 'showRegister'])->name('register');
    Route::redirect('/register', '/dang-ky');
    Route::post('/dang-ky', [AuthController::class, 'register'])
        ->middleware('throttle:5,1')
        ->name('register.submit');

    // Quên mật khẩu & Đặt lại mật khẩu bằng mã xác thực 6 chữ số gửi về Email
    Route::get('/quen-mat-khau', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/quen-mat-khau', [AuthController::class, 'sendResetLink'])
        ->middleware('throttle:5,1')
        ->name('password.email');
    Route::get('/dat-lai-mat-khau', [AuthController::class, 'showResetPassword'])
        ->name('password.reset.form');
    Route::post('/dat-lai-mat-khau', [AuthController::class, 'resetPassword'])
        ->middleware('throttle:6,1')
        ->name('password.reset.submit');

    // Đăng nhập Mạng xã hội OAuth 2.0: Google (Gmail) & Facebook
    Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])
        ->whereIn('provider', ['google', 'facebook'])
        ->middleware('throttle:15,1')
        ->name('auth.social.redirect');
    Route::match(['get', 'post'], '/auth/{provider}/callback', [SocialAuthController::class, 'callback'])
        ->whereIn('provider', ['google', 'facebook'])
        ->middleware('throttle:15,1')
        ->name('auth.social.callback');
});

// Authenticated User Routes
Route::middleware('auth')->group(function () {
    Route::post('/dang-xuat', [AuthController::class, 'logout'])->name('logout');

    Route::get('/tai-khoan', [ProfileController::class, 'index'])->name('user.profile');
    Route::put('/tai-khoan', [ProfileController::class, 'update'])
        ->middleware('throttle:15,1')
        ->name('user.profile.update');
    Route::get('/tai-khoan/yeu-thich', [ProfileController::class, 'favorites'])->name('user.favorites');
    Route::get('/tai-khoan/lich-su', [ProfileController::class, 'history'])->name('user.history');

    Route::post('/games/{game}/favorite', [ProfileController::class, 'toggleFavorite'])
        ->middleware('throttle:30,1')
        ->name('games.favorite.toggle');
    Route::post('/games/{game}/reviews', [InteractionController::class, 'storeReview'])
        ->middleware('throttle:15,1')
        ->name('games.reviews.store');
    Route::delete('/reviews/{review}', [InteractionController::class, 'deleteReview'])
        ->middleware('throttle:20,1')
        ->name('games.reviews.destroy');
});

// Admin Portal Routes (/admin)
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:super-admin|admin'])
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Game & Upload Center Management
        Route::get('/games', [AdminGameController::class, 'index'])->name('games.index');
        Route::get('/games/create', [AdminGameController::class, 'create'])->name('games.create');
        Route::post('/games', [AdminGameController::class, 'store'])->name('games.store');
        Route::get('/games/{game}/edit', [AdminGameController::class, 'edit'])->name('games.edit');
        Route::put('/games/{game}', [AdminGameController::class, 'update'])->name('games.update');
        Route::delete('/games/{game}', [AdminGameController::class, 'destroy'])->name('games.destroy');
        Route::post('/games/{id}/restore', [AdminGameController::class, 'restore'])->name('games.restore');
        Route::post('/games/{game}/toggle-featured', [AdminGameController::class, 'toggleFeatured'])->name('games.toggle-featured');

        // Game Version & ZIP Upload Pipeline
        Route::post('/games/{game}/versions', [AdminGameController::class, 'uploadVersion'])->name('games.versions.upload');
        Route::post('/games/{game}/versions/{version}/publish', [AdminGameController::class, 'publishVersion'])->name('games.versions.publish');
        Route::post('/games/{game}/versions/{version}/rollback', [AdminGameController::class, 'rollbackVersion'])->name('games.versions.rollback');

        // Audio System Management (/admin/audio)
        Route::get('/audio', [AdminAudioController::class, 'index'])->name('audio.index');
        Route::post('/audio', [AdminAudioController::class, 'store'])->name('audio.store');
        Route::put('/audio/{audioAsset}', [AdminAudioController::class, 'update'])->name('audio.update');
        Route::post('/audio/{audioAsset}/toggle-active', [AdminAudioController::class, 'toggleActive'])->name('audio.toggle-active');
        Route::post('/audio/{audioAsset}/assign-game', [AdminAudioController::class, 'assignToGame'])->name('audio.assign-game');
        Route::delete('/audio/assignments/{assignment}', [AdminAudioController::class, 'removeGameAssignment'])->name('audio.assignments.destroy');
        Route::delete('/audio/{audioAsset}', [AdminAudioController::class, 'destroy'])->name('audio.destroy');
        Route::post('/audio/synthesize-defaults', [AdminAudioController::class, 'synthesizeDefaults'])->name('audio.synthesize-defaults');

        // Categories & Tags
        Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/tags', [AdminTagController::class, 'index'])->name('tags.index');
        Route::post('/tags', [AdminTagController::class, 'store'])->name('tags.store');
        Route::delete('/tags/{tag}', [AdminTagController::class, 'destroy'])->name('tags.destroy');

        // Users & RBAC
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users/{user}/toggle-lock', [AdminUserController::class, 'toggleLock'])->name('users.toggle-lock');
        Route::put('/users/{user}/roles', [AdminUserController::class, 'updateRoles'])->name('users.roles.update');

        // Reviews & Reports
        Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::put('/reviews/{review}/status', [AdminReviewController::class, 'updateStatus'])->name('reviews.status');
        Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

        Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
        Route::put('/reports/{report}/resolve', [AdminReportController::class, 'resolve'])->name('reports.resolve');

        // CMS, Settings & Audit Logs
        Route::get('/cms', [AdminCmsController::class, 'index'])->name('cms.index');
        Route::post('/cms/banners', [AdminCmsController::class, 'storeBanner'])->name('cms.banners.store');
        Route::delete('/cms/banners/{banner}', [AdminCmsController::class, 'deleteBanner'])->name('cms.banners.destroy');
        Route::put('/cms/sections/{section}', [AdminCmsController::class, 'updateSection'])->name('cms.sections.update');
        Route::post('/cms/announcements', [AdminCmsController::class, 'storeAnnouncement'])->name('cms.announcements.store');
        Route::delete('/cms/announcements/{announcement}', [AdminCmsController::class, 'deleteAnnouncement'])->name('cms.announcements.destroy');

        Route::get('/settings', [AdminCmsController::class, 'settings'])->name('settings.index');
        Route::put('/settings', [AdminCmsController::class, 'updateSettings'])->name('settings.update');

        Route::get('/audit-logs', [AdminCmsController::class, 'auditLogs'])->name('audit-logs.index');
    });
