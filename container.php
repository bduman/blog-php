<?php
$container = $app->getContainer();

$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    $phpView = new Slim\Views\PhpRenderer($settings['template_path']);
    $site = \Model\Site::getData();
    $site["links"] = \Model\Category::getShowcases();
    $site["links"]["Ana Sayfa"] = new \Model\Url('home');
    $phpView->setAttributes([
        "site" => $site,
        "root" => \Model\Config::getConf('root'),
        "Elements" => function ($element) use ($settings) {
            return $settings['template_path']."Elements".DIRECTORY_SEPARATOR.$element.".phtml";
        }
    ]);
    return $phpView;
};

## Admin

$container['adminRenderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    $adminPath = $settings['template_path'].DIRECTORY_SEPARATOR."Admin".DIRECTORY_SEPARATOR;
    $phpView = new Slim\Views\PhpRenderer($adminPath);
    //$site = \Model\Site::getData();
    //$site["links"] = \Model\Category::getShowcases();
    //$site["links"]["Ana Sayfa"] = new \Model\Url('home');
    $phpView->setAttributes([
        "site" => \Model\Site::getData(),
        "root" => \Model\Config::getConf('root'),
        "adminRoot" => \Model\Config::getConf('root').\Model\Config::getConf('adminDirectory')."/",
        "Elements" => function ($element) use ($adminPath) {
            return $adminPath."Elements".DIRECTORY_SEPARATOR.$element.".phtml";
        }
    ]);

    return $phpView;
};