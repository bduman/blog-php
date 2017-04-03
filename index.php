<?php
require __DIR__ . '/vendor/autoload.php';
// session gereksiz cookie üzerinden dönüyor olaylar Header Token
//session_start();

// settings
$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => false, // set to false in production
        'addContentLengthHeader' => true, // Allow the web server to send the content-length header
        'renderer' => [
            'template_path' =>  __DIR__.DIRECTORY_SEPARATOR.'View'.DIRECTORY_SEPARATOR
        ]
    ]
]); 

// config
require_once __DIR__.DIRECTORY_SEPARATOR.'config.php';

if (!(isset($config) && is_array($config)))
    die ("<a href='public/install/'>Kurulum gerekli</a>");

foreach ($config as $key => $val) {
    \Model\Config::setConf($key, $val);
}

// container
require_once __DIR__.DIRECTORY_SEPARATOR.'container.php';


// routing
require_once __DIR__.DIRECTORY_SEPARATOR.'routing.php';

// run
$app->run();
