<?php

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * Trait HasBookingCode
 *
 * REUSABILITY (OOP): logika "buat kode unik" ditulis sekali, lalu dipakai
 * oleh model mana pun yang butuh kode unik (Booking -> MTX-..., Payment -> PAY-...).
 */
trait HasBookingCode
{
    /**
     * Membuat kode acak yang belum pernah dipakai di kolom $column.
     * Contoh: generateUniqueCode('PAY-', 'payment_code', 8) -> PAY-7K2QX9AB
     */
    public static function generateUniqueCode(string $prefix, string $column, int $length = 8): string
    {
        do {
            // Tanpa huruf/angka yang mirip (0/O, 1/I) supaya mudah dibaca di tiket
            $random = collect(str_split(Str::upper(Str::random($length * 3))))
                ->reject(fn (string $char) => in_array($char, ['0', 'O', '1', 'I'], true))
                ->take($length)
                ->implode('');

            $code = $prefix.$random;
        } while (strlen($random) < $length || static::where($column, $code)->exists());

        return $code;
    }
}
