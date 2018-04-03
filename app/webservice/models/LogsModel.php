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
 * model logs for database table PREFIX.'logs'
 * @package   Yodorada\Webservice
 * @author    Yodorada | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright Yodorada, 2017
 * @version 0.1.2
 */
class LogsModel extends Model
{
    protected $dbTable = "logs";

    protected static $fieldData = array(
        'id' => array(
            'type' => 'int',
            'systemOnly' => true,
            'filterable' => true,
        ),
        'created' => array(
            'type' => 'int',
            'systemOnly' => true,
        ),
        'usersId' => array(
            'type' => 'int',
            'systemOnly' => true,
            'filterable' => true,
        ),
        'resource' => array(
            'type' => 'text',
            'systemOnly' => true,
        ),
        'scope' => array(
            'type' => 'text',
            'systemOnly' => true,
        ),
        'method' => array(
            'type' => 'text',
            'systemOnly' => true,
        ),
        'controller' => array(
            'type' => 'text',
            'systemOnly' => true,
        ),
        'version' => array(
            'type' => 'text',
            'systemOnly' => true,
        ),
        'status' => array(
            'type' => 'text',
            'systemOnly' => true,
        ),
        'dataTransfer' => array(
            'type' => 'text',
            'systemOnly' => true,
            'inputCallback' => 'serialize',
            'outputCallback' => 'unserialize',
        ),
        'httpStatusCode' => array(
            'type' => 'int',
            'systemOnly' => true,
            'filterable' => true,
        ),
        'httpStatusString' => array(
            'type' => 'text',
            'systemOnly' => true,
        ),
        'message' => array(
            'type' => 'text',
            'systemOnly' => true,
        ),
    );
}
