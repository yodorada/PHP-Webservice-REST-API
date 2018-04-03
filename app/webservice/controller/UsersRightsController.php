<?php

/*
 * Part of PHP-Webservice-REST-API
 *
 * Copyright (c) Maya K. Herrmann | Yodorada
 *
 * @license LGPL-3.0+
 */

namespace Yodorada\Controller;

use Yodorada\Classes\Translate;
use Yodorada\Models\UsersModel;
use Yodorada\Models\UsersRightsModel;
use Yodorada\Modules\ServiceUser;

/**
 * class UserRightsController
 * @package   Yodorada\Webservice
 * @author    Yodorada | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright Yodorada, 2017
 * @version 0.2.0
 */
class UsersRightsController extends Controller implements ControllerInterface
{

    protected $selfInfo = 'controllers.usersrights.self_info';

    public static $version = '0.2.0';

    /**
     * method GET
     *
     */
    public function get()
    {
        // admin present?
        // else perform pre checks
        $prechecks = ((int) ServiceUser::getRole() != ServiceUser::ROLE_ADMINS);

        if ($this->resourceId) {
            // show single user
            $rights = UsersRightsModel::byId($this->resourceId);
            if (!count($rights)) {
                Errors::exitNotFound(Translate::get('controller.usersrights.no_resource', $this->resourceId));
            }

            // if ($rights->usersId != $this->parentId) {
            //     Errors::exitNotFound('The user ID and rights ID do not match.');
            // }

            $user = UsersModel::byId($rights->usersId);
            if (!count($user)) {
                Errors::exitNotFound(Translate::get('controller.usersrights.no_parent', $rights->usersId));
            }

            // User can only access minor groups
            if ($prechecks && ServiceUser::getRole() > $group->role) {
                Errors::exitMethodNotAllowed(Translate::get('controller.usersrights.no_access_superior'));
            }

            $rightsArr = $rights->makeArray();

            return $rightsArr;

        }
    }

    /**
     * method PUT
     *
     */
    public function put()
    {
        // admin present?
        // else perform pre checks
        $prechecks = ((int) ServiceUser::getRole() != ServiceUser::ROLE_ADMINS);
        // check if resource id is present
        if ($this->resourceId && $this->parentId) {

            $rights = UsersRightsModel::byId($this->resourceId);
            if (!count($rights)) {
                Errors::exitNotFound(Translate::get('controller.usersrights.no_resource', $this->resourceId));
            }

            if ($rights->usersId != $this->parentId) {
                Errors::exitNotFound(Translate::get('controller.usersrights.no_parent_match'));
            }

            $user = UsersModel::byId($rights->usersId);
            if (!count($user)) {
                Errors::exitNotFound(Translate::get('controller.usersrights.no_parent', $rights->usersId));
            }

            // do not edit own group rights
            if ($prechecks && $this->parentId == ServiceUser::get('groupsId')) {
                Errors::exitForbidden(Translate::get('controller.usersrights.no_self_edit'));
            }

            // User can only access minor groups
            if ($prechecks && ServiceUser::getRole() > $group->role) {
                Errors::exitMethodNotAllowed(Translate::get('controller.usersrights.no_access_superior'));
            }

            // complete update
            $status = $rights->updateData();
            if (!$status) {
                $rights->outputErrors();
            }
            // return new object
            return $rights->makeArray();

        } else {
            Errors::exitBadRequest(Translate::get('controller.misc.needs_id'));
        }
        return null;
    }

    /**
     * method POST
     *
     */
    public function post()
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
    {}

    /**
     * method GET collection total count
     *
     */
    public function total()
    {}

    /***
     * get fields
     *
     */
    public function fields()
    {
        return UsersRightsModel::getFieldsInfo();
    }
}
