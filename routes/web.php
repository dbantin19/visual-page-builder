<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\FooterController;
use App\Http\Controllers\Admin\NavMenuController;
use App\Http\Controllers\Admin\PagesController;
use App\Http\Controllers\Admin\TemplatesController;
use App\Http\Controllers\Admin\UploadsController;
use App\Http\Controllers\CouponPrintController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('admin.pages.index'));
Route::get('/login', fn() => redirect()->route('admin.login'))->name('login');

Route::prefix('admin')->name('admin.')->group(function () {

    // Guest-only routes
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login']);
    });

    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Authenticated routes
    Route::middleware('auth')->group(function () {
        Route::get('/', fn() => redirect()->route('admin.navigation.index'));
        Route::get('footer', [FooterController::class, 'index'])->name('footer.index');
        Route::post('footer', [FooterController::class, 'save'])->name('footer.save');
        Route::resource('pages', PagesController::class);
        Route::get('pages/{page}/builder', [PagesController::class, 'builder'])->name('pages.builder');
        Route::post('pages/{page}/builder', [PagesController::class, 'saveBuilder'])->name('pages.builder.save');
        Route::get('pages/{page}/versions', [PagesController::class, 'listVersions'])->name('pages.versions.index');
        Route::post('pages/{page}/versions/{version}/restore', [PagesController::class, 'restoreVersion'])->name('pages.versions.restore');
        Route::post('pages/{page}/use-template', [PagesController::class, 'useTemplate'])->name('pages.use-template');
        Route::post('templates', [TemplatesController::class, 'store'])->name('templates.store');
        Route::delete('templates/{template}', [TemplatesController::class, 'destroy'])->name('templates.destroy');
        Route::get('uploads', [UploadsController::class, 'index'])->name('uploads.index');
        Route::post('uploads', [UploadsController::class, 'store'])->name('uploads.store');
        Route::delete('uploads', [UploadsController::class, 'destroyMany'])->name('uploads.destroy-many');
        Route::delete('uploads/{filename}', [UploadsController::class, 'destroy'])->name('uploads.destroy');

        Route::get('navigation', [NavMenuController::class, 'index'])->name('navigation.index');
        Route::post('navigation/save-all', [NavMenuController::class, 'saveAll'])->name('navigation.save-all');
        Route::post('navigation/settings', [NavMenuController::class, 'saveSettings'])->name('navigation.settings');
        Route::post('navigation', [NavMenuController::class, 'store'])->name('navigation.store');
        Route::put('navigation/{navMenuItem}', [NavMenuController::class, 'update'])->name('navigation.update');
        Route::post('navigation/{navMenuItem}/indent', [NavMenuController::class, 'indent'])->name('navigation.indent');
        Route::post('navigation/{navMenuItem}/outdent', [NavMenuController::class, 'outdent'])->name('navigation.outdent');
        Route::delete('navigation/{navMenuItem}', [NavMenuController::class, 'destroy'])->name('navigation.destroy');
        Route::post('navigation/reorder', [NavMenuController::class, 'reorder'])->name('navigation.reorder');
    });
});

Route::get('/coupons/{footerCoupon}/print', CouponPrintController::class)->name('coupons.print');
Route::get('/{slug}', [PagesController::class, 'show'])->name('pages.show')->where('slug', '[a-z0-9-]+');
