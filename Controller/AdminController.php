<?php
namespace Controller;

use Model\Admin;
use Model\Category;
use Model\Config;
use Model\Cookie;
use Model\Post;
use Model\Site;
use Model\Tag;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;


class AdminController {

    protected $container;

    public function __construct(ContainerInterface $container = null) {
        if (!is_null($container)) // for middleware
            $this->container = $container;
    }
    // middleware for auth
    public function __invoke(Request $request, Response $response, $next) {
        $Token = Cookie::getCookieValue($request, 'Token');

        if (!is_null($Token)) {
            $admin = Admin::findByToken($Token);
            if ($admin) {
                // browser and ip check | generateToken
                if ($admin->generateToken() == $admin->getToken()) {
                    $request = $request->withAttribute('admin', $admin);
                    return $next($request, $response);
                }
                Cookie::deleteCookie($response, 'Token');
            }
        }
        return $response->withStatus(302)->withHeader('Location', Config::getConf('root').Config::getConf('adminDirectory').'/login');
    }

    public function login(Request $request, Response $response) {

        $Token = Cookie::getCookieValue($request, 'Token');

        if (!is_null($Token)) {
            $admin = Admin::findByToken($Token);
            if ($admin) {
                return $response->withStatus(302)->withHeader('Location', 'home');
            }
            Cookie::deleteCookie($response, 'Token');
        }

        return $this->container->adminRenderer->render($response, "login.phtml", []);
    }

    public function doLogin(Request $request, Response $response) {
        $parsedBody = $request->getParsedBody();
        $username = $parsedBody['username'];
        $password = $parsedBody['password'];

        $admin = Admin::findByUsername($username);

        // tüm hatalı durumlar
        $return = [
            "usernameExists" => false,
            "passwordMatch" => false
        ];

        if ($admin) {
            $return["usernameExists"] = true;
            if ($admin->passwordVerify($password)) {
                $return["passwordMatch"] = true;
                Cookie::addCookie($response, "Token", $admin->generateAndSetToken());
            }
        }

        return $response->withJson($return);
    }

    public function logout(Request $request, Response $response) {
        Cookie::deleteCookie($response, 'Token');
        return $response->withStatus(302)->withHeader('Location', 'login');
    }

    # general controllers

    public function home(Request $request, Response $response) {
        /** @var Admin $admin */
        $admin = $request->getAttribute('admin');
        
        return $this->container->adminRenderer->render($response, "home.phtml", [
            "title" => "Ana Sayfa",
            "subTitle" => "Hoş geldin, ".$admin->getUsername(),
            "Element" => "home"
        ]);
    }

    public function adminSettings(Request $request, Response $response) {
        /** @var Admin $admin */
        $admin = $request->getAttribute('admin');

        return $this->container->adminRenderer->render($response, "home.phtml", [
            "title" => "Genel Ayarlar",
            "subTitle" => "Şifre değiştir",
            "admin" => $admin->getUsername(),
            "Element" => "adminSettings"
        ]);
    }

    public function adminSettingsEdit(Request $request, Response $response) {
        /** @var Admin $admin */
        $admin = $request->getAttribute('admin');
        $data = $request->getParsedBody();

        // tüm hatalar
        $return = [
            "success" => false,
            "_" => "Güncelleme başarısız."
        ];

        if (!$admin->passwordVerify($data["oldPassword"])) {
            $return["_"] = "Eski şifre hatalı.";
        }

        if (strlen($data["newPassword"]) < 6) {
            $return["_"] = "Yeni şifre 6 haneden kısa.";
        }

        if ($admin->changePassword($data["newPassword"])) {
            $return["success"] = true;
            $return["_"] = "Şifre başarıyla değiştirildi.";
            Cookie::addCookie($response, "Token", $admin->generateAndSetToken());
        }

        return $response->withJson($return);
    }

    public function settings(Request $request, Response $response) {
        /** @var Admin $admin */
        $admin = $request->getAttribute('admin');
        $settings = Site::getData();

        return $this->container->adminRenderer->render($response, "home.phtml", [
            "title" => "Genel Ayarlar",
            "subTitle" => "Site ayarları",
            "settings" => $settings,
            "Element" => "settings"
        ]);
    }

    public function settingsEdit(Request $request, Response $response) {
        /** @var Admin $admin */
        $admin = $request->getAttribute('admin');
        $settings = Site::getData();
        $data = $request->getParsedBody();

        // tüm hatalar
        $return = [
            "success" => false,
            "_" => "Güncelleme başarısız."
        ];

        $update = array_diff($data, $settings);

        if (count($update) == 0) {
            $return["_"] = "Değişiklik yapılmadı.";
        } else {
            foreach ($update as $var => $val) {
                Site::update($var, $val);
            }

            $return = [
                "success" => true,
                "_" => "Güncelleme başarılı."
            ];
        }
        return $response->withJson($return);
    }

    # category controllers

