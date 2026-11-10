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
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi', 30)->unique();
            $table->foreignId('pelanggan_id')->constrained('pelanggan')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->dateTime('tanggal_masuk');
            $table->dateTime('tanggal_selesai')->nullable();
            $table->enum('status_laundry', ['baru', 'dihitung', 'diproses', 'selesai', 'diambil'])->default('baru');
            $table->enum('status_pembayaran', ['belum_bayar', 'lunas'])->default('belum_bayar');
            $table->decimal('total_bayar', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
