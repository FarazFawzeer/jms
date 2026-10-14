<?php

namespace App\Models;

use App\Support\Division as DivisionAccess;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'telephone',
        'image_path',
        'active',
        'comp_code',
        'stock_loc_sys_code', // division (pStock_Location_Tab)
        'user_sys_code',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'active' => 'boolean',
    ];

    public function isAdmin(): bool
    {
        return in_array($this->type, ['Super Admin', 'Admin'], true);
    }

    /** Admin who sees all divisions: may manage company details, divisions and users. */
    public function canManageSystem(): bool
    {
        return $this->isAdmin() && DivisionAccess::seesAll($this);
    }

    public function seesAllDivisions(): bool
    {
        return DivisionAccess::seesAll($this);
    }

    public function getDivisionNameAttribute(): string
    {
        return DivisionAccess::label($this);
    }
}
