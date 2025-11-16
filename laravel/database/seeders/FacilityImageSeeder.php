<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Facility;

class FacilityImageSeeder extends Seeder
{
    public function run(): void
    {
        $images = [
            1 => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=800', // Dewan Jubli Perak - Modern building
            2 => 'https://images.unsplash.com/photo-1511818966892-d7d671e672a2?w=800', // Dewan Serbaguna - Modern building
            3 => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800', // Panggung Mini - Modern building
            4 => 'https://images.unsplash.com/photo-1577495508048-b635879837f1?w=800', // Padang Bola Sepak - Modern building
            5 => 'https://images.unsplash.com/photo-1494145904049-0dca59b4bbad?w=800', // Gelanggang Futsal - Modern building
            6 => 'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=800', // Padang Ragbi - Modern building
            7 => 'https://images.unsplash.com/photo-1564760055775-d63b17a55c44?w=800', // Bilik Mesyuarat A - Modern building
            8 => 'https://images.unsplash.com/photo-1582407947304-fd86f028f716?w=800', // Bilik Aktiviti Komuniti - Modern building
            9 => 'https://images.unsplash.com/photo-1571055107559-3e67626fa8be?w=800', // Dewan Kuliah Mini - Modern building
            10 => 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=800', // Gelanggang Badminton - Modern building
            11 => 'https://images.unsplash.com/photo-1528819622765-d6bcf132f793?w=800', // Gelanggang Takraw - Modern building
            12 => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?w=800', // Dewan Ping Pong - Modern building
            13 => 'https://images.unsplash.com/photo-1480714378408-67cf0d13bc1b?w=800', // Kolam Renang - Modern building
        ];

        foreach ($images as $id => $imageUrl) {
            Facility::where('id', $id)->update(['image' => $imageUrl]);
        }

        $this->command->info('Updated ' . count($images) . ' facilities with images!');
    }
}
