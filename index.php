<?php
    namespace Nature;
    require('App.php');
    $nature = new App();
    $nature->set_psr4_autoload(APP_DIR);
    
    if(file_exists(APP_DIR.'/vendor/autoload.php')) {
        require_once(APP_DIR.'/vendor/autoload.php');
    }
    $nature->hook('PageLoad');