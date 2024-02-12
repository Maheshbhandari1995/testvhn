<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Api_mapping extends Model
{
    use HasFactory;
    protected $table = 'api_list';
    protected $primaryKey = 'id';
    //protected $fillable = [''];
}
