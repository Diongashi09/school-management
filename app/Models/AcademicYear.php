<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_current',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    /**
     * Get the classes for this academic year.
     */
    public function classes()
    {
        return $this->hasMany(ClassModel::class);
    }

    /**
     * Scope to get the current academic year.
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * Boot method to ensure only one current academic year.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($academicYear) {
            if ($academicYear->is_current) {
                static::where('is_current', true)->update(['is_current' => false]);
            }
        });

        static::updating(function ($academicYear) {
            if ($academicYear->is_current) {
                static::where('is_current', true)
                    ->where('id', '!=', $academicYear->id)
                    ->update(['is_current' => false]);
            }
        });
    }
}
