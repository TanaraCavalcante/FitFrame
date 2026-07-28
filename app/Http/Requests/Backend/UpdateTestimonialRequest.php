<?php

namespace App\Http\Requests\Backend;

use App\Models\Testimonial;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTestimonialRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Testimonial $testimonial */
        $testimonial = $this->route('testimonial');

        return $this->user()->can('manage', $testimonial->gym);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'author_name' => ['required', 'string', 'max:255'],
            'text' => ['required', 'string', 'max:1000'],
            'member_since' => ['nullable', 'string', 'max:255'],
        ];
    }
}
