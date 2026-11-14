<?php

namespace App\Http\Requests;

use App\Models\Visualisation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreArrangementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:60'],
            'levels' => ['required', 'array'],
            'levels.*' => ['required', 'integer', 'between:0,100'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $levels = (array) $this->input('levels', []);
                $known = Visualisation::ordered()->pluck('slug');

                if ($unknown = array_diff(array_keys($levels), $known->all())) {
                    $validator->errors()->add('levels', 'Unknown track: '.implode(', ', $unknown).'.');
                }

                if ($levels && array_sum(array_map('intval', $levels)) === 0) {
                    $validator->errors()->add('levels', 'Bring at least one instrument in before saving.');
                }
            },
        ];
    }

    public function levels(): array
    {
        $input = (array) $this->validated('levels');

        return Visualisation::ordered()
            ->mapWithKeys(fn ($v) => [$v->slug => (int) ($input[$v->slug] ?? 0)])
            ->all();
    }

    public function cleanTitle(): ?string
    {
        $title = trim(strip_tags((string) $this->validated('title')));

        return $title === '' ? null : $title;
    }
}
