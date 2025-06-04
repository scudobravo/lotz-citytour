<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class MonumentController extends Controller
{
    public function index(): JsonResponse
    {
        $points = DB::table('points_of_interest')
            ->select('name', 'latitude', 'longitude')
            ->get();
        return response()->json($points);
    }
} 