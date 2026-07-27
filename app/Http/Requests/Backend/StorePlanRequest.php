<?php

namespace App\Http\Requests\Backend;

use App\Models\Gym;
use Illuminate\Foundation\Http\FormRequest;

class StorePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $gym = Gym::find($this->input('gym_id'));

        return $gym !== null && $this->user()->can('manage', $gym);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'gym_id' => ['required', 'integer', 'exists:gyms,id'],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'highlighted' => ['nullable', 'boolean'],
            'features' => ['required', 'array', 'min:1'],
            'features.*' => ['required', 'string', 'max:255'],
        ];
    }
}
