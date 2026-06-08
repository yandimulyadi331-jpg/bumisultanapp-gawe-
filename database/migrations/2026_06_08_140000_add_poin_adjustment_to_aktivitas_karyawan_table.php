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
            // Original poin sebelum adjustment - untuk audit trail
            if (!Schema::hasColumn('aktivitas_karyawan', 'poin_original')) {
                $table->decimal('poin_original', 12, 2)->nullable()->after('poin_set_at')->comment('Poin original sebelum adjustment admin');
            }
            
            // Admin yang melakukan adjustment
            if (!Schema::hasColumn('aktivitas_karyawan', 'poin_adjusted_by')) {
                $table->string('poin_adjusted_by', 50)->nullable()->after('poin_original')->comment('User/Admin yang adjust poin');
            }
            
            // Waktu poin di-adjust
            if (!Schema::hasColumn('aktivitas_karyawan', 'poin_adjusted_at')) {
                $table->timestamp('poin_adjusted_at')->nullable()->after('poin_adjusted_by')->comment('Waktu poin di-adjust terakhir');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aktivitas_karyawan', function (Blueprint $table) {
            $table->dropColumn(['poin_original', 'poin_adjusted_by', 'poin_adjusted_at']);
        });
    }
};
