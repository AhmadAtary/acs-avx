<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\DeviceModel; // Import the DeviceModel
use App\Models\Node; // Import the Node model
use Illuminate\Support\Facades\Storage;

class ModelController extends Controller
{
    //

    public function index()
    {
        $deviceModels = DeviceModel::all();
        return view('Models.modelsManagment', compact('deviceModels'));
    }

    public function store(Request $request)
    {
        // dd($request);
        // Validate the request
        $request->validate([
            'model_name' => 'required|string|max:255',
            'product_class' => 'required|string|max:255',
            'oui' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'cs_nodes' => 'array',
            'cs_nodes.*.name' => 'required|string|max:255',
            'cs_nodes.*.path' => 'required|string|max:255',
            'cs_nodes.*.type' => 'nullable|string|max:255',
            'cs_nodes.*.category' => 'required|string|max:255',
        ]);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('device_models', 'public');
        }

        // Save the new model to the database
        $deviceModel = DeviceModel::create([
            'model_name' => $request->model_name,
            'product_class' => $request->product_class,
            'oui' => $request->oui,
            'image' => $imagePath,
        ]);

        // Save CS Nodes
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

        return redirect()->back()->with('success', 'Model added successfully.');
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

        return redirect()->back()->with('success', 'Device model deleted successfully.');
    }
    
}
