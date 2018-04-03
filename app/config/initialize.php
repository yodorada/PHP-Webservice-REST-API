<?php

/*
 * Part of PHP-Webservice-REST-API
 *
 * Copyright (c) Maya K. Herrmann | Yodorada
 *
 * @license LGPL-3.0+
 */

use Yodorada\Classes\Registry;
use Yodorada\Modules\ServiceUser;

/** load app config data */
include_once __DIR__ . '/config.php';

/** load database, login */
include_once __DIR__ . '/database.php';

/*

These are the roles for standard Api Controllers
Do not change anything here…

 */
$admin = ServiceUser::ROLE_ADMINS;
$editor = ServiceUser::ROLE_EDITORS;
$user = ServiceUser::ROLE_USERS;

Registry::register(
    array(
        "authorized" => array(
            "self" => "authorized",
            "routes" => array(
                "{self}" => "R",
            ),
            "controller" => "Login",
            "authorization" => "R",
            "system" => true,
        ),
        "forgot" => array(
            "self" => "forgot",
            "routes" => array(
                "{self}" => "U",
                "{self}/info" => "R",
            ),
            "controller" => "Forgot",
            "authorization" => "",
            "system" => true,
        ),
        "login" => array(
            "self" => "login",
            "routes" => array(
                "{self}" => "R",
                "{self}/info" => "R",
            ),
            "controller" => "Login",
            "authorization" => "R",
            "system" => true,
        ),
        "logs" => array(
            "self" => "logs",
            "routes" => array(
                "{self}" => "R",
                "{self}/{id}" => "R",
                "{self}/info" => "R",
            ),
            "controller" => "Logs",
            "authorization" => "R",
            "system" => true,
        ),
        "files" => array(
            "self" => "files",
            "routes" => array(
                "{self}" => "CR",
                "{self}/{id}" => "RD",
                "{self}/info" => "R",
            ),
            "controller" => "Files",
            "authorization" => "CRD",
            "system" => true,
        ),
        "account" => array(
            "self" => "account",
            "routes" => array(
                "{self}/{id}" => "RU",
                "{self}/info" => "R",
            ),
            "controller" => "Account",
            "authorization" => "RU",
            "system" => true,
        ),
        "groups" => array(
            "self" => "groups",
            "routes" => array(
                "{self}" => array(
                    $admin => "CR",
                    $editor => "CR",
                    $user => "",
                ),
                "{self}/info" => array(
                    $admin => "R",
                    $editor => "R",
                    $user => "",
                ),
                "{self}/{id}" => array(
                    $admin => "RUD",
                    $editor => "RUD",
                    $user => "",
                ),
            ),
            "controller" => "Groups",
            "authorization" => "CRUD",
            "system" => true,
        ),
        // "groupsrights" => array(
        //     "self" => "groupsrights",
        //     "routes" => array(
        //         "{parent}/{id}/{self}/info" => array(
        //             $admin => "R",
        //             $editor => "R",
        //             $user => "",
        //         ),
        //         "{parent}/{id}/{self}/{id}" => array(
        //             $admin => "RU",
        //             $editor => "RU",
        //             $user => "",
        //         ),
        //         "{self}/info" => array(
        //             $admin => "R",
        //             $editor => "R",
        //             $user => "",
        //         ),
        //         "{self}/{id}" => array(
        //             $admin => "RU",
        //             $editor => "RU",
        //             $user => "",
        //         ),
        //     ),
        //     "parent" => "groups",
        //     "controller" => "GroupsRights",
        //     "authorization" => "CRUD",
        //     "system" => true,
        // ),
        "users" => array(
            "self" => "users",
            "routes" => array(
                "{self}/{id}" => array(
                    $admin => "RUD",
                    $editor => "RUD",
                    $user => "",
                ),
                "{self}/info" => array(
                    $admin => "R",
                    $editor => "R",
                    $user => "",
                ),
                "{self}" => array(
                    $admin => "CR",
                    $editor => "CR",
                    $user => "",
                ),
                "{parent}/{id}/{self}" => array(
                    $admin => "CR",
                    $editor => "CR",
                    $user => "",
                ),
                "{parent}/{id}/{self}/info" => array(
                    $admin => "R",
                    $editor => "R",
                    $user => "",
                ),
                "{parent}/{id}/{self}/{id}" => array(
                    $admin => "RUD",
                    $editor => "RUD",
                    $user => "",
                ),
            ),
            "parent" => "groups",
            "controller" => "Users",
            "authorization" => "CRUD",
            "system" => true,
        ),
        // "usersrights" => array(
        //     "self" => "usersrights",
        //     "routes" => array(
        //         "{parent}/{id}/{self}/info" => array(
        //             $admin => "R",
        //             $editor => "R",
        //             $user => "",
        //         ),
        //         "{parent}/{id}/{self}/{id}" => array(
        //             $admin => "RU",
        //             $editor => "RU",
        //             $user => "",
        //         ),
        //         "{self}/info" => array(
        //             $admin => "R",
        //             $editor => "R",
        //             $user => "",
        //         ),
        //         "{self}/{id}" => array(
        //             $admin => "RU",
        //             $editor => "RU",
        //             $user => "",
        //         ),
        //     ),
        //     "parent" => "users",
        //     "controller" => "UsersRights",
        //     "authorization" => "CRUD",
        //     "system" => true,
        // ),
    )
);