    public function categoryAll(Request $request, Response $response) {
        /** @var Admin $admin */
        $admin = $request->getAttribute('admin');
        $allCategories = Category::getAllCategories();

        return $this->container->adminRenderer->render($response, "home.phtml", [
            "title" => "Kategori Ayarları",
            "subTitle" => "Tüm kategoriler",
            "categories" => $allCategories,
            "Element" => "categoryAll"
        ]);
    }

    public function categoryView(Request $request, Response $response) {
        /** @var Admin $admin */
        $admin = $request->getAttribute('admin');
        $id = $request->getAttribute('id');
        #$url = $request->getAttribute('url');

        $category = Category::findById($id);
        // if not exists
        if (!$category) {
            return $response->withStatus(302)->withHeader('Location', "all");
        }

        return $this->container->adminRenderer->render($response, "home.phtml", [
            "title" => "Kategori Ayarları",
            "subTitle" => "Kategori: ".$category->get('name'),
            "category" => $category,
            "Element" => "category"
        ]);
    }

    public function categoryDelete(Request $request, Response $response) {
        /** @var Admin $admin */
        $admin = $request->getAttribute('admin');
        $id = $request->getAttribute('id');
        #$url = $request->getAttribute('url');

        // tüm hatalar
        $return = [
            "success" => false,
            "_" => "Silme başarısız."
        ];

        $category = Category::findById($id);

        if (!$category) {
            $response["_"] = "Bulunamadı";
        } else {
            Category::delete($id);
            $return = [
                "success" => true,
                "_" => "Silme başarılı."
            ];
        }
        return $response->withJson($return);
    }

    public function categoryEdit(Request $request, Response $response) {
        /** @var Admin $admin */
        $admin = $request->getAttribute('admin');
        $id = $request->getAttribute('id');
        #$url = $request->getAttribute('url');
        $data = $request->getParsedBody();

        // tüm hatalar
        $return = [
            "success" => false,
            "_" => "Güncelleme başarısız."
        ];

        $category = Category::findById($id);

        if (!$category) {
            $response["_"] = "Bulunamadı";
        } else {
            Category::update($id, $data);
            $return = [
                "success" => true,
                "_" => "Güncelleme başarılı."
            ];
        }
        return $response->withJson($return);
    }

    public function categoryNew(Request $request, Response $response) {
        /** @var Admin $admin */
        $admin = $request->getAttribute('admin');

        return $this->container->adminRenderer->render($response, "home.phtml", [
            "title" => "Kategori Ayarları",
            "subTitle" => "Yeni kategori oluştur",
            "Element" => "categoryNew"
        ]);
    }

    public function doCategoryNew(Request $request, Response $response) {
        /** @var Admin $admin */
        $admin = $request->getAttribute('admin');
        $data = $request->getParsedBody();

        // tüm hatalar
        $return = [
            "success" => false,
            "_" => "Oluşturma başarısız."
        ];

        $isOk = Category::createAndSave($data);

        if ($isOk)
            $return = [
                "success" => true,
                "_" => "Oluşturma başarılı.",
            ];

        return $response->withJson($return);
    }

    # tag controllers

    public function tagAll(Request $request, Response $response) {
        /** @var Admin $admin */
        $admin = $request->getAttribute('admin');
        $allTags = Tag::getAllTags();

        return $this->container->adminRenderer->render($response, "home.phtml", [
            "title" => "Etiket Ayarları",
            "subTitle" => "Tüm etiketler",
            "tags" => $allTags,
            "Element" => "tagAll"
        ]);
    }

    public function tagNew(Request $request, Response $response) {
        /** @var Admin $admin */
        $admin = $request->getAttribute('admin');

        return $this->container->adminRenderer->render($response, "home.phtml", [
            "title" => "Etiket Ayarları",
            "subTitle" => "Yeni etiket oluştur",
            "Element" => "tagNew"
        ]);
    }

    public function doTagNew(Request $request, Response $response) {
        /** @var Admin $admin */
        $admin = $request->getAttribute('admin');
        $data = $request->getParsedBody();

        // tüm hatalar
        $return = [
            "success" => false,
            "_" => "Oluşturma başarısız."
        ];

        $isOk = Tag::createAndSave($data);

        if ($isOk)
            $return = [
                "success" => true,
                "_" => "Oluşturma başarılı.",
            ];

        return $response->withJson($return);
    }

    public function tagView(Request $request, Response $response) {
        /** @var Admin $admin */
        $admin = $request->getAttribute('admin');
        $id = $request->getAttribute('id');
        #$url = $request->getAttribute('url');

        $tag = Tag::findById($id);
        // if not exists
        if (!$tag) {
            return $response->withStatus(302)->withHeader('Location', "all");
        }

        return $this->container->adminRenderer->render($response, "home.phtml", [
            "title" => "Kategori Ayarları",
            "subTitle" => "Kategori: ".$tag->get('tag'),
            "category" => $tag,
            "Element" => "tag"
        ]);
    }

