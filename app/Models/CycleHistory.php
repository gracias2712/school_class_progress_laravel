<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CycleHistory extends Model
{
    use HasFactory;

    protected $fillable = ['changed_by_user_id', 'from_cycle', 'to_cycle', 'change_reason'];

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}