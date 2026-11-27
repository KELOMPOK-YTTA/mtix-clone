<?php

namespace App\Payments;

use App\Contracts\PaymentMethodInterface;
use App\Models\Booking;
use Illuminate\Support\Str;

/**
 * Simulasi pembayaran lewat e-wallet (GoPay, OVO, DANA, ShopeePay).
 * POLIMORFISME (OOP): implementasi process() yang berbeda dari BankTransferPayment.
 */
class EwalletPayment implements PaymentMethodInterface
{
    public function getCode(): string
    {
        return 'ewallet';
    }

    public function getLabel(): string
    {
        return 'E-Wallet';
    }

    public function getProviders(): array
    {
        return ['gopay', 'ovo', 'dana', 'shopeepay'];
    }

    public function process(Booking $booking, string $provider): array
    {
        return [
            'success' => true,
            'reference' => strtoupper($provider).'-'.Str::upper(Str::random(10)),
            'message' => 'Pembayaran via '.strtoupper($provider).' berhasil.',
        ];
    }
}
