<?php

namespace App\Http\Requests\Backend;

use App\Models\Gym;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGalleryRequest extends FormRequest
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
            'image_1' => ['nullable', 'image', 'max:5120'],
            'image_2' => ['nullable', 'image', 'max:5120'],
            'image_3' => ['nullable', 'image', 'max:5120'],
            'image_4' => ['nullable', 'image', 'max:5120'],
            'image_5' => ['nullable', 'image', 'max:5120'],
            'remove_image_1' => ['nullable', 'boolean'],
            'remove_image_2' => ['nullable', 'boolean'],
            'remove_image_3' => ['nullable', 'boolean'],
            'remove_image_4' => ['nullable', 'boolean'],
            'remove_image_5' => ['nullable', 'boolean'],
        ];
    }
}
