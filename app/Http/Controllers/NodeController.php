<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Node;

class NodeController extends Controller
{
    
    public function getNodes($modelId)
    {
        $nodes = Node::where('Model', $modelId)->select('Path', 'NodeType')->get();
        return response()->json($nodes);
    }
}
