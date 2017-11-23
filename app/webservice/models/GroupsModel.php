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
 * model group for database table PREFIX.'group'
 * @package   Yodorada\Webservice
 * @author    EINS[23].TV | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright EINS[23].TV, 2017
 * @version 0.0.2
 */
class GroupsModel extends Model
{
    protected $dbTable = "groups";
    protected $dbChildren = array('GroupsRights', 'Users');

    protected static $fieldData = array(
        'id' => array(
            'type' => 'int',
            'autoIncrement' => true,
            'primaryKey' => true,
            'omit' => 'C',
            'filterable' => true,
        ),
        'created' => array(
            'type' => 'int',
            'systemOnly' => true,
        ),
        'changed' => array(
            'type' => 'int',
            'systemOnly' => true,
        ),
        'enabled' => array(
            'type' => 'bool',
            'accepted' => array(0, 1),
            'default' => 0,
        ),
        'groupname' => array(
            'type' => 'varchar',
            'unique' => true,
            'omit' => 'D',
            'required' => true,
            'filterable' => true,
        ),
        'role' => array(
            'type' => 'int',
            'accepted' => array(100, 200, 300),
            'explanation' => 'models.explanation.groups_role',
            'default' => 300,
            'inputCallback' => array('\\Yodorada\\Modules\\ServiceUser', 'getInputMatchRole'),
            'omit' => 'D',
            'required' => true,
            'filterable' => true,
        ),
    );
}
