<?php

/*
 * Part of PHP-Webservice-REST-API
 *
 * Copyright (c) Maya K. Herrmann | Yodorada
 *
 * @license LGPL-3.0+
 */

namespace Yodorada\Controller;

/**
 * ControllerInterface
 * @package   Yodorada\Webservice
 * @author    Yodorada | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright Yodorada, 2017
 * @version 0.0.1
 */
interface ControllerInterface
{

    /**
     * get fields
     *
     */
    public function fields();

    /**
     * method POST
     *
     */
    public function post();

    /**
     * method DELETE
     *
     */
    public function delete();

    /**
     * method PUT
     *
     */
    public function put();

    /**
     * method GET
     *
     */
    public function get();

    /**
     * method GET & filter
     *
     */

    public function filter();

    /**
     * method GET & collection total count
     *
     */
    public function total();
}
