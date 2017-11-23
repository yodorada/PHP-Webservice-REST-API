<?php

/*
 * Part of PHP-Webservice-REST-API
 *
 * Copyright (c) Maya K. Herrmann | EINS[23].TV
 *
 * @license LGPL-3.0+
 */

namespace Yodorada\Controller;

use Yodorada\Classes\Translate;
use Yodorada\Models\UsersModel;
use Yodorada\Modules\ServiceUser;

/**
 * class AccountController
 * @package   Yodorada\Webservice
 * @author    EINS[23].TV | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright EINS[23].TV, 2017
 * @version 0.1.0
 */
class AccountController extends Controller implements ControllerInterface
{

    protected $selfInfo = 'controller.account.self_info';

    public static $version = '0.1.0';

    /**
     * method GET
     *
     */
    public function get()
    {

        if ($this->resourceId && $this->resourceId == ServiceUser::get('id')) {
            // show service user
            $user = UsersModel::byId(ServiceUser::get('id'));
            if (!count($user)) {
                Errors::exitNotFound(Translate::get('controller.users.no_resource', ServiceUser::get('id'));
            }
            $userArr = $user->makeArray();
            unset($userArr['location']);
            if ((int) ServiceUser::getRole() == ServiceUser::ROLE_USERS) {
                unset($userArr['overrideGroupRights']);
                unset($userArr['enabled']);
                unset($userArr['lastLogin']);
                unset($userArr['locked']);
                unset($userArr['created']);
                unset($userArr['changed']);
                unset($userArr['groupsId']);
            }

            return $userArr;
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
    {
        $user = UsersModel::byId(ServiceUser::get('id'));
        if (!count($user)) {
            Errors::exitNotFound(Translate::get('controller.users.no_resource', ServiceUser::get('id'));
        }
        $userArr = $user->prepareUpdate();
        unset($userArr['overrideGroupRights']);
        unset($userArr['enabled']);
        unset($userArr['lastLogin']);
        unset($userArr['locked']);
        if ((int) ServiceUser::getRole() == ServiceUser::ROLE_USERS) {
            unset($userArr['groupsId']);
        }
        if ($user->wasModified($userArr)) {
            $status = $user->save($userArr);
            if (!$status) {
                $user->outputErrors();
            }
        }

        // account was updated
        if (!ServiceUser::accountUpdated()) {
            Errors::exitGeneralError();
        }

        $n = array(
            'username' => ServiceUser::get('username'),
            'email' => ServiceUser::get('email'),
            'token' => ServiceUser::get('accessToken')['token'],
            'tokenExpires' => ServiceUser::get('accessToken')['expiresAt'],
            'role' => ServiceUser::get('roleId'),
            'rights' => ServiceUser::get('rights'),
            'id' => ServiceUser::get('id'),
        );
        if ($n['rights'] === null) {
            $n['rights'] = array();
        }

        return $n;
    }

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
        $fields = UsersModel::getFieldsInfo();
        if ((int) ServiceUser::getRole() == ServiceUser::ROLE_USERS) {
            unset($fields['overrideGroupRights']);
            unset($fields['enabled']);
            unset($fields['lastLogin']);
            unset($fields['locked']);
            unset($fields['groupsId']);
        }
        return $fields;
    }
}
