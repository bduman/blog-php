<?php
namespace Controller;

use Model\Config;
use Model\Post;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class PostController {
    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function post(Request $request, Response $response) {
        //$url = $request->getAttribute('url');
        $id = $request->getAttribute('id');
        $post = Post::findById($id);

        if (!$post || !$post->get('enabled'))
            return $response->withStatus(302)->withHeader('Location', Config::getConf('root'));
        // TODO: OPSIYONEL url kontrolü if ($post->get('url')->getUrl() == "post/".$url."-".$id);

        return $this->container->renderer->render($response, "post.phtml", [
            "title" => $post->get('title'),
            "post" => $post
        ]);
    }
}