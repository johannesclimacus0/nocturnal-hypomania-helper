<?php

namespace App\Models;

use App\Concerns\HasUuidRouteKey;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuidRouteKey, Notifiable;

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function taskTypes(): HasMany
    {
        return $this->hasMany(TaskType::class);
    }

    public function nightSessions(): HasMany
    {
        return $this->hasMany(NightSession::class);
    }
}
