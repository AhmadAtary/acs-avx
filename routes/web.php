<?php

use App\Http\Controllers\OwnerController;
use App\Http\Controllers\EngineerController;
use App\Http\Controllers\CustomerSupportController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ModelController;
use App\Http\Controllers\HostController;



use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});



Route::get('/login', [LoginController::class, 'loginView'])->name('auth.login');
Route::POST('/login', [LoginController::class, 'login'])->name('login');

Route::get('/device-stats', [DeviceController::class, 'devices_status']);

Route::middleware(['auth'])->group(function () {
    Route::get('/owner-dashboard', [OwnerController::class, 'index'])->name('owner.dashboard');
    Route::get('/engineer-dashboard', [EngineerController::class, 'index'])->name('engineer.dashboard');
    Route::get('/cs-dashboard', [CustomerSupportController::class, 'index'])->name('cs.dashboard');


    Route::get('/all-devices', [DeviceController::class, 'index'])->name('devices.all');
    Route::get('/search-devices', [DeviceController::class, 'searchDevices']);

    Route::get('/device-info/{serialNumber}', [DeviceController::class, 'info'])->name('device.info');

    Route::post('/device-action/set-Node', [DeviceController::class, 'setNodeValue'])->name('node.set');
    Route::post('/device-action/get-Node' , [DeviceController::class, 'getNodevalue'])->name('node.get');
    Route::post('/device-action/reboot' , [DeviceController::class, 'RebootDevice'])->name('device.reboot');
    Route::post('/device-action/reset', [DeviceController::class, 'ResetDevice'])->name('device.reset');

    Route::get('/device/hosts/{serialNumber}' , [HostController::class, 'HostsInfo'])->name('device.host');
    Route::get('/admin/hosts/create', [HostController::class, 'create'])->name('hosts.create');
    Route::post('/admin/hosts/store', [HostController::class, 'store'])->name('hosts.store');
    Route::get('/admin/hosts/{id}/edit', [HostController::class, 'edit'])->name('hosts.edit');
    Route::post('/admin/hosts/{id}/update', [HostController::class, 'update'])->name('hosts.update');


    Route::post('/model', [ModelController::class, 'store'])->name('models.store'); 
});
