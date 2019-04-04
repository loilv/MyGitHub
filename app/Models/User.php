<?php

namespace App\Models;

use App\Constants\OrderPackageCode;
use Illuminate\Database\Eloquent\Model;
use App\Constants\DefineCode;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Runner\Exception;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    protected $table = 'users';
    protected $guarded = ['id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'type',
        'role',
        'company',
        'tax_code',
        'birth_day',
        'gender',
        'image_id',
        'status',
        'vip_package_id',
        'address',
        'token_social',
        'login_type',
        'city_id',
        'district_id',
        'verification',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'updated_at',
        'password',
        'remember_token',
        'created_at',
    ];

    /**
     * User to Image
     *
     *
     */
    public function getImage()
    {
        return $this->belongsTo('\App\Models\Image', 'id', 'commom_id')->where('path', '=', 'users');
    }
    // Rest omitted for brevity

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function userToCity()
    {
        return $this->belongsTo('App\Models\Taxonomy', 'city_id');
    }

    public function userToDistrict()
    {
        return $this->belongsTo('App\Models\Taxonomy', 'district_id');
    }

    public function orderPackageBiddings()
    {
        return $this->hasMany('App\Models\OrderPackage', 'user_id', 'id')
            ->where('package_id', '=', OrderPackageCode::PACKAGE_BIDDING);
    }

    public function orderPackageProjects()
    {
        return $this->hasMany('App\Models\OrderPackage', 'user_id', 'id')
            ->where('package_id', '=', OrderPackageCode::PACKAGE_PROJECT);
    }

    public function userProject()
    {
        return $this->hasMany('App\Models\UserProject', 'user_id', 'id');
    }

    public function userBidding()
    {
        return $this->hasMany('App\Models\UserBidding', 'user_id', 'id');
    }

    public function userDocument()
    {
        return $this->hasMany('App\Models\UserDocument', 'user_id', 'id');
    }

    public function product()
    {
        return $this->hasMany('App\Models\SellProduct', 'user_id', 'id');
    }

    public function userProduct()
    {
        return $this->hasMany('App\Models\UserProduct', 'user_id', 'id');
    }
}
