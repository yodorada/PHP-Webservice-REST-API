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
 * model GroupRights for database table PREFIX.'group_rights'
 * @package   Yodorada\Webservice
 * @author    Yodorada | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright Yodorada, 2017
 * @version 0.0.2
 */
class GroupsRightsModel extends Model
{
    protected $dbTable = "groups_rights";

    public static $parentKey = "groupsId";

    protected static $fieldData = array(
        'id' => array(
            'type' => 'int',
            'autoIncrement' => true,
            'primaryKey' => true,
            'omit' => 'C',
        ),
        'groupsId' => array(
            'type' => 'int',
            'parent' => 'Groups',
            'foreignColumn' => 'id',
            'omit' => 'CU',
        ),
        'rights' => array(
            'type' => 'text',
            'format' => 'json',
            'omit' => 'D',
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
