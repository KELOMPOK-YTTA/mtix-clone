<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

/**
 * Class ScheduleException
 *
 * Error aturan bisnis modul jadwal (bentrok, sudah ada pemesanan, studio tanpa kursi).
 * Laravel otomatis memanggil render() sehingga response-nya berupa JSON 422
 * dengan format error standar kelompok.
 */
class ScheduleException extends Exception
{
    /**
     * @param  array<string, list<string>>  $fieldErrors
     */
    public function __construct(string $message, private readonly array $fieldErrors = [])
    {
        parent::__construct($message);
    }

    public static function conflict(string $movieTitle, string $startTime, string $endTime): self
    {
        return new self('Jadwal bentrok dengan jadwal lain di studio yang sama.', [
            'time' => ["Studio sudah dipakai untuk \"{$movieTitle}\" pukul {$startTime}-{$endTime}."],
        ]);
    }

    /**
     * Error aturan bisnis bukan bug aplikasi, jadi tidak perlu dicatat ke log.
     */
    public function report(): void
    {
        //
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $this->getMessage(),
            'errors' => (object) $this->fieldErrors,
        ], 422);
    }
}
