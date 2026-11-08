<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Data peta yang diinput kader/admin dan melalui verifikasi
 * sebelum tampil ke publik.
 *
 * Koordinat titik disimpan sebagai JSON {"lat": .., "lng": ..},
 * jalur sebagai JSON [[lat, lng], [lat, lng], ...].
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assembly_points', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->unsignedInteger('capacity')->nullable();
            $table->string('address')->nullable();
            $table->text('facilities')->nullable();
            $table->json('location');
            $table->string('photo')->nullable();
            $this->verificationColumns($table);
            $table->timestamps();
        });

        Schema::create('hazard_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('hazard_type');
            $table->string('risk_level');
            $table->text('description')->nullable();
            $table->json('location');
            $table->unsignedInteger('radius_m')->default(100);
            $table->string('photo')->nullable();
            $this->verificationColumns($table);
            $table->timestamps();
        });

        Schema::create('evacuation_routes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('hazard_type');
            $table->json('path');
            $table->text('description')->nullable();
            $table->foreignId('assembly_point_id')->nullable()->constrained()->nullOnDelete();
            $this->verificationColumns($table);
            $table->timestamps();
        });

        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->json('location');
            $this->verificationColumns($table);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facilities');
        Schema::dropIfExists('evacuation_routes');
        Schema::dropIfExists('hazard_zones');
        Schema::dropIfExists('assembly_points');
    }

    private function verificationColumns(Blueprint $table): void
    {
        $table->string('status')->default('menunggu')->index();
        $table->text('review_note')->nullable();
        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamp('verified_at')->nullable();
    }
};
