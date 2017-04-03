<?php
namespace Model;

class Admin {

    private $username;
    # hashed
    private $password;
    private $token;

    private function __construct($data) {
        $this->username = $data['username'];
        $this->password = $data['password'];
        $this->token = $data['token'];
    }

    public static function findByUsername($username) {
        $data = Database::getInstance()
            ->select()
            ->from('admin')
            ->where('username', '=', $username)
            ->execute()
            ->fetch();
        return ($data) ? new Admin($data) : false;
    }

    public static function findByToken($token) {
        $data = Database::getInstance()
            ->select()
            ->from('admin')
            ->where('token', '=', $token)
            ->execute()
            ->fetch();
        return ($data) ? new Admin($data) : false;
    }

    public function getUsername() {
        return $this->username;
    }
    /*
    public function changeUsername($username) {
        return Database::getInstance()
                    ->update(['username' => $username])
                    ->table('admin')
                    ->where('username', '=', $this->username)
                    ->execute();
    }
    */
    public function changePassword($newPassword) {
        $this->password = self::passwordHash($newPassword);
        return Database::getInstance()
                        ->update(['password' => $this->password])
                        ->table('admin')
                        ->where('username', '=', $this->username)
                        ->execute();
    }

    public function getToken() {
        return $this->token;
    }

    private function getRealIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    public function generateToken() {
        return md5($this->username.$this->password.$_SERVER['HTTP_USER_AGENT'].$this->getRealIP());
    }

    public function generateAndSetToken() {
        $this->token = $this->generateToken();
        // token benzerliği olma durumunda database tarafından exception atılıyor
        try {
            Database::getInstance()
                    ->update(['token' => $this->token])
                    ->table('admin')
                    ->where('username', '=', $this->username)
                    ->execute();
        } catch (\PDOException $e) {
            return $this->generateAndSetToken();
        }

        return $this->token;
    }

    public function tokenVerify($token) {
        return $this->generateToken() == $token;
    }

    public static function passwordHash($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function passwordVerify($password) {
        return password_verify($password, $this->password);
    }
}