<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\DModel;

class ModelController extends Controller
{
    //

    
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'Model' => 'required|string|max:255',
            'Product_Class' => 'required|string|max:255',
        ]);
    
        // Check if the model or product class already exists
        $existingModel = DModel::where('Model', $request->input('Model'))
            ->orWhere('Product_Class', $request->input('Product_Class'))
            ->first();
    
        if ($existingModel) {
            return redirect()->back()->with('error', 'Model or Product Class already exists.');
        }
    
        // Save the new model to the database
        $dModel = new DModel();
        $dModel->Model = $request->input('Model');
        $dModel->Product_Class = $request->input('Product_Class');
        $dModel->save();
    
        return redirect()->back()->with('success', 'Model added successfully.');
    }
    
}
