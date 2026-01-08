<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    // Automatically convert JSON strings to arrays when retrieved
    protected $casts = [
        'value' => 'json',
    ];

    // Helper function to retrieve a setting by key
    public static function get(string $key, $default = null)
    {
        return self::where('key', $key)->value('value') ?? $default;
    }

    // Helper function to set/update a setting
    public static function set(string $key, $value)
    {
        return self::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
