<?php

namespace App\Http\Requests\Backend;

use App\Models\Gym;
use Illuminate\Foundation\Http\FormRequest;

class StoreGymRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Gym::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash'],
            'domain' => ['required', 'string', 'max:255', 'unique:domains,domain'],
        ];
    }
}
