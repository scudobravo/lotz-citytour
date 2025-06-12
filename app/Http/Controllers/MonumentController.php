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

            // Prepara il messaggio XML per Twilio
            $response = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Response></Response>');

            // 1. Prima inviamo il nome
            $response->addChild('Message', "*{$point->name}* 📍");

            // 2. Poi inviamo l'immagine
            $imageUrl = $point->image_path ?? "https://placehold.co/600x400?text=" . urlencode($point->name);
            $media = $response->addChild('Message');
            $media->addChild('image', $imageUrl);

            // 3. Poi inviamo la descrizione
            if ($point->description) {
                $response->addChild('Message', "\n{$point->description}");
            }

            // 4. Infine inviamo il link per tornare alla mappa
            $response->addChild('Message', "\n\nPer tornare alla mappa, clicca qui:\nproject:{$point->project_id}");

            // Invia il messaggio a Twilio tramite API
            $twilioUrl = "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Messages.json";
            
            $response = Http::withBasicAuth($this->accountSid, $this->authToken)
                ->post($twilioUrl, [
                    'From' => $this->twilioNumber,
                    'To' => $this->whatsappNumber,
                    'Body' => $response->asXML()
                ]);

            if ($response->successful()) {
                Log::info('Messaggio Twilio inviato', [
                    'point_id' => $id,
                    'response' => $response->json()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Messaggio inviato con successo'
                ]);
            } else {
                throw new \Exception('Errore nell\'invio del messaggio: ' . $response->body());
            }

        } catch (\Exception $e) {
            Log::error('Errore nell\'invio del messaggio:', [
                'point_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Errore nell\'invio del messaggio',
                'message' => $e->getMessage()
            ], 500);
        }
    }
} 