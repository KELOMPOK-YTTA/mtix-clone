<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

/**
 * Class UpdateProfileRequest
 *
 * SOLID - Single Responsibility Principle:
 * Bertanggung jawab khusus untuk validasi perubahan profil pengguna.
 */
class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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
        $userId = $this->user()?->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'min:2', 'max:100'],
            'phone' => [
                'sometimes',
                'required',
                'string',
                'regex:/^08[0-9]{8,12}$/',
                Rule::unique('users', 'phone')->ignore($userId),
            ],
            'current_pin' => ['required_with:new_pin', 'nullable', 'string', 'digits:6'],
            'new_pin' => ['nullable', 'string', 'digits:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama tidak boleh kosong.',
            'name.min' => 'Nama minimal 2 karakter.',
            'phone.required' => 'Nomor HP tidak boleh kosong.',
            'phone.regex' => 'Format nomor HP tidak valid.',
            'phone.unique' => 'Nomor HP sudah digunakan oleh pengguna lain.',
            'current_pin.required_with' => 'PIN saat ini wajib diisi jika ingin mengganti PIN baru.',
            'current_pin.digits' => 'PIN saat ini harus terdiri dari 6 digit angka.',
            'new_pin.digits' => 'PIN baru harus terdiri dari 6 digit angka.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Validasi pembaruan profil gagal.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
