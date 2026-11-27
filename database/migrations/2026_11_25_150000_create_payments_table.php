<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel pembayaran (Anggota 6).
 * Satu booking bisa punya lebih dari satu baris payment (mis. gagal lalu dicoba lagi),
 * tapi hanya satu yang berstatus success.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_code', 20)->unique();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('method', 30);                 // bank_transfer / ewallet
            $table->string('provider', 30)->nullable();   // bca, mandiri, gopay, ovo, ...
            $table->string('reference', 50)->nullable();  // no. virtual account / no. referensi e-wallet
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'success', 'failed', 'refunded'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
