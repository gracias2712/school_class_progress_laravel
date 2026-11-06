<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classe extends Model
{
    use HasFactory;

    protected $table = 'classes';
    
    // Menambahkan kolom baru agar diizinkan untuk mass-assignment
    protected $fillable = [
        'class_name',
        'grade_level',
        'major',
        'section'
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'class_id');
    }
 
    public function students(): HasMany
    {
        return $this->hasMany(User::class, 'class_id'); 
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'class_id');
    }
}