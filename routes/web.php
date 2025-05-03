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
use App\Http\Controllers\LogController;
use App\Http\Controllers\DeviceLogController;
use App\Http\Controllers\UserDeviceController;
use App\Http\Controllers\TaskMailController;
use App\Http\Controllers\DeviceStandardNodeController;
use App\Http\Controllers\NetworkController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\enduser\AuthController;
use App\Http\Controllers\enduser\End_user_DeviceController;




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

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

    Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

    Route::get('/Customer-serves/device', [CustomerSupportController::class, 'show'])->middleware(['auth', 'verified'])->name('customer.device');

    Route::post('/device-action/reboot' , [DeviceController::class, 'RebootDevice'])->name('device.reboot');
    Route::post('/device-action/reset', [DeviceController::class, 'ResetDevice'])->name('device.reset');
    Route::post('/device-action/pushSW', [FileController::class, 'pushSW'])->name('device.pushSW');
    Route::delete('/device-action/destroy/{id}', [DeviceController::class, 'destroy'])->name('device.delete');
    Route::post('/Customer-serves/device/manage', [CustomerSupportController::class, 'manage'])->name('node.manageCustomer');


    // This is the general route for all users to access device information return json
    Route::get('/device/hosts/{serialNumber}' , [HostController::class, 'HostsInfo'])->name('device.host');
    Route::get('/device/nodes/{model}', [DeviceController::class, 'getStandardNodes'])->name('neighbor.nodes');
    Route::post('/send-task', [TaskMailController::class, 'send'])->name('send.task');

    Route::get('/device/{serialNumber}/diagnostics', [DeviceController::class, 'diagnostics'])->name('device.diagnostics');
    Route::post('/generate-link', [End_user_DeviceController::class, 'generateLink'])
    ->name('generate.link');
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

    // User Management Routes with Permission Middleware
    Route::get('/dashboard/users', [UserController::class, 'index'])
        ->middleware(['check.permission:user_management,view'])
        ->name('users.index');

    Route::post('/dashboard/users', [UserController::class, 'store'])
        ->middleware(['check.permission:user_management,create'])
        ->name('users.store');

    Route::put('/dashboard/users/{id}', [UserController::class, 'update'])
        ->middleware(['check.permission:user_management,edit'])
        ->name('users.update');

    Route::delete('/dashboard/users/{id}', [UserController::class, 'destroy'])
        ->middleware(['check.permission:user_management,delete'])
        ->name('users.destroy');



    Route::get('/dashboard/files', [FileController::class, 'index'])
        ->middleware(['check.permission:files_management,view'])
        ->name('files.index'); // List all files

    Route::post('/dashboard/files/store', [FileController::class, 'store'])
        ->middleware(['check.permission:files_management,create'])
        ->name('files.store'); // Store a new file

    Route::put('/dashboard/files/update/{id}', [FileController::class, 'update'])
        ->middleware(['check.permission:files_management,edit'])
        ->name('files.update'); // Update an existing file

    Route::delete('/dashboard/filesdelete/{id}', [FileController::class, 'destroy'])
        ->middleware(['check.permission:files_management,delete'])
        ->name('files.destroy'); // Delete a file


        Route::get('/dashboard/bulk-actions', [BulkActionsController::class, 'index'])
        ->middleware(['check.permission:bulk_actions,view'])
        ->name('bulk-actions.index'); // View bulk actions

    Route::post('/dashboard/bulk-actions/upload', [BulkActionsController::class, 'upload'])
        ->middleware(['check.permission:bulk_actions,create'])
        ->name('bulk-actions.upload'); // Upload bulk actions

    Route::get('/dashboard/bulk-actions/pause/{progressId}', [BulkActionsController::class, 'pause'])
        ->middleware(['check.permission:bulk_actions,edit'])
        ->name('bulk-actions.pause'); // Pause bulk actions

    Route::get('/dashboard/bulk-actions/resume/{progressId}', [BulkActionsController::class, 'resume'])
        ->middleware(['check.permission:bulk_actions,edit'])
        ->name('bulk-actions.resume'); // Resume bulk actions

    Route::get('/dashboard/bulk-actions/delete/{progressId}', [BulkActionsController::class, 'delete'])
        ->middleware(['check.permission:bulk_actions,delete'])
        ->name('bulk-actions.delete'); // Delete bulk actions

    Route::get('/dashboard/bulk-actions/progress/{progressId}', [BulkActionsController::class, 'progress'])
        ->middleware(['check.permission:bulk_actions,view'])
        ->name('bulk-actions.progress'); // View bulk action progress

    Route::get('/dashboard/bulk-actions/export/{id}', [BulkActionsController::class, 'exportReport'])
        ->middleware(['check.permission:bulk_actions,view'])
        ->name('bulk-actions.export'); // Export bulk action reports

    Route::get('/dashboard/bulk-actions/nodes/{modelId}', [NodeController::class, 'getNodes'])
        ->middleware(['check.permission:bulk_actions,view'])
        ->name('bulk-actions.nodes'); // View nodes for bulk actions



    Route::get('/dashboard/models-managment', [ModelController::class, 'index'])
        ->middleware(['check.permission:models_management,view'])
        ->name('device-models.index'); // View all models

    Route::post('/dashboard/models-managment', [ModelController::class, 'store'])
        ->middleware(['check.permission:models_management,create'])
        ->name('device-models.store'); // Create a new model

    Route::get('/dashboard/models-managment/edit/{id}', [ModelController::class, 'edit'])
        ->middleware(['check.permission:models_management,edit'])
        ->name('device-models.edit'); // Edit model page

    Route::put('/dashboard/models-managment/edit/{id}', [ModelController::class, 'update'])
        ->middleware(['check.permission:models_management,edit'])
        ->name('device-models.update'); // Update model details

    Route::delete('/dashboard/models-managment/destroy/{id}', [ModelController::class, 'destroy'])
        ->middleware(['check.permission:models_management,delete'])
        ->name('device-models.destroy'); // Delete a model


    Route::get('users/{user}/logs', [LogController::class, 'getLogs'])->name('users.logs');

    Route::get('/device-logs/{device_id}', [DeviceLogController::class, 'getLogs'])->name('device-logs');

    Route::put('/users/devices/full-access', [UserDeviceController::class, 'grantFullAccess'])->name('assign.devices.full-access');
    Route::put('/users/devices/upload', [UserDeviceController::class, 'uploadDevices'])->name('assign.devices.csv');
    Route::get('/users/{user}/devices/export', [UserDeviceController::class, 'export'])->name('export.devices.csv');

    Route::get('/wifi/standard-nodes/create', [DeviceStandardNodeController::class, 'create'])->name('standard-nodes.create');
    Route::post('/wifi/standard-nodes/store', [DeviceStandardNodeController::class, 'store'])->name('standard-nodes.store');
    Route::get('/wifi/standard-nodes/{serialNumber}', [DeviceStandardNodeController::class, 'getStandardNodes'])->name('standard-nodes.get');


    Route::get('/signal-node', [NetworkController::class, 'create'])->name('signal-nodes.create');
    Route::post('/signal-node/store', [NetworkController::class, 'storeMultiple'])->name('signal-nodes.storeMultiple');

    Route::get('/analysis', [AnalysisController::class, 'index'])->name('analysis.index');
    Route::get('/analysis/process', [AnalysisController::class, 'process'])->name('analysis.process');




});


Route::get('/device/{url_Id}', [End_user_DeviceController::class, 'show'])->name('device.show');
Route::post('/node/update', [End_user_DeviceController::class, 'updateNodes'])->name('node.update');
Route::get('/end-user-login/{token}', [AuthController::class, 'showLogin'])->name('end.user.login.show');
Route::post('/end-user-login', [AuthController::class, 'login'])->name('end.user.login');
Route::get('/end_session', [End_user_DeviceController::class, 'showEndSession'])
     ->name('end.session');



