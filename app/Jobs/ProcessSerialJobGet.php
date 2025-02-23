<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\UploadProgress;
use App\Models\Device;
use App\Models\JobStatus;
use GuzzleHttp\Client;

class ProcessSerialJobGet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $serialNumber;
    protected $progressId;
    protected $nodePath;
    protected $action;
    protected $newValue;
    protected $nodeTypeDetailed;
    protected $deviceModel;

    public function __construct($serialNumber, $progressId, $nodePath, $action, $newValue, $nodeTypeDetailed, $deviceModel)
    {
        $this->serialNumber = $serialNumber;
        $this->progressId = $progressId;
        $this->nodePath = $nodePath;
        $this->action = $action;
        $this->newValue = $newValue;
        $this->nodeTypeDetailed = $nodeTypeDetailed;
        $this->deviceModel = $deviceModel;
    }

    public function handle()
    {
        $progress = UploadProgress::find($this->progressId);
        if (!$progress || $progress->status === 'paused' || $progress->status === 'deleted') {
            Log::info("Skipping job for serial number: {$this->serialNumber} due to paused or deleted status.");
            return;
        }

        Log::info("Processing 'GET' action for serial number: {$this->serialNumber}");
        $device = Device::where('_deviceId._SerialNumber', $this->serialNumber)->first();
        if (!$device) {

            $this->updateProgress('not_found_count', 'Device not found');
            return;
        }

        $urlId = $this->url_ID($device);
        $json_body = [
            'device' => $device->Id,
            'name' => 'getParameterValues',
            'parameterNames' => [$this->nodePath],
        ];

        $client = new Client();
        $response = $client->post("https://172.18.0.1:7557/devices/{$urlId}/tasks?connection_request", [
            'json' => $json_body,
            'verify' => false, // Disable SSL verification
        ]);


        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            $this->updateProgress('success_count', $response->getBody()->getContents());
        } else {
            $this->updateProgress('fail_count', $response->getBody()->getContents());
        }
    }

    protected function updateProgress($field, $response)
    {
        $progress = UploadProgress::find($this->progressId);
        if ($progress) {
            $progress->increment($field);
            $progress->increment('processed');
            JobStatus::create([
                'upload_progress_id' => $this->progressId,
                'serial_number' => $this->serialNumber,
                'status' => $field === 'success_count' ? 'success' : 'fail',
                'response' => $response,
            ]);
            $this->checkAndUpdateProgressStatus($progress);
        }
    }

    protected function checkAndUpdateProgressStatus($progress)
    {
        Log::info("Checking progress status for progress ID: {$this->progressId}");
        Log::info("Processed: {$progress->processed}, Total: {$progress->total}");
        if ($progress->processed >= $progress->total) {
            Log::info("Updating status to done for progress ID: {$this->progressId}");
            $progress->status = 'done';
            $progress->save();
            $this->generateReport($progress);
        }
    }

    protected function url_ID($device)
    {
        $model = $device->_deviceId['_ProductClass'];
        $oui = $device->_deviceId['_OUI'];
        $serial = $device->_deviceId['_SerialNumber'];
        if (str_contains($model, '-')) {
            $model = str_replace('-', '%252D', $model);
            $url_Id = $oui . '-' . $model . '-' . $serial;
            return $url_Id;
        }
        return $device->_id;
    }

    protected function generateReport($progress)
    {
        $filePath = storage_path('app/public/report_' . $progress->id . '.csv');

        $file = fopen($filePath, 'w');

        fputcsv($file, ['Serial Number', 'Status', 'Response']);

        $jobs = JobStatus::where('upload_progress_id', $progress->id)->get();
        foreach ($jobs as $job) {
            fputcsv($file, [$job->serial_number, $job->status, $job->response]);
        }

        fclose($file);

        Log::info("CSV Report generated at: {$filePath}");
    }
}