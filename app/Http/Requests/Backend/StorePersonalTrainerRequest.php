<?php

namespace App\Http\Requests\Backend;

use App\Models\Gym;
use Illuminate\Foundation\Http\FormRequest;

class StorePersonalTrainerRequest extends FormRequest
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
            'specialty' => ['required', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ];
    }
}
