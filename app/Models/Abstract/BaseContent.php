<?php

namespace App\Models\Abstract;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseContent
 *
 * ABSTRAKSI (OOP): Class abstrak sebagai fondasi entitas konten media (Film, dsb.)
 * dalam sistem Cinema XXI. Tidak dapat di-instantiate secara langsung dan mendefinisikan
 * method abstrak yang wajib diimplementasikan oleh class anak.
 */
abstract class BaseContent extends Model
{
    /**
     * ENKAPSULASI (OOP): Menyembunyikan timestamp internal dari serialisasi JSON / array.
     *
     * @var list<string>
     */
    protected $hidden = ['created_at', 'updated_at'];

    /**
     * POLIMORFISME (OOP): Method abstrak yang mewajibkan class turunan menyediakan
     * representasi label teks untuk status konten.
     */
    abstract public function getStatusLabel(): string;

    /**
     * Format durasi dalam menit menjadi format jam dan menit yang mudah dibaca.
     * Contoh: 135 menit → "2h 15m"
     */
    public function getDurationFormatted(): string
    {
        $totalMinutes = (int) $this->duration_minutes;
        $h = intdiv($totalMinutes, 60);
        $m = $totalMinutes % 60;

        if ($h === 0) {
            return "{$m}m";
        }

        if ($m === 0) {
            return "{$h}h";
        }

        return "{$h}h {$m}m";
    }
}
