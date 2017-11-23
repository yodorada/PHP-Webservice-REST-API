<?php

/*
 * Part of PHP-Webservice-REST-API
 *
 * Copyright (c) Maya K. Herrmann | EINS[23].TV
 *
 * @license LGPL-3.0+
 */
namespace Yodorada\Models;

/**
 * model token for database table PREFIX.'access_token'
 * @package   Yodorada\Webservice
 * @author    EINS[23].TV | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright EINS[23].TV, 2017
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
