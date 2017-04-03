<?php
namespace Model;

class Tag {

    const URL_PATTERN = "tag/{url}";
    /**
     * @var int
     */
    private $id;
    private $tag;
    /**
     * @var Url
     */
    private $url;

    public function __construct($data) {
        $this->id = $data['id'];
        $this->tag = $data['tag'];
        $this->url = new Url($data['url'], self::URL_PATTERN);
    }

    public static function findById($id) {
        $data = Database::getInstance()
            ->select()
            ->from('tag')
            ->where('id', '=', $id)
            ->execute()
            ->fetch();
        return ($data) ? new Tag($data) : false;
    }
    
    public function get($var) {
        return $this->{$var};
    }

    public static function getAllTags() {
        $data = Database::getInstance()
            ->select()
            ->from('tag')
            ->execute()
            ->fetchAll();

        return array_map(function ($tag) {
            return new Tag($tag);
        }, $data);
    }

    public static function delete($id) {
        // TODO: try-catch
        Database::getInstance()
            ->delete()
            ->from('tag')
            ->where('id', '=', $id)
            ->execute();
    }

    public static function update($id, $data) {
        $update = [];

        if (array_key_exists("tag", $data)) {
            $update["tag"] = $data["tag"];
            $update["url"] = Url::makeUrl($data["tag"], $id);
        }

        // TODO: try-catch
        Database::getInstance()
            ->update($update)
            ->table('tag')
            ->where('id', '=', $id)
            ->execute();
    }

    public static function createAndSave($data) {
        if (array_key_exists("tag", $data)) {
            // escape "
            $data["tag"] = str_replace('"', '\'', $data["tag"]);

            $new = [
                "tag" => $data["tag"],
                "url" => "willUpdate"
            ];
            // TODO: try-catch or transaction
            $lastInsertedId = Database::getInstance()
                ->insert(['tag', 'url'])
                ->into('tag')
                ->values(array_values($new))
                ->execute(true);

            Tag::update($lastInsertedId, $data);
            return true;
        }

        return false;
    }

    public static function getIdsFromTags($tags) {
        $existsTags = Database::getInstance()
                    ->select(['id', 'tag'])
                    ->from('tag')
                    ->whereIn('tag', $tags)
                    ->execute()
                    ->fetchAll(\PDO::FETCH_KEY_PAIR);
        return [
            "tags" => array_values($existsTags),
            "ids" => array_keys($existsTags)
        ];
    }

    public function __toString() {
        return $this->tag;
    }
}