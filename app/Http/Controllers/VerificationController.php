<?php

namespace App\Http\Controllers;

use App\Http\Controllers\APIController;
use App\Models\User;
use App\Models\AdminToken;
use Authorizer;
use Input;
use Auth;
use Validator;
use Illuminate\Support\Facades\Config;
use App\Modules\Core\Helpers\Logger;
use Illuminate\Support\Facades\Log;

class VerificationController extends APIController
{
    /**
     * @SWG\Put(
     *   path="/api/users/{user_id}/verification/{verification_id}",
     *   tags={"Authentication Login and Registration Module"},
     *   summary="Activate User",
     *   description="",
     *   operationId="api.users.verification.update",
     *   produces={"application/json", "application/xml"},
     *   @SWG\Parameter(
     *     name="user_id",
     *     in="query",
     *     description="User Id",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="verification_id",
     *     in="query",
     *     description="Verification Id",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="token",
     *     in="query",
     *     description="Token",
     *     required=true,
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
     *                  @SWG\Property(property="User", type="object",
                        description="User",
                            @SWG\Property(property="id", type="integer", 
                        description="User id", default="1"),
                            @SWG\Property(property="email", type="string", 
                            description="email", default="test+1@test.com"),
                            @SWG\Property(property="first_name", type="string", 
                            description="First Name", default="Tester"),
                            @SWG\Property(property="last_name", type="string", 
                            description="Last Name", default="Last Name"),
                            @SWG\Property(property="is_active", type="integer", 
                            description="Is Active", default="1"),
                            @SWG\Property(property="logins", type="integer", 
                            description="Login", default="5"),
                            @SWG\Property(property="last_login", type="string", 
                            description="Last login", default="2017-05-22 07:10:24"),
                            @SWG\Property(property="last_login_ip", type="string", 
                            description="Last Login IP", default="10.0.181.19"),
                            @SWG\Property(property="login_attempts", type="integer", 
                            description="Login Attempts", default="1"),
                            @SWG\Property(property="created_at", type="string", 
                            description="Created At", default="2017-03-08 12:07:24"),
                            @SWG\Property(property="updated_at", type="string", 
                            description="Updated At", default="2017-05-22 04:40:24")
                        )     
     *              )
     *         )
     *   ),
     *   @SWG\Response(
     *     response=403,
     *     description="Forbidden",
     *   ),
     *   @SWG\Response(
     *     response=500,
     *     description="Internal Server Error",
     *   )
     * )
     *
     * Activate User.
     * PATCH /api/users/{user_id}/verification/{verification_id}
     *
     * @return json
     */
    public function update()
    {
        $input = Input::all();

        $token = AdminToken::isActive($input['token']);

        if (!$token)
        {
            return $this->respondValidationError(trans('validation.token.invalid_token'));
        }

        $user = User::setActive($token->admin_id);

        if (!$user)
        {
            Logger::create('VERIFICATION/CONTROLLER/UPDATE', [
                'ADMIN NOT SET TO ACTIVE'
            ]);
            return $this->respondInternalError();
        }

        $data = [
            'user' => $user
        ];

        return $this->respondWithSuccess($data);
    }
}
