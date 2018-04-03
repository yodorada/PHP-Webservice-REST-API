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
 * model User for database table PREFIX.'user'
 * @package   Yodorada\Webservice
 * @author    Yodorada | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright Yodorada, 2017
 * @version 0.1.2
 */
class UsersModel extends Model
{
    protected $dbTable = "users";
    protected $dbChildren = array('UsersRights');

    public static $parentKey = "groupsId";

    protected static $fieldData = array(
        'id' => array(
            'type' => 'int',
            'autoIncrement' => true,
            'primaryKey' => true,
            'omit' => 'C',
            'filterable' => true,
        ),
        'groupsId' => array(
            'type' => 'int',
            'parent' => 'Groups',
            'foreignColumn' => 'id',
            'required' => true,
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
        'username' => array(
            'type' => 'varchar',
            'unique' => true,
            'required' => true,
        ),
        'email' => array(
            'type' => 'varchar',
            'unique' => true,
            'required' => true,
        ),
        'password' => array(
            'type' => 'password',
            'omit' => 'D',
            'required' => true,
        ),
        'locked' => array(
            'type' => 'int',
            'format' => 'timestamp',
            'outputCallback' => array('\\Yodorada\\Classes\\Utils', 'timestampToOutput'),
            'default' => 0,
            'omit' => 'CRU',
        ),
        'enabled' => array(
            'type' => 'bool',
            'accepted' => array(0, 1),
            'default' => 1,
        ),
        'overrideGroupRights' => array(
            'type' => 'bool',
            'accepted' => array(0, 1),
            'default' => 0,
            'omit' => 'C',
        ),
        'lastLogin' => array(
            'type' => 'int',
            'format' => 'timestamp',
            'outputCallback' => array('\\Yodorada\\Classes\\Utils', 'timestampToOutput'),
            'omit' => 'CU',
        ),
        'confirmationToken' => array(
            'type' => 'text',
            'systemOnly' => true,
            'omit' => 'CRU',
        ),
        'passwordRequestedAt' => array(
            'type' => 'int',
            'systemOnly' => true,
            'omit' => 'CRU',
        ),
    );
}
