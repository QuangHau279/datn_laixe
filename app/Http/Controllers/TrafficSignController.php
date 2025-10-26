<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrafficSignCategory;
use App\Models\TrafficSign;

class TrafficSignController extends Controller
{
    public function index()
    {
        $categories = TrafficSignCategory::with('signs')->get();
        return view('traffic-signs.index', compact('categories'));
    }

    public function show($slug)
    {
        $category = TrafficSignCategory::where('slug', $slug)->with('signs')->firstOrFail();
        return view('traffic-signs.show', compact('category'));
    }
}
