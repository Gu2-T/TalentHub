<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'applicant_id',
        'cover_letter',
        'status',
    ];

    public function job()
    {
        return $this->belongsTo(JobPost::class);
    }

    public function applicant()
    {
        return $this->belongsTo(ApplicantProfile::class, 'applicant_id');
    }
}
