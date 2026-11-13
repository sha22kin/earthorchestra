<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateVisualisationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        // Drop empty repeater rows before validating.
        $this->merge([
            'citations' => collect($this->input('citations', []))
                ->filter(fn ($row) => trim($row['reference'] ?? '') !== '')
                ->values()
                ->all(),
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'short_title' => ['required', 'string', 'max:40'],
            'model' => ['required', 'in:GEOS-FP,GEOS-CF'],
            'measurement' => ['required', 'string', 'max:160'],
            'interaction' => ['required', 'in:cursor,slider'],
            'accent' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'svs_id' => ['required', 'integer', 'min:1', 'max:99999'],
            'data_starts_on' => ['required', 'date'],
            'data_ends_on' => ['required', 'date', 'after_or_equal:data_starts_on'],
            'summary' => ['required', 'string', 'max:1000'],
            'body' => ['required', 'string', 'max:30000'],
            'legend' => ['required', 'json'],
            'citations' => ['array', 'max:20'],
            'citations.*.reference' => ['required', 'string', 'max:600'],
            'citations.*.url' => ['nullable', 'url:http,https', 'max:500'],
            'video' => ['nullable', 'file', 'mimetypes:video/mp4', 'max:204800'],
            'audio' => ['nullable', 'file', 'mimes:m4a,mp3,aac,ogg,wav', 'max:30720'],
            'poster' => ['nullable', 'image', 'mimes:webp,jpg,jpeg,png', 'max:6144'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            $legend = json_decode((string) $this->input('legend'), true);
            if (! is_array($legend)) {
                return;
            }

            $stops = $legend['stops'] ?? null;
            if (! is_array($stops) || count($stops) < 2) {
                $validator->errors()->add('legend', 'The legend needs at least two "stops".');

                return;
            }

            foreach ($stops as $i => $stop) {
                if (! preg_match('/^#[0-9a-fA-F]{6}$/', $stop['color'] ?? '') || ! is_numeric($stop['value'] ?? null)) {
                    $validator->errors()->add('legend', 'Stop '.($i + 1).' needs a hex "color" and a numeric "value".');

                    return;
                }
            }

            foreach (['label', 'unit'] as $key) {
                if (! is_string($legend[$key] ?? null)) {
                    $validator->errors()->add('legend', "The legend needs a \"{$key}\".");
                }
            }
        }];
    }

    public function messages(): array
    {
        return [
            'accent.regex' => 'Use a six-digit hex colour, like #c0714a.',
            'legend.json' => 'The legend isn’t valid JSON.',
            'citations.*.url.url' => 'Each citation link must be a full http(s) URL.',
        ];
    }
}
