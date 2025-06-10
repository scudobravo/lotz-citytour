<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PointOfInterest;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TwilioController extends Controller
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

    public function handleIncomingMessage(Request $request)
    {
        Log::info('Twilio incoming message received', [
            'request' => $request->all(),
            'from' => $request->input('From'),
            'body' => $request->input('Body'),
            'message_sid' => $request->input('MessageSid')
        ]);

        $from = $request->input('From');
        $body = $request->input('Body', '');
        $messageSid = $request->input('MessageSid');

        try {
            $response = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Response></Response>');

            // Se il messaggio contiene un ID di progetto, invia la mappa
            if (preg_match('/project:(\d+)/', $body, $matches)) {
                $projectId = $matches[1];
                $this->sendProjectMap($projectId, $response);
            }
            // Se il messaggio contiene un ID di punto di interesse, invia i dettagli
            elseif (preg_match('/point:(\d+)/', $body, $matches)) {
                $pointId = $matches[1];
                $this->sendPointDetails($pointId, $response);
            }
            // Altrimenti, invia la lista dei progetti disponibili
            else {
                $this->sendProjectsList($response);
            }

            return response($response->asXML(), 200)
                ->header('Content-Type', 'text/xml');

        } catch (\Exception $e) {
            Log::error('Error processing Twilio message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $response = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Response></Response>');
            $this->safeAddMessage($response, 'Mi dispiace, si è verificato un errore. Riprova più tardi.');
            
            return response($response->asXML(), 200)
                ->header('Content-Type', 'text/xml');
        }
    }

    private function sendProjectsList(\SimpleXMLElement $response)
    {
        $projects = Project::all();
        
        $messageText = "Benvenuto nel City Tour! 🗺️\n\n";
        $messageText .= "Ecco i tour disponibili:\n\n";
        
        foreach ($projects as $project) {
            $messageText .= "project:{$project->id} - {$project->name}\n";
            if ($project->description) {
                $messageText .= "   {$project->description}\n";
            }
            $messageText .= "\n";
        }

        $this->safeAddMessage($response, $messageText);
    }

    private function sendProjectMap($projectId, \SimpleXMLElement $response)
    {
        $project = Project::find($projectId);
        if (!$project) {
            $this->safeAddMessage($response, "Tour non trovato.");
            return;
        }

        $points = PointOfInterest::where('project_id', $projectId)
                               ->orderBy('order_number')
                               ->get();

        if ($points->isEmpty()) {
            $this->safeAddMessage($response, "Nessun punto di interesse trovato per questo tour.");
            return;
        }

        // Costruiamo il link di Google Maps con tutti i punti
        $baseUrl = "https://www.google.com/maps/dir/?api=1";
        
        // Il primo punto è l'origine
        $origin = $points->first();
        $baseUrl .= "&origin=" . $origin->latitude . "," . $origin->longitude;
        
        // L'ultimo punto è la destinazione
        $destination = $points->last();
        $baseUrl .= "&destination=" . $destination->latitude . "," . $destination->longitude;
        
        // I punti intermedi sono waypoints
        $waypoints = [];
        foreach ($points as $index => $point) {
            if ($index > 0 && $index < $points->count() - 1) {
                $waypoints[] = $point->latitude . "," . $point->longitude;
            }
        }
        
        if (!empty($waypoints)) {
            $baseUrl .= "&waypoints=" . implode("|", $waypoints);
        }
        
        // Aggiungiamo il parametro per il percorso pedonale
        $baseUrl .= "&travelmode=walking";
        
        $mapsUrl = $baseUrl;
        
        $messageText = "*{$project->name}* 🗺️\n\n";
        $messageText .= "Clicca sul link qui sotto per aprire la mappa con tutti i punti di interesse:\n";
        $messageText .= $mapsUrl . "\n\n";
        
        $messageText .= "Punti del tour:\n";
        foreach ($points as $index => $point) {
            $messageText .= "point:{$point->id} - {$point->name}\n";
            if ($point->description) {
                $messageText .= "   {$point->description}\n";
            }
            $messageText .= "\n";
        }

        $this->safeAddMessage($response, $messageText);
    }

    private function sendPointDetails($pointId, \SimpleXMLElement $response)
    {
        $point = PointOfInterest::find($pointId);
        if (!$point) {
            $this->safeAddMessage($response, "Punto di interesse non trovato.");
            return;
        }

        $messageText = "*{$point->name}* 📍\n\n";
        
        if ($point->description) {
            $messageText .= "{$point->description}\n\n";
        }

        // Aggiungi l'immagine se presente
        if ($point->image_path) {
            $this->safeAddMedia($response, $point->image_path, 'image');
        }

        // Aggiungi l'audio se presente
        if ($point->audio_path) {
            $this->safeAddMedia($response, $point->audio_path, 'audio');
        }

        // Aggiungi il link per tornare alla mappa
        $messageText .= "\nPer tornare alla mappa, clicca qui:\n";
        $messageText .= "project:{$point->project_id}";

        $this->safeAddMessage($response, $messageText);
    }

    private function safeAddMessage(\SimpleXMLElement $response, string $messageText)
    {
        $response->addChild('Message', $messageText);
    }

    private function safeAddMedia(\SimpleXMLElement $response, string $mediaUrl, string $type)
    {
        $media = $response->addChild('Message');
        $media->addChild($type, $mediaUrl);
    }
}
