<?php

use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\CompanyResolutionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);

        // Settings
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

        // Companies (sync ANTES del resource)
        Route::post('companies/{company}/sync', [CompanyController::class, 'sync'])
            ->name('companies.sync');
		Route::get('companies/{company}/software', [CompanyController::class, 'editSoftware'])
			->name('companies.software.edit');
		Route::put('companies/{company}/software', [CompanyController::class, 'updateSoftware'])
			->name('companies.software.update');
		Route::get('companies/{company}/certificate', [CompanyController::class, 'editCertificate'])
			->name('companies.certificate.edit');
		Route::put('companies/{company}/certificate', [CompanyController::class, 'updateCertificate'])
			->name('companies.certificate.update');
		Route::get('companies/{company}/resolutions', [CompanyResolutionController::class, 'index'])
			->name('companies.resolutions.index');
		Route::post('companies/{company}/resolutions/habilitation', [CompanyResolutionController::class, 'storeHabilitation'])
			->name('companies.resolutions.habilitation');
		Route::post('companies/{company}/resolutions/toggle-habilitation', [CompanyResolutionController::class, 'toggleHabilitation'])
			->name('companies.resolutions.toggle-habilitation');
		Route::post('companies/{company}/resolutions', [CompanyResolutionController::class, 'store'])
			->name('companies.resolutions.store');
		Route::delete('companies/{company}/resolutions/{resolution}', [CompanyResolutionController::class, 'destroy'])
			->name('companies.resolutions.destroy');
		Route::get('companies/{company}/resolutions/test-invoice', [CompanyResolutionController::class, 'showTestInvoice'])
			->name('companies.resolutions.test-invoice.show');
		Route::post('companies/{company}/resolutions/test-invoice', [CompanyResolutionController::class, 'sendTestInvoice'])
			->name('companies.resolutions.test-invoice');
		Route::get('companies/{company}/resolutions/test-credit-note', [CompanyResolutionController::class, 'showTestCreditNote'])
			->name('companies.resolutions.test-credit-note.show');
		Route::post('companies/{company}/resolutions/test-credit-note', [CompanyResolutionController::class, 'sendTestCreditNote'])
			->name('companies.resolutions.test-credit-note');
        Route::resource('companies', CompanyController::class);
    });
});

require __DIR__.'/auth.php';