<?php

namespace App\Http\Requests\Security;

use Illuminate\Foundation\Http\FormRequest;

class BlockUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'reason'        => ['required', 'string', 'max:500'],
            'blocked_until' => ['nullable', 'date', 'after:now'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required'       => 'Alasan blokir wajib diisi.',
            'blocked_until.after'   => 'Tanggal blokir harus di masa mendatang.',
        ];
    }
}
