<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bulkfilelog extends Model
{
    use HasFactory;
    protected $table = 'bulkfile_log';
    protected $primaryKey = 'id';
    protected $fillable = ['user_id', 'client_id', 'api_id', 'vendor','filename', 'upload_url', 'downloadurl', 'is_processed','status'];
}
