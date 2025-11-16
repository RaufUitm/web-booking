<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Facility;
use App\Models\TimeSlot;

class FacilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create categories for MDHT
        $categories = [
            [
                'name' => 'Dewan & Panggung',
                'description' => 'Dewan dan panggung untuk pelbagai majlis dan acara',
                'icon' => 'hall-icon'
            ],
            [
                'name' => 'Padang & Gelanggang',
                'description' => 'Padang dan gelanggang sukan untuk aktiviti luar',
                'icon' => 'sports-icon'
            ],
            [
                'name' => 'Pusat Komuniti',
                'description' => 'Ruang komuniti untuk mesyuarat dan aktiviti penduduk',
                'icon' => 'community-icon'
            ],
            [
                'name' => 'Kemudahan Sukan',
                'description' => 'Kemudahan sukan tertutup dan terbuka',
                'icon' => 'recreation-icon'
            ],
        ];

        foreach ($categories as $categoryData) {
            $category = Category::create($categoryData);

            // Create sample facilities for each category
            $facilities = $this->getFacilitiesForCategory($category->name);

            foreach ($facilities as $facilityData) {
                $facilityData['category_id'] = $category->id;
                $facility = Facility::create($facilityData);

                // Create time slots for each facility
                $this->createTimeSlots($facility->id);
            }
        }
    }

    private function getFacilitiesForCategory($categoryName)
    {
        $facilitiesData = [
            'Dewan & Panggung' => [
                [
                    'name' => 'Dewan Jubli Perak',
                    'description' => 'Dewan besar dengan kapasiti 500 orang, sesuai untuk majlis perkahwinan, seminar dan persidangan. Dilengkapi dengan pentas, sistem PA dan pendingin hawa.',
                    'location' => 'Kompleks MDHT, Kuala Berang',
                    'capacity' => 500,
                    'price_per_hour' => 150.00,
                    'is_available' => true,
                ],
                [
                    'name' => 'Dewan Serbaguna Tasik Kenyir',
                    'description' => 'Dewan sederhana dengan kapasiti 200 orang. Sesuai untuk majlis kenduri, kursus dan mesyuarat besar.',
                    'location' => 'Kawasan Tasik Kenyir',
                    'capacity' => 200,
                    'price_per_hour' => 80.00,
                    'is_available' => true,
                ],
                [
                    'name' => 'Panggung Mini MDHT',
                    'description' => 'Panggung kecil dengan pentas dan tempat duduk 100 orang. Sesuai untuk persembahan budaya dan ceramah.',
                    'location' => 'Kompleks MDHT, Kuala Berang',
                    'capacity' => 100,
                    'price_per_hour' => 50.00,
                    'is_available' => true,
                ],
            ],
            'Padang & Gelanggang' => [
                [
                    'name' => 'Padang Bola Sepak Kuala Berang',
                    'description' => 'Padang bola sepak bersaiz penuh dengan rumput semula jadi. Dilengkapi dengan lampu limpah untuk perlawanan malam.',
                    'location' => 'Stadium MDHT, Kuala Berang',
                    'capacity' => 1000,
                    'price_per_hour' => 100.00,
                    'is_available' => true,
                ],
                [
                    'name' => 'Gelanggang Futsal Tertutup',
                    'description' => 'Gelanggang futsal dalam bangunan dengan lantai sintetik berkualiti tinggi. Sesuai untuk latihan dan perlawanan.',
                    'location' => 'Kompleks Sukan MDHT',
                    'capacity' => 50,
                    'price_per_hour' => 60.00,
                    'is_available' => true,
                ],
                [
                    'name' => 'Padang Ragbi Mini',
                    'description' => 'Padang ragbi saiz standard untuk latihan dan perlawanan ragbi 7 sebelah.',
                    'location' => 'Stadium MDHT, Kuala Berang',
                    'capacity' => 500,
                    'price_per_hour' => 80.00,
                    'is_available' => true,
                ],
            ],
            'Pusat Komuniti' => [
                [
                    'name' => 'Bilik Mesyuarat A',
                    'description' => 'Bilik mesyuarat lengkap dengan meja, kerusi, projektor dan papan putih. Sesuai untuk mesyuarat rasmi dan pembentangan.',
                    'location' => 'Pejabat MDHT, Tingkat 2',
                    'capacity' => 30,
                    'price_per_hour' => 40.00,
                    'is_available' => true,
                ],
                [
                    'name' => 'Bilik Aktiviti Komuniti',
                    'description' => 'Ruang serbaguna untuk aktiviti penduduk seperti kelas kemahiran, kursus dan bengkel.',
                    'location' => 'Pusat Komuniti Kuala Berang',
                    'capacity' => 50,
                    'price_per_hour' => 30.00,
                    'is_available' => true,
                ],
                [
                    'name' => 'Dewan Kuliah Mini',
                    'description' => 'Bilik kuliah kecil dengan sistem audio dan video. Sesuai untuk ceramah dan kelas pendidikan.',
                    'location' => 'Pusat Komuniti Kuala Berang',
                    'capacity' => 80,
                    'price_per_hour' => 50.00,
                    'is_available' => true,
                ],
            ],
            'Kemudahan Sukan' => [
                [
                    'name' => 'Gelanggang Badminton (4 Gelanggang)',
                    'description' => 'Kompleks badminton dengan 4 gelanggang standard dilengkapi dengan lantai getah dan sistem pencahayaan LED.',
                    'location' => 'Kompleks Sukan MDHT',
                    'capacity' => 80,
                    'price_per_hour' => 80.00,
                    'is_available' => true,
                ],
                [
                    'name' => 'Gelanggang Takraw',
                    'description' => 'Gelanggang sepak takraw standard dengan jaring dan lampu. Lantai getah berkualiti tinggi.',
                    'location' => 'Kompleks Sukan MDHT',
                    'capacity' => 40,
                    'price_per_hour' => 40.00,
                    'is_available' => true,
                ],
                [
                    'name' => 'Dewan Ping Pong (6 Meja)',
                    'description' => 'Dewan ping pong dengan 6 meja standard dan peralatan lengkap.',
                    'location' => 'Kompleks Sukan MDHT',
                    'capacity' => 24,
                    'price_per_hour' => 30.00,
                    'is_available' => true,
                ],
                [
                    'name' => 'Kolam Renang Awam',
                    'description' => 'Kolam renang saiz olimpik dengan kawasan kanak-kanak. Dilengkapi dengan penyelamat dan bilik persalinan.',
                    'location' => 'Kompleks Akuatik MDHT',
                    'capacity' => 150,
                    'price_per_hour' => 70.00,
                    'is_available' => true,
                ],
            ],
        ];

        return $facilitiesData[$categoryName] ?? [];
    }

    private function createTimeSlots($facilityId)
    {
        $timeSlots = [
            ['start_time' => '08:00', 'end_time' => '10:00'],
            ['start_time' => '10:00', 'end_time' => '12:00'],
            ['start_time' => '12:00', 'end_time' => '14:00'],
            ['start_time' => '14:00', 'end_time' => '16:00'],
            ['start_time' => '16:00', 'end_time' => '18:00'],
            ['start_time' => '18:00', 'end_time' => '20:00'],
            ['start_time' => '20:00', 'end_time' => '22:00'],
        ];

        foreach ($timeSlots as $slot) {
            TimeSlot::create([
                'facility_id' => $facilityId,
                'start_time' => $slot['start_time'],
                'end_time' => $slot['end_time'],
            ]);
        }
    }
}
