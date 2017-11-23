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
 * model UserRights for database table PREFIX.'user_rights'
 * @package   Yodorada\Webservice
 * @author    EINS[23].TV | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright EINS[23].TV, 2017
 * @version 0.0.1
 */
class UsersRightsModel extends Model
{
    protected $dbTable = "users_rights";

    public static $parentTable = "users";
    public static $parentKey = "usersId";

    protected static $fieldData = array(
        'id' => array(
            'type' => 'int',
            'autoIncrement' => true,
            'primaryKey' => true,
            'omit' => 'C',
        ),
        'usersId' => array(
            'type' => 'int',
            'parent' => 'Users',
            'foreignColumn' => 'id',
            'omit' => 'CU',
        ),
        'rights' => array(
            'type' => 'text',
            'format' => 'json',
            'explanation' => 'models.explanation.rights',
            'inputCallback' => array('\\Yodorada\\Classes\\Utils', 'customAllowedMethodsInput'),
            'outputCallback' => array('\\Yodorada\\Classes\\Utils', 'customAllowedMethods'),
            'required' => true,
        ),
        'created' => array(
            'type' => 'int',
            'systemOnly' => true,
        ),
        'changed' => array(
            'type' => 'int',
            'systemOnly' => true,
        ),
    );
}
