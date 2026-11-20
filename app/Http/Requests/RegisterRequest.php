<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Class RegisterRequest
 *
 * SOLID - Single Responsibility Principle:
 * Bertanggung jawab khusus untuk validasi data registrasi akun baru Cinema XXI (M-Tix).
 */
class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Persiapkan data sebelum divalidasi (normalisasi nomor HP).
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
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^08[0-9]{8,12}$/', 'unique:users,phone'],
            'pin' => ['required', 'string', 'digits:6'],
            'role' => ['nullable', 'string', 'in:user,admin'],
        ];
    }

    /**
     * Custom message untuk validasi input registrasi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.min' => 'Nama lengkap minimal 2 karakter.',
            'name.max' => 'Nama lengkap maksimal 100 karakter.',
            'phone.required' => 'Nomor HP wajib diisi.',
            'phone.regex' => 'Format nomor HP tidak valid. Gunakan format nomor HP Indonesia (misal: 081234567890).',
            'phone.unique' => 'Nomor HP sudah terdaftar di sistem Cinema XXI.',
            'pin.required' => 'PIN wajib diisi.',
            'pin.digits' => 'PIN harus terdiri dari tepat 6 digit angka.',
            'role.in' => 'Role yang dipilih tidak valid (hanya user atau admin).',
        ];
    }

    /**
     * Handle respon JSON ketika validasi gagal sesuai standar format API Section 7.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Validasi gagal, silakan periksa input data Anda.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