    public function tagEdit(Request $request, Response $response) {
        /** @var Admin $admin */
        $admin = $request->getAttribute('admin');
        $id = $request->getAttribute('id');
        #$url = $request->getAttribute('url');
        $data = $request->getParsedBody();

        // tüm hatalar
        $return = [
            "success" => false,
            "_" => "Güncelleme başarısız."
        ];

        $tag = Tag::findById($id);

        if (!$tag) {
            $response["_"] = "Bulunamadı";
        } else {
            Tag::update($id, $data);
            $return = [
                "success" => true,
                "_" => "Güncelleme başarılı."
            ];
        }
        return $response->withJson($return);
    }

    public function tagDelete(Request $request, Response $response) {
        /** @var Admin $admin */
        $admin = $request->getAttribute('admin');
        $id = $request->getAttribute('id');
        #$url = $request->getAttribute('url');

        // tüm hatalar
        $return = [
            "success" => false,
            "_" => "Silme başarısız."
        ];

        $tag = Tag::findById($id);

        if (!$tag) {
            $response["_"] = "Bulunamadı";
        } else {
            Tag::delete($id);
            $return = [
                "success" => true,
                "_" => "Silme başarılı."
            ];
        }
        return $response->withJson($return);
    }

    # post controllers

    public function postAll(Request $request, Response $response) {
        /** @var Admin $admin */
        $admin = $request->getAttribute('admin');
        $allPosts = Post::getAllPosts();

        return $this->container->adminRenderer->render($response, "home.phtml", [
            "title" => "Paylaşım Ayarları",
            "subTitle" => "Tüm paylaşımlar",
            "posts" => $allPosts,
            "Element" => "postAll"
        ]);
    }

    public function postNew(Request $request, Response $response) {
        /** @var Admin $admin */
        $admin = $request->getAttribute('admin');
        $categories = Category::getAllCategories();
        $tags = Tag::getAllTags();

        return $this->container->adminRenderer->render($response, "home.phtml", [
            "title" => "Paylaşım Ayarları",
            "subTitle" => "Yeni paylaşım oluştur",
            "categories" => $categories,
            "tagList" => $tags,
            "Element" => "postNew"
        ]);
    }

    private function whichTagIdsWillInsert($tags) {
        $allTags = explode(",", $tags);
        $existsTags = Tag::getIdsFromTags($allTags);
        // not exists tags creating..
        $notExistsTags = array_diff($allTags, $existsTags["tags"]);
        foreach ($notExistsTags as $tag) {
            Tag::createAndSave([
                "tag" => $tag
            ]);
        }
        // and all tags add to post_has_tag
        return Tag::getIdsFromTags($allTags)["ids"];
    }

    public function doPostNew(Request $request, Response $response) {
        /** @var Admin $admin */
        $admin = $request->getAttribute('admin');
        $data = $request->getParsedBody();

        // tüm hatalar
        $return = [
            "success" => false,
            "_" => "Oluşturma başarısız."
        ];

        $data["tags"] = $this->whichTagIdsWillInsert($data["tags"]);

        $isOk = Post::createAndSave($data);

        if ($isOk)
            $return = [
                "success" => true,
                "_" => "Oluşturma başarılı.",
            ];

        return $response->withJson($return);
    }

    public function postView(Request $request, Response $response) {
        /** @var Admin $admin */
        $admin = $request->getAttribute('admin');
        $id = $request->getAttribute('id');
        #$url = $request->getAttribute('url');
        $categories = Category::getAllCategories();
        $tags = Tag::getAllTags();

        $post = Post::findById($id);
        // if not exists
        if (!$post) {
            return $response->withStatus(302)->withHeader('Location', "all");
        }

        return $this->container->adminRenderer->render($response, "home.phtml", [
            "title" => "Paylaşım Ayarları",
            "subTitle" => "Paylaşım: ".$post->get('title'),
            "post" => $post,
            "categories" => $categories,
            "tagList" => $tags,
            "Element" => "post"
        ]);
    }

    public function postEdit(Request $request, Response $response) {
        /** @var Admin $admin */
        $admin = $request->getAttribute('admin');
        $id = $request->getAttribute('id');
        #$url = $request->getAttribute('url');
        $data = $request->getParsedBody();

        // tüm hatalar
        $return = [
            "success" => false,
            "_" => "Güncelleme başarısız."
        ];

        $post = Post::findById($id);

        if (!$post) {
            $response["_"] = "Bulunamadı";
        } else {
            $data["tags"] = $this->whichTagIdsWillInsert($data["tags"]);
            Post::clearTags($id);
            Post::update($id, $data);
            $return = [
                "success" => true,
                "_" => "Güncelleme başarılı."
            ];
        }
        return $response->withJson($return);
    }

    public function postDelete(Request $request, Response $response) {
        /** @var Admin $admin */
        $admin = $request->getAttribute('admin');
        $id = $request->getAttribute('id');
        #$url = $request->getAttribute('url');

        // tüm hatalar
        $return = [
            "success" => false,
            "_" => "Silme başarısız."
        ];

        $post = Post::findById($id);

        if (!$post) {
            $response["_"] = "Bulunamadı";
        } else {
            Post::delete($id);
            $return = [
                "success" => true,
                "_" => "Silme başarılı."
            ];
        }
        return $response->withJson($return);
    }
}