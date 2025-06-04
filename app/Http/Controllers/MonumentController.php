<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MonumentController extends Controller
{
    public function index()
    {
        $monuments = [
            [
                'id' => 1,
                'name' => [
                    'it' => 'Colosseo',
                    'en' => 'Colosseum',
                    'es' => 'Coliseo',
                    'fr' => 'Colisée'
                ],
                'description' => [
                    'it' => 'Anfiteatro flavio, il più grande del mondo romano.',
                    'en' => 'Flavian Amphitheatre, the largest of the Roman world.',
                    'es' => 'Anfiteatro Flavio, el más grande del mundo romano.',
                    'fr' => 'Amphithéâtre Flavien, le plus grand du monde romain.'
                ],
                'lat' => 41.8902,
                'lng' => 12.4922,
                'image' => 'colosseo.jpg'
            ],
            [
                'id' => 2,
                'name' => [
                    'it' => 'Fontana di Trevi',
                    'en' => 'Trevi Fountain',
                    'es' => 'Fuente de Trevi',
                    'fr' => 'Fontaine de Trevi'
                ],
                'description' => [
                    'it' => 'La più grande e famosa fontana di Roma.',
                    'en' => 'The largest and most famous fountain in Rome.',
                    'es' => 'La fuente más grande y famosa de Roma.',
                    'fr' => 'La plus grande et la plus célèbre fontaine de Rome.'
                ],
                'lat' => 41.9009,
                'lng' => 12.4833,
                'image' => 'trevi.jpg'
            ],
            // Aggiungerò altri monumenti in seguito
        ];

        return response()->json($monuments);
    }
} 