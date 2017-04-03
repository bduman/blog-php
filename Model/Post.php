<?php
namespace Model;

use \Model\Database as Database;

class Post {

    const URL_PATTERN = "post/{url}";
    /**
     * @var int
     */
    private $id;
    private $title;
    private $article;
    /**
     * @var Url
     */
    private $url;
    /**
     * @var Category
     */
    private $category;
    /**
     * @var int
     * timestamp
     */
    private $published;
    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var Tag[]
     */
    private $hasTags = [];

    private function __construct($data) {
        $this->id = $data['id'];
        $this->title = $data['title'];
        $this->article = $data['article'];
        $this->url = new Url($data['url'], self::URL_PATTERN);
        $this->category = Category::findById($data['category_id']);
        $this->published = $data['published'];
        $this->enabled = ($data['enabled']) ? true : false;
        $this->hasTags = $this->getAllTags();
    }

    public static function findById($id) {
        $data = Database::getInstance()
                        ->select()
                        ->from('post')
                        ->where('id', '=', $id)
                        ->execute()
                        ->fetch();
        return ($data) ? new Post($data) : false;
    }

    private function getAllTags() {
        $data = Database::getInstance()
                        ->select()
                        ->from('post_has_tag')
                        ->where('post_has_tag.post_id', '=', $this->id)
                        ->rightJoin('tag', 'post_has_tag.tag_id', '=', 'tag.id')
                        ->execute()
                        ->fetchAll();
        return array_map(function ($tag) {
            return new Tag($tag);
        }, $data);
    }

    public function get($var) {
        return $this->{$var};
    }
    
    public static function getLatestPosts($n, $offset, $where = null) {
        $statement = Database::getInstance()
                        ->select()
                        ->from('post');

        if (!is_null($where) && is_array($where))
            $statement = $statement->whereMany($where, '=');

        $data = $statement->orderBy('published', 'DESC')
                        ->limit($n, $offset)
                        ->execute()
                        ->fetchAll();
        return array_map(function ($post) {
            return new Post($post);
        }, $data);
    }

    public static function getPostsHasTag($tagId, $n, $offset) {
        $data = Database::getInstance()
                        ->select()
                        ->from('post_has_tag')
                        ->where('post_has_tag.tag_id', '=', $tagId)
                        ->rightJoin('post', 'post_has_tag.post_id', '=', 'post.id')
                        ->orderBy('published', 'DESC')
                        ->limit($n, $offset)
                        ->execute()
                        ->fetchAll();
        return array_map(function ($post) {
            return new Post($post);
        }, $data);
    }


    public static function getAllPosts() {
        $data = Database::getInstance()
            ->select()
            ->from('post')
            ->execute()
            ->fetchAll();

        return array_map(function ($tag) {
            return new Post($tag);
        }, $data);
    }

    public static function update($id, $data) {
        $update = [];

        if (array_key_exists("title", $data)) {
            $update["title"] = $data["title"];
            $update["article"] = $data["article"];
            $update["url"] = Url::makeUrl($data["title"], $id);
            $update["category_id"] = $data["category"];
            $update["enabled"] = $data["enabled"];
            foreach ($data["tags"] as $tag) {
                Database::getInstance()
                    ->insert(['post_id', 'tag_id'])
                    ->into('post_has_tag')
                    ->values([$id, $tag])
                    ->execute(false);
            }
        }

        // TODO: try-catch
        Database::getInstance()
            ->update($update)
            ->table('post')
            ->where('id', '=', $id)
            ->execute();
    }

    public static function createAndSave($data) {
        if (array_key_exists("title", $data)
            && array_key_exists("article", $data)
            && array_key_exists("category", $data)) {

            $new = [
                "title" => $data["title"],
                "url" => "willUpdate"
            ];

            // TODO: try-catch or transaction
            $lastInsertedId = Database::getInstance()
                ->insert(['title', 'url'])
                ->into('post')
                ->values(array_values($new))
                ->execute(true);

            Post::update($lastInsertedId, $data);
            return true;
        }

        return false;
    }

    public static function clearTags($id) {
        Database::getInstance()
                ->delete()
                ->from('post_has_tag')
                ->where('post_id', '=', $id)
                ->execute();
    }

    public static function delete($id) {
        // TODO: try-catch
        Database::getInstance()
            ->delete()
            ->from('post')
            ->where('id', '=', $id)
            ->execute();
    }

    public function getSummary($length) {
        $summary = html_entity_decode(strip_tags($this->article));
        return substr($summary, 0, $length)."..";
    }
}