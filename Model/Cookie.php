<?php
namespace Model;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Cookie {
    /**
     * @param Response &$response
     * @param string $key
     */
    public static function deleteCookie(Response &$response, $key) {
        $cookie = urlencode($key).'='.
            urlencode('deleted').'; expires=Thu, 01-Jan-1970 00:00:01 GMT; Max-Age=0; path=/; httponly';
        $response = $response->withAddedHeader('Set-Cookie', $cookie);
    }

    /**
     * @param Response &$response
     * @param string $cookieName
     * @param string $cookieValue
     */
    public static function addCookie(Response &$response, $cookieName, $cookieValue) {
        $expirationMinutes = 1440;
        $expiry = new \DateTimeImmutable('now + '.$expirationMinutes.'minutes');
        $cookie = urlencode($cookieName).'='.
            urlencode($cookieValue).'; expires='.$expiry->format(\DateTime::COOKIE).'; Max-Age='.
            $expirationMinutes * 60 . '; path=/; httponly';
        $response = $response->withAddedHeader('Set-Cookie', $cookie);
    }

    /**
     * @param Request $request
     * @param string $cookieName
     * @return string
     */
    public static function getCookieValue(Request $request, $cookieName) {
        $cookies = $request->getCookieParams();
        return isset($cookies[$cookieName]) ? $cookies[$cookieName] : null;
    }

}