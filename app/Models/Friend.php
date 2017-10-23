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

class Friend extends BaseModel
{
    /**
     * The Constant used to specify the Friend Request
     */
    const ACCEPT = 1;
    const REJECT = 0;

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
    protected $table = 'friends';


    /**
     * Parameter list that applies to the AD Admins table
     */
    public static $USERS_FILTERS = [
        'request_receiver' => ['request_receiver', '=', '%s'],
        'status' => ['status', '=', '%s'],
    ];

    /**
     * Parameter list that applies to date conditions in the clause
     */
    public static $DATE_FILTERS = [
        'from_date' => ['created_at', '>=', '%s'],
        'to_date' => ['created_at', '<=', '%s']
    ];

    /**
     * Parameter list that applies to sort order by  in the clause
     */
    public static $SORT_FILTERS = [
        'created_at' => ['created_at', '=', '%s'],
    ];

    /**
     * Creates new friend request.
     *
     * @return object|bool
     */
    public function createFriendRequest ($post)
    {
        $this->request_sender  = $post['request_sender'];
        $this->request_receiver  = $post['request_receiver'];

        if ($this->save())
        {
            return $this;
        }

        return false;
    }

    /**
     * Queries the Friend  table and returns related data
     * for the friend request list
     *
     * @param array $params A set of pre-defined request parameters
     * @param int $records_per_page
     * @return obj A paginated result set
     */
    public function getFriendRequest($params, $records_per_page)
    {
        $basemodel = new BaseModel();
        $query = $basemodel->useValues($this->queryAd(), $params)
            ->useFilters(self::$USERS_FILTERS)
            ->useDateFilters(self::$DATE_FILTERS)
            ->useSortFilters(self::$SORT_FILTERS)
            ->apply();
        return $query->paginate($records_per_page);
    }

    /**
     * Queries the Friend  table and returns related data
     * for the mutual list
     *
     * @param array $params A set of pre-defined request parameters
     * @param int $records_per_page
     * @return obj A paginated result set
     */
    public function getMutualFriendList($params, $records_per_page)
    {
        /*
         * get the list of friend who has user id and except login user id
         */
        $result = $this->queryMutualFriend($params)->get();

        $mutual_id_lists = array();
        foreach($result as $data){
            $mutual_find = $data->request_receiver;
            if($mutual_find == $params['request_sender'] || $mutual_find == $params['request_receiver'])
            {
                $mutual_id =  $data->request_sender;
            }
            else
            {
                $mutual_id = $data->request_receiver;
            }

            if($mutual_id != $params['request_sender'] && $mutual_id != $params['request_receiver'])
            {
                $mutual_id_lists[] = $mutual_id;
            }

        }

        $params['user_id'] = $params['request_sender'];
        $params['user_lists'] = $mutual_id_lists;

        /*
         * get the actual user list which get common user from visitor or logged in user
         */
        $result = $this->queryMutualFriendFromLoginUser($params)->get();

        $actual_friend_list = array();
        foreach($result as $data) {

            $actual_friend_list[] = ($data->request_sender == $params['request_sender'])? $data->request_receiver : $data->request_sender;

        }

        /*
         * Now here we are getting user name details and applying filters.
         */
        $basemodel = new BaseModel();
        $query = $basemodel->useValues($this->getUsersList($actual_friend_list), $params)
                ->apply();
        return $query->paginate($records_per_page);

    }

    /**
     * Returns a query that selects all the relevant fields for users
     * from the AD database
     *
     * @return obj Query object intended for the AD db
     */
    protected function queryAd($status=null)
    {
        $query = DB::connection($this->connection)
            ->table("$this->table as f");

        $query = $query->leftJoin('users as u', 'f.request_sender', '=', 'u.id');

        return $query;
    }

    /**
     * Returns a query that selects all the relevant fields for user list
     * from the AD database
     *
     * @return obj Query object intended for the AD db
     */
    protected function getUsersList($data)
    {
        $query = DB::connection($this->connection)
            ->table("users")
            ->WhereIn('id', $data);

        return $query;
    }

    /**
     *
     * * get the actual user list which get common user from visitor or logged in user
     *
     * @param array $data A set of pre-defined request parameters
     * @return obj Query object intended for the AD db
     **/
    protected function queryMutualFriendFromLoginUser($data)
    {
        $query = DB::connection($this->connection)
            ->table("$this->table")
            ->where(function ($query) use (&$data) {
                $query->where('request_sender', '=', $data['user_id'])
                    ->WhereIn('request_receiver', $data['user_lists']);
            })->orWhere(function ($query) use (&$data) {
                $query->whereIn('request_sender', $data['user_lists'])
                    ->where('request_receiver', '=', $data['user_id']);
            });

        return $query;
    }

    /**
     *
     * get the list of friend who has user id and except login user id
     *
     * @param array $data A set of pre-defined request parameters
     * @return obj Query object intended for the AD db
     **/
    protected function queryMutualFriend($data)
    {
        $query = DB::connection($this->connection)
            ->table("$this->table")
            ->where(function ($query) use (&$data) {
                $query->orWhere('request_sender', '=' ,$data['request_receiver'])
                    ->orWhere('request_receiver', '=', $data['request_receiver']);
            })->where(function ($query) use (&$data) {
                $query->orWhere('request_sender','<>' ,$data['request_sender'])
                    ->orWhere('request_receiver', '<>', $data['request_sender']);
            });

        return $query;
    }

    /**
     * Sets an Friend request to a value.
     *
     * @return object|bool
     */
    public static function setActive ($post, $value = self::ACCEPT)
    {
        $user = self::where('request_sender', $post['request_sender'])
            ->where('request_receiver', $post['request_receiver'])
            ->where('status', self::REJECT)
            ->update([
                'status' => $value
            ]);

        if ($user!=null)
        {
            return $user;
        }

        return false;
    }
}