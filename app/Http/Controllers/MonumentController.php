<?php

namespace App\Http\Controllers;

use App\Models\Monument;
use Illuminate\Http\JsonResponse;

class MonumentController extends Controller
{
    public function index(): JsonResponse
    {
        $monuments = Monument::select('name', 'latitude', 'longitude')->get();
        return response()->json($monuments);
    }
} 