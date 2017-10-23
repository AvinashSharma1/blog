<?php

namespace App\Http\Controllers;

use LucaDegasperi\OAuth2Server\Facades\Authorizer;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use App\Http\Requests;

/**
 * Class ApiController
 *
 * @package App\Http\Controllers
 *
 * @SWG\Swagger(
 *     basePath="",
 *     @SWG\Info(
 *         version="1.0",
 *         title="Blog API Documentation",
 *         @SWG\Contact(name="Avinash Sharma", url="https://www.blog.com"),
 *     ),
 *     @SWG\Definition(
 *         definition="Error",
 *         required={"code", "message"},
 *         @SWG\Property(
 *             property="code",
 *             type="integer",
 *             format="int32"
 *         ),
 *         @SWG\Property(
 *             property="message",
 *             type="string"
 *         )
 *     )
 * )
 */
class APIController extends Controller
{
    use ResponseTrait;

    protected $paginationLimit = 10;
    protected $request;
    protected $user_id;

    public function __construct(Request $request)
    {
        $this->request = $request;

        try
        {
            $this->user_id = Authorizer::getResourceOwnerId();
        }
        catch (\Exception $e)
        {
            $this->user_id = 0;
        }
    }

    /**
     * Sets the pagination limit
     *
     * @return int
     */
    public function getPaginationLimit()
    {
        return $this->paginationLimit;
    }

    /**
     * Sets the pagination limit
     *
     * @param int $paginationLimit
     * @return object
     */
    public function setPaginationLimit($paginationLimit)
    {
        $this->paginationLimit = $paginationLimit;
        return $this;
    }

    public static function getRouteNameWithoutPrefix($routename)
    {
        $prefix = config('settings.PREFIX');
        return substr(strstr($routename, $prefix), strlen($prefix)+1);
    }
}
