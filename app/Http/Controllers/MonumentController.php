<?php

namespace App\Http\Controllers;

use App\Models\PointOfInterest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Twilio\TwiML\MessagingResponse;

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

            // Crea la risposta TwiML
            $response = new MessagingResponse();

            // 1. Nome del punto
            $nameMessage = $response->message("*{$point->name}* 📍");
            $nameMessage->setAttribute('format', 'html');

            // 2. Immagine (se presente)
            if ($point->image_path) {
                $imageUrl = $point->image_path;
                $imageMessage = $response->message('');
                $imageMessage->media($imageUrl);
                Log::info('Immagine aggiunta', ['url' => $imageUrl]);
            }

            // 3. Descrizione
            if ($point->description) {
                $descMessage = $response->message($point->description);
                $descMessage->setAttribute('format', 'html');
            }

            // 4. Link per tornare alla mappa
            $mapMessage = $response->message("Per tornare alla mappa, clicca qui:\nproject:{$point->project_id}");
            $mapMessage->setAttribute('format', 'html');

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