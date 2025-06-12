<?php

namespace App\Http\Controllers;

use App\Models\PointOfInterest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class MonumentController extends Controller
{
    private $accountSid;
    private $authToken;
    private $twilioNumber;
    private $whatsappNumber;

    public function __construct()
    {
        $this->accountSid = config('services.twilio.account_sid');
        $this->authToken = config('services.twilio.auth_token');
        $this->twilioNumber = config('services.twilio.phone_number');
        $this->whatsappNumber = config('services.twilio.whatsapp_number');
    }

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

    public function show($id, Request $request)
    {
        try {
            $point = PointOfInterest::findOrFail($id);
            $lang = $request->get('lang', 'it');

            // Log della richiesta
            Log::info('Richiesta dettagli punto di interesse', [
                'point_id' => $id,
                'lang' => $lang,
                'point' => $point->toArray()
            ]);

            // Reindirizza a WhatsApp con il comando point
            $twilioNumber = config('services.twilio.whatsapp_number');
            $whatsappNumber = str_replace('whatsapp:', '', $twilioNumber);
            $message = "point:{$id}";
            
            $whatsappUrl = "https://wa.me/{$whatsappNumber}?text=" . urlencode($message);
            
            return redirect($whatsappUrl);

        } catch (\Exception $e) {
            Log::error('Errore nel recupero del punto di interesse:', [
                'point_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Punto di interesse non trovato',
                'message' => $e->getMessage()
            ], 404);
        }
    }
} 