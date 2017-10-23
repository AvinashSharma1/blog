<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use League\OAuth2\Server\Exception\InvalidCredentialsException;
use Illuminate\Support\Facades\Request;
use App\Exceptions\LoginCaptchaException;
use Illuminate\Support\Facades\Auth;
use App\Modules\Admin\Models\AdminToken;
use App\Modules\Admin\Models\Role;
use App\Modules\Admin\Models\Role\Admin as Role_Admin;
use Exception;
use DateTime;
use Hash;
use Input;

use DB;
use App\Modules\Core\Helpers\DbHelper;
use App\Models\BaseModel;

class User extends Authenticatable
{

    
    /**
     * The Constant used to specify the Active Admin
     */
    const ACTIVE = 1;
    const INACTIVE = 0;
    const STATUS_DELETE = -1;
    /**
     * The connection associated with the model.
     *
     * @var string
     */
    protected $connection = 'AD';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The Static used to show the captcha
     */
    public static $_SHOW_CAPTCHA = true;

    /**
     * The Static used to don't show the captcha
     */
    public static $_DONT_SHOW_CAPTCHA = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'password'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

    /**
     * Parameter list that applies to the AD Admins table
     */
    public static $USERS_FILTERS = [
        'first_name'            => ["first_name", 'LIKE', '%%%s%%'],
        'last_name'             => ['last_name', 'LIKE', '%%%s%%'],
        'admin_id'              => ['id', '=', '%s'],
        'email'                 => ['email', 'LIKE', '%%%s%%']
    ];

    /**
     * Parameter list that applies to date conditions in the clause
     */
    public static $DATE_FILTERS = [
        'from_date'             => ['created_at', '>=', '%s'],
        'to_date'               => ['created_at', '<=', '%s']
    ];

    /**
     * Parameter list that applies to sort order by  in the clause
     */
    public static $SORT_FILTERS = [
        'created_at'            => ['created_at', '=', '%s'],
        'first_name'            => ['first_name', '=', '%s'],
        'last_name'             => ['last_name', '=', '%s'],
        'email'                 => ['email', '=', '%s']
    ];

    /**
     * Parameter list that applies to the AD Admins table
     */
    public static $ADMIN_FILTERS = [
        'admin_id'              => ['id', '=', '%s'],
        'email'                 => ['email', 'LIKE', '%%%s%%']
    ];


    /**
     * The attributes for validation.
     *
     * @var array
     */
    public static $rules = [
        'email'                => 'required|email|unique:admins',
        'first_name'           => 'required',
        'last_name'            => 'required',
        'password'             => 'required|min:6|max:20'
    ];

    /**
     * The pattern matching filter columns.
     *
     * @var array
     */
    public static $filter_columns = [
        'like' => ['email', 'first_name', 'last_name','created_at'],
        'equal' => ['is_active','id']
    ];

    /**
     * The sort columns.
     *
     * @var array
     */
    public static $sort_columns = [];

    /**
     * Creates new admin.
     *
     * @return object|bool
     */
    public function createUser($post=[])
    {
        $user = self::where('email','=',$post['email'])->where('is_active','!=',self::STATUS_DELETE)->first();
        if(!empty($user))
        {
            return false;
        }       
              
        $user = new User();

        $user->email      = $post['email'];
        $user->first_name = $post['first_name'];
        $user->last_name  = $post['last_name'];
        $user->password   = Hash::make($post['password']);
        $user->is_active  = self::INACTIVE;

        if ($user->save())
        {
            return $user;
        }
        
        return false;
    }

    /**
     * Sets an admin's is_active to a value.
     *
     * @return object|bool
     */
    public static function setActive ($admin_id, $value = self::ACTIVE)
    {
        $admin = self::find($admin_id);
        $admin->is_active = $value;
        
        if ($admin->save())
        {
            return $admin;
        }
        
        return false;
    }

