<?php

namespace App\Http\Controllers;

use App\Models\UploadProgress;
use App\Models\JobStatus;
use App\Models\DModel;
use App\Jobs\ProcessSerialJob;
use App\Jobs\ProcessSerialJobGet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BulkActionsController extends Controller
{
    public function index()
    {
        $progresses = UploadProgress::all();
        $models = DModel::all(); // Assuming you have a way to get models, adjust as needed
        return view('Bulk-action.indexBulk', compact('progresses', 'models'));
    }


    public function upload(Request $request)
    {
        // Validate the request
        $request->validate([
            'csvFile' => 'required|file|mimes:csv,txt',
            'model' => 'required|string',
            'action' => 'required|string',
            'nodePath' => 'required|string',
            'newValue' => 'nullable|string',
            'nodeTypeDetailed' => 'nullable|string'
        ]);

        // Get details from the request
        $deviceModel = $request->input('model');
        $action = $request->input('action');
        $nodePath = $request->input('nodePath');
        $newValue = $request->input('newValue') ?: 'N/A';
        $nodeTypeDetailed = $request->input('nodeTypeDetailed');
        $date = now()->format('Y-m-d_H-i-s');

        // Generate the filename
        $filename = "{$deviceModel}_{$action}_{$date}.csv";

        // Store the file
        $path = $request->file('csvFile')->storeAs('csv_files', $filename);

        try {
            // Read and process the CSV data
            $csvData = array_map('str_getcsv', file(Storage::path($path)));
        } catch (\Exception $e) {
            Log::error('Error reading CSV file: ' . $e->getMessage());
            return redirect()->back()->withErrors(['csvFile' => 'There was an error processing the CSV file.']);
        }

        $total = count($csvData);
        $bulkName = $total . ' Devices / Node Path: ' . $nodePath . ' / Action: ' . $action;

        // Create progress tracking record
        $progress = UploadProgress::create([
            'name' => $bulkName,
            'file_name' => $path,
            'total' => $total,
            'status' => 'processing',
        ]);

        // Dispatch jobs based on the action type
        foreach ($csvData as $row) {
            if (isset($row[0])) {
                $serialNumber = trim($row[0]);
                if ($action === "set") {
                    ProcessSerialJob::dispatch($serialNumber, $progress->id, $nodePath, $action, $newValue, $nodeTypeDetailed, $deviceModel);
                } elseif ($action === "get") {
                    ProcessSerialJobGet::dispatch($serialNumber, $progress->id, $nodePath, $action, null, $nodeTypeDetailed, $deviceModel);
                }
            } else {
                Log::warning('CSV row missing serial number: ' . json_encode($row));
            }
        }

        // Delete the uploaded CSV file after dispatching jobs
        Storage::delete($path);

        return redirect()->back()->with('success', 'Bulk action successfully started. Progress is being tracked.');
    }

    public function pause($progressId)
    {
        $progress = UploadProgress::find($progressId);
        if (!$progress) {
            return response()->json(['status' => 'error', 'message' => 'Progress not found.'], 404);
        }

        // Update the status to 'paused'
        $progress->status = 'paused';
        $progress->paused_at = now();
        $progress->save();

        return response()->json(['status' => 'success', 'message' => 'Jobs paused successfully.']);
    }


    public function resume($progressId)
    {
        $progress = UploadProgress::find($progressId);
        if (!$progress) {
            return response()->json(['status' => 'error', 'message' => 'Progress not found.'], 404);
        }

        // Update the status to 'processing'
        $progress->status = 'processing';
        $progress->resumed_at = now();
        $progress->save();

        // Dispatch jobs for remaining (pending) serial numbers
        $pendingJobs = JobStatus::where('upload_progress_id', $progress->id)
            ->where('status', 'pending')
            ->pluck('serial_number')
            ->toArray();

        foreach ($pendingJobs as $serialNumber) {
            // Dispatch jobs based on action type
            if ($progress->action === 'set') {
                ProcessSerialJob::dispatch(
                    $serialNumber,
                    $progress->id,
                    $progress->nodePath,
                    $progress->action,
                    $progress->newValue,
                    $progress->nodeTypeDetailed,
                    $progress->deviceModel
                );
            } elseif ($progress->action === 'get') {
                ProcessSerialJobGet::dispatch(
                    $serialNumber,
                    $progress->id,
                    $progress->nodePath,
                    $progress->action,
                    null,
                    $progress->nodeTypeDetailed,
                    $progress->deviceModel
                );
            }
        }

        return response()->json(['status' => 'success', 'message' => 'Jobs resumed successfully.']);
    }


    public function stop($progressId)
    {
        $progress = UploadProgress::find($progressId);
        if (!$progress) {
            return response()->json(['status' => 'error', 'message' => 'Progress not found.'], 404);
        }

        $progress->status = 'stopped';
        $progress->deleted_at = now();
        $progress->save();

        return response()->json(['status' => 'success', 'message' => 'Job stopped successfully.']);
    }

    public function delete($progressId)
    {
        $progress = UploadProgress::find($progressId);
        if (!$progress) {
            return response()->json(['status' => 'error', 'message' => 'Progress not found.'], 404);
        }

        // Delete all related job statuses
        JobStatus::where('upload_progress_id', $progress->id)->delete();

        // Delete the progress record
        $progress->delete();

        return response()->json(['status' => 'success', 'message' => 'Progress and related jobs deleted successfully.']);
    }

    public function exportReport($progressId)
    {
        $progress = UploadProgress::find($progressId);
        if (!$progress) {
            return response()->json(['status' => 'error', 'message' => 'Progress not found.'], 404);
        }

        $filePath = storage_path('app/public/report_' . $progress->id . '.csv');

        // Open a file in write mode
        $file = fopen($filePath, 'w');

        // Write the header row
        fputcsv($file, ['Serial Number', 'Status', 'Response']);

        // Write each job's data to the CSV
        $jobs = JobStatus::where('upload_progress_id', $progress->id)->get();
        foreach ($jobs as $job) {
            fputcsv($file, [$job->serial_number, $job->status, $job->response]);
        }

        fclose($file);

        return response()->download($filePath, 'report_' . $progress->id . '.csv')->deleteFileAfterSend(true);
    }

    public function progress($progressId)
    {
        $progress = UploadProgress::find($progressId);
        if (!$progress) {
            return response()->json(['status' => 'error', 'message' => 'Progress not found.'], 404);
        }

        return response()->json([
            'status' => $progress->status,
            'processed' => $progress->processed,
            'success_count' => $progress->success_count,
            'fail_count' => $progress->fail_count,
            'not_found_count' => $progress->not_found_count,
        ]);
    }
}
