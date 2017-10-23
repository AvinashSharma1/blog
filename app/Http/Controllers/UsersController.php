<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Input;
use App\Helpers\Pagination;
use App\Http\Requests;
use App\Helpers\Arr;
class UsersController extends ActionController
{
    /**
     * @SWG\Get(
     *   path="/api/users",
     *   tags={"User Module"},
     *   summary="Get all users lists",
     *   description="List of register users",
     *   operationId="api.users.index",
     *   consumes={"application/x-www-form-urlencoded"},
     *   produces={"application/json", "application/xml"},
     *   @SWG\Parameter(
     *     name="user_id",
     *     in="query",
     *     description="User ID",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="email",
     *     in="query",
     *     description="User Email ID",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="first_name",
     *     in="query",
     *     description="First Name",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="last_name",
     *     in="query",
     *     description="Last Name",
     *     required=false,
     *     type="string"
     *   ),
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
     *     description="Sort by field (`created_at` | `first_name` | `last_name` | `email`)",
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
     *
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
    @SWG\Items(
    @SWG\Property(property="self", type="string",
    description="Total number of records", default="http://local.blog.api.com/api/users?&records_per_page=10&page=1"),

     *                                  @SWG\Property(property="first", type="string", description="Total number of records", default="http://local.blog.api.com/api/users?&records_per_page=10&page=1"),

    @SWG\Property(property="last", type="string",
    description="Total number of records", default="http://local.blog.api.com/api/users?&records_per_page=10&page=3844"),

    @SWG\Property(property="next", type="string",
    description="Total number of records", default="http://local.blog.api.com/api/users?&records_per_page=10&page=2")
    )
    ),
     *                       ),
     *     ),
     *     ),
     *                       @SWG\Property(title="Response", property="response", type="array",
     *                                @SWG\Items(
     *                                      @SWG\Property(property="users_id", type="integer",
    description="users Id", default="1"),
    @SWG\Property(property="email", type="string", description="Email Id", default="test+1@test.com"),
    @SWG\Property(property="first_name", type="string", description="First Name", default="test 1"),
    @SWG\Property(property="last_name", type="string", description="Last Name", default="test"),
    @SWG\Property(property="status", type="integer", description="Status", default="1"),
    @SWG\Property(property="singup_status", type="integer", description="Singup status", default="null"),
     *                                      @SWG\Property(property="created_at", type="string",
    description="Created Date", default="2017-03-08 10:59:11"),
    @SWG\Property(property="updated_at", type="string", description="Updated Date", default="2017-03-08 10:59:11")
     *                              )
     *                       ),
     *              ),
     *         )
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
     * )
     *
     * Displays all users.
     *
     * @param \App\Http\Controllers\Request $request
     * @return json response
     * @throws Exception
     */
    public function index()
    {
        /**
         * Need to create Endpoint Dependant validation rules
         */
        $validation_rules = array(
            'sort_by' => 'sometimes|in:'.implode(array_keys(User::$SORT_FILTERS), ',')
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

        $user = new User();
        $result = array();
        $user_data = $user->getUsers($search, $this->records_per_page);

        if(count($user_data) > 0)
        {
            $query_params = Input::all();
            $result = array('pagination' => Pagination::formatPagination($this->request, $user_data, $query_params));
            $result['response'] = $this->getFormatedData($user_data);
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
}
