<?php

namespace App\Http\Requests\Backend;

use App\Models\GymClass;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGymClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var GymClass $gymClass */
        $gymClass = $this->route('gymClass');

        return $this->user()->can('manage', $gymClass->gym);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'icon' => ['required', 'string', 'max:100'],
        ];
    }
}