    /**
     * This function check the users login Attempt
     * @param type $username
     * @return boolean
     * @throws LoginCaptchaException
     */
    public function verifyLoginAttempt($username)
    {
        $ip = Request::ip();
        $users = self::where('email', $username)
                        ->take(1)
                        ->get();

        if(!isset($users[0])||empty($users[0]))
        {
            return false;
        }

        try
        {
            $login_config = config('userlogin.login_attempt');
            $login_attempt = $login_config['LOGIN_ATTEMPT_COUNT'];
            $login_attempt_time = $login_config['LOGIN_ATTEMPT_TIME'];

            $cur_date = new DateTime(date('Y-m-d H:i:s'));
            $since_start = $cur_date->diff(new DateTime($users[0]->last_login));
            $minutes = $since_start->days * 24 * 60;
            $minutes += $since_start->h * 60;
            $minutes += $since_start->i;
            $time_diff = $minutes;

            //For first time login attempt
            $users[0]->login_attempts++;

            $users[0]->last_login_ip != $ip ?$users[0]->last_login_ip = $ip:'';

            if($users[0]->login_attempts <= 1 || $time_diff >= $login_attempt_time)
            { 
                $users[0]->last_login = date("Y-m-d H:i:s");
                $users[0]->login_attempts = 1;
            }

            $users[0]->update();
        }
        catch(Exception $e)
        {
            /**
             * Return false will throw InvalidCredentialsException in Exception_Handler_Middleware 
             */
            return false;
        }

        if($users[0]->login_attempts > $login_attempt 
                && $time_diff <= $login_attempt_time
                && $users[0]->last_login_ip == $ip)
        {
           throw new InvalidCredentialsException('invalid_credentials');
        }

        return false;
    }

    /**
     * This function updates the user info after successfully login
     * @return boolean
     */
    public function login($credentials) 
    {

        try
        {

            if(Auth::once($credentials))
            {
                Auth::user()->login_attempts = 1;
                Auth::user()->logins++;
                Auth::user()->last_login_ip = Request::ip();
                Auth::user()->last_login = date('Y-m-d H:i:s');
                Auth::user()->update();
                return Auth::user()->id;
            }
            else
            {
                return false;
            }

        }
        catch(Exception $ex) 
        {
            /**
             * Return false will throw InvalidCredentialsException in Exception_Handler_Middleware 
             */
            return false;
        }

        return false;
    }
    
    /**
     * This function used to check user email is existing or not
     * @param type $email
     * @return array
     */
    public function checkUserExist($email)
    {
        $users = self::where('email', $email)
                        ->where('is_active', self::ACTIVE)
                        ->take(1)
                        ->get();
        return (!isset($users[0])||empty($users[0]))?false:$users[0];
    }
    
    /**
     * This function is used to send the password reset link for existing user
     * and create the token for password reset request
     * @param type $data
     * @return string
     */
    public function sendPasswordResetLink($data)
    {

        if(!isset($data['username']) || empty($data['username']))
        {
            return false;
        }

        $user = $this->checkUserExist($data['username']);

        if(!$user)
        {
            return false;
        }

        $token = new AdminToken();
        $admin_token = $token->generatePasswordResetToken($user);

        $data['token'] = $admin_token->token;
        $this->sendMail($user, Mailer::TOKEN_TYPE_PASSWORD_RECOVERY, $data);

        return $admin_token;
    }

    /**
     * This function is used to Update the new password into DB
     * @param type $data
     * @return boolean
     */
    public function updatePassword($data)
    {

        try
        {
            $admin =  $this->verifyAdminId($data['id']);

            if(!$admin)
            {
                return false;
            }

            $token = AdminToken::where('token',$data['token'])
                                ->take(1)
                                ->get();
            $token[0]->is_active = AdminToken::INACTIVE;
            $token[0]->update();

            $admin->password = Hash::make($data['password']);
            $admin->update();
            return true;
        }
        catch(Exception $e)
        {
            throw new \PDOException($e->getMessage());
        }

    }

    /**
     * This function verify the admin id from database
     * @param int $id
     * @return object
     */
    public function verifyAdminId($id)
    {
        $users = self::where('id', $id)
                        ->where('is_active', self::ACTIVE)
                        ->take(1)
                        ->get();

        return (!isset($users[0])||empty($users[0]))?false:$users[0];
    }

    /**
     * Queries the AD Admins table and returns related data
     * for the admins list
     *
     * @param array $params A set of pre-defined request parameters
     * @param int $records_per_page
     * @return obj A paginated result set
     */
    public function getUsers($params, $records_per_page)
    {
    	$basemodel = new BaseModel();
        $query = $basemodel->useValues($this->queryAd(true), $params)
            ->useFilters(self::$USERS_FILTERS)
            ->useDateFilters(self::$DATE_FILTERS)
            ->useSortFilters(self::$SORT_FILTERS)
            ->apply();
        return $query->paginate($records_per_page);
    }

