<?php
    namespace Nature;
    require('App.php');
    $nature = new App();
    $nature->set_psr4_autoload(APP_DIR);
    
    if(PHP_SAPI=='cli') {
        $app_dir = dirname(\Phar::running(false)).DIRECTORY_SEPARATOR;
        if($argc > 1) {
            switch($argv[1]) {
                case '.user.ini':
                    file_put_contents($app_dir.'../.user.ini', "user_ini.cache_ttl=0\ndisplay_errors=on\nauto_prepend_file=".\Phar::running(false)."\nauto_append_file=run.php");
                break;
                case 'nginx.conf':
                    file_put_contents($app_dir.'nginx.conf', "location /app {\n    return 404;\n}\n\nlocation /.user.ini {\n    return 404;\n}\n\nlocation / {\n    index index.php;\n    try_files \$uri \$uri/ \$uri.php\$is_args\$args;\n}");
                break;
                case '.htaccess':
                    file_put_contents($app_dir."../.htaccess", "RewriteEngine On\nRewriteBase /\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteCond %{REQUEST_FILENAME}.php -f\nRewriteRule ^(.+)$ /$1.php [L,QSA]\n\nphp_value auto_prepend_file \"".\Phar::running(false)."\"\nphp_value auto_append_file \"run.php\"");
                break;
            }
            echo "writing $argv[1]\n";
        }
    }