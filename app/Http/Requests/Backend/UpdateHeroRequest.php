<?php

namespace App\Http\Requests\Backend;

use App\Models\Gym;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHeroRequest extends FormRequest
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
            'hero_kicker' => ['nullable', 'string', 'max:255'],
            'hero_title' => ['nullable', 'string', 'max:255'],
            'hero_subtitle' => ['nullable', 'string', 'max:1000'],
            'hero_cta_primary' => ['nullable', 'string', 'max:255'],
            'hero_cta_secondary' => ['nullable', 'string', 'max:255'],
            'hero_stat_number' => ['nullable', 'integer'],
            'hero_stat_label' => ['nullable', 'string', 'max:255'],
            'visual_mode' => ['required', Rule::in(['images', 'video'])],
            'image_1' => ['nullable', 'image', 'max:5120'],
            'image_2' => ['nullable', 'image', 'max:5120'],
            'image_3' => ['nullable', 'image', 'max:5120'],
            'remove_image_1' => ['nullable', 'boolean'],
            'remove_image_2' => ['nullable', 'boolean'],
            'remove_image_3' => ['nullable', 'boolean'],
            'video' => ['nullable', 'file', 'mimetypes:video/mp4,video/quicktime', 'max:51200'],
            'remove_video' => ['nullable', 'boolean'],
        ];
    }
}
