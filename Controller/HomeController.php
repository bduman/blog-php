<?php
namespace Controller;

use Model\Category;
use Model\Config;
use Model\Post;
use Model\Tag;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Tackk\Cartographer\ChangeFrequency;
use Tackk\Cartographer\Sitemap;

class HomeController {

    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function home(Request $request, Response $response) {
        $page = $request->getAttribute('page');
        $page = ($page < 2) ? 1 : $page;
        $postPerPage = Config::getConf('postPerPage');
        $offset = ($page - 1) * $postPerPage;

        $posts = Post::getLatestPosts($postPerPage + 1, $offset, [
            "enabled" => true
        ]);

        if ($page != 1 && count($posts) == 0)
            return $response->withStatus(302)->withHeader('Location', Config::getConf('root'));

        $olderState = (count($posts) > $postPerPage);
        if ($olderState)
            array_pop($posts);

        return $this->container->renderer->render($response, "home.phtml", [
            "title" => "Ana Sayfa",
            "posts" => $posts,
            "filter" => "home/",
            "newer" => [
                "state" => ($page > 1),
                "page" => ($page > 1) ? $page - 1 : 1
            ],
            "older" => [
                "state" => $olderState,
                "page" => ($olderState) ? $page + 1 : $page
            ]
        ]);
    }

    public function category(Request $request, Response $response) {
        $categoryId = $request->getAttribute('id');
        $url = $request->getAttribute('url');
        $page = $request->getAttribute('page');
        $page = ($page < 2) ? 1 : $page;
        $postPerPage = Config::getConf('postPerPage');
        $offset = ($page - 1) * $postPerPage;

        $posts = Post::getLatestPosts($postPerPage + 1, $offset, [
            "category_id" => $categoryId,
            "enabled" => true
        ]);

        if (count($posts) == 0 || !Category::findById($categoryId)->get('enabled'))
            return $response->withStatus(302)->withHeader('Location', Config::getConf('root'));

        $categoryName = $posts[0]->get('category')->get('name');

        $olderState = (count($posts) > $postPerPage);
        if ($olderState)
            array_pop($posts);

        return $this->container->renderer->render($response, "home.phtml", [
            "title" => $categoryName,
            "posts" => $posts,
            // TODO: filterı category->get('url') şeklinde al
            "filter" => "category/".$url."-".$categoryId."/",
            "filterName" => "Kategoriye göre: ".$categoryName,
            "newer" => [
                "state" => ($page > 1),
                "page" => ($page > 1) ? $page - 1 : 1
            ],
            "older" => [
                "state" => $olderState,
                "page" => ($olderState) ? $page + 1 : $page
            ]
        ]);
    }

    public function tag(Request $request, Response $response) {
        $tagId = $request->getAttribute('id');
        $url = $request->getAttribute('url');
        $page = $request->getAttribute('page');
        $page = ($page < 2) ? 1 : $page;
        $postPerPage = Config::getConf('postPerPage');
        $offset = ($page - 1) * $postPerPage;

        $posts = Post::getPostsHasTag($tagId, $postPerPage + 1, $offset);

        if (count($posts) == 0)
            return $response->withStatus(302)->withHeader('Location', Config::getConf('root'));

        $hasTags = $posts[0]->get('hasTags');
        $tagName = "";
        foreach ($hasTags as $tag) {
            if ($tag->get('id') == $tagId)
                $tagName = $tag->get('tag');
        }

        $olderState = (count($posts) > $postPerPage);
        if ($olderState)
            array_pop($posts);

        return $this->container->renderer->render($response, "home.phtml", [
            "title" => $tagName,
            "posts" => $posts,
            // TODO: filterı tag->get('url') şeklinde al
            "filter" => "tag/".$url."-".$tagId."/",
            "filterName" => "Etikete göre: ".$tagName,
            "newer" => [
                "state" => ($page > 1),
                "page" => ($page > 1) ? $page - 1 : 1
            ],
            "older" => [
                "state" => $olderState,
                "page" => ($olderState) ? $page + 1 : $page
            ]
        ]);
    }

    public function sitemap(Request $request, Response $response) {
        $lastPosts = Post::getLatestPosts(10, 0, [
            "enabled" => true
        ]);
        $lastCategories = Category::getAllCategories();
        $lastCategories = array_slice($lastCategories, 0, 15);
        $lastTags = Tag::getAllTags();
        $lastTags = array_slice($lastTags, 0, 15);
        $today = date("Y-m-d");

        $sitemap = new Sitemap();
        $sitemap->add(Config::getConf('root'), $today, ChangeFrequency::WEEKLY, 1.0);
        /** @var Post $post */
        foreach ($lastPosts as $post) {
            $sitemap->add(
                $post->get('url')->getFullUrl(Config::getConf('root')),
                $post->get('published'),
                ChangeFrequency::DAILY,
                0.9
            );
        }
        /** @var Category $category */
        foreach ($lastCategories as $category) {
            $sitemap->add(
                $category->get('url')->getFullUrl(Config::getConf('root')),
                $category->get('created'),
                ChangeFrequency::WEEKLY,
                0.7
            );
        }
        /** @var Tag $tag */
        foreach ($lastTags as $tag) {
            $sitemap->add(
                $tag->get('url')->getFullUrl(Config::getConf('root')),
                $today,
                ChangeFrequency::HOURLY,
                0.4
            );
        }

        $response = $response->withHeader("Content-Type", "text/xml");
        return $response->write($sitemap->toString());
    }
    
    public function robots(Request $request, Response $response) {
        $response = $response->withHeader("Content-Type", "text/plain");
        return $response
            ->write("User-Agent: *".PHP_EOL."Allow:".PHP_EOL."Sitemap: ".Config::getConf('root')."sitemap.xml");
    }
    
    
}