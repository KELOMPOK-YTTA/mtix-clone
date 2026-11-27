<?php

namespace App\Payments;

use App\Contracts\PaymentMethodInterface;
use App\Models\Booking;

/**
 * Simulasi pembayaran lewat Virtual Account bank.
 * POLIMORFISME (OOP): process() berperilaku berbeda dari EwalletPayment.
 */
class BankTransferPayment implements PaymentMethodInterface
{
    /** Prefix virtual account per bank (dummy). */
    private const VA_PREFIX = [
        'bca' => '39001',
        'mandiri' => '89508',
        'bni' => '98812',
        'bri' => '26215',
    ];

    public function getCode(): string
    {
        return 'bank_transfer';
    }

    public function getLabel(): string
    {
        return 'Transfer Bank (Virtual Account)';
    }

    public function getProviders(): array
    {
        return array_keys(self::VA_PREFIX);
    }

    public function process(Booking $booking, string $provider): array
    {
        // Nomor VA = prefix bank + nomor HP user (dummy, cukup untuk simulasi)
        $phone = preg_replace('/\D/', '', (string) $booking->user?->phone);
        $virtualAccount = self::VA_PREFIX[$provider].substr(str_pad($phone, 11, '0', STR_PAD_LEFT), -11);

        return [
            'success' => true,
            'reference' => $virtualAccount,
            'message' => 'Pembayaran via VA '.strtoupper($provider).' berhasil.',
        ];
    }
}
