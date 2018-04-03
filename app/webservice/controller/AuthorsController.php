<?php

/*
 * Part of PHP-Webservice-REST-API
 *
 * Copyright (c) Maya K. Herrmann | Yodorada
 *
 * @license LGPL-3.0+
 */

namespace Yodorada\Controller;

use Yodorada\Classes\Errors;
use Yodorada\Classes\Translate;
use Yodorada\Models\AuthorsModel;

/**
 * class AuthorsController
 * @package   Yodorada\Webservice
 * @author    Yodorada | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright Yodorada, 2017
 * @version 0.0.2
 *
 * CustomController classes must provide functions: get, post, put, delete, fields
 */
class AuthorsController extends Controller implements ControllerInterface
{

    protected $selfInfo = 'controller.authors.self_info';

    public static $version = '0.0.2';

    /**
     * method GET
     *
     */
    public function get()
    {
        if ($this->resourceId) {
            // show single author entry
            $entries = AuthorsModel::byId($this->resourceId);
            if (!count($entries)) {
                Errors::exitNotFound(Translate::get('controller.authors.no_resource', $this->resourceId));
            }
            return $entries->makeArray();
        }

        // show collection
        $entries = AuthorsModel::sorting("authorname", "asc")->getWithLimit();
        if (count($entries)) {
            $returnArr = array();
            foreach ($entries as $n) {
                $returnArr[] = $n->makeArray();
            }
            return $returnArr;
        }

        // return empty data
        return array();
    }

    /**
     * method POST
     *
     */
    public function post()
    {
        $newData = AuthorsModel::newResource();

        if (Errors::hasError()) {
            Errors::exitWithErrors();
        }
        $newModel = new AuthorsModel($newData);
        $newData['id'] = $newModel->save();

        return AuthorsModel::outputLocationResource($newData);
    }

    /**
     * method PUT
     *
     */
    public function put()
    {
        if ($this->resourceId) {
            $entries = AuthorsModel::byId($this->resourceId);
            if (!count($entries)) {
                Errors::exitNotFound(Translate::get('controller.authors.no_resource', $this->resourceId));
            }

            $newData = $entries->prepareUpdate();
            if (!$entries->wasModified($newData)) {
                // data was not modified, no need to save
                // return same object
                return $entries->makeArray();
            }
            $status = $entries->save($newData);
            if (!$status) {
                $entries->outputErrors();
            }
            // return new object
            return $entries->makeArray();
        } else {
            Errors::exitBadRequest(Translate::get('controller.authors.needs_id'));
        }
    }

    /**
     * method DELETE
     *
     */
    public function delete()
    {
        if ($this->resourceId) {
            $entries = AuthorsModel::byId($this->resourceId);
            if (!count($entries)) {
                Errors::exitNotFound(Translate::get('controller.authors.no_resource', $this->resourceId));
            }

            $status = $entries->deleteWithChildren();

            if (!$status) {
                $this->outputErrors();
            }
            return $entries->makeArray();
        } else {
            Errors::exitBadRequest(Translate::get('controller.authors.needs_id'));
        }

    }

    /**
     * method GET and filter
     *
     */
    public function filter()
    {
        // show collection
        return AuthorsModel::filter();

    }

    /**
     * method GET collection total count
     *
     */
    public function total()
    {
        return AuthorsModel::total();
    }

    /***
     * get fields
     *
     */
    public function fields()
    {
        return AuthorsModel::getFieldsInfo();
    }
}
