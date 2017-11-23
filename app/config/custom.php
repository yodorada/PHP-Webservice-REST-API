<?php

/*
 * Part of PHP-Webservice-REST-API
 *
 * Copyright (c) Maya K. Herrmann | EINS[23].TV
 *
 * @license LGPL-3.0+
 */

/**
 *  Add custom controllers and define their routes to resources in your Api.
 *
 *  ----------------------------------------------------
 *  array key "controller": (REQUIRED)
 *  your controller class must be named like "CustomController" and extend "\Yodorada\Controller\Controller"
 *  the file itself must be named like the class e.g. "CustomController.php"
 *  in the config array omit the word Controller e.g. "controller" => "Custom" (and not "CustomController")
 *  when using the model features of MysqliDb be sure to name the
 *  corresponding Models similar to the Controllers e.g. "CustomModel"
 *
 *  array key "authorization": (REQUIRED)
 *  defines the methods where authentification is required
 *  C = create > POST
 *  R = read > GET
 *  U = update > PUT/PATCH
 *  D = delete > DELETE
 *
 *  array key "self": (REQUIRED)
 *  defines the request segment of an URi
 *
 *  array key "parent": (OPTIONAL)
 *  defines the related parent controller
 *
 *  array key "routes": (REQUIRED)
 *  Variables are marked in brackets {}.
 *  {self} refers to the value of key "self" (request key) in parenting array.
 *  {parent} refers to the the value of key "parent" in parenting array.
 *  {id} refers to the preceding string…
 *  …and indicates the primary key of the corresponding database table (DB column name MUST be id).
 *
 *  The values of "routes" define the allowed methods for that request (use string format):
 *  C = create > POST
 *  R = read > GET
 *  U = update > PUT/PATCH
 *  D = delete > DELETE
 *  OPTIONS are always callable (except on "/info")
 *
 *  e.g.: the UsersController would be callable under the following routes with allowed methods:
 *  api.tld/users/1234 (GET, PUT, PATCH, DELETE, OPTIONS)
 *  api.tld/users (GET, POST, OPTIONS)
 *  api.tld/users/info (GET)
 *
 */

/*
return array(
"example" => array(
"self" => "example", // < the resource URI
"routes" => array(
"{self}" => "CR", // < Let's you read the collection and create new entries in it
"{self}/{id}" => "RUD", // < Let's you read, update and delete entries
"{self}/info" => "R" // < Let's you read the info of the controller
),
"controller" => "Example", // < the controller name
"authorization" => "CUD" // < the methods that require auth
)
);

In case you don't use FLAT_HIERARCHY ( see config.php ) nested records are possible:

"examplechildren" => array(
"self" => "children",
"routes" => array(
"{parent}/{id}/{self}" => "CR",
"{parent}/{id}/{self}/{id}" => "RUD",
"{self}" => "CR",
"{self}/{id}" => "RUD",
"{self}/info" => "R",
),
"parent" => "example",
"label" => "Children",
"controller" => "ExampleChildren",
"authorization" => "CUD",
),

Below are two demo controllers.
Parent: authors
Child: aphorisms

Take a look at the related php files in webservice/controller and webservice/models
for further understanding

 */
return array(
    "authors" => array(
        "self" => "authors",
        "routes" => array(
            "{self}" => "CR",
            "{self}/{id}" => "RUD",
            "{self}/info" => "R",
        ),
        "label" => "Authors",
        "controller" => "Authors",
        "authorization" => "CUD",
    ),
    "aphorisms" => array(
        "self" => "aphorisms",
        "routes" => array(
            "{self}" => "CR",
            "{self}/{id}" => "RUD",
            "{self}/info" => "R",
        ),
        "parent" => "authors",
        "label" => "Aphorisms",
        "controller" => "AuthorsAphorisms",
        "authorization" => "CUD",
    ),
);
