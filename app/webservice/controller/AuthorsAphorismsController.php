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
use Yodorada\Models\AuthorsAphorismsModel;
use Yodorada\Models\AuthorsModel;

/**
 * class AuthorsAphorismsController
 * @package   Yodorada\Webservice
 * @author    Yodorada | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright Yodorada, 2017
 * @version 0.0.2
 *
 * CustomController classes must provide functions: get, post, put, delete, fields
 */
class AuthorsAphorismsController extends Controller implements ControllerInterface
{

    protected $selfInfo = 'controller.authorsaphorisms.self_info';

    public static $version = '0.0.2';

    /**
     * method GET
     *
     */
    public function get()
    {

        // check if parent id must be set
        if (!$this->needsAndHasParent()) {
            Errors::exitBadRequest(Translate::get('controller.authorsaphorisms.needs_parent'));
        }

        if ($this->resourceId) {
            // show single aphorism entry
            $entries = AuthorsAphorismsModel::byId($this->resourceId);
            if (!count($entries)) {
                Errors::exitNotFound(Translate::get('controller.authorsaphorisms.no_resource', $this->resourceId));
            }
            return $entries;
        }

        // get collection
        if ($this->parentId) {
            $entries = AuthorsAphorismsModel::sorting("authorsId", "desc")->byParent($this->parentId)->getWithLimit();

        } else {
            $entries = AuthorsAphorismsModel::sorting("authorsId", "desc")->getWithLimit();
        }
        $returnArr = array();

        if (count($entries)) {

            foreach ($entries as $n) {
                $returnArr[] = $n->makeArray();
            }
        }

        return $returnArr;

    }

    /**
     * method POST
     *
     */
    public function post()
    {
        // check if parent id is available
        if (!$this->parentId) {
            Errors::exitBadRequest(Translate::get('controller.authorsaphorisms.needs_parent'));
        }

        $author = AuthorsModel::byId($this->parentId);
        if (!count($author)) {
            // fail, author does not exist
            Errors::exitBadRequest(Translate::get('controller.authorsaphorisms.no_parent'));
        }

        $newData = AuthorsAphorismsModel::newResource();
        $newData['authorsId'] = $author->id;

        if (Errors::hasError()) {
            Errors::exitWithErrors();
        }
        $newModel = new AuthorsAphorismsModel($newData);
        $newData['id'] = $newModel->save();

        return AuthorsAphorismsModel::outputLocationResource($newData);
    }

    /**
     * method PUT
     *
     */
    public function put()
    {
        if ($this->resourceId && $this->parentId) {
            $author = AuthorsModel::byId($this->parentId);
            if (!count($author)) {
                // fail, author does not exist
                Errors::exitBadRequest(Translate::get('controller.authorsaphorisms.no_parent'));
            }
            $entries = AuthorsAphorismsModel::byId($this->resourceId);
            if (!count($entries)) {
                Errors::exitNotFound(Translate::get('controller.authorsaphorisms.no_resource', $this->resourceId));
            }

            $newData = $entries->prepareUpdate();
            if (!$entries->wasModified($newData)) {
                // data was not modified, no need to save
                return $entries->makeArray();
            }
            $status = $entries->save($newData);
            if (!$status) {
                $entries->outputErrors();
            }
            // return new object
            return $entries->makeArray();
        } else {
            Errors::exitBadRequest(Translate::get('controller.misc.needs_id'));
        }
    }

    /**
     * method DELETE
     *
     */
    public function delete()
    {

        if ($this->resourceId) {
            $entries = AuthorsAphorismsModel::byId($this->resourceId);
            if (!count($entries)) {
                Errors::exitNotFound(Translate::get('controller.authorsaphorisms.no_resource', $this->resourceId));
            }

            $status = $entries->delete();

            if (!$status) {
                $this->outputErrors();
            }
            return $entries->makeArray();
        } else {
            Errors::exitBadRequest(Translate::get('controller.misc.needs_id'));
        }
        return true;
    }

    /**
     * method GET and filter
     *
     */
    public function filter()
    {
        // show collection
        return AuthorsAphorismsModel::filter();
    }

    /**
     * method GET collection total count
     *
     */
    public function total()
    {
        return AuthorsAphorismsModel::total();
    }

    /***
     * get fields
     *
     */
    public function fields()
    {
        return AuthorsAphorismsModel::getFieldsInfo();
    }
}
