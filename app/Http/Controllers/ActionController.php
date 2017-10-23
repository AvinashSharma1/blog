<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Exception;
use Validator;
use Illuminate\Support\Facades\Config;

class ActionController extends APIController
{
    protected $sort_by_columns = array();
    protected $expected_request_parameters = array();
    protected $search_parameters = array();
    protected $records_per_page;
    protected $errors = array();
    public $routename;
    const ASC = 'asc';
    const DESC = 'desc';
    const ASC_STATUS = 1;
    const DESC_STATUS = -1;

    /**
     * Constructor function to set all the initial required parameters
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        //Invoke Parent's constructor
        parent::__construct($request);

        $method = $request->method();

        if($request->route())
        {
            $routename = $request->route()->getPath();
            $routename = self::getRouteNameWithoutPrefix($routename);
            $this->routename = $routename;
            $this->sort_by_columns = Config::get('action_params.'.$routename.'.'.$method.'.sort', array());
            $this->expected_request_parameters = Config::get('action_params.'.$routename.'.'.$method.'.search_params', array());

            $this->search_parameters = $request->only(
                array_keys($this->expected_request_parameters)
            );

            if(!empty($this->search_parameters['sort_type']))
            {
                $this->search_parameters['sort_type'] =
                    (strtolower($this->search_parameters['sort_type']) == self::ASC
                        || $this->search_parameters['sort_type'] == self::ASC_STATUS)
                        ? self::ASC
                        : self::DESC;
            }

            if(! empty($this->search_parameters['records_per_page']) )
            {
                $this->setPaginationLimit($this->search_parameters['records_per_page']);
            }
        }

        $this->records_per_page = $this->getPaginationLimit();
    }

    /**
     * Validate and get search parameters
     *
     * @param Object $main_model
     * @param array $validation_rules
     * @param array $validation_messages
     * @return array formatted search params
     * @throws Exception
     */
    public function validateParameters($validation_rules = array(), $validation_messages = array(), $main_model = null)
    {
        if(empty($this->expected_request_parameters))
        {
            throw new Exception(trans('validation.generic.missing_request_parameters'));
        }

        if (!empty($this->search_parameters['records_per_page']) && !ctype_digit($this->search_parameters['records_per_page']))
        {
            $this->errors = [
                'records_per_page' => trans('validation.generic.invalid_records_per_page')
            ];

            return false;
        }

        $validator = Validator::make($this->search_parameters, $validation_rules);

        if ($validator->fails())
        {
            $failed_fields = $validator->errors()->keys();
            $errors = array();
            $default_validation_messages = $validator->errors();

            if(is_array($failed_fields) && count($failed_fields) > 0)
            {

                foreach($failed_fields as $field)
                {
                    $errors[$field] = isset($validation_messages[$field]) ? $validation_messages[$field] : '';
                }
            }

            if(!array_filter($errors))
            {
                $errors = $default_validation_messages;
            }

            $this->errors = $errors;

            return false;
        }

       return $this->cleanseParameters($main_model);
    }

    /**
     * Cleanse parameters
     *
     * @param null $main_model
     * @return array
     */
    protected function cleanseParameters($main_model = null)
    {

        foreach($this->search_parameters as $input_field => $input_value)
        {
            $search[$this->expected_request_parameters[$input_field]] = $input_value;
        }

        if(!empty($search['is_active']) && !empty($main_model))
        {
            $search['is_active'] = strtolower($search['is_active']) != 'active'
                ? $main_model::INACTIVE
                : $main_model::ACTIVE;
        }

        return $search;
    }
}
