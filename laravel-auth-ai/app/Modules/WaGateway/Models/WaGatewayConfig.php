<?php

namespace App\Modules\WaGateway\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WaGatewayConfig extends Model
{
    use HasFactory;

    protected $table = 'wa_gateway_configs';

    protected $fillable = [
        'user_id',
        'name',
        'purpose',
        'token',
        'is_active',
        'send_on_critical_alert',
        'alert_phone_number',
        'webhook_url',
        'meta',
    ];

    protected $hidden = ['token'];

    protected $casts = [
        'is_active' => 'boolean',
        'send_on_critical_alert' => 'boolean',
        'meta' => 'array',
        'token' => 'encrypted',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function logs()
    {
        return $this->hasMany(WaGatewayLog::class);
    }

    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    public function shouldSendAlerts(): bool
    {
        return $this->send_on_critical_alert === true && $this->isActive();
    }
}
