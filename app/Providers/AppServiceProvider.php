<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->includeConsoleAndDBLogger();
        $this->includeCustomValidations();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->register(\L5Swagger\L5SwaggerServiceProvider::class);
    }

    /**
     * Logs application activities
     * To include logging during testing : 'testing' == config('app.env')
     */
    private function includeConsoleAndDBLogger()
    {
        /**
         * To include logging during testing : 'testing' == config('app.env')
         */
        if(('local' === config('app.env') && true === config('app.debug')) ||  ('testing' === config('app.env') && true === config('app.debug')))
        {
            /**
             * Logs Every DB queries
             */
            DB::listen(function($query)
            {
                Log::debug(Request::url());
                Log::debug('Query: ');
                Log::debug($query->sql);
                Log::debug('Bindings: ');
                Log::debug($query->bindings);
            });

            /**
             * Logs the running command line script
             */
            if(! empty($consoleCommand = Request::server('argv', null)))
            {
                Log::debug('Console Command: '.implode(' ', $consoleCommand));
                Log::debug("Client ID: ".implode(',',Request::ips()).(! empty(get_current_user()) ? ' '.get_current_user() : '')."\n" );
            }
        }
    }


    /**
     * This function contains custom validations
     */
    private function includeCustomValidations()
    {
        /**
         * Custom Validator to ignore the case while comparing with each elements in $parameters array
         */
        Validator::extend('ignore_case_in', function($attribute, $value, $parameters, $validator) {
            if (is_array($value) && $this->hasRule($attribute, 'Array')) {
                foreach ($value as $element) {
                    if (is_array($element)) {
                        return false;
                    }
                }

                return count(array_diff($value, $parameters)) == 0;
            }
            if(!is_array($value))
            {
                $value = strtolower($value);
                array_walk_recursive(
                    $parameters,
                    function(&$v, $k)
                    {
                        $v = strtolower($v);
                    }
                );
            }
            return ! is_array($value) && in_array((string) $value, $parameters);
        });

        //create Email validation rule for Store method only.
        Validator::extend('email_exist', function($field,$value,$parameters){
            $result = User::where('email','=',$value)
                ->where('is_active','=',  User::ACTIVE)->first();

            return empty($result) && count($result) <= 0 ? true : false;
        });

        /**
         * Custom Validator to check if the input value is greater than the value its compared with
         */
        Validator::extend('greater_than', function($attribute, $value, $parameters, $validator) {
            $fieldValue = \Input::get($attribute);
            return ($fieldValue > $parameters[0]) ? true : false;
        });

        /**
         * Custom Validator to check if the input value is less than the value its compared with
         */
        Validator::extend('less_than', function($attribute, $value, $parameters, $validator) {
            $fieldValue = \Input::get($attribute);
            return ($fieldValue < $parameters[0]) ? true : false;
        });

        //create Weekly start day validation rule for Show method
        Validator::extend('start_day', function($value, $parameters, $validator){
            $start_day = date('l', strtotime($parameters));
            return $start_day ==  config('settings.WEEK_START_DAY') ? true : false;

        });

        //create Weekly end day validation rule for Show method
        Validator::extend('end_day', function($value, $parameters, $validator){
            $end_day = date('l', strtotime($parameters));
            return $end_day ==  config('settings.WEEK_END_DAY') ? true : false;

        });

        //create start date validation rule
        Validator::extend('less_or_equal', function($attribute,$value, $parameters, $validator){
            $date = $validator->getData();
            return strtotime($date['start_date']) <= strtotime($date['end_date']) ? true : false;

        });

        //create end date validation rule
        Validator::extend('greater_or_equal', function($attribute,$value, $parameters, $validator){
            $date = $validator->getData();
            return strtotime($date['end_date']) >= strtotime($date['start_date']) ? true : false;

        });
    }
}
