<?php

namespace App\Http\Requests\Backend;

use App\Models\Plan;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Plan $plan */
        $plan = $this->route('plan');

        return $this->user()->can('manage', $plan->gym);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'highlighted' => ['nullable', 'boolean'],
            'features' => ['required', 'array', 'min:1'],
            'features.*' => ['required', 'string', 'max:255'],
        ];
    }
}
