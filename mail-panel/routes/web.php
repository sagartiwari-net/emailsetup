<?php

use App\Http\Controllers\Admin\ApiKeyController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DomainController;
use App\Http\Controllers\Admin\FailedJobController;
use App\Http\Controllers\Admin\MailLogController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SubscriberListController;
use App\Http\Controllers\Admin\TemplateController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\TestMailController;
use App\Http\Controllers\Admin\WebsiteController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UnsubscribeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/unsubscribe/{token}', UnsubscribeController::class)->name('unsubscribe');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'tenant.active'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/logs', [MailLogController::class, 'index'])->name('logs.index');
    Route::get('/failed-jobs', [FailedJobController::class, 'index'])->name('failed-jobs.index');
    Route::post('/failed-jobs/{id}/retry', [FailedJobController::class, 'retry'])->name('failed-jobs.retry');
    Route::delete('/failed-jobs/{id}', [FailedJobController::class, 'destroy'])->name('failed-jobs.destroy');

    Route::get('/domains', [DomainController::class, 'index'])->name('domains.index');
    Route::post('/domains', [DomainController::class, 'store'])->name('domains.store');
    Route::put('/domains/{domain}', [DomainController::class, 'update'])->name('domains.update');

    Route::get('/websites', [WebsiteController::class, 'index'])->name('websites.index');
    Route::post('/websites', [WebsiteController::class, 'store'])->name('websites.store');
    Route::put('/websites/{website}', [WebsiteController::class, 'update'])->name('websites.update');
    Route::delete('/websites/{website}', [WebsiteController::class, 'destroy'])->name('websites.destroy');
    Route::get('/api-keys', [ApiKeyController::class, 'index'])->name('api-keys.index');
    Route::post('/api-keys', [ApiKeyController::class, 'store'])->name('api-keys.store');
    Route::delete('/api-keys/{apiKey}', [ApiKeyController::class, 'destroy'])->name('api-keys.destroy');

    Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
    Route::get('/templates/create', [TemplateController::class, 'create'])->name('templates.create');
    Route::post('/templates', [TemplateController::class, 'store'])->name('templates.store');
    Route::get('/templates/{template}/edit', [TemplateController::class, 'edit'])->name('templates.edit');
    Route::put('/templates/{template}', [TemplateController::class, 'update'])->name('templates.update');
    Route::delete('/templates/{template}', [TemplateController::class, 'destroy'])->name('templates.destroy');

    Route::get('/subscribers', [SubscriberListController::class, 'index'])->name('subscribers.index');
    Route::post('/subscribers', [SubscriberListController::class, 'store'])->name('subscribers.store');
    Route::get('/subscribers/{subscriberList}', [SubscriberListController::class, 'show'])->name('subscribers.show');
    Route::post('/subscribers/{subscriberList}/import', [SubscriberListController::class, 'import'])->name('subscribers.import');
    Route::post('/subscribers/{subscriberList}/add', [SubscriberListController::class, 'addSubscriber'])->name('subscribers.add');
    Route::delete('/subscribers/{subscriberList}/{subscriber}', [SubscriberListController::class, 'destroySubscriber'])->name('subscribers.remove');

    Route::get('/campaigns', [CampaignController::class, 'index'])->name('campaigns.index');
    Route::get('/campaigns/create', [CampaignController::class, 'create'])->name('campaigns.create');
    Route::post('/campaigns', [CampaignController::class, 'store'])->name('campaigns.store');
    Route::get('/campaigns/{campaign}', [CampaignController::class, 'show'])->name('campaigns.show');
    Route::post('/campaigns/{campaign}/test', [CampaignController::class, 'testSend'])->name('campaigns.test');
    Route::post('/campaigns/{campaign}/pause', [CampaignController::class, 'pause'])->name('campaigns.pause');
    Route::post('/campaigns/{campaign}/resume', [CampaignController::class, 'resume'])->name('campaigns.resume');
    Route::post('/campaigns/{campaign}/cancel', [CampaignController::class, 'cancel'])->name('campaigns.cancel');

    Route::get('/test-mail', [TestMailController::class, 'create'])->name('test-mail.create');
    Route::post('/test-mail', [TestMailController::class, 'send'])->name('test-mail.send');

    Route::get('/account/password', [ProfileController::class, 'editPassword'])->name('account.password.edit');
    Route::put('/account/password', [ProfileController::class, 'updatePassword'])->name('account.password.update');

    Route::middleware('super_admin')->group(function () {
        Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
        Route::get('/tenants/create', [TenantController::class, 'create'])->name('tenants.create');
        Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
        Route::get('/tenants/{tenant}', [TenantController::class, 'show'])->name('tenants.show');
        Route::get('/tenants/{tenant}/edit', [TenantController::class, 'edit'])->name('tenants.edit');
        Route::put('/tenants/{tenant}', [TenantController::class, 'update'])->name('tenants.update');
    });
});
