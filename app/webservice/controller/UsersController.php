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
use Yodorada\Classes\Utils;
use Yodorada\Models\GroupsModel;
use Yodorada\Models\GroupsRightsModel;
use Yodorada\Models\UsersModel;
use Yodorada\Models\UsersRightsModel;
use Yodorada\Modules\ServiceUser;

/**
 * class UsersController
 * @package   Yodorada\Webservice
 * @author    Yodorada | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright Yodorada, 2017
 * @version 0.2.0
 */
class UsersController extends Controller implements ControllerInterface
{

    protected $selfInfo = 'controllers.users.self_info';

    public static $version = '0.2.0';

    /**
     * method GET
     *
     */
    public function get()
    {
        $prechecks = ((int) ServiceUser::getRole() != ServiceUser::ROLE_ADMINS);

        // check if resource id is set
        if ($this->resourceId) {

            // show single user
            $user = UsersModel::byId($this->resourceId);
            if (!count($user)) {
                Errors::exitNotFound(Translate::get('controller.users.no_resource', $this->resourceId));
            }
            $group = GroupsModel::byId($user->groupsId);

            if ($prechecks) {
                // can not access user from superior group
                if (ServiceUser::getRole() >= $group->role) {
                    Errors::exitMethodNotAllowed(Translate::get('controller.users.no_access_superior'));
                }
            }
            $userArr = $user->makeArray();
            $userArr['rightsId'] = null;
            $userArr['rights'] = null;

            if ($group->role != ServiceUser::ROLE_ADMINS) {
                $groupRights = GroupsRightsModel::where(GroupsRightsModel::$parentKey, $group->id)->getOne();
                $userRights = UsersRightsModel::where(UsersRightsModel::$parentKey, $user->id)->getOne();
                if (count($userRights)) {
                    $r = $userRights->makeArray();
                    $userArr['rightsId'] = $r['id'];
                    if ($user->overrideGroupRights) {
                        $userArr['rights'] = Utils::customAllowedMethodsAdminOutput($r['rights']);
                    } else {
                        if (count($groupRights)) {
                            $r = $groupRights->makeArray();
                            $userArr['rights'] = Utils::customAllowedMethodsAdminOutput($r['rights']);
                        }
                    }
                }
                if ($userArr['rights'] === null) {
                    // create new rights dataset
                    $rights = array();
                    if (count($groupRights)) {
                        $rights = $groupRights->makeArray();
                    }
                    $newRights = new UsersRightsModel();
                    $newRights->usersId = $id;
                    $newRights->created = time();
                    $newRights->changed = time();
                    $newRights->rights = Utils::customAllowedMethodsInput($rights);
                    $rid = $newRights->save();
                    $userArr['rightsId'] = $rid;
                    $userArr['rights'] = Utils::customAllowedMethodsAdminOutput($rights);
                }
            }

            return $userArr;

        }

        $db = Database::getInstance();

        if ($this->parentId) {
            $group = GroupsModel::byId($this->parentId);
            if ($prechecks) {
                // can not access users from superior group
                if (ServiceUser::getRole() >= $group->role) {
                    Errors::exitMethodNotAllowed(Translate::get('controller.users.no_access_superior'));
                }
            }
            $db->where("u." . UsersModel::$parentKey, $this->parentId);
            $db->join("users_rights r", "r.usersId=u.id", "LEFT");
            $db->join("groups g", "u.groupsId=g.id", "LEFT");

        } elseif ($prechecks) {
            $db->where("g.role", ServiceUser::getRole(), ">");
            $db->join("groups g", "u.groupsId=g.id", "LEFT");
            $db->join("users_rights r", "r.usersId=u.id", "LEFT");

        } else {
            $db->join("users_rights r", "r.usersId=u.id", "LEFT");
            $db->join("groups g", "u.groupsId=g.id", "LEFT");

        }

        $db->join("groups_rights gr", "gr.groupsId=g.id", "LEFT");

        // do not show own record
        $db->where("u.id", ServiceUser::get('id'), '!=');

        // sorting
        $sort = Query::get('sort');
        $order = Query::get('order');

        if ($sort !== null) {
            $db->orderBy("u." . $sort, ($order !== null ? $order : 'ASC'));
        } else {
            $db->orderBy('u.username', 'ASC');
        }

        // limit
        $start = Query::get('start');
        $end = Query::get('end');
        if ($start == null && $end == null) {
            $limit = null;
        } else {
            $limit = array($start, ($end - $start));
        }

        $fData = UsersModel::getDataFields();
        $filters = Filters::getAllData();
        foreach ($filters as $key => $value) {
            if (array_key_exists($key, $fData)) {
                if ($fData['type'] == 'bool') {
                    $db->where("u." . $key, ($value == 'false' ? 0 : 1));

                } else {
                    if (!is_array($value)) {
                        $value = array($value);
                    }
                    $db->where("u." . $key, $value, 'IN');

                }
            }
        }

        $queryFields = Query::get('fields');
        if (null !== $queryFields) {
            foreach ($queryFields as $key => $value) {
                if ($key == UsersModel::$parentKey) {
                    continue;
                }
                if (array_key_exists($key, $fData)) {
                    if ($fData[$key]['type'] == 'bool') {
                        $db->where("u." . $key, ($value == 'false' ? 0 : 1));
                    } else {
                        $db->where("u." . $key, $value);
                    }
                }
            }
        }

        $users = $db->get("users u", $limit, "u.*, r.id AS rid, g.role as role, gr.rights as groupRights");

        if (count($users)) {
            $returnArr = array();
            foreach ($users as $u) {
                $u = $this->getRightsOutput($u);
                $userArr = (new UsersModel($u))->makeArray();
                $returnArr[] = $userArr;
            }
            return $returnArr;

        }
        return array();
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

            // do not edit own account here
            if ($this->resourceId == ServiceUser::get('id')) {
                Errors::exitForbidden(Translate::get('controller.users.edit_via_account'));
            }
            $user = UsersModel::byId($this->resourceId);

            // no user found
            if (!count($user)) {
                Errors::exitNotFound(Translate::get('controller.users.no_resource', $this->resourceId));
            }
            $group = GroupsModel::byId($user->groupsId);

            if ($prechecks) {
                // can not access user from superior group
                if (ServiceUser::getRole() >= $group->role) {
                    Errors::exitMethodNotAllowed(Translate::get('controller.users.no_access_superior'));
                }
            }

            // complete update
            $status = $user->updateData();
            if (!$status) {
                $user->outputErrors();
            }

            $userArr = $user->makeArray();
            $userArr['rightsId'] = null;
            $userArr['rights'] = null;

            if (Input::get('rights') !== null && Input::get('rightsId') !== null && $group->role != ServiceUser::ROLE_ADMINS && $user->overrideGroupRights) {
                $rights = Input::get('rights');
                $userRights = UsersRightsModel::byId(Input::get('rightsId'));
                if (count($userRights) && $userRights->userId == $user->id) {
                    $userRights->rights = Utils::customAllowedMethodsAdminInput($rights);
                }
                $userRights->save();

                $r = $userRights->makeArray();
                $userArr['rightsId'] = $r['id'];
                $userArr['rights'] = Utils::customAllowedMethodsAdminOutput($r['rights']);

            }
            // return new object
            return $userArr;

        }
    }

    /**
     * method POST
     *
     * required data: password, username, email, groupsId
     */
    public function post()
    {

        // check if group id is available
        if (!$this->parentId) {
            Errors::exitBadRequest(Translate::get('controller.users.needs_parent'));
        }

        $group = GroupsModel::byId($this->parentId);
        if (!count($group)) {
            // fail, group does not exist
            Errors::exitBadRequest(Translate::get('controller.users.parent_must_exist'));
        }

        if (Input::get('password') &&
            Input::get('username') &&
            Input::get('email')) {

            // check if email or username exists
            $duplicate = UsersModel::where("email", Input::get('email'))->orWhere("username", Input::get('username'))->getOne();
            if (count($duplicate)) {
                // fail, username or email already exists
                Errors::exitAlreadyExists(Translate::get('controller.users.username_or_email_exists'));
            }

            // add new user
            $newData = UsersModel::processInputData();
            if (Errors::hasError()) {
                Errors::exitWithErrors();
            }
            $newData['groupsId'] = $group->id;
            $newData['created'] = time();
            $newData['changed'] = time();
            $newData['lastLogin'] = 0;
            $newData['locked'] = 0;
            $newData['confirmationToken'] = '';
            $newData['overrideGroupRights'] = 0;
            $newData['passwordRequestedAt'] = 0;

            $newModel = new UsersModel($newData);

            $id = $newModel->save();
            $newData['id'] = $id;
            $newData['password'] = "*****";

            // add group rights dataset
            if ($group->role != ServiceUser::ROLE_ADMINS) {
                $rights = array();
                $groupRights = GroupsRightsModel::where(GroupsRightsModel::$parentKey, $group->id)->getOne();
                if (count($groupRights)) {
                    $r = $groupRights->makeArray();
                    $rights = Utils::customAllowedMethodsAdminOutput($r['rights']);
                }

                $newRights = new UsersRightsModel();
                $newRights->usersId = $id;
                $newRights->created = time();
                $newRights->changed = time();
                $newRights->rights = Utils::customAllowedMethodsInput($rights);
                $rid = $newRights->save();
            }

            return UsersModel::outputLocationResource($newData);

        } else {
            // fail, not all required data submitted
            Errors::exitBadRequest(Translate::get('controller.users.required_data'));
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
            $user = UsersModel::byId($this->resourceId);
            if (!count($user)) {
                Errors::exitNotFound(Translate::get('controller.users.no_resource', $this->resourceId));
            }

            // can not delete own group
            if (ServiceUser::get('id') == $user->id) {
                Errors::exitMethodNotAllowed(Translate::get('controller.users.no_self_delete'));
            }

            // User can only delete users from minor groups
            $group = GroupsModel::byId($user->groupsId);
            if ($prechecks && ServiceUser::getRole() >= $group->role) {
                Errors::exitMethodNotAllowed(Translate::get('controller.users.no_delete_in_group'));
            }

            $db = Database::getInstance();
            $prefix = $GLOBALS['CONFIG']['DB']['PREFIX'];

            // delete user
            // and user rights
            if ($group->role == ServiceUser::ROLE_ADMINS) {
                // has no group rights
                $usersQ = "DELETE FROM " . $prefix . "users WHERE " . $prefix . "users.id=?";
            } else {
                $usersQ = "DELETE " . $prefix . "users, " . $prefix . "users_rights FROM " . $prefix . "users INNER JOIN " . $prefix . "users_rights ON " . $prefix . "users.id=" . $prefix . "users_rights.usersId WHERE " . $prefix . "users.id=?";
            }

            $affectedUsers = $db->rawQuery($usersQ, array($this->resourceId));

            if ($db->getLastErrno() != 0) {
                Errors::add($db->getLastError());
                Errors::exitWithErrors();
            }

            return $user->makeArray();

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
        // show collection
        return UsersModel::filter();
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
            $db->where("g.role", ServiceUser::getRole(), ">");
            $db->join("groups g", "u.groupsId=g.id", "LEFT");
        }

        $fData = UsersModel::getDataFields();
        $filters = Filters::getAllData();
        foreach ($filters as $key => $value) {
            if (array_key_exists($key, $fData)) {
                if (!is_array($value)) {
                    $value = array($value);
                }
                $db->where("u." . $key, $value, 'IN');
            }
        }
        $queryFields = Query::get('fields');
        if (null !== $queryFields) {
            foreach ($queryFields as $key => $value) {
                if ($key == UsersModel::$parentKey) {
                    continue;
                }
                if (array_key_exists($key, $fData)) {
                    if ($fData[$key]['type'] == 'bool') {
                        $db->where("u." . $key, ($value == 'false' ? 0 : 1));
                    } else {
                        $db->where("u." . $key, $value);
                    }
                }
            }
        }
        if ($this->parentId) {
            $db->where("u." . UsersModel::$parentKey, $this->parentId);
        }
        // do not show own record
        $db->where("u.id", ServiceUser::get('id'), '!=');

        return $db->getValue(UsersModel::tableName() . " u", "count(*)");

    }

    /***
     * get fields
     *
     */
    public function fields()
    {
        return UsersModel::getFieldsInfo();
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
                if ($arr['overrideGroupRights']) {
                    $userRights = UsersRightsModel::byId($arr['rid']);
                    if (count($userRights)) {
                        $r = $userRights->makeArray();
                        $arr['rightsId'] = $r['id'];
                        $arr['rights'] = Utils::customAllowedMethodsAdminOutput($r['rights']);
                    }
                } else {
                    $arr['rightsId'] = $arr['rid'];
                    $arr['rights'] = Utils::customAllowedMethodsAdminOutput($arr['groupRights']);
                }

            }
        }
        unset($arr['rid']);
        unset($arr['groupRights']);
        return $arr;
    }

}
