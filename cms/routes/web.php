<?php

use App\Domain\Access\Permission;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BuilderTemplateController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CommerceCategoryController;
use App\Http\Controllers\Admin\ContentPreviewController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FormController;
use App\Http\Controllers\Admin\FormSubmissionController;
use App\Http\Controllers\Admin\IdentityController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\MfaController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PageBuilderController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PluginController;
use App\Http\Controllers\Admin\PluginSettingsController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ProductController as CommerceProductController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Admin\ThemeCustomizerController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogueController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\PublicFormController;
use App\Http\Controllers\SeoPublicController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [SeoPublicController::class, 'sitemap'])->name('seo.sitemap');
Route::get('/robots.txt', [SeoPublicController::class, 'robots'])->name('seo.robots');

Route::get('/', [FrontendController::class, 'home'])->name('frontend.home');
Route::get('/catalogue', [CatalogueController::class, 'index'])->name('catalogue.index');
Route::get('/catalogue/category/{category:slug}', [CatalogueController::class, 'category'])->name('catalogue.category');
Route::get('/catalogue/products/{slug}', [CatalogueController::class, 'show'])->name('catalogue.show');
Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
Route::post('/cart/items', [CartController::class, 'add'])->name('cart.items.add');
Route::put('/cart/items/{item}', [CartController::class, 'update'])->name('cart.items.update');
Route::delete('/cart/items/{item}', [CartController::class, 'destroy'])->name('cart.items.destroy');
Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1')->name('checkout.store');
Route::get('/orders/{reference}', [CheckoutController::class, 'order'])->name('orders.show');

Route::get('/forms/{form:slug}', [PublicFormController::class, 'show'])->name('forms.show');
Route::post('/forms/{form:slug}', [PublicFormController::class, 'store'])->middleware('throttle:10,1')->name('forms.store');

