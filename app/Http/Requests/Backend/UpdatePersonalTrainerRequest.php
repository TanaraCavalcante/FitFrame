<?php

namespace App\Http\Requests\Backend;

use App\Models\PersonalTrainer;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePersonalTrainerRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var PersonalTrainer $personalTrainer */
        $personalTrainer = $this->route('personalTrainer');

        return $this->user()->can('manage', $personalTrainer->gym);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'specialty' => ['required', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'remove_photo' => ['nullable', 'boolean'],
        ];
    }
}
