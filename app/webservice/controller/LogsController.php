<?php

/*
 * Part of PHP-Webservice-REST-API
 *
 * Copyright (c) Maya K. Herrmann | EINS[23].TV
 *
 * @license LGPL-3.0+
 */

namespace Yodorada\Controller;

use Yodorada\Classes\Errors;
use Yodorada\Classes\Translate;
use Yodorada\Models\LogsModel;
use Yodorada\Modules\ServiceUser;

/**
 * class LogsController
 * @package   Yodorada\Webservice
 * @author    EINS[23].TV | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright EINS[23].TV, 2017
 * @version 0.2.0
 */
class LogsController extends Controller implements ControllerInterface
{

    protected $selfInfo = 'controller.logs.self_info';

    public static $version = '0.2.0';

    /**
     * method GET
     *
     */
    public function get()
    {

        if ((int) ServiceUser::getRole() != ServiceUser::ROLE_ADMINS) {
            // only admins can access logs
            Errors::exitMethodNotAllowed(Translate::get('controller.logs.no_access'));
        }

        if ($this->resourceId) {
            // show single author entry
            $entries = LogsModel::byId($this->resourceId);
            if (!count($entries)) {
                Errors::exitNotFound(Translate::get('controller.logs.no_resource', $this->resourceId));
            }
            return $entries->makeArray();
        }

        // show logs
        $logs = LogsModel::sorting("created", "desc")->getWithLimit(0, 10);
        if (count($logs)) {
            $returnArr = array();
            foreach ($logs as $g) {
                $returnArr[] = $g->makeArray();
            }
            return $returnArr;

        }

    }

    /**
     * method POST
     *
     */
    public function post()
    {}

    /**
     * method PUT
     *
     */
    public function put()
    {}

    /**
     * method DELETE
     *
     */
    public function delete()
    {}

    /**
     * method GET and filter
     *
     */
    public function filter()
    {
        return LogsModel::filter();
    }

    /**
     * method GET collection total count
     *
     */
    public function total()
    {
        return LogsModel::total();
    }

    /***
     * get fields
     *
     */
    public function fields()
    {
        return LogsModel::getFieldsInfo();
    }
}
