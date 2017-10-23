<?php

namespace App\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

class ParamsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @param Router $router
     */
    public static $action_params_modules_param = array();

    /**
     * set action_params values as in config value
     * @param Router $router
     *
     */
    public function boot(Router $router)
    {
        $appPath = app_path()."/Http/";
        $file_name = "action_params.json";
        $file_path = $appPath.$file_name;
        if(file_exists($file_path))
        {
            self::$action_params_modules_param = json_decode(file_get_contents($file_path), true);
        }

        Config::set(['action_params' => self::$action_params_modules_param]);
    }

    /**
     * Register the application services.
     *
     * @return array
     */
    public function register()
    {

    }
}
