<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;

class ProjectSeeder extends Seeder
{
    public function run()
    {
        Project::create([
            'id' => 1,
            'name' => 'Tour di Roma',
            'description' => 'Tour guidato dei principali monumenti di Roma'
        ]);
    }
} 