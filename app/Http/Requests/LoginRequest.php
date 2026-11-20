<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Class LoginRequest
 *
 * SOLID - Single Responsibility Principle:
 * Bertanggung jawab khusus untuk validasi input kredensial login (Nomor HP + PIN).
 */
class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Persiapkan data sebelum divalidasi.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $phone = preg_replace('/[^0-9]/', '', (string) $this->phone);
            if (str_starts_with($phone, '628')) {
                $phone = '0'.substr($phone, 2);
            }
            $this->merge(['phone' => $phone]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:/^08[0-9]{8,12}$/'],
            'pin' => ['required', 'string', 'digits:6'],
        ];
    }

    /**
     * Custom message untuk validasi input login.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.required' => 'Nomor HP wajib diisi.',
            'phone.regex' => 'Format nomor HP tidak valid.',
            'pin.required' => 'PIN wajib diisi.',
            'pin.digits' => 'PIN harus terdiri dari tepat 6 digit angka.',
        ];
    }

    /**
     * Format respon JSON jika validasi input gagal.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Validasi gagal, silakan periksa kredensial Anda.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
