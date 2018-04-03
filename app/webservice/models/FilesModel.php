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
 * model files for database table PREFIX.'files'
 * @package   Yodorada\Webservice
 * @author    Yodorada | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright Yodorada, 2017
 * @version 0.0.1
 */
class FilesModel extends Model
{
    // required by MysqliDb!
    protected $dbTable = "files";

    public static $parentTable = "users";
    public static $parentKey = "usersId";

    // required for webservice functions
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
        'usersId' => array(
            'type' => 'int',
            'parent' => 'Users',
            'foreignColumn' => 'id',
            'omit' => 'CU',
            'systemOnly' => true,
            'filterable' => true,
        ),
        'hash' => array(
            'type' => 'varchar',
            'systemOnly' => true,
        ),
        'filename' => array(
            'type' => 'varchar',
            'unique' => true,
            'required' => true,
        ),
        'path' => array(
            'type' => 'varchar',
            'unique' => true,
            'systemOnly' => true,
            'outputCallback' => array('\\Yodorada\\Classes\\Utils', 'getFilePaths'),
        ),
    );
}
