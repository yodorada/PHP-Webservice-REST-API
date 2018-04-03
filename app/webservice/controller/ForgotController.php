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
use Yodorada\Classes\Input;
use Yodorada\Classes\Setting;
use Yodorada\Classes\Utils;
use Yodorada\Models\GroupsModel;
use Yodorada\Models\UsersModel;
use Yodorada\Modules\Emailer;
use Yodorada\Modules\ServiceUser;

/**
 * class ForgotController
 * @package   Yodorada\Webservice
 * @author    Yodorada | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright Yodorada, 2017
 * @version 0.0.3
 */
class ForgotController extends Controller implements ControllerInterface
{

    public static $scope = self::SCOPE_RESOURCE;

    protected $selfInfo = 'controller.forgot.self_info';

    public static $version = '0.0.1';
    /**
     * method GET
     *
     */
    public function get()
    {}

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
    {
        if (Input::get('username') &&
            Input::get('email')) {

            $user = UsersModel::where("username", Input::get('username'))->where("email", Input::get('email'))->getOne();

            if (count($user)) {
                $group = GroupsModel::byId($user->groupsId);
                if (!count($group) || $group->role != ServiceUser::ROLE_EDITORS || (Setting::get('realm') && Setting::get('realm') != base64_encode($GLOBALS['CONFIG']['API']['FRONTEND_KEY']))) {
                    Errors::exitBadRequest(Translate::get('controller.forgot.only_frontend'));
                }
                $confirmationToken = md5(uniqid(rand(), true));
                $passwordRequestedAt = time();

                $user->confirmationToken = $confirmationToken;
                $user->passwordRequestedAt = $passwordRequestedAt;
                $user->changed = $passwordRequestedAt;
                $user->save();

                $to = Input::get('email');

                $subject = Translate::get('controller.forgot.email_subject');
                $message = Translate::get('controller.forgot.email_body', Input::get('username'), Utils::getHostAndApiPath() . "/reset.php?token=" . $confirmationToken);

                if ($GLOBALS['CONFIG']['MAILER']['USE_SWIFT'] && version_compare(PHP_VERSION, '7.0.0', '>=')) {
                    // implement swift
                    $swift = new Emailer();
                    $swift->set('subject', $subject);
                    $swift->set('body', $message);

                    $sendmail = $swift->send();
                    if (!$sendmail) {
                        Errors::exitGeneralError(Translate::get('controller.forgot.error_sending'));
                    }
                } else {
                    // use php mail

                    $headers = "From: " . $GLOBALS['CONFIG']['MAILER']['MAIL_FROM'] . "\nReply-To: " . $GLOBALS['CONFIG']['MAILER']['MAIL_FROM'];
                    $sendmail = mail($to, $subject, $message, $headers);
                    if (!$sendmail) {
                        Errors::exitGeneralError(Translate::get('controller.forgot.error_sending'));
                    }
                }

                return array('reset' => true, 'email' => Input::get('email'), 'username' => Input::get('username'));

            } else {
                Errors::exitBadRequest('controller.forgot.unknown');
            }

        } else {
            Errors::exitBadRequest('controller.forgot.name_and_mail');
        }
    }

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
        return array_intersect_key($fields, array_fill_keys(array('username', 'email'), ''));
    }
}
