<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropTimeSlotIdFromBookings extends Migration
{
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Drop foreign key if exists, then drop the column
            if (Schema::hasColumn('bookings', 'time_slot_id')) {
                try {
                    $table->dropForeign(['time_slot_id']);
                } catch (\Exception $e) {
                    // ignore if foreign key not present
                }
                $table->dropColumn('time_slot_id');
            }
        });
    }

    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'time_slot_id')) {
                $table->foreignId('time_slot_id')->constrained('time_slots')->onDelete('cascade');
            }
        });
    }
}
