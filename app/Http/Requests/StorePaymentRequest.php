<?php

namespace App\Http\Requests;

use App\Services\PaymentService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

/**
 * Validasi input POST /api/payments.
 * Nominal TIDAK dikirim client — server mengambilnya dari total_amount booking.
 */
class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
            // Daftar metode diambil dari PaymentService, jadi otomatis ikut bertambah
            'method' => ['required', 'string', Rule::in(app(PaymentService::class)->availableMethods()->pluck('code')->all())],
            'provider' => ['required', 'string', 'max:30'],
        ];
    }

    public function messages(): array
    {
        return [
            'booking_id.exists' => 'Booking tidak ditemukan.',
            'method.in' => 'Metode pembayaran tidak dikenal.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Data pembayaran tidak valid.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
