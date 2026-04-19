<?php

namespace App\Modules\Authentication\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class LoginRequest extends FormRequest
{
    /*
    |--------------------------------------------------------------------------
    | Form Request untuk validasi input login.
    |
    | Validasi dilakukan di sini sebelum menyentuh controller,
    | memisahkan tanggung jawab validasi dari logika bisnis.
    |--------------------------------------------------------------------------
    */

    public function authorize(): bool
    {
        // Semua pengguna boleh mencoba login
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email:rfc,dns', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'max:128'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => 'Alamat email wajib diisi.',
            'email.email'       => 'Format alamat email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 8 karakter.',
        ];
    }

    /**
     * Pastikan error validasi dikembalikan dalam format JSON
     * agar konsisten dengan respons API lainnya.
     */
    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            response()->json([
                'message'    => 'Data yang dikirimkan tidak valid.',
                'error_code' => 'VALIDATION_FAILED',
                'errors'     => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY)
        );
    }

    /**
     * Bersihkan input sebelum validasi: trim spasi dan lowercase email.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->input('email', ''))),
        ]);
    }
}
