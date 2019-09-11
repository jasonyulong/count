<?php
// +----------------------------------------------------------------------
// | 自定义常量中心
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: kevin


// 安全秘钥
defined('APP_SECRETKEY') or define('APP_SECRETKEY', 'opjajsdpoas09d2w174e21i3enlxa0987120J');

// erp old redis库
defined('REDIS_ERP') or define('REDIS_ERP', 0);

// erp v5.0 redis库
defined('REDIS_ERP5') or define('REDIS_ERP5', 1);

// ERP 域名
defined('ERP_DOMAIN') or define('ERP_DOMAIN', 'http://erp.spocoo.com/');

// PHP命令
defined('PHP_BIN') or define('PHP_BIN', '/usr/local/php/bin/php');

/************************************************
 * 这些配置来自FMS
 ************************************************/
// API请求密钥
defined('API_KEY') or define('API_KEY', 'sjh2259*YASD6612921%$%*@1KSHtgsSK2');

// 安全秘钥
defined('APP_SECRETKEY') or define('APP_SECRETKEY', 'opjajsdpoas09d2w174e21i3enlxa0987120J');

/**
 * 证书公钥
 */
defined('PEM_PUBLICKEY') or define('PEM_PUBLICKEY', EXTEND_PATH . 'rsa/pem/public_key.pem');

/**
 * 证书私钥
 */
defined('PEM_PRIVATEKEY') or define('PEM_PRIVATEKEY', EXTEND_PATH . 'rsa/pem/private_key.pem');