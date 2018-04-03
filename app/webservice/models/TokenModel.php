<?php

/*
 * Part of PHP-Webservice-REST-API
 *
 * Copyright (c) Maya K. Herrmann | Yodorada
 *
 * @license LGPL-3.0+
 */
namespace Yodorada\Models;

/**
 * model token for database table PREFIX.'access_token'
 * @package   Yodorada\Webservice
 * @author    Yodorada | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright Yodorada, 2017
 * @version 0.0.1
 */
class TokenModel extends Model
{
    protected $dbTable = "access_token";

    protected static $fieldData = array(
        'id' => array(
            'type' => 'int',
            'systemOnly' => true,
        ),
        'usersId' => array(
            'type' => 'int',
            'systemOnly' => true,
        ),
        'token' => array(
            'type' => 'text',
            'systemOnly' => true,
        ),
        'expiresAt' => array(
            'type' => 'int',
            'systemOnly' => true,
        ),
    );
}
