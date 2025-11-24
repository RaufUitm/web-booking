<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Use raw SQL to alter the column to nullable to avoid requiring doctrine/dbal.
        // This assumes MySQL. Adjust if using a different driver.
        DB::statement("ALTER TABLE `bookings` MODIFY `time_slot_id` BIGINT UNSIGNED NULL;");
    }

    public function down(): void
    {
        // Revert to NOT NULL (no default) — be cautious: this will fail if NULLs exist.
        DB::statement("ALTER TABLE `bookings` MODIFY `time_slot_id` BIGINT UNSIGNED NOT NULL;");
    }
};
