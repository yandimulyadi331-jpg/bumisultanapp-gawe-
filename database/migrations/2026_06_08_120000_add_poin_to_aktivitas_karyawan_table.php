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
        Schema::table('aktivitas_karyawan', function (Blueprint $table) {
            // Poin field - dapat dikalkulasi otomatis atau input manual
            $table->decimal('poin', 12, 2)->default(0)->after('lokasi')->comment('Poin aktivitas (0-100)');
            
            // Tipe poin: 'auto' (sistem hitung) atau 'manual' (admin input)
            $table->enum('tipe_poin', ['auto', 'manual'])->default('auto')->after('poin')->comment('Tipe poin: auto atau manual');
            
            // Admin yang input poin jika manual
            $table->string('poin_input_by', 50)->nullable()->after('tipe_poin')->comment('User/Admin yang input poin manual');
            
            // Waktu poin di-set
            $table->timestamp('poin_set_at')->nullable()->after('poin_input_by')->comment('Waktu poin di-set');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aktivitas_karyawan', function (Blueprint $table) {
            $table->dropColumn(['poin', 'tipe_poin', 'poin_input_by', 'poin_set_at']);
        });
    }
};
