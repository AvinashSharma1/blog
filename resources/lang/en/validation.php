<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => 'The :attribute must be accepted.',
    'active_url'           => 'The :attribute is not a valid URL.',
    'after'                => 'The :attribute must be a date after :date.',
    'alpha'                => 'The :attribute may only contain letters.',
    'alpha_dash'           => 'The :attribute may only contain letters, numbers, and dashes.',
    'alpha_num'            => 'The :attribute may only contain letters and numbers.',
    'array'                => 'The :attribute must be an array.',
    'before'               => 'The :attribute must be a date before :date.',
    'between'              => [
        'numeric' => 'The :attribute must be between :min and :max.',
        'file'    => 'The :attribute must be between :min and :max kilobytes.',
        'string'  => 'The :attribute must be between :min and :max characters.',
        'array'   => 'The :attribute must have between :min and :max items.',
    ],
    'boolean'              => 'The :attribute field must be true or false.',
    'confirmed'            => 'The :attribute confirmation does not match.',
    'date'                 => 'The :attribute is not a valid date.',
    'date_format'          => 'The :attribute does not match the format :format.',
    'different'            => 'The :attribute and :other must be different.',
    'digits'               => 'The :attribute must be :digits digits.',
    'digits_between'       => 'The :attribute must be between :min and :max digits.',
    'distinct'             => 'The :attribute field has a duplicate value.',
    'email'                => 'The :attribute must be a valid email address.',
    'exists'               => 'The selected :attribute is invalid.',
    'filled'               => 'The :attribute field is required.',
    'image'                => 'The :attribute must be an image.',
    'in'                   => 'The selected :attribute is invalid.',
    'in_array'             => 'The :attribute field does not exist in :other.',
    'integer'              => 'The :attribute must be an integer.',
    'ip'                   => 'The :attribute must be a valid IP address.',
    'json'                 => 'The :attribute must be a valid JSON string.',
    'max'                  => [
        'numeric' => 'The :attribute may not be greater than :max.',
        'file'    => 'The :attribute may not be greater than :max kilobytes.',
        'string'  => 'The :attribute may not be greater than :max characters.',
        'array'   => 'The :attribute may not have more than :max items.',
    ],
    'mimes'                => 'The :attribute must be a file of type: :values.',
    'min'                  => [
        'numeric' => 'The :attribute must be at least :min.',
        'file'    => 'The :attribute must be at least :min kilobytes.',
        'string'  => 'The :attribute must be at least :min characters.',
        'array'   => 'The :attribute must have at least :min items.',
    ],
    'not_in'               => 'The selected :attribute is invalid.',
    'numeric'              => 'The :attribute must be a number.',
    'present'              => 'The :attribute field must be present.',
    'regex'                => 'The :attribute format is invalid.',
    'required'             => 'The :attribute field is required.',
    'required_if'          => 'The :attribute field is required when :other is :value.',
    'required_unless'      => 'The :attribute field is required unless :other is in :values.',
    'required_with'        => 'The :attribute field is required when :values is present.',
    'required_with_all'    => 'The :attribute field is required when :values is present.',
    'required_without'     => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same'                 => 'The :attribute and :other must match.',
    'size'                 => [
        'numeric' => 'The :attribute must be :size.',
        'file'    => 'The :attribute must be :size kilobytes.',
        'string'  => 'The :attribute must be :size characters.',
        'array'   => 'The :attribute must contain :size items.',
    ],
    'string'               => 'The :attribute must be a string.',
    'timezone'             => 'The :attribute must be a valid zone.',
    'unique'               => 'The :attribute has already been taken.',
    'url'                  => 'The :attribute format is invalid.',
    'email_exist'          => 'Email already exist!',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
   |--------------------------------------------------------------------------
   | Generic Validation Language Lines
   |--------------------------------------------------------------------------
   |
   */

    'generic' => [
        'route_not_found' => 'Route not found',
        'no_access' => 'You dont have access to this resource',
        'missing_request_parameters' => 'Request parameters not specified',
        'invalid_number' => 'invalid number',
        'invalid_user_id'=>'Invalid User ID',
        'invalid_user_hash_id'=>'Invalid user hash id',
        'invalid_status' => 'The status field is invalid',
        'invalid_provider' => 'The provider field is invalid',
        'invalid_sort_by' => 'The sort by field is invalid',
        'invalid_sort_type' => 'Possible values are only limited to : \'asc\', and \'desc\'',
        'missing_balance_setting' => 'Missing Balance Setting',
        'invalid_date_format' => 'Invalid date format',
        'invalid_page_number' => 'Invalid page number',
        'invalid_records_per_page' => 'Invalid records per page.',
        'missing_action' => 'The action is not specified.',
        'invalid_action' => 'The action is invalid.',
        'invalid_file' => 'The file is invalid.',
        'invalid_file_format' => 'The file format is invalid.',
        'invalid_client' => 'B2B Client is invalid.',
        'no_records_found' => 'No records found.',
        'query_failure' => 'Query failed.',
        'invalid_consumer_id' => 'Invalid Consumer Id',
        'invalid_currency' => 'Currency is required',
        'update_successful' => 'Update Successful',
        'delete_successful' => 'Delete Successful',
        'update_failed' => 'Updated Failed',
        'invalid_notification_type' => 'Notification Type is Invalid.',
        'invalid_wallet_id' => 'Invalid Wallet ID.',
        'invalid_from_date' => 'From date should be less than current date in Y-m-d format.',
        'invalid_to_date' => 'To date should be less than current date in Y-m-d format.',
        'invalid_prefund_amount' => 'Invalid prefund amount.',
        'invalid_payment_ref_no' => 'Invalid payment referrance number.',
        'invalid_offset' => 'Offset has to be numeric',
        'invalid_user_type' => 'Invalid User type',
        'invalid_date'      => 'Invalid Date. Should be in YYYY-MM-DD format',
        'start_date_greater_than_end_date'      => 'Invalid Date. Start date should be less than end date',
        'invalid_customer_id'      => 'Invalid Customer id',
        'insert_failed' => 'Insert Failed',
        'identifier' => 'Invalid Document identifier',
        'invalid_level' => 'Invalid KYC Level',
        'invalid_notification_id'      => 'Invalid Notification Id',
        'must_be_a_number' => 'Field must be a number'
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [],

    /*
    |--------------------------------------------------------------------------
    | Login Validation Language Lines
    |--------------------------------------------------------------------------
    |
    */

    'login' => [
        'invalid_credentials'   => 'The user credentials were incorrect.',
        'login_success'         => 'User login successful.',
        'invalid_parameters'   => '`grant_type`, `client_id`, `username`, `password`, `client_secret` are mandatory POST parameters.'
    ],

    /*
    |--------------------------------------------------------------------------
    | Captcha Validation Language Lines
    |--------------------------------------------------------------------------
    |
    */

    'captcha' => [
        'invalid_captcha' => 'The captcha is invalid.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Token Validation Language Lines
    |--------------------------------------------------------------------------
    |
    */

    'token' => [
        'invalid_token' => 'The token is invalid.',
    ],

    /*
  |--------------------------------------------------------------------------
  | Admins Validation Language Lines
  |--------------------------------------------------------------------------
  |
  */

    'admins' => [
        'admin_not_found' => 'The admin does not exist.',
        'invalid_admin_id' => 'The admin id must be a number.',
        'delete_super_admin'=>'Super Admin can not be Deleted.',
        'delete_current_admin'=>'Logged in Admin can not be deleted.',
        'delete_admin_not_exist'=>'Admin Record not found.',
        'email_already_exist' => 'Entered email already exists.'
    ],

    /*
    |--------------------------------------------------------------------------
    | Users Validation Language Lines
    |--------------------------------------------------------------------------
    |
    */

    'users' => [
        'user_not_found' => 'The user does not exist.',
        'invalid_user_id' => 'The user id must be a number.',
        'document_not_found' => 'The document does not exist.',
        'status_already_updated' => 'The kyc status is already updated to',
        'user_hash_id_required' => 'The user hash id field is required.',
        'user_hash_id_not_removed' => 'user-hash-id was not removed as it was not found or invalid.'
    ],

    /*
   |--------------------------------------------------------------------------
   | Permissions Validation Language Lines
   |--------------------------------------------------------------------------
   |
   */

    'permissions' => [
        'invalid_user_id' => 'Invalid User Id',
        'empty_role_id' => 'Role Id missing',
        'user_id_not_found' => 'User Id not found'
    ],


];
