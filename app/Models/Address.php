<?php
namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'phone',
        'email',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'country',
        'postal_code',
        'is_default',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
