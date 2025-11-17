<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Change role column to support multiple admin levels
            // Roles: 'user', 'district_admin', 'state_admin', 'master_admin'
            $table->string('role')->default('user')->change();
        });

        // Update existing admin users to district_admin by default
        DB::table('users')->where('role', 'admin')->update(['role' => 'district_admin']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert district_admin, state_admin, master_admin back to admin
        DB::table('users')->whereIn('role', ['district_admin', 'state_admin', 'master_admin'])
            ->update(['role' => 'admin']);
    }
};
