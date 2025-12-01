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
                'name' => 'Dewan Serbaguna MDB Jertih',
                'description' => 'Dewan serbaguna di Majlis Daerah Besut dengan kapasiti 300 orang, lengkap dengan sistem audio dan penghawa dingin.',
                'category_id' => $dewan->id ?? 1,
                'price_per_hour' => 120,
                'price_per_day' => 800,
                'capacity' => 300,
                'location' => 'Pejabat MDB, Jertih, Besut',
                'is_available' => true,
            ],
            [
                'name' => 'Stadium Mini Jertih',
                'description' => 'Stadium mini dengan padang rumput sintetik, lampu malam dan tempat duduk untuk penonton.',
                'category_id' => $sukan->id ?? 2,
                'price_per_hour' => 80,
                'price_per_day' => 500,
                'capacity' => 50,
                'location' => 'Jertih, Besut',
                'is_available' => true,
            ],
            [
                'name' => 'Gelanggang Futsal Besut',
                'description' => 'Gelanggang futsal indoor berhawa dingin dengan lantai berkualiti.',
                'category_id' => $sukan->id ?? 2,
                'price_per_hour' => 50,
                'price_per_day' => 300,
                'capacity' => 20,
                'location' => 'Jertih, Besut',
                'is_available' => true,
            ],
            [
                'name' => 'Bilik Mesyuarat Utama MDB',
                'description' => 'Bilik mesyuarat utama dengan kemudahan projektor dan sistem audio.',
                'category_id' => $bilik->id ?? 3,
                'price_per_hour' => 60,
                'price_per_day' => 400,
                'capacity' => 30,
                'location' => 'Pejabat MDB, Jertih',
                'is_available' => true,
            ],
            [
                'name' => 'Dewan Komuniti Kampung Raja',
                'description' => 'Dewan komuniti untuk majlis dan aktiviti masyarakat setempat.',
                'category_id' => $dewan->id ?? 1,
                'price_per_hour' => 70,
                'price_per_day' => 450,
                'capacity' => 150,
                'location' => 'Kampung Raja, Besut',
                'is_available' => true,
            ],
        ];

        // MARANG Facilities
        $marangFacilities = [
            [
                'name' => 'Dewan Serbaguna MDM',
                'description' => 'Dewan serbaguna Majlis Daerah Marang dengan kapasiti 250 orang, sesuai untuk majlis perkahwinan dan seminar.',
                'category_id' => $dewan->id ?? 1,
                'price_per_hour' => 100,
                'price_per_day' => 650,
                'capacity' => 250,
                'location' => 'Pejabat MDM, Marang',
                'is_available' => true,
            ],
            [
                'name' => 'Kompleks Sukan Marang',
                'description' => 'Kompleks sukan dengan padang bola sepak, gelanggang badminton dan trek larian.',
                'category_id' => $sukan->id ?? 2,
                'price_per_hour' => 80,
                'price_per_day' => 500,
                'capacity' => 50,
                'location' => 'Marang',
                'is_available' => true,
            ],
            [
                'name' => 'Gelanggang Badminton Marang',
                'description' => 'Gelanggang badminton indoor dengan 3 court dan penghawa dingin.',
                'category_id' => $sukan->id ?? 2,
                'price_per_hour' => 40,
                'price_per_day' => 250,
                'capacity' => 30,
                'location' => 'Marang',
                'is_available' => true,
            ],
            [
                'name' => 'Bilik Seminar MDM',
                'description' => 'Bilik seminar lengkap dengan projektor dan sistem audio untuk 50 orang.',
                'category_id' => $bilik->id ?? 3,
                'price_per_hour' => 70,
                'capacity' => 50,
                'location' => 'Pejabat MDM, Marang',
                'is_available' => true,
            ],
            [
                'name' => 'Dewan Bukit Bayas',
                'description' => 'Dewan komuniti di kawasan Bukit Bayas dengan kapasiti 120 orang.',
                'category_id' => $dewan->id ?? 1,
                'price_per_hour' => 60,
                'price_per_day' => 400,
                'capacity' => 120,
                'location' => 'Bukit Bayas, Marang',
                'is_available' => true,
            ],
        ];

        // SETIU Facilities
        $setiuFacilities = [
            [
                'name' => 'Dewan Serbaguna MDS Permaisuri',
                'description' => 'Dewan serbaguna Majlis Daerah Setiu dengan kapasiti 200 orang dan sistem audio visual.',
                'category_id' => $dewan->id ?? 1,
                'price_per_hour' => 90,
                'price_per_day' => 600,
                'capacity' => 200,
                'location' => 'Permaisuri, Setiu',
                'is_available' => true,
            ],
            [
                'name' => 'Kompleks Sukan Setiu',
                'description' => 'Kompleks sukan dengan gelanggang badminton indoor 4 court dan padang futsal.',
                'category_id' => $sukan->id ?? 2,
                'price_per_hour' => 40,
                'price_per_day' => 250,
                'capacity' => 40,
                'location' => 'Permaisuri, Setiu',
                'is_available' => true,
            ],
            [
                'name' => 'Padang Bola Sepak Setiu',
                'description' => 'Padang bola sepak dengan rumput semula jadi dan lampu malam.',
                'category_id' => $sukan->id ?? 2,
                'price_per_hour' => 70,
                'price_per_day' => 450,
                'capacity' => 50,
                'location' => 'Permaisuri, Setiu',
                'is_available' => true,
            ],
            [
                'name' => 'Bilik Mesyuarat MDS',
                'description' => 'Bilik mesyuarat dengan kemudahan projektor LCD.',
                'category_id' => $bilik->id ?? 3,
                'price_per_hour' => 50,
                'price_per_day' => 300,
                'capacity' => 25,
                'location' => 'Pejabat MDS, Permaisuri',
                'is_available' => true,
            ],
            [
                'name' => 'Dewan Chalok',
                'description' => 'Dewan komuniti di kawasan Chalok untuk aktiviti masyarakat.',
                'category_id' => $dewan->id ?? 1,
                'price_per_hour' => 60,
                'price_per_day' => 400,
                'capacity' => 100,
                'location' => 'Chalok, Setiu',
                'is_available' => true,
            ],
        ];

        // KUALA TERENGGANU (MBKT) Facilities
        $mbktFacilities = [
            [
                'name' => 'Dewan Bandaraya Kuala Terengganu',
                'description' => 'Dewan bandaraya megah dengan kapasiti 500 orang, dilengkapi sistem audio visual HD, penghawa dingin dan tempat letak kereta yang luas.',
                'category_id' => $dewan->id ?? 1,
                'price_per_hour' => 200,
                'price_per_day' => 1400,
                'capacity' => 500,
                'location' => 'Wisma MBKT, Kuala Terengganu',
                'is_available' => true,
            ],
            [
                'name' => 'Stadium Sultan Mizan Zainal Abidin',
                'description' => 'Stadium utama dengan padang berumput berkualiti antarabangsa dan kapasiti penonton besar.',
                'category_id' => $sukan->id ?? 2,
                'price_per_hour' => 300,
                'price_per_day' => 2000,
                'capacity' => 200,
                'location' => 'Gong Badak, Kuala Terengganu',
                'is_available' => true,
            ],
            [
                'name' => 'Kompleks Sukan Batu Buruk',
                'description' => 'Kompleks sukan lengkap dengan gelanggang futsal indoor, badminton, squash dan gimnasium.',
                'category_id' => $sukan->id ?? 2,
                'price_per_hour' => 100,
                'price_per_day' => 650,
                'capacity' => 100,
                'location' => 'Batu Buruk, Kuala Terengganu',
                'is_available' => true,
            ],
            [
                'name' => 'Dewan Jubli Perak',
                'description' => 'Dewan serba guna berkapasiti 300 orang dengan kemudahan moden.',
                'category_id' => $dewan->id ?? 1,
                'price_per_hour' => 150,
                'price_per_day' => 1000,
                'capacity' => 300,
                'location' => 'Kuala Terengganu',
                'is_available' => true,
            ],
            [
                'name' => 'Bilik Mesyuarat Eksekutif MBKT',
                'description' => 'Bilik mesyuarat eksekutif dengan video conferencing dan smart board.',
                'category_id' => $bilik->id ?? 3,
                'price_per_hour' => 150,
                'price_per_day' => 1000,
                'capacity' => 40,
                'location' => 'Wisma MBKT, Kuala Terengganu',
                'is_available' => true,
            ],
            [
                'name' => 'Gelanggang Futsal Gong Badak',
                'description' => 'Gelanggang futsal indoor berhawa dingin dengan lantai berkualiti tinggi.',
                'category_id' => $sukan->id ?? 2,
                'price_per_hour' => 80,
                'price_per_day' => 500,
                'capacity' => 20,
                'location' => 'Gong Badak, Kuala Terengganu',
                'is_available' => true,
            ],
        ];

        // KEMAMAN (MPK) Facilities
        $kemanFacilities = [
            [
                'name' => 'Dewan Balairaya Chukai',
                'description' => 'Dewan besar dengan kapasiti 400 orang untuk majlis perkahwinan, persidangan dan acara rasmi. Dilengkapi dengan pentas dan sistem audio berkualiti.',
                'category_id' => $dewan->id ?? 1,
                'price_per_hour' => 180,
                'price_per_day' => 1200,
                'capacity' => 400,
                'location' => 'Chukai, Kemaman',
                'is_available' => true,
            ],
            [
                'name' => 'Kompleks Sukan Kertih',
                'description' => 'Kompleks sukan moden dengan gelanggang badminton 6 court, futsal dan fasiliti gimnasium.',
                'category_id' => $sukan->id ?? 2,
                'price_per_hour' => 90,
                'price_per_day' => 600,
                'capacity' => 80,
                'location' => 'Kertih, Kemaman',
                'is_available' => true,
            ],
            [
                'name' => 'Stadium Mini Kemaman',
                'description' => 'Stadium mini dengan padang rumput tiruan untuk bola sepak dan acara sukan luar.',
                'category_id' => $sukan->id ?? 2,
                'price_per_hour' => 120,
                'price_per_day' => 800,
                'capacity' => 150,
                'location' => 'Chukai, Kemaman',
                'is_available' => true,
            ],
            [
                'name' => 'Dewan Komuniti Kijal',
                'description' => 'Dewan komuniti berhawa dingin berkapasiti 250 orang, sesuai untuk kenduri dan program kemasyarakatan.',
                'category_id' => $dewan->id ?? 1,
                'price_per_hour' => 100,
                'price_per_day' => 650,
                'capacity' => 250,
                'location' => 'Kijal, Kemaman',
                'is_available' => true,
            ],
            [
                'name' => 'Bilik Seminar MPK',
                'description' => 'Bilik seminar berhawa dingin untuk 60 orang dengan projector LCD dan video conferencing.',
                'category_id' => $bilik->id ?? 3,
                'price_per_hour' => 90,
                'price_per_day' => 600,
                'capacity' => 60,
                'location' => 'Pejabat MPK Chukai',
                'is_available' => true,
            ],
        ];

        // DUNGUN (MPD) Facilities
        $dungunFacilities = [
            [
                'name' => 'Dewan Jubli Perak Sultan Mahmud',
                'description' => 'Dewan utama dengan kapasiti 400 orang, dilengkapi dengan pentas besar dan sistem audio visual moden. Sesuai untuk majlis rasmi dan persidangan.',
                'category_id' => $dewan->id ?? 1,
                'price_per_hour' => 150,
                'price_per_day' => 1000,
                'capacity' => 400,
                'location' => 'Dungun',
                'is_available' => true,
            ],
            [
                'name' => 'Kompleks Sukan Dungun',
                'description' => 'Kompleks sukan lengkap dengan gelanggang badminton, futsal dan kemudahan gimnasium.',
                'category_id' => $sukan->id ?? 2,
                'price_per_hour' => 80,
                'price_per_day' => 500,
                'capacity' => 100,
                'location' => 'Dungun',
                'is_available' => true,
            ],
            [
                'name' => 'Gelanggang Futsal Paka',
                'description' => 'Gelanggang futsal indoor dengan lantai berkualiti di kawasan perindustrian Paka.',
                'category_id' => $sukan->id ?? 2,
                'price_per_hour' => 60,
                'price_per_day' => 350,
                'capacity' => 20,
                'location' => 'Paka, Dungun',
                'is_available' => true,
            ],
            [
                'name' => 'Dewan Komuniti Sura',
                'description' => 'Dewan komuniti berhawa dingin berkapasiti 200 orang untuk aktiviti kemasyarakatan.',
                'category_id' => $dewan->id ?? 1,
                'price_per_hour' => 90,
                'price_per_day' => 600,
                'capacity' => 200,
                'location' => 'Sura, Dungun',
                'is_available' => true,
            ],
            [
                'name' => 'Bilik Persidangan MPD',
                'description' => 'Bilik persidangan moden dengan kapasiti 50 orang, video conferencing dan projector LCD.',
                'category_id' => $bilik->id ?? 3,
                'price_per_hour' => 80,
                'price_per_day' => 500,
                'capacity' => 50,
                'location' => 'Wisma MPD, Dungun',
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

        foreach ($mbktFacilities as $facility) {
            Facility::create(array_merge($facility, ['district' => 'Kuala Terengganu']));
        }
        $this->command->info('Created facilities for Kuala Terengganu (MBKT)');

        foreach ($kemanFacilities as $facility) {
            Facility::create(array_merge($facility, ['district' => 'Kemaman']));
        }
        $this->command->info('Created facilities for Kemaman (MPK)');

        foreach ($dungunFacilities as $facility) {
            Facility::create(array_merge($facility, ['district' => 'Dungun']));
        }
        $this->command->info('Created facilities for Dungun (MPD)');
    }
}
