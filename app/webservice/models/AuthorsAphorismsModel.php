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
 * model Authors aphorisms for database table PREFIX.'authors_aphorisms'
 * @package   Yodorada\Webservice
 * @author    Yodorada | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright Yodorada, 2017
 * @version 0.0.1
 */
class AuthorsAphorismsModel extends Model
{
    // required by MysqliDb!
    protected $dbTable = "authors_aphorisms";

    // required when parent table exists
    // defining the column name in the child table
    public static $parentKey = "authorsId";

    // required for webservice functions
    protected static $fieldData = array(
        'id' => array(
            'type' => 'int',
            'autoIncrement' => true,
            'primaryKey' => true,
            'omit' => 'C',
            'filterable' => true,
        ),
        'authorsId' => array(
            'type' => 'int',
            'parent' => 'Authors',
            'foreignColumn' => 'id',
            'omit' => 'CU',
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
        'aphorism' => array(
            'type' => 'varchar',
            'unique' => true,
            'required' => true,
        ),
    );
}
