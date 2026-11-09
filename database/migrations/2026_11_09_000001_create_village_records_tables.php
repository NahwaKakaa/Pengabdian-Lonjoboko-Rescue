<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Data desa tambahan:
 * - hamlet_populations : jumlah penduduk dan warga rentan per dusun
 * - disaster_events    : riwayat kejadian bencana + jumlah korban (tanpa nama)
 * - incident_reports   : laporan kejadian dari warga, dicek kader, disahkan admin
 * - activities         : kegiatan kesiapsiagaan Karang Taruna
 * - facilities         : tambahan jam layanan
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hamlet_populations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('population')->nullable();
            $table->unsignedInteger('households')->nullable();
            $table->unsignedInteger('male')->nullable();
            $table->unsignedInteger('female')->nullable();
            $table->unsignedInteger('elderly')->nullable();
            $table->unsignedInteger('toddlers')->nullable();
            $table->unsignedInteger('pregnant')->nullable();
            $table->unsignedInteger('disabled')->nullable();
            $table->json('location')->nullable();
            $table->text('note')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('disaster_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('hazard_type');
            $table->date('occurred_on');
            $table->string('hamlet')->nullable();
            $table->json('location')->nullable();
            $table->text('description')->nullable();
            $table->text('impact')->nullable();
            $table->unsignedInteger('deaths')->default(0);
            $table->unsignedInteger('injured')->default(0);
            $table->unsignedInteger('missing')->default(0);
            $table->unsignedInteger('displaced')->default(0);
            $table->unsignedInteger('houses_damaged')->default(0);
            $table->string('source')->nullable();
            $table->string('source_url', 500)->nullable();
            $table->string('photo')->nullable();
            $table->string('status')->default('menunggu')->index();
            $table->text('review_note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('incident_reports', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('kind');
            $table->text('description');
            $table->string('hamlet')->nullable();
            $table->string('landmark')->nullable();
            $table->json('location')->nullable();
            $table->string('photo')->nullable();
            $table->string('reporter_name')->nullable();
            $table->string('reporter_phone', 30)->nullable();
            $table->string('status')->default('baru')->index();
            $table->text('check_note')->nullable();
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->foreignId('disaster_event_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamps();
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('held_on');
            $table->string('place')->nullable();
            $table->text('description')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('is_published')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('facilities', function (Blueprint $table) {
            $table->string('opening_hours')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->dropColumn('opening_hours');
        });
        Schema::dropIfExists('activities');
        Schema::dropIfExists('incident_reports');
        Schema::dropIfExists('disaster_events');
        Schema::dropIfExists('hamlet_populations');
    }
};
