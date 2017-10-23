<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use League\OAuth2\Server\Util\SecureKey;
use Illuminate\Support\Facades\Request;

class AdminToken extends Model
{
    const TYPE_ADMIN_CREATION = 'admin_creation';
    const TYPE_PASSWORD_RESET = 'password_reset';

    const TOKEN_INVALID = 'token_invalid';
    const TOKEN_EXPIRED = 'token_expired';

    const ACTIVE = 1;
    const INACTIVE = 0;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin_tokens';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'token'
    ];
    
    /**
     * The attributes for validation.
     *
     * @var array
     */
    public static $rules = [
        'admin_id' => 'required',
        'token'    => 'required|unique:admin_tokens',
        'type'     => 'required'
    ];

    /**
     * Creates new admin token.
     *
     * @return object|bool
     */
    public function createAdminToken ($admin_id)
    {
        $this->admin_id  = $admin_id;
        $this->token     = SecureKey::generate();
        $this->type      = self::TYPE_ADMIN_CREATION;
        $this->is_active = self::ACTIVE;

        if ($this->save())
        {
            return $this;
        }

        return false;
    }
    
    /**
     * This function create the password reset token
     * @param type $admin
     * @return boolean|\App\Modules\User\Models\AdminToken
     */
    public function generatePasswordResetToken($admin)
    {

        try
        {
            $this->admin_id  = $admin->id;
            $this->email     = $admin->email;
            $this->token     = SecureKey::generate();
            $this->type      = self::TYPE_PASSWORD_RESET;
            $this->is_active = self::ACTIVE;
            $this->created = time();

            /**
             * By Default Password reset toke expies after 60 min.
             * You can change expire time from Auth Config
             */

            $this->expires = strtotime("+".config('auth.passwords.admins.expire')." minutes");

            $this->user_agent = Request::header('User-Agent');

            if ($this->save())
            {
                return $this;
            }
        } 
        catch(Exception $ex) {
            return false;
        }

        return false;
    }

    /**
     * Checks if admin token is valid.
     *
     * @return object
     */
    public static function isActive ($token)
    {
        $admin_token = self::where('token', $token)
            ->where('type', self::TYPE_ADMIN_CREATION)
            ->where('is_active', self::ACTIVE)
            ->first();

        if($admin_token)
        {
            self::setActive($admin_token->id, self::INACTIVE);
        }

        return $admin_token;
    }

    /**
     * Sets an admin token's is_active to a value.
     *
     * @return object|bool
     */
    public static function setActive ($admin_token_id, $value = self::ACTIVE)
    {
        $admin_token = self::find($admin_token_id);
        $admin_token->is_active = $value;
        
        if ($admin_token->save())
        {
            return $admin_token;
        }
        
        return false;
    }
    
    /**
     * This function verify the TYPE_PASSWORD_RESET token
     * @param type $token
     * @return string
     */
    public function verifyToken($token,$type)
    {

        try
        {
            $admin_token = self::where('token', $token['token'])
                ->where('admin_id',$token['id'])
                ->where('type', $type)
                ->where('is_active', self::ACTIVE)
                ->first();

            if(empty($admin_token))
            {
                return self::TOKEN_INVALID;
            }

            if(time() > $admin_token->expires)
            {
                // set token inactive
                $admin_token->is_active = self::INACTIVE;
                $admin_token->update();
                return self::TOKEN_EXPIRED;
            }
        } 
        catch (Exception $ex) 
        {
            return false;
        }

        return $admin_token;
    }
}
