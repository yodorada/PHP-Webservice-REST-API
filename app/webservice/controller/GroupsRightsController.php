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
use Yodorada\Models\GroupsModel;
use Yodorada\Models\GroupsRightsModel;
use Yodorada\Modules\ServiceUser;

/**
 * class GroupRightsController
 * @package   Yodorada\Webservice
 * @author    Yodorada | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright Yodorada, 2017
 * @version 0.2.0
 */
class GroupsRightsController extends Controller implements ControllerInterface
{

    protected $selfInfo = 'controllers.groupsrights.self_info';

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
            $rights = GroupsRightsModel::byId($this->resourceId);
            if (!count($rights)) {
                Errors::exitNotFound(Translate::get('controller.groupsrights.no_resource', $this->resourceId));
            }

            // if ($rights->groupsId != $this->parentId) {
            //     Errors::exitNotFound('The group ID and rights ID do not match.');
            // }

            $group = GroupsModel::byId($rights->groupsId);
            if (!count($group)) {
                Errors::exitNotFound(Translate::get('controller.groupsrights.no_parent', $rights->groupsId));
            }

            // User can only access minor groups
            if ($prechecks && ServiceUser::getRole() > $group->role) {
                Errors::exitMethodNotAllowed(Translate::get('controller.groupsrights.no_access_superior'));
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
        if ($this->resourceId) {

            $rights = GroupsRightsModel::byId($this->resourceId);
            if (!count($rights)) {
                Errors::exitNotFound(Translate::get('controller.groupsrights.no_resource', $this->resourceId));
            }

            if ($rights->groupsId != $this->parentId) {
                Errors::exitNotFound(Translate::get('controller.groupsrights.no_parent_match'));
            }

            // no group found
            // $group = GroupsModel::byId($this->parentId);
            // if (!count($group)) {
            //     Errors::exitNotFound('The group with ID ' . $this->parentId . ' could not be found.');
            // }

            // do not edit own group rights
            if ($prechecks && $rights->groupId == ServiceUser::get('groupsId')) {
                Errors::exitForbidden(Translate::get('controller.groupsrights.no_self_edit'));
            }

            // User can only access minor groups
            if ($prechecks && ServiceUser::getRole() > $group->role) {
                Errors::exitMethodNotAllowed(Translate::get('controller.groupsrights.no_access_superior'));
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
        return GroupsRightsModel::getFieldsInfo();
    }
}
