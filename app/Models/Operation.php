<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Operation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'operation_number',
        'title',
        'description',
        'status',
    ];
}
