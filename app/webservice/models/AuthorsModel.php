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
 * model Authors for database table PREFIX.'authors'
 * @package   Yodorada\Webservice
 * @author    EINS[23].TV | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright EINS[23].TV, 2017
 * @version 0.0.1
 */
class AuthorsModel extends Model
{
    // required by MysqliDb!
    protected $dbTable = "authors";

    // required when child records exist
    // defining the controller and model name of the children
    protected $dbChildren = array('AuthorsAphorisms');

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
        'authorname' => array(
            'type' => 'varchar',
            'unique' => true,
            'required' => true,
            'filterable' => true,
        ),
    );
}
