<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\DeviceModel; // Import the DeviceModel
use App\Models\DataModelNode;
use App\Models\Node; // Import the Node model
use App\Http\Controllers\LogController;
use Illuminate\Support\Facades\Storage;

class ModelController extends Controller
{
    //

    public function index()
    {
        LogController::saveLog("models_managment", "User opened models managmanet page ");

        $deviceModels = DeviceModel::all();
        return view('Models.modelsManagment', compact('deviceModels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'model_name' => 'required|string|max:255',
            'product_class' => 'required|string|max:255',
            'oui' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'cs_nodes_csv' => 'nullable|mimes:csv,txt|max:2048',
            'data_model_file' => 'nullable|mimes:csv|max:4096',
        ]);
    
        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('device_models', 'public');
        }
    
        // Save the new device model
        $deviceModel = DeviceModel::create([
            'model_name' => $request->model_name,
            'product_class' => $request->product_class,
            'oui' => $request->oui,
            'image' => $imagePath,
        ]);
    
        // Process CS Nodes CSV
        if ($request->hasFile('cs_nodes_file')) {
            $csvFile = $request->file('cs_nodes_file');
            $fileHandle = fopen($csvFile->getRealPath(), 'r');
        
            if ($fileHandle !== false) {
                fgetcsv($fileHandle); // Skip the header row
                while (($row = fgetcsv($fileHandle, 1000, ',')) !== false) {
                    if (count($row) < 4) {
                        continue; // 🚨 Prevent errors if the row doesn't have enough columns
                    }
        
                    Node::create([
                        'device_model_id' => $deviceModel->id, // Ensure this links to the right model
                        'name' => trim($row[0]), // ✅ Trim to remove extra spaces
                        'path' => trim($row[1]),
                        'type' => trim($row[2]) ?? null,
                        'category' => trim($row[3]),
                    ]);
                }
                fclose($fileHandle);
            }
        }
        
    
        // Process DataModel CSV
        if ($request->hasFile('data_model_file')) {
            $dataModelFile = $request->file('data_model_file');
            $fileHandle = fopen($dataModelFile->getRealPath(), 'r');
    
            if ($fileHandle !== false) {
                fgetcsv($fileHandle); // Skip the header row
                while (($row = fgetcsv($fileHandle, 1000, ',')) !== false) {
                    if (count($row) >= 3) {
                        DataModelNode::create([
                            'device_model_id' => $deviceModel->id,
                            'name' => $row[0],
                            'path' => $row[1],
                            'type' => $row[2] ?? null,
                        ]);
                    }
                }
                fclose($fileHandle);
            }
        }
    
        LogController::saveLog("model_upload", "User uploaded new model {$request->$model_name}");

        return redirect()->back()->with('success', 'Model and DataModel uploaded successfully.');
    }
    

    public function edit($id)
    {
        $deviceModel = DeviceModel::with('nodes')->findOrFail($id);
        $deviceModels = DeviceModel::all(); // To maintain the list of models on the side
        return view('Models.modelEdit', compact('deviceModel', 'deviceModels'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'model_name' => 'required|string|max:255',
            'product_class' => 'required|string|max:255',
            'oui' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        $deviceModel = DeviceModel::findOrFail($id);
        
        // Update device model data
        $deviceModel->model_name = $request->input('model_name');
        $deviceModel->product_class = $request->input('product_class');
        $deviceModel->oui = $request->input('oui');

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($deviceModel->image) {
                Storage::delete($deviceModel->image);
            }
            
            $path = $request->file('image')->store('device_models', 'public');
            $deviceModel->image = $path;
        }
        
        $deviceModel->save();

        // Update CS Nodes
        $deviceModel->nodes()->delete(); // Remove old nodes

        if ($request->has('cs_nodes')) {
            foreach ($request->cs_nodes as $nodeData) {
                Node::create([
                    'device_model_id' => $deviceModel->id,
                    'name' => $nodeData['name'],
                    'path' => $nodeData['path'],
                    'type' => $nodeData['type'] ?? null,
                    'category' => $nodeData['category'],
                ]);
            }
        }

        return redirect()->route('device-models.index')->with('success', 'Device model updated successfully.');
    }

    public function destroy($id)
    {
        $deviceModel = DeviceModel::findOrFail($id);
        
        // Delete associated nodes first
        $deviceModel->nodes()->delete();
        
        // Delete the model
        $deviceModel->delete();

        LogController::saveLog("model_upload", "User delete mdoel {$id}");
        return redirect()->back()->with('success', 'Device model deleted successfully.');
    }
    
}
