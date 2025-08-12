<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportOnService extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reportable_id',
        'reportable_type',
        'report_reason_id',
        'additional_comments',
        'status',
        'admin_id',
        'admin_notes',
    ];

    /**
     * Get the parent reportable model (property or service provider).
     */
    public function reportable()
    {
        return $this->morphTo();
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function reason()
    {
        return $this->belongsTo(ReportReason::class, 'report_reason_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}