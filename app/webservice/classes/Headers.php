<?php

/*
 * Part of PHP-Webservice-REST-API
 *
 * Copyright (c) Maya K. Herrmann | Yodorada
 *
 * @license LGPL-3.0+
 */

namespace Yodorada\Classes;

/**
 * class Headers
 * @package   Yodorada\Webservice
 * @author    Yodorada | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright Yodorada, 2017
 * @version 0.0.2
 */

class Headers
{
    /**
     * header codes
     * @var array
     */
    protected static $codes = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        449 => 'Retry With',
        450 => 'Blocked by Windows Parental Controls',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended',
    );

    /**
     * current header status code
     * @var int
     */
    public static $statusCode = 200;

    /**
     * get current header status code
     * @param array
     */
    public static function statusString()
    {
        return static::$codes[static::$statusCode];
    }

    /**
     * set allowed methods
     *
     * @param   array $methods allowed methods
     *
     */
    public static function allowMethods($methods = array('get'))
    {
        $methods = implode(', ', $methods);
        header('Access-Control-Allow-Methods: ' . strtoupper($methods));
    }
    /**
     * set access control headers
     *
     * @param   array $domains allowed domains
     *
     */
    public static function allowOrigin($domains = array('*'))
    {
        if (!is_array($domains) || count($domains) < 1) {
            $domains = array('*');
        }
        header('Access-Control-Allow-Origin: ' . implode(', ', $domains));
    }

    /**
     * set content type (default json headers)
     *
     * @param   string $type
     *
     */
    public static function contentType($type = 'application/json')
    {
        header('Content-Type: ' . $type . '; charset=utf-8');
    }

    /**
     * set record count on lists
     *
     * @param   string $type
     *
     */
    public static function totalCount($count)
    {
        header('x-total-count: ' . $count);
    }

    /**
     * set last modified
     *
     * @param   int $tstamp
     *
     */
    public static function lastModified($tstamp = 0)
    {
        if (!$tstamp) {
            return;
        }

        $dt = new DateTime('UTC');
        header('Last-Modified: ' . $dt->format('D, d M Y H:i:s \G\M\T'));
    }

    /**
     * set cache control
     *
     * @param   int $expire cache expires in
     *
     */
    public static function cacheControl($expire = 86400)
    {
        header('Cache-Control: max-age=' . $expire);
        header('Charset: max-age=' . $expire);
    }

    /**
     * set Strict Transport Security
     *
     * @param   int $expire time, in seconds, that the browser should remember that a site is only to be accessed using HTTPS.
     *
     */
    public static function strictTransportSecurity($expire = 31536000)
    {
        header('Strict-Transport-Security: max-age=' . $expire);
    }

    /**
     * set token return
     *
     * @param   array $token
     *
     */
    public static function exposeToken($token)
    {
        header('Token: ' . $token['token']);
        header('TokenExpires: ' . Utils::timestampToOutput($token['expiresAt']));
    }

    /**
     * send Headers
     *
     * @param   integer $code status code
     * @param   array $header additional header fields
     *
     *
     */
    public static function send($code = 200, $header = array())
    {
        header('Access-Control-Expose-Headers: x-total-count, Token, TokenExpires');
        header('Access-Control-Allow-Headers: Token, Authorization, Realm, Content-Type');
        // header('X-HTTP-Method-Override: PUT, DELETE');

        if (isset($header['nocache'])) {
            header('Cache-Control: no-cache, must-revalidate');
        }
        // Charset
        header('Charset: utf-8');

        // status code
        static::$statusCode = $code;
        header('HTTP/1.1 ' . $code . ' ' . static::$codes[$code]);
    }
}
