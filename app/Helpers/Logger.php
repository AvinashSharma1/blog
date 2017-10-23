<?php 
namespace App\Helpers;

class Logger
{
    const DIRECTORY_PERMISSION = 0755;
    public static $log_dir = LOG_TMP_DIR;
    const GENERATE_OP_LOG_YES = 1;
    const GENERATE_OP_LOG_NO = 0;
    public static $op_log = 'op_log';
    public static $payload = [];
    
    public static $serverTrace = [
        'SERVER_PROTOCOL',
        'REQUEST_METHOD',
        'REQUEST_TIME_FLOAT',
        'QUERY_STRING',
        'HTTP_ACCEPT',
        'HTTP_ACCEPT_CHARSET',
        'HTTP_ACCEPT_ENCODING',
        'HTTP_ACCEPT_LANGUAGE',
        'HTTP_CONNECTION',
        'HTTP_HOST',
        'HTTP_REFERER',
        'HTTP_USER_AGENT',
        'HTTPS',
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR',
        'REMOTE_HOST',
        'REMOTE_PORT',
        'REDIRECT_REMOTE_USER',
        'REQUEST_URI',
        'PHP_AUTH_DIGEST',
        'PATH_INFO',
        'ORIG_PATH_INFO'
    ];

    /**
     * Function that creates the log file
     * @param  string  $name Name of the Log file
     * @param  array   $data User defined messages in array format
     * @param  boolean $serverTrace True to include server variables in the log 
     * along with $_GET and $_POST parameters
     * @param  boolean $backtrace True to include debug_backtrace of the function
     * @return object
     */
    public static function create($name, array $data, $serverTrace = false, $backtrace = false)
    {

        if(
            isset($data['log_type']) 
            && $data['log_type'] == self::$op_log 
            && GENERATE_OP_LOG == self::GENERATE_OP_LOG_NO 
        )
        {
            return null;
        }

        if($serverTrace)
        {
            $data['server'] = self::getServerTrace();
        }

        if($backtrace)
        {
            $data['backtrace'] = debug_backtrace();
        }

        return new Logger($name, $data);
    }

    /**
     * Logger Constructor
     * @param [type] $name filename
     * @param array  $data data to be written to the log file
     */
    public function __construct($name, array $data)
    {
        $isCli = php_sapi_name() == 'cli';
        $name = strtoupper($name);

        if($isCli)
        {
            $data['CLI'] = 'YES';
        }
        
        $data['CODENAME'] = $name;
        $this->write($name, $data);
        
        return $this;
    }

    /**
     * Function that returns an array of server variables, $_GET and $_POST variables
     * @return array $traces
     */
    public static function getServerTrace()
    {
        $traces = [];
        
        if ('cli' === php_sapi_name()) {
            return 'CLI';
        }
        
        foreach (Logger::$serverTrace as $trace)
        {
            if (!empty($_SERVER[$trace]))
            {
                $traces[$trace] = $_SERVER[$trace];
            }
        }
        
        $traces['CLIENT_IP'] = $_SERVER['REMOTE_ADDR'];
        
        $traces['_PAYLOAD'] = [
            'QUERY'  => $_GET,
            'BODY'   => $_POST
        ];
        
        return $traces;
    }

    /**
     * Function that returns the filename in yyyy/mm/dd/hh/mm/filename.json
     * @param  string $name log file name
     * @return string formatted log file name
     */
    public static function get_filename($name)
    {
        return rtrim(self::$log_dir, '/') . '/' . date('Y/m/d/H/i/'). str_replace('/', '-', $name) . '-' . microtime(true) . '.json';
    }

    /**
     * The function which writes the data to the log file
     * @param  string $name log file name
     * @param  array  $data log data
     */
    protected function write($name, array $data)
    {
        $filename = Logger::get_filename($name);
        $dir = dirname($filename);

        if (!is_dir($dir) && !mkdir($dir, Logger::DIRECTORY_PERMISSION, true))
        {
            trigger_error('Failed to create: `'.$dir.'`.', E_USER_ERROR);
        }

        file_put_contents($filename, json_encode($data). "\n\n", FILE_APPEND);
        $this->_dailyProductLog($data);
    }
    
    /**
     * This function is used to capture the daily logs
     * @param $data
     */
    private function _dailyProductLog($data)
    {
        // Check for product
        $product = !empty($_SERVER['PRODUCT_CODE']) ? $_SERVER['PRODUCT_CODE'] : 'uncaught';
        $filename = rtrim(self::$log_dir, '/') . DIRECTORY_SEPARATOR . 'daily' . DIRECTORY_SEPARATOR . 'ad-api-' . $product . '-' . date('Ymd') . '.log';
        $dir = dirname($filename);
        
        if (!is_dir($dir) && !mkdir($dir, Logger::DIRECTORY_PERMISSION, true)) 
        {
            trigger_error('Failed to create: `'.$dir.'`.', E_USER_ERROR);
        }

        file_put_contents($filename, json_encode($data) . "\n\n", FILE_APPEND);
    }
}
