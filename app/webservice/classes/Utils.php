<?php

/*
 * Part of PHP-Webservice-REST-API
 *
 * Copyright (c) Maya K. Herrmann | EINS[23].TV
 *
 * @license LGPL-3.0+
 */

namespace Yodorada\Classes;

use Yodorada\Classes\Setting;
use Yodorada\Models\TokenModel;
use \Firebase\JWT\JWT;

/**
 * static class utils helper
 *
 * @package   Yodorada\Webservice
 * @author    EINS[23].TV | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright EINS[23].TV, 2017
 * @version 0.0.4
 */
class Utils
{

    /**
     * get timestamp from an utc string
     *
     * @param string $timeStr
     *
     * @return int
     *
     */
    public static function utcDateToTimestamp($timeStr)
    {
        return new DateTime($timeStr, new DateTimezone('UTC'));
    }

    /**
     * check if string is timestamp
     *
     * @param string $timeStr
     *
     * @return int
     *
     */
    public static function isTimestamp($tstamp)
    {
        return (ctype_digit($tstamp) && strtotime(date('Y-m-d H:i:s', $tstamp)) === (int) $tstamp);
    }

    /**
     * check if string should be converted to tstamp
     *
     * @param string $timeStr
     *
     * @return int
     *
     */
    public static function timestampFromInput($input)
    {
        if (preg_match('/^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{4})$/i', $input)) {
            return strtotime($input);
        }
        if (static::isTimestamp($input)) {
            return $input;
        }
        return 0;
    }

    /**
     * check if string should be converted to tstamp
     *
     * @param string $timeStr
     *
     * @return int
     *
     */
    public static function timestampToOutput($tstamp)
    {
        if (static::isTimestamp($tstamp)) {
            return date("Y-m-d\TH:i:sO", $tstamp);
        }
        return '';
    }

    /**
     * password hash
     *
     * @param string $pw unencrypted password
     *
     * @return string encrypted password
     *
     */
    public static function hashPassword($pw)
    {
        return password_hash($pw, PASSWORD_BCRYPT, array('cost' => 10));
    }

    /**
     * Verify a password against a password hash
     *
     * @param string $pw  readable password
     * @param string $strHash  password hash
     *
     * @return boolean
     */
    public static function verifyPassword($pw, $strHash)
    {
        return password_verify($pw, $strHash);
    }

    /**
     * create token for user
     *
     * @param int $usersId
     * @return  \TokenModel
     */
    public static function createToken($usersId)
    {
        $token = new TokenModel();
        $token->expiresAt = time() + $GLOBALS['CONFIG']['TOKEN']['EXPIRES'];
        $token->token = static::accessToken();
        $token->usersId = $usersId;
        $id = $token->save();
        return $token;
    }

    /**
     * generate a new JWT access token
     *
     * @return string
     *
     * @see  https://github.com/firebase/php-jwt
     */
    public static function accessToken()
    {
        $now = time();
        $token = array(
            "iss" => static::getHostAndApiPath(),
            "iat" => ($now - 600),
            "nbf" => $now,
        );
        return JWT::encode($token, $GLOBALS['CONFIG']['TOKEN']['SECRET_KEY']);
    }

