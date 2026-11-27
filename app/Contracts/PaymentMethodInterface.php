<?php

namespace App\Contracts;

use App\Models\Booking;

/**
 * Interface PaymentMethodInterface
 *
 * SOLID - Open/Closed Principle & POLIMORFISME (OOP):
 * Setiap metode pembayaran (transfer bank, e-wallet, ...) adalah class terpisah
 * yang mengimplementasikan interface ini. Menambah metode baru (mis. QRIS)
 * cukup membuat class baru + daftarkan di AppServiceProvider,
 * TANPA mengubah PaymentService.
 */
interface PaymentMethodInterface
{
    /** Kode metode yang dikirim client, mis. "bank_transfer". */
    public function getCode(): string;

    /** Nama yang ditampilkan ke user, mis. "Transfer Bank (Virtual Account)". */
    public function getLabel(): string;

    /** @return list<string> daftar provider yang didukung, mis. ['bca', 'mandiri'] */
    public function getProviders(): array;

    /**
     * Proses pembayaran (SIMULASI, tidak ada uang sungguhan).
     *
     * @return array{success: bool, reference: string|null, message: string}
     */
    public function process(Booking $booking, string $provider): array;
}
