<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PointOfInterest;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Twilio\TwiML\MessagingResponse;

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
        Log::info('Twilio webhook ricevuto', [
            'request' => $request->all(),
            'from' => $request->input('From'),
            'body' => $request->input('Body'),
            'message_sid' => $request->input('MessageSid'),
            'headers' => $request->headers->all()
        ]);

        $from = $request->input('From');
        $body = $request->input('Body', '');
        $messageSid = $request->input('MessageSid');

        try {
            $response = new MessagingResponse();

            // Se il messaggio contiene un ID di progetto, invia la mappa
            if (preg_match('/project:(\d+)/', $body, $matches)) {
                Log::info('Comando project rilevato', ['matches' => $matches]);
                $projectId = $matches[1];
                $this->sendProjectMap($projectId, $response);
            }
            // Se il messaggio contiene un ID di punto di interesse, invia i dettagli
            elseif (preg_match('/point:(\d+)/', $body, $matches)) {
                Log::info('Comando point rilevato', ['matches' => $matches]);
                $pointId = $matches[1];
                $this->sendPointDetails($pointId, $response);
            }
            // Altrimenti, invia la lista dei progetti disponibili
            else {
                Log::info('Nessun comando specifico rilevato, invio lista progetti');
                $this->sendProjectsList($response);
            }

            $xml = $response->asXML();
            Log::info('Risposta TwiML generata', ['xml' => $xml]);

            return response($xml, 200)
                ->header('Content-Type', 'text/xml');

        } catch (\Exception $e) {
            Log::error('Errore nel processare il messaggio Twilio', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'body' => $body,
                'from' => $from
            ]);

            $response = new MessagingResponse();
            $this->safeAddMessage($response, 'Mi dispiace, si è verificato un errore. Riprova più tardi.');
            
            return response($response->asXML(), 200)
                ->header('Content-Type', 'text/xml');
        }
    }

    private function sendProjectsList(MessagingResponse $response)
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

        Log::info('Invio lista progetti', ['message' => $messageText]);
        $this->safeAddMessage($response, $messageText);
    }

    private function sendProjectMap($projectId, MessagingResponse $response)
    {
        $project = Project::find($projectId);
        if (!$project) {
            Log::warning('Progetto non trovato', ['project_id' => $projectId]);
            $this->safeAddMessage($response, "Tour non trovato.");
            return;
        }

        $points = PointOfInterest::where('project_id', $projectId)
                               ->orderBy('order_number')
                               ->get();

        if ($points->isEmpty()) {
            Log::warning('Nessun punto trovato per il progetto', ['project_id' => $projectId]);
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
        
        // I punti intermedi sono waypoints (massimo 8)
        $waypoints = [];
        foreach ($points as $index => $point) {
            if ($index > 0 && $index < $points->count() - 1 && count($waypoints) < 8) {
                $waypoints[] = $point->latitude . "," . $point->longitude;
            }
        }
        
        if (!empty($waypoints)) {
            $baseUrl .= "&waypoints=" . implode("|", $waypoints);
        }
        
        // Aggiungiamo i parametri per il percorso pedonale e la lingua italiana
        $baseUrl .= "&travelmode=walking&hl=it";
        
        $mapsUrl = $baseUrl;
        
        $messageText = "*{$project->name}* 🗺️\n\n";
        $messageText .= "Clicca qui per aprire la mappa con tutti i punti di interesse:\n";
        $messageText .= $mapsUrl . "\n\n";
        $messageText .= "Per iniziare la navigazione:\n";
        $messageText .= "1. Apri il link da smartphone\n";
        $messageText .= "2. Clicca su \"Indicazioni\"\n";
        $messageText .= "3. Seleziona \"A piedi\"\n\n";
        
        $messageText .= "Punti del tour:\n";
        foreach ($points as $index => $point) {
            $messageText .= "point:{$point->id} - {$point->name}\n";
            if ($point->description) {
                $messageText .= "   {$point->description}\n";
            }
            // Aggiungiamo l'immagine di placeholder
            $imageUrl = "https://placehold.co/600x400?text=" . urlencode($point->name);
            $this->safeAddMedia($response, $imageUrl, 'image');
            $messageText .= "\n";
        }

        Log::info('Invio mappa progetto', [
            'project_id' => $projectId,
            'points_count' => $points->count(),
            'message_length' => strlen($messageText)
        ]);
        $this->safeAddMessage($response, $messageText);
    }

    private function sendPointDetails($pointId, MessagingResponse $response)
    {
        Log::info('Invio dettagli punto', ['point_id' => $pointId]);
        
        $point = PointOfInterest::find($pointId);
        if (!$point) {
            Log::warning('Punto non trovato', ['point_id' => $pointId]);
            $this->safeAddMessage($response, "Punto di interesse non trovato.");
            return;
        }

        // 1. Prima inviamo il nome
        $nameMessage = $response->message("*{$point->name}* 📍");
        $nameMessage->setAttribute('format', 'html');
        Log::info('Nome punto inviato', ['name' => $point->name]);

        // 2. Poi inviamo l'immagine
        $imageUrl = $point->image_path ?? "https://placehold.co/600x400?text=" . urlencode($point->name);
        $imageMessage = $response->message('');
        $imageMessage->media($imageUrl);
        Log::info('Immagine punto inviata', ['url' => $imageUrl]);

        // 3. Poi inviamo la descrizione
        if ($point->description) {
            $descMessage = $response->message($point->description);
            $descMessage->setAttribute('format', 'html');
            Log::info('Descrizione punto inviata', ['description' => $point->description]);
        }

        // 4. Infine inviamo il link per tornare alla mappa
        $mapMessage = $response->message("Per tornare alla mappa, clicca qui:\nproject:{$point->project_id}");
        $mapMessage->setAttribute('format', 'html');
        Log::info('Link mappa inviato', ['project_id' => $point->project_id]);
    }

    private function safeAddMessage(MessagingResponse $response, string $messageText)
    {
        $message = $response->message($messageText);
        $message->setAttribute('format', 'html');
        Log::info('Messaggio aggiunto alla risposta', ['text' => $messageText]);
    }

    private function safeAddMedia(MessagingResponse $response, string $mediaUrl, string $type)
    {
        $media = $response->message('');
        $media->media($mediaUrl);
        Log::info('Media aggiunta alla risposta', ['url' => $mediaUrl, 'type' => $type]);
    }
}
