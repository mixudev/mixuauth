<?php
// ============================================================
// app/Models/IpWhitelist.php
// ============================================================
namespace App\Modules\Security\Models;

use Illuminate\Database\Eloquent\Model;

class IpWhitelist extends Model
{
    protected $table = 'ip_whitelist';

    protected $fillable = ['ip_address', 'label', 'added_by'];
}
