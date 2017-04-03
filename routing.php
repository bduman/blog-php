<?php
## General

# / | /home | /home/ | /home/1
$app->get('/[home[/[{page:[0-9]+}]]]', \Controller\HomeController::class.':home');
# /post/{url}
$app->get('/post/{url:[A-z0-9-]+}-{id:[0-9]+}', \Controller\PostController::class.':post');
# /category/{url} | /category/{url}/ | /category/{url}/1
$app->get('/category/{url:[A-z0-9-]+}-{id:[0-9]+}[/[{page:[0-9]+}]]', \Controller\HomeController::class.':category');
# /tag/{url} | /tag/{url}/ | /tag/{url}/1
$app->get('/tag/{url:[A-z0-9-]+}-{id:[0-9]+}[/[{page:[0-9]+}]]', \Controller\HomeController::class.':tag');
# /robots.txt
$app->get('/robots.txt', \Controller\HomeController::class.':robots');
# /sitemap.xml
$app->get('/sitemap.xml', \Controller\HomeController::class.':sitemap');

## Admin

# middleware for auth
/** @noinspection PhpParamsInspection */
$adminMiddleware = new \Controller\AdminController();
$adminDirectory = \Model\Config::getConf('adminDirectory');

# /{adminDirectory}/login
$app->get('/'.$adminDirectory.'/login', \Controller\AdminController::class.':login');
# /{adminDirectory}/login
$app->post('/'.$adminDirectory.'/login', \Controller\AdminController::class.':doLogin');
# /{adminDirectory}/logout
$app->get('/'.$adminDirectory.'/logout', \Controller\AdminController::class.':logout');
# /{adminDirectory}/home
$app->get('/'.$adminDirectory.'[/[home[/]]]', \Controller\AdminController::class.':home')->add($adminMiddleware);
# /{adminDirectory}/settings
$app->get('/'.$adminDirectory.'/settings', \Controller\AdminController::class.':settings')->add($adminMiddleware);
# /{adminDirectory}/settings
$app->put('/'.$adminDirectory.'/settings', \Controller\AdminController::class.':settingsEdit')->add($adminMiddleware);
# /{adminDirectory}/adminSettings
$app->get('/'.$adminDirectory.'/adminSettings', \Controller\AdminController::class.':adminSettings')->add($adminMiddleware);
# /{adminDirectory}/adminSettings
$app->put('/'.$adminDirectory.'/adminSettings', \Controller\AdminController::class.':adminSettingsEdit')->add($adminMiddleware);

# Category directory

# /{adminDirectory}/category/all
$app->get('/'.$adminDirectory.'/category/all', \Controller\AdminController::class.':categoryAll')->add($adminMiddleware);
# /{adminDirectory}/category/new
$app->get('/'.$adminDirectory.'/category/new', \Controller\AdminController::class.':categoryNew')->add($adminMiddleware);
# /{adminDirectory}/category/new
$app->post('/'.$adminDirectory.'/category/new', \Controller\AdminController::class.':doCategoryNew')->add($adminMiddleware);
# /{adminDirectory}/category/cat-1
$app->get('/'.$adminDirectory.'/category/{url:[A-z0-9-]+}-{id:[0-9]+}', \Controller\AdminController::class.':categoryView')->add($adminMiddleware);
# /{adminDirectory}/category/cat-1
$app->put('/'.$adminDirectory.'/category/{url:[A-z0-9-]+}-{id:[0-9]+}', \Controller\AdminController::class.':categoryEdit')->add($adminMiddleware);
# /{adminDirectory}/category/cat-1
$app->delete('/'.$adminDirectory.'/category/{url:[A-z0-9-]+}-{id:[0-9]+}', \Controller\AdminController::class.':categoryDelete')->add($adminMiddleware);

# Tag directory

# /{adminDirectory}/tag/all
$app->get('/'.$adminDirectory.'/tag/all', \Controller\AdminController::class.':tagAll')->add($adminMiddleware);
# /{adminDirectory}/tag/new
$app->get('/'.$adminDirectory.'/tag/new', \Controller\AdminController::class.':tagNew')->add($adminMiddleware);
# /{adminDirectory}/tag/new
$app->post('/'.$adminDirectory.'/tag/new', \Controller\AdminController::class.':doTagNew')->add($adminMiddleware);
# /{adminDirectory}/tag/tag-1
$app->get('/'.$adminDirectory.'/tag/{url:[A-z0-9-]+}-{id:[0-9]+}', \Controller\AdminController::class.':tagView')->add($adminMiddleware);
# /{adminDirectory}/tag/tag-1
$app->put('/'.$adminDirectory.'/tag/{url:[A-z0-9-]+}-{id:[0-9]+}', \Controller\AdminController::class.':tagEdit')->add($adminMiddleware);
# /{adminDirectory}/tag/tag-1
$app->delete('/'.$adminDirectory.'/tag/{url:[A-z0-9-]+}-{id:[0-9]+}', \Controller\AdminController::class.':tagDelete')->add($adminMiddleware);

# Post directory

# /{adminDirectory}/post/all
$app->get('/'.$adminDirectory.'/post/all', \Controller\AdminController::class.':postAll')->add($adminMiddleware);
# /{adminDirectory}/post/new
$app->get('/'.$adminDirectory.'/post/new', \Controller\AdminController::class.':postNew')->add($adminMiddleware);
# /{adminDirectory}/post/new
$app->post('/'.$adminDirectory.'/post/new', \Controller\AdminController::class.':doPostNew')->add($adminMiddleware);
# /{adminDirectory}/post/post-1
$app->get('/'.$adminDirectory.'/post/{url:[A-z0-9-]+}-{id:[0-9]+}', \Controller\AdminController::class.':postView')->add($adminMiddleware);
# /{adminDirectory}/post/post-1
$app->put('/'.$adminDirectory.'/post/{url:[A-z0-9-]+}-{id:[0-9]+}', \Controller\AdminController::class.':postEdit')->add($adminMiddleware);
# /{adminDirectory}/post/post-1
$app->delete('/'.$adminDirectory.'/post/{url:[A-z0-9-]+}-{id:[0-9]+}', \Controller\AdminController::class.':postDelete')->add($adminMiddleware);
