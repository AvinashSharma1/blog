<?php

namespace App\Traits;

use Illuminate\Http\Response as IlluminateResponse;
use Illuminate\Support\Facades\Response as Response;
use Illuminate\Http\Request;
use Authorizer;
use Mews\Purifier\Facades\Purifier;

trait ResponseTrait
{
    protected $statusCode = IlluminateResponse::HTTP_OK;

    /**
     * Gets the status code
     *
     * @return int status code
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Sets the status code
     *
     * @param int $statusCode
     * @return object
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * Sets the status code to IlluminateResponse::HTTP_CREATED
     * Gets the formatted success response
     *
     * @param array $data
     * @param string $message
     * @return json
     */
    public function respondCreated($data = [], $message = 'Created!')
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_CREATED)->respondWithSuccess($data, $message);
    }

    /**
     * Sets the status code to IlluminateResponse::HTTP_BAD_REQUEST
     * Gets the formatted error response
     *
     * @param string $message
     * @return json
     */
    public function respondValidationError($message = 'Validation Error!')
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_BAD_REQUEST)->respondWithError($message);
    }

    /**
     * Sets the status code to IlluminateResponse::HTTP_UNAUTHORIZED
     * Gets the formatted error response
     *
     * @param string $message
     * @return json
     */
    public function respondAuthorizationError($data = [], $message = '')
    {
        $message = !empty($message) ? $message : trans('validation.login.invalid_credentials');
        return $this->setStatusCode(IlluminateResponse::HTTP_UNAUTHORIZED)->respondWithErrorData($data, $message);
    }

    /**
     * Sets the status code to IlluminateResponse::HTTP_FORBIDDEN
     * Gets the formatted error response
     *
     * @param string $message
     * @return json
     */
    public function respondForbiddenError($message = '')
    {
        $message = !empty($message) ? $message : trans('validation.generic.no_access');
        return $this->setStatusCode(IlluminateResponse::HTTP_FORBIDDEN)->respondWithError($message);
    }

    /**
     * Sets the status code to IlluminateResponse::HTTP_NOT_FOUND
     * Gets the formatted error response
     *
     * @param string $message
     * @return json
     */
    public function respondNotFound($message = '')
    {
        $message = !empty($message) ? $message : trans('validation.generic.route_not_found');
        return $this->setStatusCode(IlluminateResponse::HTTP_NOT_FOUND)->respondWithError($message);
    }

    /**
     * Sets the status code to IlluminateResponse::HTTP_NOT_FOUND
     * Gets the formatted error response
     *
     * @param string $message
     * @return json
     */
    public function respondMethodNotAllowed($message = '')
    {
        $message = !empty($message) ? $message : trans('validation.generic.route_not_found');
        return $this->setStatusCode(IlluminateResponse::HTTP_METHOD_NOT_ALLOWED)->respondWithError($message);
    }

    /**
     * Sets the status code to IlluminateResponse::HTTP_UNPROCESSABLE_ENTITY
     * Gets the formatted error response
     *
     * @param string $message
     * @return json
     */
    public function respondMissingParameters($message = 'Missing Parameters!')
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_UNPROCESSABLE_ENTITY)->respondWithError($message);
    }

    /**
     * Sets the status code to IlluminateResponse::HTTP_INTERNAL_SERVER_ERROR
     * Gets the formatted error response
     *
     * @param string $message
     * @return json
     */
    public function respondInternalError($message = 'Internal Error!')
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_INTERNAL_SERVER_ERROR)->respondWithError($message);
    }

    /**
     * Gets the formatted success response
     *
     * @param array $data
     * @param string $message
     * @return json
     */
    public function respondWithSuccess($data, $message = 'Success')
    {
        return $this->respond([
            'status' => $this->getStatusCode(),
            'success' => true,
            'data' => $data,
            'message' => $message
        ]);
    }

    /**
     * Gets the formatted error response
     *
     * @param string $message
     * @return json
     */
    public function respondWithError($message = 'Error')
    {
        return $this->respond([
            'status' => $this->getStatusCode(),
            'success' => false,
            'data' => null,
            'message' => $message
        ]);
    }

    /**
     * Gets the formatted response
     *
     * @param array $data
     * @param string $message
     * @return json
     */
    public function respondWithErrorData($data, $message = 'Success')
    {
        return $this->respond([
            'status' => $this->getStatusCode(),
            'success' => false,
            'data' => $data,
            'message' => $message
        ]);
    }

    /**
     * Gets the formatted success response with pagination
     *
     * @param object $paginator
     * @param array $data
     * @return json
     */
    public function respondWithPagination($paginator, $data)
    {
        $data = array_merge($data, [
            'paginator' => [
                'limit' => $paginator->getPerPage(),
                'current_page' => $paginator->getCurrentPage(),
                'total_count' => $paginator->getTotal(),
                'total_pages' => ceil($paginator->getTotal() / $paginator->getPerPage())
            ]
        ]);

        return $this->respond($data);
    }

    /**
     * Function to update the user attributes by reference
     *
     * @param  string &$item    field value that has to be updated
     * @param  string $key      field key
     * @param  object $response user data
     */
    public function updateJson(&$item, $key, $response)
    {
        $item = Purifier::clean($response->{$item});
    }

    /**
     * Get the formatted success response
     *
     * @return json
     */
    public function authenticate(Request $request)
    {
        ##### Reverting the changes of MMTECH-2414 until FE completes its ticket - MMTECH-2411 #####
        return $this->setStatusCode(IlluminateResponse::HTTP_OK)->respondWithSuccess(Authorizer::issueAccessToken(), trans('validation.login.login_success'));
        ###########################################################################################

        $required = ['grant_type', 'client_id', 'username', 'password', 'client_secret'];
        $post_params = $request->capture()->request->all();
        $get_params = $request->query->all();
        $duplicate_parameters = [];

        /**
         * We have to do this because, the testcase doesn't make http calls to the endpoints
         * and hence, the parameters are not passed in either $_GET or $_POST superglobal, 
         * but as an argument.
         */

        if($request->server->get('TESTCASE') == true)
        {
            $post_params = $request->request->all();
        }

        if(count($get_params) != 0)
        {
            $duplicate_parameters = array_intersect_key($post_params, $get_params);
        }

        if(count($post_params) == 0
            || count($duplicate_parameters) > 0
            || count(array_intersect_key(array_flip($required), $post_params)) !== count($required)
        )
        {
            return $this->respondValidationError(trans('validation.login.invalid_parameters'));
        }

        return $this->setStatusCode(IlluminateResponse::HTTP_OK)->respondWithSuccess(Authorizer::issueAccessToken(), trans('validation.login.login_success'));
    }

    /**
     * Gets the formatted default response
     *
     * @param array $data
     * @param array $headers
     * @return json
     */
    public function respond($data, $headers = [])
    {
        return Response::json($data, $this->getStatusCode(), $headers);
    }
}
