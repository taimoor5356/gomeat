<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class DataCon extends Model
{
    use HasFactory;
    protected $table = 'data_con';

    // protected $casts = [
    //     'parent_id' => 'integer',
    //     'position' => 'integer',
    //     'priority' => 'integer',
    //     'status' => 'integer'
    // ];

    protected $fillable = [
        'time',
        'activity',
        'lifecycle', 
        'filepath', 
        'created_at', 
        'updated_at'
    ];

    // public function translations()
    // {
    //     return $this->morphMany(Translation::class, 'translationable');
    // }

    // public function module()
    // {
    //     return $this->belongsTo(Module::class);
    // }
    
    // public function scopeModule($query, $module_id)
    // {
    //     return $query->where('module_id', $module_id);
    // }

    // public function scopeActive($query)
    // {
    //     return $query->where('status', '=', 1);
    // }

    // public function childes()
    // {
    //     return $this->hasMany(Category::class, 'parent_id');
    // }

    // public function parent()
    // {
    //     return $this->belongsTo(Category::class, 'parent_id');
    // }

    // protected static function booted()
    // {
    //     static::addGlobalScope('translate', function (Builder $builder) {
    //         $builder->with(['translations' => function ($query) {
    //             return $query->where('locale', app()->getLocale());
    //         }]);
    //     });
    // }
}
