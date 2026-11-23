<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Class StoreScheduleRequest
 *
 * Validasi input saat admin membuat jadwal tayang baru.
 */
class StoreScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Proteksi admin dilakukan lewat middleware di routes/api.php
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'movie_id' => ['required', 'integer', 'exists:movies,id'],
            'studio_id' => ['required', 'integer', 'exists:studios,id'],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],
            'price' => ['nullable', 'integer', 'min:0'],
            'format' => ['nullable', 'in:2D,3D,IMAX'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'movie_id.exists' => 'Film tidak ditemukan.',
            'studio_id.exists' => 'Studio tidak ditemukan.',
            'date.date_format' => 'Format tanggal harus YYYY-MM-DD.',
            'date.after_or_equal' => 'Tanggal jadwal tidak boleh sebelum hari ini.',
            'time.date_format' => 'Format jam harus HH:MM, contoh 19:30.',
            'format.in' => 'Format harus 2D, 3D, atau IMAX.',
        ];
    }

    /**
     * Kembalikan error validasi dalam format JSON standar kelompok.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Data jadwal tidak valid.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
