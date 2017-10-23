<?php

namespace App\Http\Controllers;

use App\Models\AdminToken;
use App\Models\User;
use App\Http\Requests;
use Input;
use Validator;

class RegisterController extends APIController
{
    /**
     * @SWG\Post(
     *   path="/registration",
     *   tags={"Authentication Login and Registration Module"},
     *   summary="Stores new User entry",
     *   description="",
     *   operationId="register.store",
     *   consumes={"application/x-www-form-urlencoded"},
     *   produces={"application/json","application/xml"},
     *   @SWG\Parameter(
     *     name="email",
     *     in="formData",
     *     description="Email",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="first_name",
     *     in="formData",
     *     description="First Name",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="last_name",
     *     in="formData",
     *     description="Last Name",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="password",
     *     in="formData",
     *     description="Password",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="g-recaptcha-response",
     *     in="formData",
     *     description="Captcha",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="user_verify_link",
     *     in="formData",
     *     description="User Verify Link",
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
     *                  @SWG\Property(property="user_id", type="integer",
    description="User Id", default="1"),
    @SWG\Property(property="verification_id", type="integer", description="Verification Id", default="0"),
    @SWG\Property(property="token", type="string", description="Token", default="OhlKm0rTYSgxLKmIttkHSZ9rFw7a21V6HwpvCRCs"),
    @SWG\Property(property="user_verify_link", type="string", description="Verification Link", default="http://google.com/test.php")
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
     *   )
     * )
     *
     * Stores new user entry.
     * POST /oauth/registration
     *
     * @return Response
     */
    public function store()
    {
        $input = Input::all();

        User::$rules['email'] = 'required|email|email_exist';

        $rules = array_merge(User::$rules,['user_verify_link'=> 'required']);

        $validator = Validator::make($input, $rules);

        if ($validator->fails())
        {
            return $this->respondValidationError($validator->errors());
        }

        $user = new User();
        $result_user = $user->createUser($input);

        if(!$result_user)
        {
            return $this->respondValidationError(trans('validation.admins.email_already_exist'));
        }

        if (!$user)
        {
            return $this->respondInternalError();
        }

        $admin_token = new AdminToken();
        $admin_token->createAdminToken($result_user->id);

        if (!$admin_token)
        {
            Logger::create('Register/CONTROLLER/STORE', [
                'message' => 'INVALID ADMIN TOKEN'
            ]);
            return $this->respondInternalError();
        }

        $data = [
            'user_id' => $result_user->id,
            'verification_id' => 0,
            'token' => $admin_token->token,
            'user_verify_link'=>$input['user_verify_link']
        ];

        //$user->sendMail($result_admin, Mailer::TOKEN_TYPE_REGISTRATION, $data);


        return $this->respondWithSuccess($data);
    }
}
