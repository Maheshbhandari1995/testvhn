<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Creditslog extends Model
{
    use HasFactory;
    protected $table = 'credits_log';
    protected $primaryKey = 'id';
}
