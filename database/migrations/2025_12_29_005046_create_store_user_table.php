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
        Schema::create('store_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            // Opsional: role di toko tertentu (misal di toko A jadi manajer, di B jadi kasir)
            // $table->string('role')->default('staff'); 
            $table->timestamps();
        });

        // Hapus kolom store_id di users karena sudah tidak dipakai
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'store_id')) {

                // Cek apakah driver database support foreign key dropping (MySQL support)
                // Kita bungkus pakai try-catch biar kalau key gak ada, migrasi ttp jalan
                try {
                    $table->dropForeign(['store_id']);
                } catch (\Exception $e) {
                    // Biarkan kosong, artinya kalau gagal drop key, lanjut aja
                }

                $table->dropColumn('store_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_user');
    }
};
