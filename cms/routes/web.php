<?php

use App\Domain\Access\Permission;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\PageBuilderController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PluginController;
use App\Http\Controllers\Admin\PluginSettingsController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Admin\ThemeCustomizerController;
use App\Http\Controllers\Admin\ThemeSettingsController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\SeoPublicController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [SeoPublicController::class, 'sitemap'])->name('seo.sitemap');
Route::get('/robots.txt', [SeoPublicController::class, 'robots'])->name('seo.robots');

Route::get('/', [FrontendController::class, 'home'])->name('frontend.home');

Route::get('/blog', [FrontendController::class, 'blog'])->name('frontend.blog');
Route::get('/blog/{slug}', [FrontendController::class, 'post'])->name('frontend.blog.show');

Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    });

    Route::middleware(['auth', 'account.active'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('can:'.Permission::ViewDashboard->value)->name('dashboard');

        Route::post('/system/clear-cache', [SystemController::class, 'clearCache'])->middleware('can:'.Permission::ManageSystem->value)->name('system.clear-cache');

        Route::get('/media', [MediaController::class, 'index'])->middleware('can:'.Permission::ManageMedia->value)->name('media');
        Route::get('/media/library', [MediaController::class, 'library'])->middleware('can:'.Permission::ManageMedia->value)->name('media.library');
        Route::get('/media/upload', [MediaController::class, 'upload'])->middleware('can:'.Permission::ManageMedia->value)->name('media.upload');
        Route::post('/media', [MediaController::class, 'store'])->middleware('can:'.Permission::ManageMedia->value)->name('media.store');
        Route::delete('/media/{media}', [MediaController::class, 'destroy'])->middleware('can:'.Permission::ManageMedia->value)->name('media.destroy');

        Route::get('/posts', [PostController::class, 'index'])->middleware('can:'.Permission::ManagePosts->value)->name('posts');
        Route::get('/posts/create', [PostController::class, 'create'])->middleware('can:'.Permission::ManagePosts->value)->name('posts.create');
        Route::post('/posts', [PostController::class, 'store'])->middleware('can:'.Permission::ManagePosts->value)->name('posts.store');
        Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->middleware('can:'.Permission::ManagePosts->value)->name('posts.edit');
        Route::put('/posts/{post}', [PostController::class, 'update'])->middleware('can:'.Permission::ManagePosts->value)->name('posts.update');
        Route::delete('/posts/{post}', [PostController::class, 'destroy'])->middleware('can:'.Permission::ManagePosts->value)->name('posts.destroy');

        Route::get('/categories', [CategoryController::class, 'index'])->middleware('can:'.Permission::ManageTaxonomy->value)->name('categories');
        Route::post('/categories', [CategoryController::class, 'store'])->middleware('can:'.Permission::ManageTaxonomy->value)->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->middleware('can:'.Permission::ManageTaxonomy->value)->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->middleware('can:'.Permission::ManageTaxonomy->value)->name('categories.destroy');

        Route::get('/tags', [TagController::class, 'index'])->middleware('can:'.Permission::ManageTaxonomy->value)->name('tags');
        Route::post('/tags', [TagController::class, 'store'])->middleware('can:'.Permission::ManageTaxonomy->value)->name('tags.store');
        Route::put('/tags/{tag}', [TagController::class, 'update'])->middleware('can:'.Permission::ManageTaxonomy->value)->name('tags.update');
        Route::delete('/tags/{tag}', [TagController::class, 'destroy'])->middleware('can:'.Permission::ManageTaxonomy->value)->name('tags.destroy');

        Route::get('/pages', [PageController::class, 'index'])->middleware('can:'.Permission::ManagePages->value)->name('pages');
        Route::get('/pages/create', [PageController::class, 'create'])->middleware('can:'.Permission::ManagePages->value)->name('pages.create');
        Route::post('/pages', [PageController::class, 'store'])->middleware('can:'.Permission::ManagePages->value)->name('pages.store');
        Route::get('/pages/{page}/edit', [PageController::class, 'edit'])->middleware('can:'.Permission::ManagePages->value)->name('pages.edit');
        Route::put('/pages/{page}', [PageController::class, 'update'])->middleware('can:'.Permission::ManagePages->value)->name('pages.update');
        Route::delete('/pages/{page}', [PageController::class, 'destroy'])->middleware('can:'.Permission::ManagePages->value)->name('pages.destroy');
        Route::get('/pages/{page}/builder', [PageBuilderController::class, 'edit'])->middleware('can:'.Permission::ManagePages->value)->name('pages.builder');
        Route::post('/pages/{page}/builder', [PageBuilderController::class, 'update'])->middleware('can:'.Permission::ManagePages->value)->name('pages.builder.update');

        Route::get('/menus', [MenuController::class, 'index'])->middleware('can:'.Permission::ManageMenus->value)->name('menus');
        Route::get('/menus/create', [MenuController::class, 'create'])->middleware('can:'.Permission::ManageMenus->value)->name('menus.create');
        Route::post('/menus', [MenuController::class, 'store'])->middleware('can:'.Permission::ManageMenus->value)->name('menus.store');
        Route::get('/menus/{menu}/edit', [MenuController::class, 'edit'])->middleware('can:'.Permission::ManageMenus->value)->name('menus.edit');
        Route::put('/menus/{menu}', [MenuController::class, 'update'])->middleware('can:'.Permission::ManageMenus->value)->name('menus.update');
        Route::delete('/menus/{menu}', [MenuController::class, 'destroy'])->middleware('can:'.Permission::ManageMenus->value)->name('menus.destroy');
        Route::post('/menus/{menu}/items', [MenuController::class, 'storeItem'])->middleware('can:'.Permission::ManageMenus->value)->name('menus.items.store');
        Route::delete('/menu-items/{item}', [MenuController::class, 'destroyItem'])->middleware('can:'.Permission::ManageMenus->value)->name('menus.items.destroy');

        Route::get('/plugins', [PluginController::class, 'index'])->middleware('can:'.Permission::ManagePlugins->value)->name('plugins');
        Route::post('/plugins/{plugin}/toggle', [PluginController::class, 'toggle'])->middleware('can:'.Permission::ManagePlugins->value)->name('plugins.toggle');
        Route::get('/plugins/{plugin}/settings', [PluginSettingsController::class, 'index'])->middleware('can:'.Permission::ManagePlugins->value)->name('plugins.settings');
        Route::post('/plugins/{plugin}/settings', [PluginSettingsController::class, 'update'])->middleware('can:'.Permission::ManagePlugins->value)->name('plugins.settings.update');

        Route::get('/themes', [ThemeController::class, 'index'])->middleware('can:'.Permission::ManageThemes->value)->name('themes');
        Route::post('/themes/{theme}/activate', [ThemeController::class, 'activate'])->middleware('can:'.Permission::ManageThemes->value)->name('themes.activate');
        Route::get('/themes/{theme}/settings', [ThemeSettingsController::class, 'index'])->middleware('can:'.Permission::ManageThemes->value)->name('themes.settings');
        Route::post('/themes/{theme}/settings', [ThemeSettingsController::class, 'update'])->middleware('can:'.Permission::ManageThemes->value)->name('themes.settings.update');
        Route::get('/themes/{theme}/customizer', [ThemeCustomizerController::class, 'index'])->middleware('can:'.Permission::ManageThemes->value)->name('themes.customizer');
        Route::post('/themes/{theme}/customizer', [ThemeCustomizerController::class, 'update'])->middleware('can:'.Permission::ManageThemes->value)->name('themes.customizer.update');

        Route::get('/seo', [SeoController::class, 'index'])->middleware('can:'.Permission::ManageSeo->value)->name('seo');
        Route::post('/seo', [SeoController::class, 'update'])->middleware('can:'.Permission::ManageSeo->value)->name('seo.update');

        Route::get('/settings', [SettingsController::class, 'index'])->middleware('can:'.Permission::ManageSettings->value)->name('settings');
        Route::post('/settings', [SettingsController::class, 'update'])->middleware('can:'.Permission::ManageSettings->value)->name('settings.update');

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});

Route::get('/{slug}', [FrontendController::class, 'page'])->name('frontend.page');
