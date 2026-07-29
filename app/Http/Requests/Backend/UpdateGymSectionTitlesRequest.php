<?php

namespace App\Http\Requests\Backend;

use App\Models\Gym;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGymSectionTitlesRequest extends FormRequest
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
            'classes_title' => ['nullable', 'string', 'max:255'],
            'plans_title' => ['nullable', 'string', 'max:255'],
            'gallery_title' => ['nullable', 'string', 'max:255'],
            'team_title' => ['nullable', 'string', 'max:255'],
            'testimonials_title' => ['nullable', 'string', 'max:255'],
        ];
    }
}