    /**
     * Returns a query that selects all the relevant fields for Admins
     * from the AD database
     *
     * @return obj Query object intended for the AD db
     */
    protected function queryAd($status=null)
    {
        $query = DB::connection($this->connection)
                ->table($this->table);

        if($status!=null){
            $query = $query->where('is_active', '!=', self::STATUS_DELETE);
        }

        return $query;
    }

    /**
     * Get admin using search parameters.
     *
     * @param array $search_parameters
     * @return object
     */
    public function getUser($search_parameters)
    {
        $basemodel = new BaseModel();
        $user = $basemodel->useValues($this->queryAd(), $search_parameters)
            ->useFilters(self::$USERS_FILTERS)
            ->useSortFilters(self::$SORT_FILTERS)
            ->apply();
        return $user->first();
    }
    
    /**
     * Gets admins with specific role
     *
     * @param string $role
     * @return object
     */
    public function getAdminsWithRole($role)
    {
        $admins = $this->whereHas('roles', function($q) use($role) {
            $q->where('name', $role);
        })->get();

        return $admins;
    }

    /**
     * Filter result with search parameters.
     *
     * @param object $query
     * @param array $search_parameters
     * @return object
     */
    public function filterResult($query, $search_parameters)
    {
        foreach($search_parameters as $key => $value)
        {
            if($value === 0 || !empty($value))
            {
                if(in_array($key, self::$filter_columns['like']))
                {
                    $query->where($key, 'LIKE', '%'. $value .'%');
                }

                if(in_array($key, self::$filter_columns['equal']))
                {
                    $query->where($key, $value);
                }
            }
        }

        if(!empty($search_parameters['from_date']) || !empty($search_parameters['to_date']))
        {
            $from_date = $search_parameters['from_date'];
            $to_date = $search_parameters['to_date'];
            $from_date = (DbHelper::validateDate($from_date)) ? $from_date : DbHelper::getFromDate($from_date);
            $to_date = (DbHelper::validateDate($to_date)) ? $to_date : DbHelper::getToDate($to_date);

            $query->where(
                'created_at', 
                '>=', 
               $from_date
            );
            $query->where(
                'created_at',
                '<=',
                $to_date
            );
        }

        if(!empty($search_parameters['sort_by']))
        {
            $sort_by = $search_parameters['sort_by'];

            if(in_array($sort_by, self::$filter_columns['equal']) 
                || (in_array($sort_by, self::$filter_columns['like'])))
            {
                $query = $query->orderBy($sort_by, $search_parameters['sort_type']);
            }
        }

        return $query;
    }

    /**
     * This function used to delete admin
     * @param type $admin_id
     * @return boolean
     * @throws Exception
     */
    public function deleteAdmin($admin_id)
    {

        try
        {
            //delete refer Role_Admin
            $roles = Role_Admin::where('user_id','=',$admin_id)->get();

            foreach($roles as $role)
            {
                  $role->where('user_id','=',$role->user_id)
                    ->where('role_id','=',$role->role_id)
                    ->delete();
            }
            
            if($this->isSuperAdmin($admin_id) == false)
            {
                //Admin Soft delete 
                $admin = self::find($admin_id);
                $admin->is_active = self::STATUS_DELETE;
                $admin->save();
            }
            else
            {
                return false;
            }
        } 
        catch (Exception $ex) 
        {
            throw $ex;
        }

        return true;
    }

    /**
     * check the Admin is super admin or not
     * @param type $admin_id
     * @return boolean
     */
    public function isSuperAdmin($admin_id)
    {
        $super_admin_status = false;
        //delete refer Role_Admin
        $roles = Role_Admin::where('user_id','=',$admin_id)->get();

        foreach($roles as $role)
        {
            $super_admin_role = Role::where('name','=','Super Admin')->take(1)->get();

            if($role->role_id == $super_admin_role[0]->id)
            {
                $super_admin_status = true;
            }

        }
        
        return $super_admin_status;
    }
}
