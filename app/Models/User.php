<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasTimestamps, SoftDeletes;
    protected $table = 'user';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
		'username',
		'firstname',
		'lastname',
        'phone',
        'money',
        'max_bet',
        'max_draw_bet',
        'board_access',
        'allowed_sides',
        'lang_code',
        'ui_code',
        'group_id',
		'password',
		'password_raw',
		'pin',
		'type',
		'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    /**
     * The attributes that should be included in the payload of jwt
     */
    public function getPayload()
    {
        return [
            'exp' => strtotime(date('Y-m-d H:i:s')) + config('jwt.ttl'),
            'sub' => $this->id,
            'type' => $this->type
        ];
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function getCreatedAtAttribute($date)
    {
        return Carbon::parse($date)->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtAttribute($date)
    {
        return Carbon::parse($date)->format('Y-m-d H:i:s');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
