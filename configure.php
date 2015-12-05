<?php
/**
 * nature 默认配置文件
 */
return array(
    'environment'=>'development',
    'domain'=>isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '',
    'x-powered-by'=>true,
    'debug'=>true,
    'Nature'=>array(
        'MySQL'=>array(
            'dsn'=>getenv('MYSQL_DSN'),
            'username'=>getenv('MYSQL_USER'),
            'password'=>getenv('MYSQL_PASSWORD'),
            'charset'=>'utf8mb4'
        ),
    ),
    'Nature.Template'=>array(
        'root'=>APP_DIR.'/template'
    ),
    'Nature.cURL.timeout'=>10,
);
