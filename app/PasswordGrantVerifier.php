<?php
namespace App;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\APIController;

class PasswordGrantVerifier
{

    /**
     * @SWG\Post(
     *   path="/oauth/access_token",
     *   tags={"Authentication Login and Registration Module"},
     *   summary="Do User Login and Generate API Access Token",
     *   description="",
     *   operationId="oauth.access_token",
     *   consumes={"application/x-www-form-urlencoded"},
     *   produces={"application/xml", "application/json"},
     *   @SWG\Parameter(
     *     name="username",
     *     in="formData",
     *     description="User's email",
     *     required=true,
     *     type="string",
     *     default="test+1@test.com"
     *   ),
     *   @SWG\Parameter(
     *     name="password",
     *     in="formData",
     *     description="Password",
     *     required=true,
     *     type="string",
     *     default="password"
     *   ),
     *   @SWG\Parameter(
     *     name="client_id",
     *     in="formData",
     *     description="Clients ID",
     *     required=true,
     *     type="string",
     *     default="f3d259ddd3ed8ff3843839b"
     *   ),
     *   @SWG\Parameter(
     *     name="client_secret",
     *     in="formData",
     *     description="Client Secret Key",
     *     required=true,
     *     type="string",
     *     default="4c7f6f8fa93d59c45502c0ae8c4a95b"
     *   ),
     *   @SWG\Parameter(
     *     name="grant_type",
     *     in="formData",
     *     description="Grant type",
     *     required=true,
     *     type="string",
     *     default="password"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="OK"
     *   ),
     *   @SWG\Response(
     *     response=401,
     *     description="Unauthorized action.",
     *   ),
     *   @SWG\Response(
     *     response=500,
     *     description="Internal Server Error.",
     *   )
     * )
     * 
     * Login by oauth2
     * @param $username String user name
     * @param $password String password
     *
     * @return mixed
     */
    public function verify($username, $password) 
    {
        $user = new User();
        $credentials = [
            'email' => $username,
            'password' => $password,
            'is_active' => User::ACTIVE,
        ];

        $id = $user->login($credentials);

        return ($id)?$id:$user->verifyLoginAttempt($username);
    }

}
