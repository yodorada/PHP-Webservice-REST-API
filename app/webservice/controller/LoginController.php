<?php

/*
 * Part of PHP-Webservice-REST-API
 *
 * Copyright (c) Maya K. Herrmann | EINS[23].TV
 *
 * @license LGPL-3.0+
 */

namespace Yodorada\Controller;

use Yodorada\Classes\Headers;
use Yodorada\Classes\Setting;
use Yodorada\Classes\Utils;
use Yodorada\Models\UsersModel;
use Yodorada\Modules\ServiceUser;

/**
 * class LoginController
 * @package   Yodorada\Webservice
 * @author    EINS[23].TV | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright EINS[23].TV, 2017
 * @version 0.2.0
 */
class LoginController extends Controller implements ControllerInterface
{

    public static $scope = self::SCOPE_RESOURCE;

    protected $selfInfo = 'controller.login.self_info';

    public static $version = '0.2.1';
    /**
     * method GET
     *
     */
    public function get()
    {
        // return success because authentication has not failed until here
        //
        Headers::cacheControl(0);

        $n = array(
            'username' => ServiceUser::get('username'),
            'token' => ServiceUser::get('accessToken')['token'],
            'tokenExpires' => Utils::timestampToOutput(ServiceUser::get('accessToken')['expiresAt']),
        );

        // add some extra fields to your return array if logged user is not of group "user" but "editors" or "admins"
        if ((int) ServiceUser::getRole() != ServiceUser::ROLE_USERS) {
            $n['rights'] = ServiceUser::get('rights');
            $n['email'] = ServiceUser::get('email');
            $n['id'] = ServiceUser::get('id');
            $n['role'] = ServiceUser::get('roleId');
            if ($n['rights'] === null) {
                $n['rights'] = array();
            }
        }

        // add some extra fields to your return array if logged user of group "admins"
        if ((int) ServiceUser::getRole() == ServiceUser::ROLE_ADMINS) {
            $n['config'] = array(
                'applicationHost' => $GLOBALS['CONFIG']['APPLICATION']['HOST'],
                'allowedUploadTypes' => explode("|", $GLOBALS['CONFIG']['UPLOADS']['ALLOWED']),
                'base64Upload' => $GLOBALS['CONFIG']['UPLOADS']['BASE64'],
            );
        }

        if (
            ServiceUser::getRole() == ServiceUser::ROLE_USERS &&
            Setting::get('realm') == base64_encode($GLOBALS['CONFIG']['API']['FRONTEND_KEY'])
        ) {
            if (Setting::get('route')[0] == 'authorized') {
                // route is "authorized" and was requested from the frontend app
                // to check if current standard user login is still valid
                // return array with username, token, tokenExpires
                return $n;
            } else if (Setting::get('route')[0] == 'login') {
                // here you can add some extra content/fields to the return array
                // that you can store for example in localStorage in your frontend app
                //
                // $n = array_merge(array('foo' => 'bar'), $n);
            }
        }

        // save lastlogin time
        $user = UsersModel::byId(ServiceUser::get('id'));
        $user->lastLogin = time();
        $user->save();

        return $n;

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
     * method PUT
     *
     */
    public function put()
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
        return UsersModel::getFieldsInfo();
    }
}
