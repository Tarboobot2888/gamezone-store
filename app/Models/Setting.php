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
        'group',
    ];

    public function getValueAttribute($value)
    {
        switch ($this->type) {
            case 'boolean':
                return (bool) $value;
            case 'integer':
                return (int) $value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    public function setValueAttribute($value)
    {
        switch ($this->type) {
            case 'boolean':
                $this->attributes['value'] = $value ? '1' : '0';
                break;
            case 'integer':
                $this->attributes['value'] = (string) $value;
                break;
            case 'json':
                $this->attributes['value'] = json_encode($value);
                break;
            default:
                $this->attributes['value'] = $value;
        }
    }

    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set($key, $value, $type = 'string', $group = 'general')
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group
            ]
        );
    }

    public static function getGroup($group)
    {
        return static::where('group', $group)->pluck('value', 'key')->toArray();
    }
}
