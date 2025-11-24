<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Facility;

class DefaultFacilityPricingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Set a default price_per_day for facility id 20 if not set.
        $facility = Facility::find(20);
        if (!$facility) {
            $this->command->info('Facility id=20 not found; skipping.');
            return;
        }

        if ($facility->price_per_day === null) {
            // Use an 8-hour day multiplier as a reasonable default
            $perHour = floatval($facility->price_per_hour ?? 0);
            $facility->price_per_day = round($perHour * 8, 2);
            $facility->save();
            $this->command->info('Set price_per_day for facility id=20 to ' . $facility->price_per_day);
        } else {
            $this->command->info('Facility id=20 already has price_per_day: ' . $facility->price_per_day);
        }
    }
}
