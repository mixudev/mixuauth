<?php

namespace App\Modules\Authentication\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class VerifyOtpRequest extends FormRequest
{
    /*
    |--------------------------------------------------------------------------
    | Form Request untuk validasi input verifikasi OTP.
    |--------------------------------------------------------------------------
    */

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $otpLength = config('security.otp.length', 6);

        return [
            'session_token' => ['required', 'string', 'size:64'],
            'otp_code'      => ['required', 'string', "size:{$otpLength}", 'regex:/^\d+$/'],
        ];
    }

    public function messages(): array
    {
        $length = config('security.otp.length', 6);

        return [
            'session_token.required' => 'Token sesi OTP wajib disertakan.',
            'session_token.size'     => 'Token sesi tidak valid.',
            'otp_code.required'      => 'Kode OTP wajib diisi.',
            'otp_code.size'          => "Kode OTP harus tepat {$length} digit.",
            'otp_code.regex'         => 'Kode OTP hanya boleh berisi angka.',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            response()->json([
                'message'    => 'Data verifikasi OTP tidak valid.',
                'error_code' => 'VALIDATION_FAILED',
                'errors'     => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
