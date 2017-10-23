<?php

namespace App\Http\Controllers;

use App\Models\Friend;
use App\Models\User;
use Illuminate\Http\Request;
use Input;
use App\Helpers\Pagination;
use App\Helpers\Arr;
use App\Http\Requests;

class FriendsController extends ActionController
{

    /**
     * @SWG\Get(
     *   path="/api/users/request",
     *   tags={"Friend Module"},
     *   summary="Get all friend request",
     *   description="",
     *   operationId="api.users.friends.index",
     *   consumes={"application/x-www-form-urlencoded"},
     *   produces={"application/json", "application/xml"},
     *   @SWG\Parameter(
     *     name="from_date",
     *     in="query",
     *     description="From Date Format => (YYYY-MM-DD)",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="to_date",
     *     in="query",
     *     description="To Date Format => (YYYY-MM-DD)",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="sort_by",
     *     in="query",
     *     description="Sort by field (`created_at`)",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="sort_type",
     *     in="query",
     *     description="ASC or DESC",
     *     required=false,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     description="Page to view",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="records_per_page",
     *     in="query",
     *     description="Records to display per page",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="OK",
     *     @SWG\Schema(title="response", type="object", required={"status", "success", "message", "data"},
     *              @SWG\Property(property="status", type="integer", format="int64", description="Response code", default="200"),
     *              @SWG\Property(property="success", type="boolean", description="Is success", default="true"),
     *              @SWG\Property(property="message", type="string", description="Success message", default="Success"),
     *              @SWG\Property(title="Data", property="data", type="object", description="Data response",
     *                       @SWG\Property(title="Pagination", property="pagination", type="object",
     *                              @SWG\Property(property="total_records", type="integer", format="int64", description="Total number of records", default="38434"),
     *                              @SWG\Property(property="records_per_page", type="integer", format="int64", description="Number of records to display per page", default="10"),
     *                              @SWG\Property(property="total_pages", type="integer", format="int64", description="Total number of pages", default="3844"),
     *                              @SWG\Property(property="page", type="integer", format="int64", description="Current page number", default="1"),
     *                              @SWG\Property(property="links", type="array",
     *                              @SWG\Items(
     *                                  @SWG\Property(property="self", type="string",description="Total number of records", default="http://local.blog.api.com/api/users?&records_per_page=10&page=1"),
     *                                  @SWG\Property(property="first", type="string", description="Total number of records", default="http://local.blog.api.com/api/users?&records_per_page=10&page=1"),
     *                                  @SWG\Property(property="last", type="string",description="Total number of records", default="http://local.blog.api.com/api/users?&records_per_page=10&page=3844"),
     *                                  @SWG\Property(property="next", type="string",description="Total number of records", default="http://local.blog.api.com/api/users?&records_per_page=10&page=2"),
     *                                  ),
     *                       ),
     *
     *                       @SWG\Property(title="Response", property="response", type="array",
     *                                @SWG\Items(
     *                                      @SWG\Property(property="users_id", type="integer",description="users Id", default="1"),
     *                                      @SWG\Property(property="email", type="string", description="Email Id", default="test+1@test.com"),
     *                                      @SWG\Property(property="first_name", type="string", description="First Name", default="test 1"),
     *                                      @SWG\Property(property="last_name", type="string", description="Last Name", default="test"),
     *                                      @SWG\Property(property="status", type="integer", description="Status", default="1"),
     *                                      @SWG\Property(property="singup_status", type="integer", description="Singup status", default="null"),
     *                                      @SWG\Property(property="created_at", type="string",description="Created Date", default="2017-03-08 10:59:11"),
     *                                      @SWG\Property(property="updated_at", type="string", description="Updated Date", default="2017-03-08 10:59:11")
     *                                  ),
     *                       ),
     *    ),
     *   ),
     *   @SWG\Response(
     *     response=400,
     *     description="Bad Request",
     *   ),
     *   @SWG\Response(
     *     response=403,
     *     description="Forbidden",
     *   ),
     *   @SWG\Response(
     *     response=500,
     *     description="Internal Server Error",
     *   ),
     * ),),)
     *
     */

