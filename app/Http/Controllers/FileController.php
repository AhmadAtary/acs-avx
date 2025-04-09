<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\File;
use App\Models\DeviceModel;
use App\Models\Device;
use GuzzleHttp\Client;
use App\Http\Controllers\LogController;



class FileController extends Controller
{
    private function url_ID($device)
    {
        $model = $device['_deviceId']['_ProductClass'];
        $oui = $device['_deviceId']['_OUI'];
        $serial = $device['_deviceId']['_SerialNumber'];

        if (str_contains($model, '-')) {
            $model = str_replace('-', '%252D', $model);
            $url_Id = $oui . '-' . $model . '-' . $serial;
            return $url_Id;
        }
        return $device->_id;
    }
    
    public function index()
    {
        $models = DeviceModel::all();
        $files = File::all(); // Fetch all files from the collection

        // Log the action (viewing files)
        LogController::saveLog("software_files_managment", "User opened Software files managmanet page ");

        return view('files.indexFiles', compact('files', 'models'));
    }

    // Store a new file
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:102400', // Limit to software image file types and 10MB max
            'fileType' => 'required|string|in:1 Firmware Upgrade Image,3 Vendor Configuration File',
            'oui' => 'required|string',
            'productClass' => 'required|string',
            'version' => 'required|string',
        ]);

        try {
            $file = $request->file('file');
            $originalFileName = $file->getClientOriginalName();
            $path = $file->storeAs('uploads', $originalFileName);

            // Check if the file exists
            if (!Storage::exists($path)) {
                LogController::saveLog("software_file_upload_failed", "File was not saved correctly.");
                return back()->with('error', 'File was not saved correctly.');
            }

            // Read the file content
            $fileContent = Storage::get($path);

            $response = Http::withHeaders([
                'fileType' => $request->fileType,
                'oui' => $request->oui,
                'productClass' => $request->productClass,
                'version' => $request->version,
            ])->withoutVerifying()->withBody($fileContent, 'application/octet-stream')
            ->put("https://10.106.45.1:7557/files/" . $originalFileName);

            // Delete the file after upload attempt
            Storage::delete($path);

            if ($response->successful()) {
                LogController::saveLog("software_file_upload_success", "File: {$originalFileName}");
                return back()->with('success', 'File uploaded successfully.');
            } else {
                LogController::saveLog("software_file_upload_failed", "Failed to upload: {$originalFileName} Status: {$response->status()}");
                return back()->with('error', 'Upload failed: ' . $response->status());
            }
        } catch (\Exception $e) {
            LogController::saveLog("File upload error", $e->getMessage());
            return back()->with('error', 'File upload failed: ' . $e->getMessage());
        }
    }

    public function pushSW(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'device_id' => 'required|string',
            'swFile' => 'required|string',
        ]);
    
        $serialNumber = $request->input('device_id');
        $filename = $request->input('swFile');
    
        // Retrieve the device data by serial number
        $deviceData = Device::where('_deviceId._SerialNumber', $serialNumber)->first();
    
        if (!$deviceData) {
            LogController::saveLog("Software_file_push_failed", "Device ID: {$serialNumber} Not Found !");
            return back()->with('error', 'Device not found.');
        }
    
        // Validate and retrieve the device ID
        $id = $deviceData->_id;
        if (!$id) {
            // LogController::saveLog("Invalid device ID", "Device ID: {$serialNumber}");
            return back()->with('error', 'Invalid device ID.');
        }
    
        // Generate the URL-safe ID
        $url_id = $this->url_ID($deviceData);
    
        if (!$url_id) {
            // LogController::saveLog("Invalid device ID format", "Device ID: {$serialNumber}");
            return back()->with('error', 'Invalid device ID format.');
        }
    
        // Prepare the JSON body for the HTTP request
        $json_body = [
            'fileName' => $filename,
            'fileType' => '1 Firmware Upgrade Image',
            'name' => 'download',
        ];
    
        // Use Guzzle HTTP client to send the request
        $client = new Client();
    
        try {
            // Send the request to the specified URL
            $response = $client->post("https://10.106.45.1:7557/devices/{$url_id}/tasks?connection_request", [
                'json' => $json_body,
                'verify' => false, // Disable SSL verification for self-signed certificates
            ]);
    
            $statusCode = $response->getStatusCode();
    
            // Handle different response statuses
            if ($statusCode == 200) {
                LogController::saveLog("update_success", "Device ID: {$serialNumber}, File: {$filename}");
                return back()->with('success', 'Update process started successfully.');
            } elseif ($statusCode == 202) {
                LogController::saveLog("Update_pending_task", "Device ID: {$serialNumber}, File: {$filename}");
                return back()->with('task', 'Update process is pending.');
            } else {
                LogController::saveLog("update_failed", "Device ID: {$serialNumber}, Status: {$statusCode}");
                return back()->with('error', 'Unexpected response status: ' . $statusCode);
            }
        } catch (\Exception $e) {
            // Log the activity for the failed update
            LogController::saveLog("update_failed", "Device ID: {$serialNumber}, Error: {$e->getMessage()}");
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // Delete a file
    public function destroy($id)
    {
        $file = File::findOrFail($id);
        $file->delete();

        // Log file deletion
        LogController::saveLog("Software_file_deleted", "File ID: {$id}, File Name: {$file->filename}");

        return redirect()->route('files.index')->with('success', 'File deleted successfully!');
    }

    // Update an existing file
    public function update(Request $request, $id)
    {
        $file = File::findOrFail($id);

        $validated = $request->validate([
            'filename' => 'required|string|max:255',
            'metadata.fileType' => 'required|string',
            'metadata.oui' => 'required|string',
            'metadata.productClass' => 'required|string',
            'metadata.version' => 'required|string',
        ]);

        $file->update($validated);

        // Log file update
        LogController::saveLog("File updated", "File ID: {$id}, File Name: {$file->filename}");

        return redirect()->back()->with('success', 'File updated successfully!');
    }
}







