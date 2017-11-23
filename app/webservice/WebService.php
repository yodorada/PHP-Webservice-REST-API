<?php

/*
 * Part of PHP-Webservice-REST-API
 *
 * Copyright (c) Maya K. Herrmann | EINS[23].TV
 *
 * @license LGPL-3.0+
 */

namespace Yodorada;

use Yodorada\Classes\Errors;
use Yodorada\Classes\Headers;
use Yodorada\Classes\Input;
use Yodorada\Classes\Registry;
use Yodorada\Classes\Setting;
use Yodorada\Classes\Translate;
use Yodorada\Classes\Utils;
use Yodorada\Core;
use Yodorada\Modules\ServiceUser;

define('WEBSERVICE_MODE', 'development');
// define('WEBSERVICE_MODE', 'production');

/**
 * class WebService
 * @package   Yodorada\Webservice
 * @author    EINS[23].TV | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright EINS[23].TV, 2017
 * @version 0.2.0
 */
class WebService extends Core
{
/**
 * current WebService version
 *
 * @var string
 */
    protected $version = '0.2.0';

    /**
     * WebService name
     *
     * @var string
     */
    protected $name = 'misc.webservice.name';

    /**
     * brief self explanatory text
     *
     * @var string
     */
    protected $selfInfo = "misc.webservice.self_info";

    /**
     * the executing module
     * @var \Controller
     */
    protected $Controller;

    /**
     * run the service, perform checks
     * and if successful run the controller
     *
     * @return  void
     *
     */
    public function run()
    {
        // initalize and process core functions
        $this->initialize();

        // if there is no header content-type: "application/json" or "application/x-www-form-urlencoded"
        if (!Setting::get('outputFormat')) {
            Headers::send(501);
            Utils::outputError(Translate::get('misc.webservice.output_format'));
            exit;
        }

        // check if this is the root url
        if (!count(Setting::get('route'))) {
            // then output available resources
            Headers::send(200);
            static::log(array('message' => Translate::get('misc.webservice.no_route'), 'status' => 'ok'));
            $output = array('name' => Translate::get($this->name), 'description' => Translate::get($this->selfInfo), 'version' => $this->version, 'resources' => Registry::availableResources());

            switch ($GLOBALS['CONFIG']['APPLICATION']['OUTPUT']['FORMAT']) {
                case 'xml':
                    // xml output
                    Headers::contentType('text/xml');
                    echo Utils::generateXML(array('data' => $output));
                    break;
                case 'json':
                default:
                    // json output
                    echo json_encode($output);
                    break;
            }
            exit;
        }

        // get the current controller by request url
        $controller = Registry::getController();

        // a controller by this name does not exist
        if ($controller === null) {
            Errors::exitNotFound(Translate::get('misc.webservice.no_controller'));
        }

        // current method
        $method = Setting::get('method');

        // registered controller methods
        $allowedMethods = $controller['methods'];

        // method OPTIONS needs no auth and returns allowed options
        if ($method == 'options') {
            // is system & user based role management
            if ($controller['roleset'] && $controller['system']) {
                // populate admin methods
                $allowedMethods = $controller['methods'][ServiceUser::ROLE_ADMINS];
            }
            Headers::allowMethods(array_merge($allowedMethods, array('options')));
            Headers::send(204);
            exit;
        }

        // check if current method requires authentification
        $authRequired = in_array($method, $controller['authorization']);

        // check if user authenticated
        $authorized = ServiceUser::authenticate();

        // if requires auth or user is authorized
        if ($authRequired || $authorized) {

            // method requires auth but user is not authorized
            if (!$authorized) {
                Headers::send(401);
                Utils::outputError(Translate::get('errors.codes.401'));
                exit;
            }

            // send access token in header
            Headers::exposeToken(ServiceUser::get('accessToken'));

            // get user role
            $role = ServiceUser::getRole();

            if ($controller['system']) {
                // is system & user based role management
                if ($controller['roleset'] && array_key_exists($role, $controller['methods'])) {
                    // apply it's own ruleset
                    $allowedMethods = $controller['methods'][$role];
                }
            } else {
                if ($role != ServiceUser::ROLE_ADMINS) {
                    // no admin, therefore check if user is allowed to execute current method
                    if (!$authRequired) {
                        // method requires no auth
                        $allowedMethods = array_diff(Utils::crudToMethods('CRUD'), $controller['authorization']);
                    } else {
                        $allowedMethods = array();
                        $rights = ServiceUser::get('rights');
                        $rKey = $controller['registerKey'];
                        $match = $controller['match'];
                        if (isset($rights[$rKey])) {
                            if (isset($rights[$rKey]['resources'][$match])) {
                                $allowedMethods = array_keys(array_filter($rights[$rKey]['resources'][$match]['methods']));
                            }
                        }
                    }

                }
            }
        } else {
            // public access granted
            $allowedMethods = array_diff(Utils::crudToMethods('CRUD'), $controller['authorization']);
            self::$accessLevel = self::ACCESS_LEVEL_PUBLIC;
        }

        // an error occurred
        if (Utils::hasStringKeys($allowedMethods)) {
            Errors::exitGeneralError(Translate::get('errors.misc.allowed_methods'));
        }

        // add OPTIONS as method and send header allowed methods
        $allowedMethods = array_merge($allowedMethods, array('options'));
        Headers::allowMethods($allowedMethods);

        // check if current method is allowed
        if (!in_array($method, $allowedMethods)) {
            Errors::exitMethodNotAllowed();
        }

        // if method is POST, PUT or PATCH but there is no json data
        if (Setting::get('inputFormat') == 'json') {
            if (!Input::hasData() && ($method == 'post' || $method == 'put' || $method == 'patch')) {
                Errors::exitBadRequest(Translate::get((!Input::$validJson ? 'errors.misc.invalid_json_input' : 'errors.misc.no_data')));
            }
        }

        // class string
        $classLoader = "Yodorada\Controller\\" . $controller['controller'] . "Controller";
        // instantiate the controller
        $this->Controller = new $classLoader();
        // to do
        // add caching
        // http://blog.mugunthkumar.com/articles/restful-api-server-doing-it-the-right-way-part-2/
        // http://www.apiacademy.co/how-to-http-caching-for-restful-hypermedia-apis/
        // http://fideloper.com/api-etag-conditional-get
        //
        // run the controller and let it output
        $this->Controller->run();

    }

}
