<?php

namespace App;

//use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable ////implements JWTSubject
{
	protected $table = 'users';
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'username', 'email', 'roleId', 'password',  'active', 'fullname', 'phone', 'address', 'provinceId', 'cityId', 'imageId'
    ];
	public static function generateId ()
	{
		$number = date('ymd').sprintf('%04s',mt_rand(0, 9999));
		if (Self::isIdExist($number)) {
		   return generateId();
		}
		return $number;
	}

	public static function isIdExist ($number)
	{
		return self::where('id',$number)->exists();
	}
	
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
	/*public function getJWTIdentifier()
    {
        return $this->getKey();
    }*/

	public static function dataUser()
	{
		return self::get();
	}
	
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    /*public function getJWTCustomClaims()
    {
        return [];
    }*/
}