    public function index()
    {
        /**
         * Need to create Endpoint Dependant validation rules
         */
        $validation_rules = array(
            'sort_by' => 'sometimes|in:'.implode(array_keys(Friend::$SORT_FILTERS), ',')
        );

        /**
         * Need to create Endpoint Dependant validation messages
         */
        $validation_messages = array(
            'sort_by' => trans('validation.generic.invalid_sort_by')
        );

        /**
         * validate requested Params and get the search filters
         */
        $search = $this->validateParameters($validation_rules, $validation_messages, new User());

        /**
         * Need to return the error response from current controller only
         * otherwsie it will return FatalErrorException
         * so we can not return the json response from BaseController
         */
        if($search == false)
        {
            return $this->respondValidationError($this->errors);
        }

        $search['request_receiver'] = $this->user_id;
        $search['status'] = 0;

        $obj = new Friend();
        $result = array();
        $data = $obj->getFriendRequest($search, $this->records_per_page);

        if(count($data) > 0)
        {
            $query_params = Input::all();
            $result = array('pagination' => Pagination::formatPagination($this->request, $data, $query_params));
            $result['response'] = $this->getFormatedData($data);
        }

        return $this->respondWithSuccess($result);
    }

    /**
     * Format and clean the result data
     * @param object Users
     * @return array
     */
    public function getFormatedData($users)
    {
        $data_output = [];
        foreach($users as $key => $data)
        {
            $data_output[$key] = [
                'user_id'      => $data->id,
                'email'         => $data->email,
                'first_name'    => $data->first_name,
                'last_name'     => $data->last_name,
                'status'        => $data->is_active,
                'singup_status' => null, //we dont have any other singup statuses right now
                'created_at'    => $data->created_at,
                'updated_at'    => $data->updated_at
            ];

            //Clean HTML
            Arr::getPurifiedDataArray($data_output[$key]);
        }

        return $data_output;
    }

    /**
     * @SWG\Post(
     *   path="/api/users/request",
     *   tags={"Friend Module"},
     *   summary="Send friend request to user",
     *   description="",
     *   operationId="api.users.friends.store",
     *   produces={"application/json", "application/xml"},
     *   @SWG\Parameter(
     *     name="user_id",
     *     in="formData",
     *     description="User ID",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="OK",
     *     @SWG\Schema(title="response", type="object", required={"status", "success", "message", "data"},
     *              @SWG\Property(property="status", type="integer", format="int64",description="Response code", default="200"),
     *              @SWG\Property(property="success", type="boolean", description="Is success",default="true"),
     *              @SWG\Property(property="message", type="string", description="Success message",default="Your friend request has been sent!"),
     *              @SWG\Property(title="Data", property="data", type="array", description="Data response"),
     *    ),
     *   ),
     *  @SWG\Response(
     *     response=400,
     *     description="Bad Request",
     *   ),
     *   @SWG\Response(
     *     response=403,
     *     description="Forbidden",
     *   ),
     *   @SWG\Response(
     *     response=500,
     *     description="Internal Server Error",
     *   ),
     * )
     */

    /**
     * Send Friend request to user
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $result = array();

        $validation_rules = array(
            'user_id' => 'required|numeric',
        );

        $validation_messages = array(
            'user_id' => trans('validation.permissions.invalid_user_id'),
            'user_id_not_found' => trans('validation.permissions.user_id_not_found')
        );

        $search = $this->validateStore($validation_rules, $validation_messages);

        if ($search == false)
        {
            return $this->respondValidationError($this->errors);
        }

        $user_object = User::find($search['user_id']);
        if (!$user_object)
        {
            return $this->respondValidationError(trans('validation.permissions.invalid_user_id'));
        }

        $post['request_sender'] = $this->user_id;
        $post['request_receiver'] = $search['user_id'];

        $friend_object = new Friend();
        $response = $friend_object->createFriendRequest($post);

        /**
         * return success: status code 200
         */
        if($response == true)
        {
            return $this->respondWithSuccess($response);
        }

