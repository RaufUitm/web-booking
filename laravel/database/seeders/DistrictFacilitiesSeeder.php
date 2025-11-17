<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Facility;
use App\Models\Category;

class DistrictFacilitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing categories
        $categories = Category::all();

        if ($categories->isEmpty()) {
            $this->command->info('No categories found. Please run the categories seeder first.');
            return;
        }

        $dewan = $categories->where('name', 'Dewan')->first();
        $sukan = $categories->where('name', 'Sukan & Rekreasi')->first();
        $bilik = $categories->where('name', 'Bilik Mesyuarat')->first();

        // BESUT Facilities
        $besutFacilities = [
            [
                'name' => 'Dewan Serbaguna Besut',
                'description' => 'Dewan serbaguna dengan kapasiti 300 orang.',
                'category_id' => $dewan->id ?? 1,
                'price_per_hour' => 120,
                'capacity' => 300,
                'location' => 'Jertih, Besut',
                'is_available' => true,
            ],
            [
                'name' => 'Gelanggang Futsal Besut',
                'description' => 'Gelanggang futsal indoor.',
                'category_id' => $sukan->id ?? 2,
                'price_per_hour' => 50,
                'capacity' => 20,
                'location' => 'Jertih, Besut',
                'is_available' => true,
            ],
            [
                'name' => 'Bilik Mesyuarat MDB',
                'description' => 'Bilik mesyuarat dengan kapasiti 30 orang.',
                'category_id' => $bilik->id ?? 3,
                'price_per_hour' => 60,
                'capacity' => 30,
                'location' => 'Pejabat MDB, Jertih',
                'is_available' => true,
            ],
        ];

        // MARANG Facilities
        $marangFacilities = [
            [
                'name' => 'Dewan Komuniti Marang',
                'description' => 'Dewan komuniti berkapasiti 250 orang.',
                'category_id' => $dewan->id ?? 1,
                'price_per_hour' => 100,
                'capacity' => 250,
                'location' => 'Marang',
                'is_available' => true,
            ],
            [
                'name' => 'Padang Bola MDM',
                'description' => 'Padang bola sepak dengan rumput berkualiti.',
                'category_id' => $sukan->id ?? 2,
                'price_per_hour' => 80,
                'capacity' => 50,
                'location' => 'Marang',
                'is_available' => true,
            ],
            [
                'name' => 'Bilik Seminar MDM',
                'description' => 'Bilik seminar dengan kapasiti 50 orang.',
                'category_id' => $bilik->id ?? 3,
                'price_per_hour' => 70,
                'capacity' => 50,
                'location' => 'Pejabat MDM',
                'is_available' => true,
            ],
        ];

        // SETIU Facilities
        $setiuFacilities = [
            [
                'name' => 'Dewan Serbaguna Setiu',
                'description' => 'Dewan serbaguna dengan kapasiti 200 orang.',
                'category_id' => $dewan->id ?? 1,
                'price_per_hour' => 90,
                'capacity' => 200,
                'location' => 'Permaisuri, Setiu',
                'is_available' => true,
            ],
            [
                'name' => 'Gelanggang Badminton MDS',
                'description' => 'Gelanggang badminton indoor dengan 4 court.',
                'category_id' => $sukan->id ?? 2,
                'price_per_hour' => 40,
                'capacity' => 40,
                'location' => 'Permaisuri, Setiu',
                'is_available' => true,
            ],
            [
                'name' => 'Bilik Mesyuarat Setiu',
                'description' => 'Bilik mesyuarat kecil untuk 20 orang.',
                'category_id' => $bilik->id ?? 3,
                'price_per_hour' => 50,
                'capacity' => 20,
                'location' => 'Pejabat MDS',
                'is_available' => true,
            ],
        ];

        // Insert facilities
        foreach ($besutFacilities as $facility) {
            Facility::create(array_merge($facility, ['district' => 'Besut']));
        }
        $this->command->info('Created facilities for Besut');

        foreach ($marangFacilities as $facility) {
            Facility::create(array_merge($facility, ['district' => 'Marang']));
        }
        $this->command->info('Created facilities for Marang');

        foreach ($setiuFacilities as $facility) {
            Facility::create(array_merge($facility, ['district' => 'Setiu']));
        }
        $this->command->info('Created facilities for Setiu');
    }
}
