<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Notifications\Notifiable;

use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject

{

    use HasFactory, Notifiable;

protected $fillable = [

    'name',

    'email',

    'password',

    'role',

    'is_verified',

    'verification_code',

    'email_verified_at',

    'remember_token', // ✅ allow mass assignment

];

    protected $hidden = [

        'password',

        'remember_token',

    ];

    protected function casts(): array

    {

        return [

            'email_verified_at' => 'datetime',

            'password' => 'hashed',

        ];

    }

    // ✅ Human-readable role

/**

 * Convert numeric role to human-readable role name.

 */

public function getRoleNameAttribute()

{

    $role = $this->attributes['role'] ?? null;

    return match((int) $role) {

        1 => 'admin',

        2 => 'employer',

        3 => 'applicant',

        default => 'unknown',

    };

}

    public function getJWTIdentifier()

    {

        return $this->getKey();

    }

    public function getJWTCustomClaims()

    {

        return [];

    }
    public function employerProfile()
{
    return $this->hasOne(EmployerProfile::class, 'user_id');
}

public function applicantProfile()
{
    return $this->hasOne(ApplicantProfile::class, 'user_id');
}

public function applications()
{
    return $this->hasMany(Application::class);
}

public function jobPosts()
{
    return $this->hasMany(JobPost::class);
}

}