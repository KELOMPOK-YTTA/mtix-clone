<?php

namespace App\Http\Requests;

/**
 * Class UpdateScheduleRequest
 *
 * INHERITANCE (OOP): Mewarisi aturan & pesan dari StoreScheduleRequest,
 * tetapi semua field menjadi opsional (boleh mengubah sebagian saja).
 * Field yang dikirim tidak boleh bernilai null, karena kolomnya di database wajib terisi.
 */
class UpdateScheduleRequest extends StoreScheduleRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return collect(parent::rules())
            ->map(fn (array $rules) => array_map(
                fn (string $rule) => in_array($rule, ['required', 'nullable'], true) ? 'sometimes' : $rule,
                $rules
            ))
            ->all();
    }
}