        return $this->respondValidationError();
    }

    /**
     * @SWG\Put(
     *   path="/api/users/{users}/accept/{user_id}",
     *   tags={"Friend Module"},
     *   summary="Friend Request Accept to user",
     *   description="",
     *   operationId="api.users.friends.update",
     *   produces={"application/json", "application/xml"},
     *   @SWG\Parameter(
     *     name="user_id",
     *     in="query",
     *     description="Sender Request User ID",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="OK",
     *     @SWG\Schema(title="response", type="object", required={"status", "success", "message", "data"},
     *              @SWG\Property(property="status", type="integer", format="int64",description="Response code", default="200"),
     *              @SWG\Property(property="success", type="boolean", description="Is success",default="true"),
     *              @SWG\Property(property="message", type="string", description="Success message",default="Friend Request has been accepted by you!"),
     *
     *    ),
     *   ),
     *  @SWG\Response(
     *     response=400,
     *     description="Bad Request",
     *   ),
     *   @SWG\Response(
     *     response=403,
     *     description="Forbidden",
     *   ),
     *   @SWG\Response(
     *     response=500,
     *     description="Internal Server Error",
     *   ),
     * )
     */

    /**
     * Send Friend request to user
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update()
    {
        $result = array();

        $validation_rules = array(
            'user_id' => 'required|numeric',
        );

        $validation_messages = array(
            'user_id' => trans('validation.permissions.invalid_user_id'),
            'user_id_not_found' => trans('validation.permissions.user_id_not_found')
        );

        $search = $this->validateStore($validation_rules, $validation_messages);

        if ($search == false)
        {
            return $this->respondValidationError($this->errors);
        }

        $user_object = User::find($search['user_id']);
        if (!$user_object)
        {
            return $this->respondValidationError(trans('validation.permissions.invalid_user_id'));
        }

        $post['request_sender'] = $search['user_id'];
        $post['request_receiver'] = $this->user_id;

        $friend_object = new Friend();
        $response = $friend_object->setActive($post);
        /**
         * return success: status code 200
         */
        if($response!=null)
        {
            return $this->respondWithSuccess($response);
        }

        return $this->respondValidationError();
    }


    /**
     * @SWG\Get(
     *   path="/api/users/mutual-friend-lists",
     *   tags={"Friend Module"},
     *   summary="Get mutual friend lists",
     *   description="",
     *   operationId="api.users.friends.index",
     *   consumes={"application/x-www-form-urlencoded"},
     *   produces={"application/json", "application/xml"},
     *   @SWG\Parameter(
     *     name="user_id",
     *     in="query",
     *     description="Profile visitor User ID",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     description="Page to view",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="records_per_page",
     *     in="query",
     *     description="Records to display per page",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="OK",
     *     @SWG\Schema(title="response", type="object", required={"status", "success", "message", "data"},
     *              @SWG\Property(property="status", type="integer", format="int64", description="Response code", default="200"),
     *              @SWG\Property(property="success", type="boolean", description="Is success", default="true"),
     *              @SWG\Property(property="message", type="string", description="Success message", default="Success"),
     *              @SWG\Property(title="Data", property="data", type="object", description="Data response",
     *                       @SWG\Property(title="Pagination", property="pagination", type="object",
     *                              @SWG\Property(property="total_records", type="integer", format="int64", description="Total number of records", default="38434"),
     *                              @SWG\Property(property="records_per_page", type="integer", format="int64", description="Number of records to display per page", default="10"),
     *                              @SWG\Property(property="total_pages", type="integer", format="int64", description="Total number of pages", default="3844"),
     *                              @SWG\Property(property="page", type="integer", format="int64", description="Current page number", default="1"),
     *                              @SWG\Property(property="links", type="array",
     *                              @SWG\Items(
     *                                  @SWG\Property(property="self", type="string",description="Total number of records", default="http://local.blog.api.com/api/users?&records_per_page=10&page=1"),
     *                                  @SWG\Property(property="first", type="string", description="Total number of records", default="http://local.blog.api.com/api/users?&records_per_page=10&page=1"),
     *                                  @SWG\Property(property="last", type="string",description="Total number of records", default="http://local.blog.api.com/api/users?&records_per_page=10&page=3844"),
     *                                  @SWG\Property(property="next", type="string",description="Total number of records", default="http://local.blog.api.com/api/users?&records_per_page=10&page=2"),
     *                                  ),
     *                       ),
     *
     *                       @SWG\Property(title="Response", property="response", type="array",
     *                                @SWG\Items(
     *                                      @SWG\Property(property="users_id", type="integer",description="users Id", default="1"),
     *                                      @SWG\Property(property="email", type="string", description="Email Id", default="test+1@test.com"),
     *                                      @SWG\Property(property="first_name", type="string", description="First Name", default="test 1"),
     *                                      @SWG\Property(property="last_name", type="string", description="Last Name", default="test"),
     *                                      @SWG\Property(property="status", type="integer", description="Status", default="1"),
     *                                      @SWG\Property(property="singup_status", type="integer", description="Singup status", default="null"),
     *                                      @SWG\Property(property="created_at", type="string",description="Created Date", default="2017-03-08 10:59:11"),
     *                                      @SWG\Property(property="updated_at", type="string", description="Updated Date", default="2017-03-08 10:59:11")
     *                                  ),
     *                       ),
     *    ),
     *   ),
     *   @SWG\Response(
     *     response=400,
     *     description="Bad Request",
     *   ),
     *   @SWG\Response(
     *     response=403,
     *     description="Forbidden",
     *   ),
     *   @SWG\Response(
     *     response=500,
     *     description="Internal Server Error",
     *   ),
     * ),),)
     *
     */

    public function show()
    {
        /**
         * Need to create Endpoint Dependant validation rules
         */
        $validation_rules = array(
            'user_id' => 'required|numeric',
            'sort_by' => 'sometimes|in:'.implode(array_keys(Friend::$SORT_FILTERS), ',')
        );

        /**
         * Need to create Endpoint Dependant validation messages
         */
        $validation_messages = array(
            'user_id' => trans('validation.permissions.invalid_user_id'),
            'user_id_not_found' => trans('validation.permissions.user_id_not_found'),
            'sort_by' => trans('validation.generic.invalid_sort_by')
        );

        /**
         * validate requested Params and get the search filters
         */
        $search = $this->validateParameters($validation_rules, $validation_messages, new User());

        /**
         * Need to return the error response from current controller only
         * otherwsie it will return FatalErrorException
         * so we can not return the json response from BaseController
         */
        if($search == false)
        {
            return $this->respondValidationError($this->errors);
        }

        $search['request_receiver'] = $search['user_id'];
        $search['request_sender'] = $this->user_id;

        $obj = new Friend();
        $result = array();
        $data = $obj->getMutualFriendList($search, $this->records_per_page);

        if(count($data) > 0)
        {
            $query_params = Input::all();
            $result = array('pagination' => Pagination::formatPagination($this->request, $data, $query_params));
            $result['response'] = $this->getFormatedData($data);
        }

        return $this->respondWithSuccess($result);
    }


    /**
     * Function to validate store method
     * @param array $validation_rules
     * @param array $validation_messages
     * @return boolean
     */
    public function validateStore($validation_rules = array(), $validation_messages = array())
    {
        $search = parent::validateParameters($validation_rules, $validation_messages, new Friend());
        return $search;
    }



}
