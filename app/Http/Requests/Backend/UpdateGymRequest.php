<?php

namespace App\Http\Requests\Backend;

use App\Models\Gym;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGymRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('gym'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Gym $gym */
        $gym = $this->route('gym');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash'],
            'domain' => [
                'required', 'string', 'max:255',
                Rule::unique('domains', 'domain')->ignore($gym->domains->first()?->id),
            ],
        ];
    }
}
