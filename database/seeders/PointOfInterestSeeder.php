<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PointOfInterest;

class PointOfInterestSeeder extends Seeder
{
    public function run()
    {
        $points = [
            [
                'order_number' => 1,
                'name' => 'Biblioteca Comunale',
                'latitude' => 41.9042,
                'longitude' => 12.4963,
                'description' => 'La biblioteca comunale, un importante centro culturale della città.',
                'project_id' => 1
            ],
            [
                'order_number' => 2,
                'name' => 'Chiesa di Sant\'Andrea Apostolo',
                'latitude' => 41.9175,
                'longitude' => 12.4642,
                'description' => 'Antica chiesa dedicata a Sant\'Andrea Apostolo.',
                'project_id' => 1
            ],
            [
                'order_number' => 3,
                'name' => 'Arco Trionfale',
                'latitude' => 41.8986,
                'longitude' => 12.4814,
                'description' => 'Imponente arco trionfale che celebra le vittorie dell\'antica Roma.',
                'project_id' => 1
            ],
            [
                'order_number' => 4,
                'name' => 'Piazzetta Pietra Sprecata',
                'latitude' => 41.8963,
                'longitude' => 12.4338,
                'description' => 'Caratteristica piazzetta con una pietra storica al centro.',
                'project_id' => 1
            ],
            [
                'order_number' => 5,
                'name' => 'Fontana Barberini',
                'latitude' => 41.9036,
                'longitude' => 12.4883,
                'description' => 'Splendida fontana barocca progettata da Gian Lorenzo Bernini.',
                'project_id' => 1
            ],
            [
                'order_number' => 6,
                'name' => 'Chiesa di San Pietro',
                'latitude' => 41.9022,
                'longitude' => 12.4539,
                'description' => 'La più grande chiesa del mondo, capolavoro dell\'architettura rinascimentale.',
                'project_id' => 1
            ],
            [
                'order_number' => 7,
                'name' => 'Borgo Medievale degli Opifici',
                'latitude' => 41.9007,
                'longitude' => 12.4565,
                'description' => 'Antico quartiere medievale con botteghe artigiane.',
                'project_id' => 1
            ],
            [
                'order_number' => 8,
                'name' => 'Ponte di San Francesco',
                'latitude' => 41.9047,
                'longitude' => 12.4450,
                'description' => 'Ponte storico dedicato a San Francesco d\'Assisi.',
                'project_id' => 1
            ],
            [
                'order_number' => 9,
                'name' => 'Convento di San Francesco',
                'latitude' => 41.9005,
                'longitude' => 12.4719,
                'description' => 'Antico convento francescano con importanti opere d\'arte.',
                'project_id' => 1
            ],
            [
                'order_number' => 10,
                'name' => 'Chiesa Madonna della Croce',
                'latitude' => 41.8979,
                'longitude' => 12.4770,
                'description' => 'Chiesa dedicata alla Madonna della Croce.',
                'project_id' => 1
            ],
            [
                'order_number' => 11,
                'name' => 'Chiesa di Santa Maria della Valle',
                'latitude' => 41.9006,
                'longitude' => 12.4692,
                'description' => 'Chiesa storica dedicata a Santa Maria della Valle.',
                'project_id' => 1
            ]
        ];

        foreach ($points as $point) {
            PointOfInterest::create($point);
        }
    }
} 