<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Users extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     * 
     */
    protected $fillable = [
        'id',
        'role',
        'client_id',        
        'name',
        'mobile',
        'email',
        'username',
        'password',
        'status',
        'gender',
        'created_at',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getStatusAttribute($value)
    {
        return $value == 1 || $value == 0 ? 'Active' : 'Inactive';
    }
    public function getRoleAttribute($value)
    {
       if($value == 'user'){
        return 'User';
       }else if($value == 'admin'){
        return 'Admin';
        }else if($value == 'mis'){
            return 'Mis';
        }
    }
    
    public function getClientidameAttribute()
    {
        return $this->client_id  = ($this->client_id == 1) ? 'Authbridge' : 'Nothing';
    }

    protected function runSoftDelete()
    {
        $query = $this->newModelQuery()->where($this->getKeyName(), $this->getKey());

        $this->{$this->getDeletedAtColumn()} = $time = $this->freshTimestamp();
        $this->{$this->getDelStatusColumn()} = 2;

        $query->update([
            $this->getDeletedAtColumn() => $this->fromDateTime($time),
            $this->getDelStatusColumn() => 2,
        ]);
    }

    public function getDelStatusColumn()
    {
       return 'del_status';
    }
}
