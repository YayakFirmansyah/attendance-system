<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
    ];

    public static function getValue(string $key, $default = null)
    {
        $setting = static::query()->where('key', $key)->value('value');

        return $setting ?? $default;
    }

    public static function setValue(string $key, $value, string $type = 'string'): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => (string) $value,
                'type' => $type,
            ]
        );
    }
}
