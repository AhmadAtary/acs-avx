<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Node;
use App\Models\DataModelNode;

class NodeController extends Controller
{
    
    public function getNodes($modelId)
    {
        
        // Retrieve DataModel Nodes for the given device model
        $dataModelNodes = DataModelNode::where('device_model_id', $modelId)
                                        ->select('name', 'path', 'type')
                                        ->get();
    
        // Return JSON response with only DataModel Nodes
        return response()->json($dataModelNodes);
    }
    
    
}