Route::get('/blog', [FrontendController::class, 'blog'])->name('frontend.blog');
Route::get('/blog/category/{slug}', [FrontendController::class, 'category'])->name('frontend.blog.category');
Route::get('/blog/tag/{slug}', [FrontendController::class, 'tag'])->name('frontend.blog.tag');
Route::get('/blog/{slug}', [FrontendController::class, 'post'])->name('frontend.blog.show');

Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.post');
        Route::get('/forgot-password', [IdentityController::class, 'forgotPassword'])->name('password.request');
        Route::post('/forgot-password', [IdentityController::class, 'sendResetLink'])->middleware('throttle:5,1')->name('password.email');
        Route::get('/reset-password/{token}', [IdentityController::class, 'resetPassword'])->name('password.reset');
        Route::post('/reset-password', [IdentityController::class, 'updateResetPassword'])->name('password.update');
    });

    Route::middleware(['auth', 'account.active'])->group(function () {
        Route::get('/verify-email', [IdentityController::class, 'verificationNotice'])->name('verification.notice');
        Route::get('/verify-email/{id}/{hash}', [IdentityController::class, 'verify'])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
        Route::post('/email/verification-notification', [IdentityController::class, 'resendVerification'])->middleware('throttle:6,1')->name('verification.send');
        Route::get('/confirm-password', [IdentityController::class, 'confirmPassword'])->name('password.confirm');
        Route::post('/confirm-password', [IdentityController::class, 'storePasswordConfirmation'])->name('password.confirm.store');
        Route::get('/profile', [IdentityController::class, 'editProfile'])->name('profile.edit');
        Route::patch('/profile', [IdentityController::class, 'updateProfile'])->name('profile.update');
        Route::put('/profile/password', [IdentityController::class, 'updatePassword'])->middleware('password.confirm:admin.password.confirm')->name('profile.password');
        Route::delete('/profile/sessions/{session}', [IdentityController::class, 'revokeSession'])->middleware('password.confirm:admin.password.confirm')->name('profile.sessions.destroy');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('/mfa', [MfaController::class, 'show'])->name('mfa.show');
        Route::post('/mfa/enroll', [MfaController::class, 'enroll'])->middleware('password.confirm:admin.password.confirm')->name('mfa.enroll');
        Route::post('/mfa/confirm', [MfaController::class, 'confirm'])->middleware('password.confirm:admin.password.confirm')->name('mfa.confirm');
        Route::get('/mfa/challenge', [MfaController::class, 'challenge'])->name('mfa.challenge');
        Route::post('/mfa/challenge', [MfaController::class, 'verify'])->middleware('throttle:10,1')->name('mfa.verify');
        Route::post('/mfa/recovery-codes', [MfaController::class, 'regenerate'])->middleware('password.confirm:admin.password.confirm')->name('mfa.recovery.regenerate');
        Route::delete('/mfa', [MfaController::class, 'disable'])->middleware('password.confirm:admin.password.confirm')->name('mfa.disable');
        Route::delete('/users/{user}/mfa', [MfaController::class, 'administrativeReset'])->middleware(['verified:admin.verification.notice', 'privileged.mfa', 'can:'.Permission::ManageUsers->value, 'password.confirm:admin.password.confirm'])->name('users.mfa.reset');

        Route::middleware(['verified:admin.verification.notice', 'privileged.mfa'])->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('can:'.Permission::ViewDashboard->value)->name('dashboard');

            Route::resource('users', UserController::class)->except('show')->middleware('can:'.Permission::ManageUsers->value);
            Route::resource('roles', RoleController::class)->except('show')->middleware('can:'.Permission::ManageRoles->value);

            Route::post('/system/clear-cache', [SystemController::class, 'clearCache'])->middleware('can:'.Permission::ManageSystem->value)->name('system.clear-cache');

            Route::get('/media', [MediaController::class, 'index'])->middleware('can:'.Permission::ManageMedia->value)->name('media');
            Route::get('/media/library', [MediaController::class, 'library'])->middleware('can:'.Permission::ManageMedia->value)->name('media.library');
            Route::get('/media/upload', [MediaController::class, 'upload'])->middleware('can:'.Permission::ManageMedia->value)->name('media.upload');
            Route::post('/media', [MediaController::class, 'store'])->middleware('can:'.Permission::ManageMedia->value)->name('media.store');
            Route::patch('/media/{media}', [MediaController::class, 'update'])->middleware('can:'.Permission::ManageMedia->value)->name('media.update');
            Route::delete('/media/{media}', [MediaController::class, 'destroy'])->middleware('can:'.Permission::ManageMedia->value)->name('media.destroy');

            Route::get('/commerce/products', [CommerceProductController::class, 'index'])->middleware('can:'.Permission::ManageCommerceProducts->value)->name('commerce.products.index');
            Route::get('/commerce/products/create', [CommerceProductController::class, 'create'])->middleware('can:'.Permission::ManageCommerceProducts->value)->name('commerce.products.create');
            Route::post('/commerce/products', [CommerceProductController::class, 'store'])->middleware('can:'.Permission::ManageCommerceProducts->value)->name('commerce.products.store');
            Route::get('/commerce/products/{product}/edit', [CommerceProductController::class, 'edit'])->middleware('can:'.Permission::ManageCommerceProducts->value)->name('commerce.products.edit');
            Route::put('/commerce/products/{product}', [CommerceProductController::class, 'update'])->middleware('can:'.Permission::ManageCommerceProducts->value)->name('commerce.products.update');
            Route::post('/commerce/products/{product}/variants', [CommerceProductController::class, 'storeVariant'])->middleware('can:'.Permission::ManageCommerceProducts->value)->name('commerce.variants.store');
            Route::put('/commerce/products/{product}/variants/{variant}', [CommerceProductController::class, 'updateVariant'])->middleware('can:'.Permission::ManageCommerceProducts->value)->name('commerce.variants.update');
            Route::get('/commerce/categories', [CommerceCategoryController::class, 'index'])->middleware('can:'.Permission::ManageCommerceProducts->value)->name('commerce.categories.index');
            Route::post('/commerce/categories', [CommerceCategoryController::class, 'store'])->middleware('can:'.Permission::ManageCommerceProducts->value)->name('commerce.categories.store');
            Route::put('/commerce/categories/{category}', [CommerceCategoryController::class, 'update'])->middleware('can:'.Permission::ManageCommerceProducts->value)->name('commerce.categories.update');
            Route::delete('/commerce/categories/{category}', [CommerceCategoryController::class, 'destroy'])->middleware('can:'.Permission::ManageCommerceProducts->value)->name('commerce.categories.destroy');
            Route::get('/commerce/orders', [OrderController::class, 'index'])->middleware('can:'.Permission::ManageCommerceOrders->value)->name('commerce.orders.index');
            Route::get('/commerce/orders/{order}', [OrderController::class, 'show'])->middleware('can:'.Permission::ManageCommerceOrders->value)->name('commerce.orders.show');
            Route::post('/commerce/orders/{order}/cancel', [OrderController::class, 'cancel'])->middleware('can:'.Permission::ManageCommerceOrders->value)->name('commerce.orders.cancel');

            Route::get('/commerce/inventory', [InventoryController::class, 'index'])->middleware('can:'.Permission::ManageCommerceInventory->value)->name('commerce.inventory.index');
            Route::get('/commerce/inventory/{variant}', [InventoryController::class, 'show'])->middleware('can:'.Permission::ManageCommerceInventory->value)->name('commerce.inventory.show');
            Route::post('/commerce/inventory/{variant}/adjust', [InventoryController::class, 'adjust'])->middleware('can:'.Permission::ManageCommerceInventory->value)->name('commerce.inventory.adjust');

            Route::get('/posts', [PostController::class, 'index'])->middleware('can:'.Permission::ManagePosts->value)->name('posts');
            Route::get('/posts/create', [PostController::class, 'create'])->middleware('can:'.Permission::ManagePosts->value)->name('posts.create');
            Route::post('/posts', [PostController::class, 'store'])->middleware('can:'.Permission::ManagePosts->value)->name('posts.store');
            Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->middleware('can:'.Permission::ManagePosts->value)->name('posts.edit');
            Route::put('/posts/{post}', [PostController::class, 'update'])->middleware('can:'.Permission::ManagePosts->value)->name('posts.update');
            Route::delete('/posts/{post}', [PostController::class, 'destroy'])->middleware('can:'.Permission::ManagePosts->value)->name('posts.destroy');
            Route::get('/posts/{post}/preview', [ContentPreviewController::class, 'post'])->middleware(['can:'.Permission::ManagePosts->value, 'signed'])->name('posts.preview');

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
            Route::get('/pages/{page}/preview', [ContentPreviewController::class, 'page'])->middleware(['can:'.Permission::ManagePages->value, 'signed'])->name('pages.preview');
            Route::get('/pages/{page}/builder', [PageBuilderController::class, 'edit'])->middleware('can:'.Permission::ManagePages->value)->name('pages.builder');
            Route::post('/pages/{page}/builder', [PageBuilderController::class, 'update'])->middleware('can:'.Permission::ManagePages->value)->name('pages.builder.update');
            Route::post('/builder/templates', [BuilderTemplateController::class, 'store'])->middleware('can:'.Permission::ManagePages->value)->name('builder.templates.store');
            Route::delete('/builder/templates/{template}', [BuilderTemplateController::class, 'destroy'])->middleware('can:'.Permission::ManagePages->value)->name('builder.templates.destroy');

            Route::get('/menus', [MenuController::class, 'index'])->middleware('can:'.Permission::ManageMenus->value)->name('menus');
            Route::get('/menus/create', [MenuController::class, 'create'])->middleware('can:'.Permission::ManageMenus->value)->name('menus.create');
            Route::post('/menus', [MenuController::class, 'store'])->middleware('can:'.Permission::ManageMenus->value)->name('menus.store');
            Route::get('/menus/{menu}/edit', [MenuController::class, 'edit'])->middleware('can:'.Permission::ManageMenus->value)->name('menus.edit');
            Route::put('/menus/{menu}', [MenuController::class, 'update'])->middleware('can:'.Permission::ManageMenus->value)->name('menus.update');
            Route::delete('/menus/{menu}', [MenuController::class, 'destroy'])->middleware('can:'.Permission::ManageMenus->value)->name('menus.destroy');
            Route::post('/menus/{menu}/items', [MenuController::class, 'storeItem'])->middleware('can:'.Permission::ManageMenus->value)->name('menus.items.store');
            Route::put('/menus/{menu}/items/{item}', [MenuController::class, 'updateItem'])->middleware('can:'.Permission::ManageMenus->value)->name('menus.items.update');
            Route::put('/menus/{menu}/items', [MenuController::class, 'reorder'])->middleware('can:'.Permission::ManageMenus->value)->name('menus.items.reorder');
            Route::delete('/menus/{menu}/items/{item}', [MenuController::class, 'destroyItem'])->middleware('can:'.Permission::ManageMenus->value)->name('menus.items.destroy');

            Route::get('/plugins', [PluginController::class, 'index'])->middleware('can:'.Permission::ManagePlugins->value)->name('plugins');
            Route::post('/plugins/{plugin}/activate', [PluginController::class, 'activate'])->middleware('can:'.Permission::ManagePlugins->value)->name('plugins.activate');
            Route::post('/plugins/{plugin}/deactivate', [PluginController::class, 'deactivate'])->middleware('can:'.Permission::ManagePlugins->value)->name('plugins.deactivate');
            Route::get('/plugins/{plugin}/settings', [PluginSettingsController::class, 'index'])->middleware('can:'.Permission::ManagePlugins->value)->name('plugins.settings');
            Route::post('/plugins/{plugin}/settings', [PluginSettingsController::class, 'update'])->middleware('can:'.Permission::ManagePlugins->value)->name('plugins.settings.update');

            Route::get('/themes', [ThemeController::class, 'index'])->middleware('can:'.Permission::ManageThemes->value)->name('themes');
            Route::post('/themes/{theme}/activate', [ThemeController::class, 'activate'])->middleware('can:'.Permission::ManageThemes->value)->name('themes.activate');
            Route::post('/themes/history/{activation}/rollback', [ThemeController::class, 'rollback'])->middleware('can:'.Permission::ManageThemes->value)->name('themes.rollback');
            Route::get('/themes/{theme}/preview/{page}', [ThemeController::class, 'preview'])->middleware('can:'.Permission::ManageThemes->value)->name('themes.preview');
            Route::get('/themes/{theme}/customizer', [ThemeCustomizerController::class, 'index'])->middleware('can:'.Permission::ManageThemes->value)->name('themes.customizer');
            Route::post('/themes/{theme}/customizer', [ThemeCustomizerController::class, 'update'])->middleware('can:'.Permission::ManageThemes->value)->name('themes.customizer.update');

            Route::get('/seo', [SeoController::class, 'index'])->middleware('can:'.Permission::ManageSeo->value)->name('seo');
            Route::post('/seo', [SeoController::class, 'update'])->middleware('can:'.Permission::ManageSeo->value)->name('seo.update');

            Route::get('/forms', [FormController::class, 'index'])->middleware('can:'.Permission::ManageForms->value)->name('forms');
            Route::get('/forms/create', [FormController::class, 'create'])->middleware('can:'.Permission::ManageForms->value)->name('forms.create');
            Route::post('/forms', [FormController::class, 'store'])->middleware('can:'.Permission::ManageForms->value)->name('forms.store');
            Route::get('/forms/{form}/edit', [FormController::class, 'edit'])->middleware('can:'.Permission::ManageForms->value)->name('forms.edit');
            Route::put('/forms/{form}', [FormController::class, 'update'])->middleware('can:'.Permission::ManageForms->value)->name('forms.update');
            Route::delete('/forms/{form}', [FormController::class, 'destroy'])->middleware('can:'.Permission::ManageForms->value)->name('forms.destroy');
            Route::post('/forms/{form}/fields', [FormController::class, 'storeField'])->middleware('can:'.Permission::ManageForms->value)->name('forms.fields.store');
            Route::put('/forms/{form}/fields/{field}', [FormController::class, 'updateField'])->middleware('can:'.Permission::ManageForms->value)->name('forms.fields.update');
            Route::delete('/forms/{form}/fields/{field}', [FormController::class, 'destroyField'])->middleware('can:'.Permission::ManageForms->value)->name('forms.fields.destroy');
            Route::put('/forms/{form}/fields', [FormController::class, 'reorder'])->middleware('can:'.Permission::ManageForms->value)->name('forms.fields.reorder');
            Route::get('/forms/{form}/submissions', [FormSubmissionController::class, 'index'])->middleware('can:'.Permission::ManageForms->value)->name('forms.submissions');
            Route::get('/forms/{form}/submissions/{submission}', [FormSubmissionController::class, 'show'])->middleware('can:'.Permission::ManageForms->value)->name('forms.submissions.show');

            Route::get('/settings', [SettingsController::class, 'index'])->middleware('can:'.Permission::ManageSettings->value)->name('settings');
            Route::post('/settings', [SettingsController::class, 'update'])->middleware('can:'.Permission::ManageSettings->value)->name('settings.update');

        });
    });
});

Route::get('/{slug}', [FrontendController::class, 'page'])->name('frontend.page');
