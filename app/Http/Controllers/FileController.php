<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\File;
use App\Models\DModel;

class FileController extends Controller
{
    // Display all files
    public function index()
    {
        $models = DModel::all();
        $files = File::all(); // Fetch all files from the collection
        return view('files.indexFiles', compact('files','models'));
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
          ->put("https://10.223.169.1:7557/files/" . $originalFileName);

        // Delete the file after upload attempt
        Storage::delete($path);

        if ($response->successful()) {
            return back()->with('success', 'File uploaded successfully.');
        } else {
            return back()->with('error', 'Upload failed: ' . $response->status());
        }
    } catch (\Exception $e) {
        return back()->with('error', 'File upload failed: ' . $e->getMessage());
    }
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

        return redirect()->back()->with('success', 'File updated successfully!');
    }


    // Delete a file
    public function destroy($id)
    {
        $file = File::findOrFail($id);
        $file->delete();

        return redirect()->route('files.index')->with('success', 'File deleted successfully!');
    }
}
