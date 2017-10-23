<?php

if (!defined('DEFAULT_CURRENCY'))
{
    define('DEFAULT_CURRENCY', !empty($_SERVER['CURRENCY']) ? $_SERVER['CURRENCY'] : 'SGD');
}

if(!defined('DEFAULT_DECIMAL_PLACE'))
{
    define('DEFAULT_DECIMAL_PLACE', 2);
}

if(!defined('DEFAULT_DECIMAL_POINT'))
{
    define('DEFAULT_DECIMAL_POINT', '.');
}

if(!defined('DEFAULT_THOUSAND_SEPARATOR'))
{
    define('DEFAULT_THOUSAND_SEPARATOR', ',');
}

if(!defined('LOG_TMP_DIR'))
{
    define('LOG_TMP_DIR', isset($_SERVER['LOG_TMP_DIR']) ? $_SERVER['LOG_TMP_DIR']: '/tmp');
}

if(!defined('GENERATE_OP_LOG'))
{
    define('GENERATE_OP_LOG', isset($_SERVER['GENERATE_OP_LOG']) ? $_SERVER['GENERATE_OP_LOG']: 0);
}

if(!defined('REPLICATION_ENABLE'))
{
    define('REPLICATION_ENABLE', isset($_SERVER['REPLICATION_ENABLE']) ? $_SERVER['REPLICATION_ENABLE'] : 0);
}
?>