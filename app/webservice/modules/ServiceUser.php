<?php

/*
 * Part of PHP-Webservice-REST-API
 *
 * Copyright (c) Maya K. Herrmann | Yodorada
 *
 * @license LGPL-3.0+
 */

namespace Yodorada\Modules;

use Yodorada\Classes\Utils;
use Yodorada\Models\GroupsModel;
use Yodorada\Models\GroupsRightsModel;
use Yodorada\Models\TokenModel;
use Yodorada\Models\UsersModel;
use Yodorada\Models\UsersRightsModel;

/**
 * class ServiceUser
 * @package   Yodorada\Webservice
 * @author    Yodorada | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright Yodorada, 2017
 * @version 0.0.8
 */
class ServiceUser
{
    const ROLE_ADMINS = 100;
    const ROLE_EDITORS = 200;
    const ROLE_USERS = 300;

    const PASSWORD_RETURN = '*****';

    /**
     * User Roles
     * @var array
     */
    protected static $roles = array('admins' => self::ROLE_ADMINS, 'editors' => self::ROLE_EDITORS, 'users' => self::ROLE_USERS);

    /**
     * Current data
     * @var array
     */
    protected static $arrData = array();

    /**
     * Return all object properties
     *
     * @return array
     */
    public static function getAllData()
    {
        return static::$arrData;

    }

    /**
     * set all object properties
     *
     * @param array  $arr
     */
    public static function setData($arr)
    {
        static::$arrData = $arr;
    }

    /**
     * Set an object property
     *
     * @param string $strKey
     * @param mixed  $varValue
     */
    public static function set($strKey, $varValue)
    {
        static::$arrData[$strKey] = $varValue;
    }

    /**
     * Return current role int
     *
     * @return int The role value
     */
    public static function getRole()
    {
        if (isset(static::$arrData['role'])) {
            return static::$roles[static::$arrData['role']];
        }
        return null;
    }

    /**
     * Return a role prop
     *
     * @param string $strKey The variable name
     *
     * @return int The role value
     */
    public static function role($strKey)
    {
        if (isset(static::$roles[$strKey])) {
            return static::$roles[$strKey];
        }
        return null;
    }

    /**
     * Return an object prop
     *
     * @param string $strKey The variable name
     *
     * @return mixed The variable value
     */
    public static function get($strKey)
    {
        if (isset(static::$arrData[$strKey])) {
            return static::$arrData[$strKey];
        }
        if (in_array($strKey, get_class_methods('\Yodorada\Modules\ServiceUser'))) {
            static::$arrData[$strKey] = static::$strKey();
        }
        if (isset(static::$arrData[$strKey])) {
            return static::$arrData[$strKey];
        }
        return null;
    }

    /**
     * authentification
     *
     * @return  void
     */
    public static function authenticate()
    {

        if (isset($_SERVER['HTTP_AUTHORIZATION']) || isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $auth = $_SERVER['HTTP_AUTHORIZATION'];
            } else {
                $auth = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            }
            preg_match('/Basic\s+(.*)$/i', $auth, $basicAuth);
            if (count($basicAuth)) {
                // HTTP AUTH
                list($username, $pw) = explode(':', base64_decode($basicAuth[1]));

                $user = UsersModel::where("username", $username)->getOne();

                if (count($user)) {
                    if (Utils::verifyPassword($pw, $user->password)) {
                        //

                        $userData = static::verifyUser($user);
                        if ($userData === false) {
                            return false;
                        }
                        static::$arrData = $userData;
                        return true;
                    }
                }
            }
        } elseif (isset($_SERVER['HTTP_TOKEN'])) {
            $tokenAuth = $_SERVER['HTTP_TOKEN'];
            // TOKEN AUTH
            $token = TokenModel::where("token", $tokenAuth)->getOne();
            if (!count($token)) {
                return false;
            }
            $valid = Utils::verifyToken($token->token);

            if (!$valid || $token->expiresAt < time()) {
                return false;
            }
            $user = UsersModel::byId($token->usersId);
            if (count($user)) {
                $userData = static::verifyUser($user, $token);
                if ($userData === false) {
                    return false;
                }
                static::$arrData = $userData;
                return true;
            }

        }
        return false;
    }

    protected static function verifyUser($user, $token = null)
    {

        // get the parenting group for user
        $userGroup = GroupsModel::where("id", $user->groupsId)->getOne();
        if (!count($userGroup)) {
            // has no group
            return false;
        }
        if (!$userGroup->enabled) {
            // group not allowed
            return false;
        }

        if ($userGroup->role != ServiceUser::ROLE_ADMINS) {
            if ($user->overrideGroupRights) {
                $rights = UsersRightsModel::where("usersId", $user->id)->getOne();
            } else {
                $rights = GroupsRightsModel::where("groupsId", $user->groupsId)->getOne();
            }
            if (count($rights)) {
                $rights = $rights->toArray();
                $user->rights = Utils::customAllowedMethods($rights['rights']);

            }
        }

        $user->userGroup = $userGroup->toArray();

        $roles = array_flip(static::$roles);
        $user->role = $roles[$userGroup->role];
        $user->roleId = $userGroup->role;

        if ($token === null) {
            // no token provided > was basicAuth
            // find matching token
            $token = TokenModel::where("usersId", $user->id)->getOne();
            if (!count($token)) {
                // no token found > create a new one

                $token = Utils::createToken($user->id)->toArray();
                $user->accessToken = array('token' => $token['token'], 'expiresAt' => $token['expiresAt']);
                $user->password = ServiceUser::PASSWORD_RETURN;
                return $user->toArray();
            } else {
                // update token
                $tokenData = array(
                    'expiresAt' => time() + $GLOBALS['CONFIG']['TOKEN']['EXPIRES'],
                    'token' => Utils::accessToken(),
                );
                $token->expiresAt = $tokenData['expiresAt'];
                $token->token = $tokenData['token'];
                $token->save();
            }
        } else {
            $token->expiresAt = time() + $GLOBALS['CONFIG']['TOKEN']['EXPIRES'];
            $token->save();
        }

        $user->accessToken = array('expiresAt' => $token->expiresAt, 'token' => $token->token);
        $user->password = ServiceUser::PASSWORD_RETURN;
        return $user->toArray();
    }

    /**
     * account was updated
     *
     * @param array
     */
    public static function accountUpdated()
    {
        $user = UsersModel::byId(static::$arrData['id']);
        if (count($user)) {
            $token = TokenModel::where("usersId", $user->id)->getOne();
            $userData = static::verifyUser($user, $token);
            if ($userData === false) {
                return false;
            }
            static::$arrData = $userData;
            return true;
        }

    }

    /**
     * Get property accessToken
     *
     * @param array
     */
    public static function getAccessToken()
    {
        return static::get('accessToken');
    }

    /**
     * Get account data for output
     *
     * @param array
     */
    public static function getAccountOutput()
    {
        $n = array(
            'username' => static::get('username'),
            'email' => static::get('email'),
            'role' => static::get('roleId'),
            'rights' => static::get('rights'),
            'id' => static::get('id'),
            'groupsId' => static::get('groupsId'),
            'password' => static::PASSWORD_RETURN,
        );
        if ($n['rights'] === null) {
            $n['rights'] = array();
        }
        return $n;
    }

    /**
     * Get role by input string
     *
     * @param string
     * @return int|null
     */
    public static function getInputMatchRole($input)
    {
        $role = static::role($input);
        if ($role === null) {
            return $input;
        }
        return $role;
    }

}