    /**
     * verify JWT access token
     *
     * @return bool
     *
     * @see  https://github.com/firebase/php-jwt
     */
    public static function verifyToken($token)
    {
        try {
            $jwt = JWT::decode($token, $GLOBALS['CONFIG']['TOKEN']['SECRET_KEY'], array('HS256'));
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * sanitize array values
     *
     * @param   array $arr
     * @return  array
     *
     */
    public static function sanitize($arr)
    {
        $link = Database::getInstance();
        return array_map(function ($value) use ($link) {
            if ($value === null) {
                return null;
            }

            $value = filter_var(rawurldecode($value), FILTER_SANITIZE_STRING);
            return $link->escape($value);

        }, $arr);
    }

    /**
     * output json/xml error
     *
     * @param   string|array $n
     * @return  void
     *
     */
    public static function outputError($n)
    {
        $key = 'message';
        if (is_array($n)) {
            $key = 'messages';
        }

        $arr = array(
            'errors' => array('httpStatusCode' => Headers::$statusCode, $key => $n),
        );
        if ($GLOBALS['CONFIG']['APPLICATION']['OUTPUT']['ADD_WEBSERVICE_STATS']) {
            // service response
            $service = array(
                'start' => static::timestampToOutput(Setting::get('scriptStart')),
                'stop' => static::timestampToOutput(time()),
                'elapsed' => (microtime(true) - Setting::get('elapsedStart')),
                'resource' => static::getHostAndApiPath() . '/' . join('/', Setting::get('route')),
                'method' => strtoupper(Setting::get('method')),
            );
            $arr['webservice'] = $service;
            $arr['status'] = Headers::$statusCode;
            $arr['scope'] = '';
        }
        switch ($GLOBALS['CONFIG']['APPLICATION']['OUTPUT']['FORMAT']) {
            case 'xml':
                Headers::contentType('text/xml');
                echo static::generateXML($arr);
                break;
            case 'json':
            default:
                Headers::contentType('application/x-resource+json');
                echo json_encode($arr);
                break;
        }

    }

    /**
     * generate XML Doc from array
     *
     * @param   array $arr
     * @return  SimpleXML
     *
     */
    public static function generateXML($arr)
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><root></root>');
        $xml->addAttribute('statusCode', Headers::$statusCode);
        $xml->addAttribute('statusMessage', Headers::statusString());
        if ($GLOBALS['CONFIG']['APPLICATION']['OUTPUT']['ADD_WEBSERVICE_STATS'] && isset($arr['webservice'])) {
            $webservice = $xml->addChild('webservice');
            foreach ($arr['webservice'] as $key => $value) {
                $subnode = $webservice->addChild($key, $value);
            }
            $subnode = $webservice->addChild('status', $arr['status']);
            $subnode = $webservice->addChild('scope', $arr['scope']);
        }
        if (isset($arr['errors'])) {
            $key = 'errors';
            $arr = $arr['errors'];
            $node = $xml->addChild('errors');
        } else {
            $key = 'data';
            $arr = $arr['data'];
            $node = $xml->addChild('data');
        }
        static::recursiveBuildXML($arr, $node);

        return static::selfClosingTagsXML($xml->asXML());
    }

    /**
     * recursive function to add xml childs
     *
     * @param   array $arr
     * @param   SimpleXMLElement $xml
     * @return  void
     *
     */
    private static function recursiveBuildXML($arr, \SimpleXMLElement &$xml)
    {
        foreach ($arr as $key => $value) {
            $key = is_numeric($key) ? "item" : $key;
            if (is_array($value)) {
                $node = $xml->addChild($key);
                static::recursiveBuildXML($value, $node);
            } else {
                $node = $xml->addChild($key, $value);
            }
        }
    }

    /**
     * check for XML self closing tags and convert if required
     *
     * @param   string $xml
     * @return  string
     *
     */
    public static function selfClosingTagsXML($xml)
    {
        if ($GLOBALS['CONFIG']['APPLICATION']['OUTPUT']['XML_SELF_CLOSING_TAGS']) {
            return $xml;
        }
        preg_match_all('/<(\w{2,100})+\s{0,}\/>/', $xml, $emptyTags);

        if (count($emptyTags)) {
            for ($i = 0; $i < count($emptyTags[0]); $i++) {
                $xml = str_replace($emptyTags[0][$i], "<" . $emptyTags[1][$i] . "></" . $emptyTags[1][$i] . ">", $xml);
            }

        }
        return $xml;
    }

    /**
     * is associative array
     *
     * @param   array $arr
     * @return  bool
     *
     */
    public static function hasStringKeys($arr)
    {
        return count(array_filter(array_keys($arr), 'is_string')) > 0;
    }

    /**
     * get file paths relative and absolute
     *
     * @return   string $str
     *
     */
    public static function getFilePaths($str)
    {
        return array(
            'absolute' => static::getApplicationAbsolutePath($str),
            'relative' => $str,
        );
    }

    /**
     * get absolute path
     *
     * @return   string $str
     *
     */
    public static function getApplicationAbsolutePath($str = '')
    {
        return static::getApplicationHost() . (strlen($str) ? '/' . $str : '');
    }

    /**
     * dont save absolute path
     *
     * @return   string $str
     *
     */
    public static function dontSaveApplicationAbsolutePath($str = '')
    {
        return str_replace(static::getApplicationHost() . '/', '', $str);
    }

    /**
     * get full host
     *
     * @return   string $str
     *
     */
    public static function getHost()
    {
        return (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
    }

    /**
     * get app host
     *
     * @return   string $str
     *
     */
    public static function getApplicationHost()
    {

        if (strlen($GLOBALS['CONFIG']['APPLICATION']['HOST'])) {
            $host = trim($GLOBALS['CONFIG']['APPLICATION']['HOST'], '/');
            if (preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $appHost)) {
                return $host;
            }
        }
        return static::getHost();
    }

    /**
     * get full host
     *
     * @return   string $str
     *
     */
    public static function getHostAndApiPath()
    {
        $host = static::getHost();
        if (strlen($GLOBALS['CONFIG']['API']['DIRECTORY'])) {
            $dir = trim($GLOBALS['CONFIG']['API']['DIRECTORY'], '/');
            $host .= '/' . $dir;
        }
        return $host;
    }

    /**
     * get methods from string CRUD
     *
     * @return   array
     *
     */
    public static function crudToMethods($crud)
    {
        $methods = array('C' => 'post', 'R' => 'get', 'U' => 'put', 'D' => 'delete');
        $allowed = array_map(
            function ($el) use ($methods) {
                return (array_key_exists($el, $methods) ? $methods[$el] : null);
            },
            str_split(strtoupper($crud))
        );
        $flat = array();
        foreach (new \RecursiveIteratorIterator(new \RecursiveArrayIterator($allowed)) as $v) {
            $flat[] = $v;
        }
        return $flat;
    }

    /**
     * check if a string is valid json
     *
     * @param   string $string the input string
     * @return  boolean
     *
     */
    public static function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * pair array values for quicker access
     *
     * @param   array $arr The request array
     * @return  array the paired key=>value array
     *
     */
    public static function pairArrayNth($arr, $nth = 1)
    {
        $newArr = array();
        for ($i = 0; $i + $nth < count($arr); $i++) {
            if (is_int($arr[$i])) {
                // dont add integer as keys
                continue;
            }
            if (isset($arr[$i + $nth])) {
                $newArr[$arr[$i]] = $arr[$i + $nth];
            }
        }

        return $newArr;
    }

    /**
     * generate custom allowed methods
     *
     * @return array
     */
    public static function getAllowedMethodsArray($rights)
    {
        if (is_string($rights) && static::isJson($rights)) {
            $rights = json_decode($rights, true);
        }
        if (!is_array($rights)) {
            $rights = array();
        }
        return $rights;
    }

    /**
     * generate methods from available custom controllers and current user rights
     *
     * @return json
     */
    public static function customAllowedMethodsInput($rights)
    {
        return json_encode(static::customAllowedMethods($rights));
    }

    /**
     * generate methods from available custom controllers and current user rights
     *
     * @return array
     */
    public static function customAllowedMethods($rights)
    {

        $rights = static::getAllowedMethodsArray($rights);
        $return = array();
        $custom = Registry::getCustom();
        $routes = Registry::getRoutes();
        foreach ($custom as $key => $arr) {
            if (count($arr['resources'])) {
                $return[$key]['label'] = $arr['label'];
                foreach ($arr['resources'] as $resource) {
                    // if (substr($resource, -5) == '/info') {
                    //     continue;
                    // }
                    $chunk = 'chunks_' . (substr_count($resource, '/') + 1);
                    if (isset($routes[$chunk]) && array_key_exists($resource, $routes[$chunk])) {
                        $methodArr = $routes[$chunk][$resource]['methods'];
                        $publicArr = array_fill_keys(array_diff($methodArr, $routes[$chunk][$resource]['authorization']), true);

                        $defaultMethods = array_fill_keys($methodArr, true);
                        $userMethods = array_merge(array_fill_keys($methodArr, false), $publicArr);

                        $methods = array();
                        if (array_key_exists($key, $rights) && isset($rights[$key]['resources'][$resource])) {
                            $userMethods = $rights[$key]['resources'][$resource]['methods'];
                        }
                        foreach ($defaultMethods as $methodKey => $methodValue) {
                            if (array_key_exists($methodKey, $userMethods)) {
                                $methods[$methodKey] = $userMethods[$methodKey];
                            } else {
                                $methods[$methodKey] = $methodValue;
                            }
                        }
                        $return[$key]['resources'][$resource] = array('type' => static::getResourceType($resource), 'methods' => $methods);
                    }
                }

            }
        }
        return $return;
    }

    /**
     * generate methods from available custom controllers and current user rights
     *
     * @return array
     */
    public static function customAllowedMethodsAdminInput($rights)
    {
        $rights = static::getAllowedMethodsArray($rights);
        $return = array();
        $custom = Registry::getCustom();
        $routes = Registry::getRoutes();

        foreach ($rights as $right) {
            $key = $right['key'];
            $label = $right['label'];

            if (count($right['resources']) && isset($custom[$key])) {
                $return[$key]['label'] = $label;

                foreach ($right['resources'] as $resource) {
                    $resKey = $resource['key'];
                    // if (substr($resKey, -5) == '/info') {
                    //     continue;
                    // }
                    $chunk = 'chunks_' . (substr_count($resKey, '/') + 1);
                    if (isset($routes[$chunk]) && array_key_exists($resKey, $routes[$chunk])) {
                        $methodArr = $routes[$chunk][$resKey]['methods'];
                        $publicArr = array_fill_keys(array_diff($methodArr, $routes[$chunk][$resKey]['authorization']), true);

                        $defaultMethods = array_fill_keys($methodArr, true);
                        $userMethods = array_merge(array_fill_keys($methodArr, false), $publicArr);

                        $methods = array();
                        foreach ($resource['methods'] as $method) {
                            $tempMethods[$method['method']] = $method['state'];
                        }
                        $userMethods = $tempMethods;

                        foreach ($defaultMethods as $methodKey => $methodValue) {
                            if (array_key_exists($methodKey, $userMethods)) {
                                $methods[$methodKey] = $userMethods[$methodKey];
                            } else {
                                $methods[$methodKey] = $methodValue;
                            }
                        }
                        $return[$key]['resources'][$resKey] = array('type' => static::getResourceType($resKey), 'methods' => $methods);
                    }
                }
            }

        }
        return json_encode($return);
    }

    /**
     * generate methods from available custom controllers and current user rights
     *
     * @return array
     */
    public static function customAllowedMethodsAdminOutput($rights)
    {
        $rights = static::getAllowedMethodsArray($rights);
        $return = array();
        $custom = Registry::getCustom();
        $routes = Registry::getRoutes();
        foreach ($custom as $key => $arr) {
            if (count($arr['resources'])) {
                $tempArr = array();
                $tempArr['key'] = $key;
                $tempArr['label'] = $arr['label'];

                foreach ($arr['resources'] as $resource) {
                    // if (substr($resource, -5) == '/info') {
                    //     continue;
                    // }
                    $chunk = 'chunks_' . (substr_count($resource, '/') + 1);
                    if (isset($routes[$chunk]) && array_key_exists($resource, $routes[$chunk])) {
                        if (!isset($tempArr['resources'])) {
                            $tempArr['resources'] = array();
                        }

                        $methodArr = $routes[$chunk][$resource]['methods'];
                        $publicArr = array_fill_keys(array_diff($methodArr, $routes[$chunk][$resource]['authorization']), true);

                        $defaultMethods = array_fill_keys($methodArr, true);
                        $userMethods = array_merge(array_fill_keys($methodArr, false), $publicArr);

                        $methods = array();
                        if (array_key_exists($key, $rights) && isset($rights[$key]['resources'][$resource])) {
                            $userMethods = $rights[$key]['resources'][$resource]['methods'];
                        }
                        foreach ($defaultMethods as $methodKey => $methodValue) {
                            if (array_key_exists($methodKey, $userMethods)) {
                                $methods[] = array('method' => $methodKey, 'state' => $userMethods[$methodKey]);
                            } else {
                                $methods[] = array('method' => $methodKey, 'state' => $methodValue);
                            }
                        }
                        $tempArr['resources'][] = array('key' => $resource, 'type' => static::getResourceType($resource), 'methods' => $methods);

                    }
                }
                $return[] = $tempArr;
            }
        }
        return $return;

    }

    /**
     * eval resource type by
     *
     * @param   string $resource
     * @return  string
     *
     */
    public static function getResourceType($resource)
    {
        if (strpos($resource, '/info') !== false) {
            return 'Info';
        }
        $ids = substr_count($resource, "{id}");
        $chunks = substr_count($resource, "/");
        if ($ids == 2 || ($ids == 1 && $chunks == 1)) {
            return 'Resource';
        }
        return 'Collection';
    }

    /**
     * create url friendly string
     * @param string
     * @return string
     */
    public static function urlFriendly($string)
    {
        $string = trim($string);
        if (ctype_digit($string)) {
            return $string;
        } else {
            $search = array("Ä", "Ö", "Ü", "ä", "ö", "ü", "ß");
            $replace = array("ae", "oe", "ue", "ae", "oe", "ue", "ss");
            $string = str_replace($search, $replace, $string);
            $string_encoded = htmlentities($string, ENT_NOQUOTES, "UTF-8");
            $accents = "/&([A-Za-z]{1,2})(grave|acute|circ|cedil|uml|lig|tilde);/";
            $string = preg_replace($accents, "$1", $string_encoded);
            $replace = array("([\40])", "([\[\]:])", "([^a-zA-Z0-9-._])", "(-{2,})");
            $with = array("-", "-", "", "-");
            $string = preg_replace($replace, $with, $string);
        }
        return strtolower($string);
    }

}
