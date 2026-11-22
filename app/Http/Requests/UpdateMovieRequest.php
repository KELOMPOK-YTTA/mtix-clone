<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Class UpdateMovieRequest
 *
 * SOLID - Single Responsibility Principle:
 * Bertanggung jawab khusus untuk validasi input pembaruan data film.
 */
class UpdateMovieRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'synopsis' => ['sometimes', 'required', 'string'],
            'genre' => ['sometimes', 'required', 'string', 'max:100'],
            'duration_minutes' => ['sometimes', 'required', 'integer', 'min:1', 'max:600'],
            'format' => ['sometimes', 'required', 'string', 'in:2D,3D,IMAX'],
            'poster' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'release_date' => ['sometimes', 'required', 'date'],
            'status' => ['sometimes', 'required', 'string', 'in:now_playing,coming_soon'],
        ];
    }

    /**
     * Custom message untuk validasi input pembaruan film.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul film wajib diisi.',
            'title.max' => 'Judul film maksimal 255 karakter.',
            'synopsis.required' => 'Sinopsis film wajib diisi.',
            'genre.required' => 'Genre film wajib diisi.',
            'duration_minutes.integer' => 'Durasi film harus berupa angka.',
            'duration_minutes.min' => 'Durasi film minimal 1 menit.',
            'format.in' => 'Format film hanya boleh: 2D, 3D, atau IMAX.',
            'poster.image' => 'File poster harus berupa gambar.',
            'poster.mimes' => 'Format poster harus jpg, jpeg, png, atau webp.',
            'poster.max' => 'Ukuran file poster maksimal 2MB.',
            'release_date.date' => 'Format tanggal rilis tidak valid.',
            'status.in' => 'Status film hanya boleh: now_playing atau coming_soon.',
        ];
    }

    /**
     * Format respon JSON jika validasi input gagal.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Validasi gagal, silakan periksa data film yang diinput.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
