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
use Yodorada\Models\AuthorsModel;

/**
 * class AuthorsController
 * @package   Yodorada\Webservice
 * @author    EINS[23].TV | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright EINS[23].TV, 2017
 * @version 0.0.2
 *
 * CustomController classes must provide functions: get, post, put, delete, fields
 */
class AuthorsController extends Controller implements ControllerInterface
{

    protected $selfInfo = 'This ressource manages the Authors entries.';

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
                Errors::exitNotFound('The author entry with ID ' . $this->resourceId . ' could not be found.');
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
                Errors::exitNotFound('The author entry with ID ' . $this->resourceId . ' could not be found.');
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
            Errors::exitBadRequest('An identifier must be provided.');
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
                Errors::exitNotFound('The author entry with ID ' . $this->resourceId . ' could not be found.');
            }

            $status = $entries->deleteWithChildren();

            if (!$status) {
                $this->outputErrors();
            }
            return $entries->makeArray();
        } else {
            Errors::exitBadRequest('An identifier must be provided.');
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
