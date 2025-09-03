<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

class JobPost extends Model

{

    use HasFactory;

    protected $fillable = [

        'user_id',

        'title',

        'description',

        'location',

        'salary',

        'deadline',

    ];

    protected $casts = [

        'deadline' => 'date',

    ];

    // ✅ Relationship: Job belongs to Employer (User)

    public function employer()

    {

        return $this->belongsTo(User::class, 'user_id');

    }

    // ✅ Helper: check if job is active (deadline not passed)

    public function getIsActiveAttribute()

    {

        return !$this->deadline || $this->deadline->isFuture();

    }

    public function applications()
{
    return $this->hasMany(Application::class);
}

public function employerProfile()
{
    return $this->belongsTo(EmployerProfile::class, 'user_id', 'user_id');
}

}