<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apimaster extends Model
{
    use HasFactory;
    protected $table = 'api_master';
    protected $primaryKey = 'id';
}
