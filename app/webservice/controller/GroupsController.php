<?php

/*
 * Part of PHP-Webservice-REST-API
 *
 * Copyright (c) Maya K. Herrmann | Yodorada
 *
 * @license LGPL-3.0+
 */

namespace Yodorada\Controller;

use Yodorada\Classes\Database;
use Yodorada\Classes\Errors;
use Yodorada\Classes\Filters;
use Yodorada\Classes\Input;
use Yodorada\Classes\Query;
use Yodorada\Classes\Translate;
use Yodorada\Classes\Utils;
use Yodorada\Models\GroupsModel;
use Yodorada\Models\GroupsRightsModel;
use Yodorada\Modules\ServiceUser;

/**
 * class GroupsController
 * @package   Yodorada\Webservice
 * @author    Yodorada | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright Yodorada, 2017
 * @version 0.2.0
 */
class GroupsController extends Controller implements ControllerInterface
{

    protected $selfInfo = 'controller.groups.self_info';

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

        // check if there is another view than list all
        if ($this->resourceId) {
            // show single user
            $group = GroupsModel::byId($this->resourceId);
            if (!count($group)) {
                Errors::exitNotFound(Translate::get('controller.groups.no_resource', $this->resourceId));
            }

            // User can only access own group or minor groups
            if ($prechecks && (ServiceUser::get('groupsId') != $group->id || ServiceUser::getRole() > $group->role)) {
                Errors::exitMethodNotAllowed(Translate::get('controller.groups.no_access_superior'));
            }

            $groupArr = $group->makeArray();
            $groupArr['rightsId'] = null;
            $groupArr['rights'] = null;

            if ($group->role != ServiceUser::ROLE_ADMINS) {
                $groupRights = GroupsRightsModel::where(GroupsRightsModel::$parentKey, $group->id)->getOne();
                if (count($groupRights)) {
                    $r = $groupRights->makeArray();
                    $groupArr['rightsId'] = $r['id'];
                    $groupArr['rights'] = Utils::customAllowedMethodsAdminOutput($r['rights']);
                }
            }
            return $groupArr;

        }

        $db = Database::getInstance();
        $db->join("groups_rights r", "r.groupsId=g.id", "LEFT");
        if ($prechecks) {
            // list groups
            // User can only access minor groups
            $db->where("g.role", ServiceUser::getRole(), ">");
        }

        // sorting
        $sort = Query::get('sort');
        $order = Query::get('order');

        if ($sort !== null) {
            $db->orderBy("g." . $sort, ($order !== null ? $order : 'ASC'));
        } else {
            $db->orderBy('g.groupname', 'ASC');
        }

        // limit
        $start = Query::get('start');
        $end = Query::get('end');
        if ($start == null && $end == null) {
            $limit = null;
        } else {
            $limit = array($start, ($end - $start));
        }

        $groups = $db->get("groups g", $limit, "g.*, r.id AS rid");

