<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JazzcashOrder extends Model
{
    use HasFactory;
    // mass assignment
    protected $guarded = ['id'];
}
