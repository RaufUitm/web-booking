<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add district to facilities table
        Schema::table('facilities', function (Blueprint $table) {
            $table->string('district')->default('Hulu Terengganu')->after('id');
            $table->index('district');
        });

        // Add district to users table (optional - to restrict users to specific districts)
        Schema::table('users', function (Blueprint $table) {
            $table->string('district')->nullable()->after('role');
            $table->index('district');
        });

        // Add district to services table if it exists
        if (Schema::hasTable('services')) {
            Schema::table('services', function (Blueprint $table) {
                $table->string('district')->default('Hulu Terengganu')->after('id');
                $table->index('district');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->dropIndex(['district']);
            $table->dropColumn('district');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['district']);
            $table->dropColumn('district');
        });

        if (Schema::hasTable('services')) {
            Schema::table('services', function (Blueprint $table) {
                $table->dropIndex(['district']);
                $table->dropColumn('district');
            });
        }
    }
};