        if (count($groups)) {
            $returnArr = array();
            foreach ($groups as $g) {
                $g = $this->getRightsOutput($g);
                $groupArr = (new GroupsModel($g))->makeArray();
                $returnArr[] = $groupArr;
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
        // admin present?
        // else perform pre checks
        $prechecks = ((int) ServiceUser::getRole() != ServiceUser::ROLE_ADMINS);

        if (Input::get('groupname') &&
            Input::get('role')) {

            // if current service user tries to create a superior group or group with same role level
            // POST not allowed
            if ($prechecks && (int) ServiceUser::getRole() >= (int) Input::get('role')) {
                Errors::exitMethodNotAllowed();
            }

            // check if name exists
            $duplicate = GroupsModel::where("groupname", Input::get('groupname'))->getOne();
            if (count($duplicate)) {
                // fail, group already exists
                Errors::exitAlreadyExists(Translate::get('controller.groups.groupname_exists'));
            }

            // add new group

            $newData = GroupsModel::processInputData();
            if (Errors::hasError()) {
                Errors::exitWithErrors();
            }
            $newData['created'] = time();
            $newData['changed'] = time();
            $newModel = new GroupsModel($newData);

            $id = $newModel->save();
            $newData['id'] = $id;

            // add group rights dataset
            if ($newModel->role != ServiceUser::ROLE_ADMINS) {
                $newRights = new GroupsRightsModel();
                $newRights->groupsId = $id;
                $newRights->created = time();
                $newRights->changed = time();
                $newRights->rights = Utils::customAllowedMethodsInput(array());
                $rid = $newRights->save();
            }

            return GroupsModel::outputLocationResource($newData);

        } else {
            // fail, not all required data submitted
            Errors::exitBadRequest(Translate::get('controller.groups.required_data'));
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

            // do not edit own group
            if ($prechecks && $this->resourceId == ServiceUser::get('groupsId')) {
                Errors::exitForbidden(Translate::get('controller.groups.no_self_edit'));
            }

            // no group found
            $group = GroupsModel::byId($this->resourceId);
            if (!count($group)) {
                Errors::exitNotFound(Translate::get('controller.groups.no_resource', $this->resourceId));
            }

            // User can only access minor groups
            if ($prechecks && ServiceUser::getRole() > $group->role) {
                Errors::exitMethodNotAllowed(Translate::get('controller.groups.access_limited'));
            }

            // complete update
            $status = $group->updateData();
            if (!$status) {
                $group->outputErrors();
            }

            $groupArr = $group->makeArray();
            $groupArr['rightsId'] = null;
            $groupArr['rights'] = null;

            if (Input::get('rights') !== null && Input::get('rightsId') !== null && $group->role != ServiceUser::ROLE_ADMINS) {
                $rights = Input::get('rights');
                $groupRights = GroupsRightsModel::byId(Input::get('rightsId'));
                if (count($groupRights) && $groupRights->groupsId == $group->id) {
                    $groupRights->rights = Utils::customAllowedMethodsAdminInput($rights);
                }
                $groupRights->save();

                $r = $groupRights->makeArray();
                $groupArr['rightsId'] = $r['id'];
                $groupArr['rights'] = Utils::customAllowedMethodsAdminOutput($r['rights']);

            }
            // return new object
            return $groupArr;

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
        // admin present?
        // else perform pre checks
        $prechecks = ((int) ServiceUser::getRole() != ServiceUser::ROLE_ADMINS);
        // check if resource id is present
        if ($this->resourceId) {
            $group = GroupsModel::byId($this->resourceId);
            if (!count($group)) {
                Errors::exitNotFound(Translate::get('controller.groups.no_resource', $this->resourceId));
            }

            // can not delete own group
            if (ServiceUser::get('groupsId') == $group->id) {
                Errors::exitMethodNotAllowed(Translate::get('controller.groups.no_self_delete'));
            }

            // User can only delete minor groups
            if ($prechecks && ServiceUser::getRole() >= $group->role) {
                Errors::exitMethodNotAllowed(Translate::get('controller.groups.no_delete'));
            }

            $db = Database::getInstance();
            $prefix = $GLOBALS['CONFIG']['DB']['PREFIX'];

            // delete all containing users
            // and user rights
            $usersQ = "DELETE " . $prefix . "users, " . $prefix . "users_rights FROM " . $prefix . "users INNER JOIN " . $prefix . "users_rights ON " . $prefix . "users.id=" . $prefix . "users_rights.usersId WHERE " . $prefix . "users.groupsId=?";
            $affectedUsers = $db->rawQuery($usersQ, array($this->resourceId));

            // delete group
            // and group rights
            $groupsQ = "DELETE " . $prefix . "groups, " . $prefix . "groups_rights FROM " . $prefix . "groups INNER JOIN " . $prefix . "groups_rights ON " . $prefix . "groups.id=" . $prefix . "groups_rights.groupsId WHERE " . $prefix . "groups.id=?";
            $affected = $db->rawQuery($groupsQ, array($this->resourceId));

            if ($db->getLastErrno() != 0) {
                Errors::add($db->getLastError());
                Errors::exitWithErrors();
            }
            return $group->makeArray();

        } else {
            Errors::exitBadRequest(Translate::get('controller.misc.needs_id'));
        }
    }

    /**
     * method GET and filter
     *
     */
    public function filter()
    {
        // admin present?
        // else perform pre checks
        $prechecks = ((int) ServiceUser::getRole() != ServiceUser::ROLE_ADMINS);

        $db = Database::getInstance();
        $db->join("groups_rights r", "r.groupsId=g.id", "LEFT");
        if ($prechecks) {
            // list groups
            // User can only access own group or minor groups
            $db->where("g.role", ServiceUser::getRole(), ">")->orWhere("g.id", ServiceUser::get('groupsId'));
        }

        // filter only by groupname or role
        $fData = GroupsModel::getDataFields();
        $filters = Filters::getAllData();
        foreach ($filters as $key => $value) {
            if (array_key_exists($key, $fData)) {
                if (!is_array($value)) {
                    $value = array($value);
                }
                $db->where("g." . $key, $value, 'IN');
            }
        }
        $groups = $db->get("groups g", null, "g.*, r.id AS rid");

        if (count($groups)) {
            $returnArr = array();
            foreach ($groups as $g) {
                $g = $this->getRightsOutput($g);
                $groupArr = (new GroupsModel($g))->makeArray();
                $returnArr[] = $groupArr;
            }
            return $returnArr;

        }
    }

    /**
     * method GET collection total count
     *
     */
    public function total()
    {
        // admin present?
        // else perform pre checks
        $prechecks = ((int) ServiceUser::getRole() != ServiceUser::ROLE_ADMINS);

        $db = Database::getInstance();
        if ($prechecks) {
            // list groups
            // User can only access own group or minor groups
            $db->where("role", ServiceUser::getRole(), ">")->orWhere("id", ServiceUser::get('groupsId'));
        }

        $fData = GroupsModel::getDataFields();
        $filters = Filters::getAllData();
        foreach ($filters as $key => $value) {
            if (array_key_exists($key, $fData)) {
                if (!is_array($value)) {
                    $value = array($value);
                }
                $db->where($key, $value, 'IN');
            }
        }

        return $db->getValue(GroupsModel::tableName(), "count(*)");
    }

    /**
     * get fields
     *
     */
    public function fields()
    {
        return GroupsModel::getFieldsInfo();
    }

    /**
     * add rights
     *
     * @return array
     */
    public function getRightsOutput($arr)
    {
        $arr['rightsId'] = null;
        $arr['rights'] = null;

        if ($arr['rid'] != null) {
            if ($arr['role'] != ServiceUser::ROLE_ADMINS) {
                $groupRights = GroupsRightsModel::byId($arr['rid']);
                if (count($groupRights)) {
                    $r = $groupRights->makeArray();
                    $arr['rightsId'] = $r['id'];
                    $arr['rights'] = Utils::customAllowedMethodsAdminOutput($r['rights']);
                }
            }
        }
        unset($arr['rid']);
        return $arr;
    }
}
