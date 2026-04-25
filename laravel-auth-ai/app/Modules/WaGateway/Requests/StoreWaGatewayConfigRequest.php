<?php

namespace App\Modules\WaGateway\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\WaGateway\Models\WaGatewayConfig;

class StoreWaGatewayConfigRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if (!$user) {
            return false;
        }

        $config = $this->route('config');

        return $config instanceof WaGatewayConfig
            ? $user->can('update', $config)
            : $user->can('create', WaGatewayConfig::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $supportedProviders = implode(',', array_keys(config('wa_gateway.providers', ['fonnte' => []])));
        $isUpdate = $this->route('config') instanceof WaGatewayConfig;

        return [
            'name' => 'required|string|max:255',
            'purpose' => 'required|string|in:security,auth,info,system',
            'token' => $isUpdate ? 'nullable|string|min:10|max:255' : 'required|string|min:10|max:255',
            'alert_phone_number' => ['required', 'string', 'max:20', 'regex:/^\d{8,16}$/'],
            'send_on_critical_alert' => 'boolean',
            'is_active' => 'boolean',
            'meta' => 'nullable|array',
            'meta.provider' => 'nullable|string|in:' . $supportedProviders,
        ];
    }
}
