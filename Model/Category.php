<?php
namespace Model;

use Exception;

class Category {

    const URL_PATTERN = "category/{url}";
    /**
     * @var int
     */
    private $id;
    private $name;
    /**
     * @var Url
     */
    private $url;
    /**
     * @var bool
     */
    private $enabled;
    /**
     * @var bool
     */
    private $showcase;
    /**
     * @var int
     */
    private $created;

    private function __construct($data) {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->url = new Url($data['url'], self::URL_PATTERN);
        $this->enabled = ($data['enabled']) ? true : false;
        $this->showcase = ($data['showcase']) ? true : false;
        $this->created = $data['created'];
    }

    public static function findById($id) {
        $data = Database::getInstance()
            ->select()
            ->from('category')
            ->where('id', '=', $id)
            ->execute()
            ->fetch();
        return ($data) ? new Category($data) : false;
    }

    public function get($var) {
        return $this->{$var};
    }

    public static function getShowcases() {
        $data = Database::getInstance()
                    ->select(['name', 'url'])
                    ->from('category')
                    ->whereMany(['enabled' => true, 'showcase' => true], '=')
                    ->execute()
                    ->fetchAll(\PDO::FETCH_KEY_PAIR);

        return array_map(function ($link) {
            return new Url($link, self::URL_PATTERN);
        }, $data);
    }

    public static function getAllCategories() {
        $data = Database::getInstance()
            ->select()
            ->from('category')
            ->execute()
            ->fetchAll();

        return array_map(function ($category) {
            return new Category($category);
        }, $data);
    }

    public static function delete($id) {
        // TODO: try-catch
        Database::getInstance()
                ->delete()
                ->from('category')
                ->where('id', '=', $id)
                ->execute();
    }

    public static function update($id, $data) {
        $update = [];

        if (array_key_exists("name", $data)) {
            $update["name"] = $data["name"];
            $update["url"] = Url::makeUrl($data["name"], $id);
        }

        if (array_key_exists("enabled", $data))
            $update["enabled"] = boolval($data["enabled"]);

        if (array_key_exists("showcase", $data))
            $update["showcase"] = boolval($data["showcase"]);
        // TODO: try-catch
        Database::getInstance()
                ->update($update)
                ->table('category')
                ->where('id', '=', $id)
                ->execute();
    }

    public static function createAndSave($data) {
        if (array_key_exists("name", $data)
            && array_key_exists("enabled", $data)
            && array_key_exists("showcase", $data)) {

            $new = [
                "name" => $data["name"],
                "url" => "willUpdate"
            ];
            // TODO: try-catch or transaction
            $lastInsertedId = Database::getInstance()
                ->insert(['name', 'url'])
                ->into('category')
                ->values(array_values($new))
                ->execute(true);
            
            Category::update($lastInsertedId, $data);
            return true;
        }

        return false;
    }
}