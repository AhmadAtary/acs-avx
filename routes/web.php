<?php

use App\Http\Controllers\OwnerController;
use App\Http\Controllers\EngineerController;
use App\Http\Controllers\CustomerSupportController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ModelController;
use App\Http\Controllers\HostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\BulkActionsController;
use App\Http\Controllers\NodeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OtpController;



use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(Auth::check() ? 'dashboard' : 'auth.login');
})->name('home');

// Login Routes
Route::get('/login', [LoginController::class, 'loginView'])->name('auth.login');
Route::post('/login', [LoginController::class, 'login'])->name('login');

// OTP Routes
Route::middleware('auth')->group(function () {
    Route::get('/otp', [OtpController::class, 'prompt'])->name('otp.prompt');
    Route::post('/otp/resend', [OtpController::class, 'resend'])->name('otp.resend');
    Route::post('/otp/verify', [OtpController::class, 'verify'])->name('otp.validation');
});



Route::middleware(['auth', 'otp.verify'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
    Route::get('/Customer-serves/device', [CustomerSupportController::class, 'show'])->middleware(['auth', 'verified'])->name('customer.device');

    Route::post('/device-action/reboot' , [DeviceController::class, 'RebootDevice'])->name('device.reboot');
    Route::post('/device-action/reset', [DeviceController::class, 'ResetDevice'])->name('device.reset');
    Route::post('/device-action/pushSW', [FileController::class, 'pushSW'])->name('device.pushSW');
    Route::delete('/device-action/destroy/{id}', [DeviceController::class, 'destroy'])->name('device.delete');

    Route::get('/device/hosts/{serialNumber}' , [HostController::class, 'HostsInfo'])->name('device.host');

    Route::post('/Customer-serves/device/manage', [CustomerSupportController::class, 'manage'])->name('node.manageCustomer');


});

Route::middleware(['auth', 'otp.verify','eng'])->group(function () {
    Route::get('/device-stats', [DeviceController::class, 'devices_status']);

    Route::get('/all-devices', [DeviceController::class, 'index'])->name('devices.all');
    Route::get('/devices/search', [DeviceController::class, 'searchDevices'])->name('devices.search');

    Route::get('/device-info/{serialNumber}', [DeviceController::class, 'info'])->name('device.info');

    Route::post('/device-action/set-Node', [DeviceController::class, 'setNodeValue'])->name('node.set');
    Route::post('/device-action/get-Node' , [DeviceController::class, 'getNodevalue'])->name('node.get');


    
    Route::get('/admin/hosts/create', [HostController::class, 'create'])->name('hosts.create');
    Route::post('/admin/hosts/store', [HostController::class, 'store'])->name('hosts.store');
    Route::get('/admin/hosts/{id}/edit', [HostController::class, 'edit'])->name('hosts.edit');
    Route::post('/admin/hosts/{id}/update', [HostController::class, 'update'])->name('hosts.update');

    Route::get('/device-per-Model', [DeviceController::class, 'device_model'])->name('device.model');
    Route::get('/device-per-Model/{model}', [DeviceController::class, 'index_Models'])->name('device.modelShow');
    Route::get('/devices/search/model', [DeviceController::class, 'searchDevicesByModel'])->name('devicesModels.search');

    Route::get('/dashboard/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/dashboard/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/dashboard/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/dashboard/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');


    Route::get('/dashboard/files', [FileController::class, 'index'])->name('files.index'); // List all files
    Route::post('/dashboard/files/store', [FileController::class, 'store'])->name('files.store'); // Store a new file
    Route::put('/dashboard/files/update/{id}', [FileController::class, 'update'])->name('files.update'); // Update an existing file
    Route::delete('/dashboard/filesdelete/{id}', [FileController::class, 'destroy'])->name('files.destroy'); // Delete a file

    Route::get('/dashboard/bulk-actions', [BulkActionsController::class, 'index'])->name('bulk-actions.index');
    Route::post('/dashboard/bulk-actions/upload', [BulkActionsController::class, 'upload'])->name('bulk-actions.upload');
    Route::get('/dashboard/bulk-actions/pause/{progressId}', [BulkActionsController::class, 'pause'])->name('bulk-actions.pause');
    Route::get('/dashboard/bulk-actions/resume/{progressId}', [BulkActionsController::class, 'resume'])->name('bulk-actions.resume');
    Route::get('/dashboard/bulk-actions/delete/{progressId}', [BulkActionsController::class, 'delete'])->name('bulk-actions.delete');
    Route::get('/dashboard/bulk-actions/progress/{progressId}', [BulkActionsController::class, 'progress'])->name('bulk-actions.progress');
    Route::get('/dashboard/bulk-actions/export/{id}', [BulkActionsController::class, 'exportReport'])->name('bulk-actions.export');

    Route::get('/dashboard/bulk-actions/nodes/{modelId}', [NodeController::class, 'getNodes'])->name('bulk-actions.nodes');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/dashboard/models-managment', [ModelController::class, 'index'])->name('device-models.index');
    Route::post('/dashboard/models-managment', [ModelController::class, 'store'])->name('device-models.store'); 
    Route::get('/dashboard/models-managment/edit/{id}', [ModelController::class, 'edit'])->name('device-models.edit');
    Route::put('/dashboard/models-managment/edit/{id}', [ModelController::class, 'update'])->name('device-models.update');
    Route::delete('/dashboard/models-managment/destroy/{id}', [ModelController::class, 'destroy'])->name('device-models.destroy');

});


Route::get('/error/403', function () {
    return response()->view('Errors.page-error-403', [], 403);
})->name('Errors.page-error-403');

