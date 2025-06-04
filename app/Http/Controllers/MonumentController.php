<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MonumentController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $points = DB::table('points_of_interest')
                ->select('id', 'name', 'latitude', 'longitude', 'description')
                ->get();

            Log::info('Punti di interesse recuperati:', ['count' => $points->count()]);
            
            return response()->json($points);
        } catch (\Exception $e) {
            Log::error('Errore nel recupero dei punti di interesse:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Errore nel recupero dei punti di interesse',
                'message' => $e->getMessage()
            ], 500);
        }
    }
} 