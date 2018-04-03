<?php

/*
 * Part of PHP-Webservice-REST-API
 *
 * Copyright (c) Maya K. Herrmann | Yodorada
 *
 * @license LGPL-3.0+
 */

namespace Yodorada\Classes;

/**
 * Class Registry
 *
 * register controller and maintain hierachy
 * @package   Yodorada\Webservice
 * @author    Yodorada | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright Yodorada, 2017
 * @version 0.0.5
 */

class Registry
{

    /**
     * controller - hold the current controller
     * @var array
     */
    protected static $controller;

    /**
     * system (first the system controllers must be initiated)
     * @var bool
     */
    protected static $system = true;

    /**
     * initialized
     * @var bool
     */
    protected static $initialized = false;

    /**
     * registered
     * @var array
     */
    protected static $registered = array();

    /**
     * custom
     * @var array
     */
    protected static $custom = array();

    /**
     * byname
     * @var array
     */
    protected static $byname = array();

    /**
     * parents
     * @var array
     */
    protected static $parents = array();

    /**
     * routes
     * @var array
     */
    protected static $routes = array();

    /**
     * Register routes
     *
     * @return void
     */
    public static function register($routes)
    {
        foreach ($routes as $key => $arr) {
            $resources = array();
            $controller = $arr['controller'];

            // the resource was already registered
            if (array_key_exists($key, static::$registered)) {
                trigger_error('The resource ' . $key . ' is already registered.', E_USER_ERROR);
            }

            // all required set?
            $required = array('self', 'routes', 'controller', 'authorization');
            if (count(array_intersect_key(array_flip($required), $arr)) !== count($required)) {
                // not all required keys exist!
                trigger_error('Not all options of resource ' . $key . ' are defined (' . implode(', ', $required) . ').', E_USER_ERROR);
            }
            if (file_exists(__DIR__ . "/../controller/" . $controller . "Controller.php")) {
                $locationResource = array();
                $locationCollection = array();
                foreach ($arr['routes'] as $route => $crud) {
                    // throw exception when $arr['self'] is not set
                    if (!isset($arr['self'])) {
                        trigger_error('The replacement value for {self} is not defined.', E_USER_ERROR);
                    }

                    preg_match('/(\{self\})\/(\{id\})$/', $route, $matchesResource);
                    preg_match('/(\{self\})$/', $route, $matchesCollection);
                    $route = str_replace('{self}', $arr['self'], $route);

                    if (strpos($route, '{parent}') !== false) {
                        if ($GLOBALS['CONFIG']['APPLICATION']['FLAT_HIERARCHY']) {
                            // if only flat hierachy is allowed continue with next
                            continue;
                        }
                        // throw exception when $arr['parent'] is not set
                        if (!isset($arr['parent'])) {
                            trigger_error('The replacement value for {parent} is not defined.', E_USER_ERROR);
                        } else {
                            $route = str_replace('{parent}', $arr['parent'], $route);
                        }
                    }

                    $route = ltrim(rtrim($route, '/'), '/');
                    $resources[] = $route;

                    if (count($matchesResource)) {
                        $locationResource[] = $route;
                    }
                    if (count($matchesCollection)) {
                        $locationCollection[] = $route;
                    }

                    if (is_array($crud) && static::$system) {
                        $roleset = true;
                        $methods = array();
                        foreach ($crud as $role => $allowed) {
                            $methods[$role] = Utils::crudToMethods($allowed);
                        }
                    } else {
                        $roleset = false;
                        $methods = Utils::crudToMethods($crud);
                    }

                    $authorization = Utils::crudToMethods($arr['authorization']);

                    $chunks = count(explode('/', $route));
                    if (array_key_exists('chunks_' . $chunks, static::$routes) && array_key_exists($route, static::$routes['chunks_' . $chunks])) {
                        trigger_error('The URI ' . $route . ' is already registered.', E_USER_ERROR);
                    }
                    static::$routes['chunks_' . $chunks][$route] = array(
                        'name' => $key,
                        'primaryKey' => $arr['self'],
                        'parentKey' => (isset($arr['parent']) ? $arr['parent'] : null),
                        'methods' => $methods,
                        'roleset' => $roleset,
                        'authorization' => $authorization,
                        'system' => static::$system,
                    );
                }
                $locations = array('resource' => $locationResource, 'collection' => $locationCollection);
                if (isset($arr['parent'])) {
                    static::$parents[$arr['parent']][$key] = $controller;
                }
                static::$registered[$key] = array('name' => $controller, 'label' => (isset($arr['label']) ? $arr['label'] : $controller), 'resources' => $resources, 'locations' => $locations);
                static::$byname[strtolower($controller)] = array('key' => $key, 'locations' => $locations);

                if (!static::$system) {
                    static::$custom[] = $key;
                }

            }
        }
        if (static::$system) {
            static::$system = false;
        }
    }

    /**
     * get controller that matches the current route
     *
     * @return array|null
     */
    public static function getController()
    {
        if (!static::$initialized) {
            static::initialize();
        }
        return static::$controller;
    }

    /**
     * initialize
     *
     * @return void
     */
    public static function initialize()
    {
        static::$controller = static::findController();
        static::$initialized = true;
    }

    /**
     * find controller that matches the current route
     *
     * @return array|null
     */
    protected static function findController()
    {
        $request = Setting::get('route');
        $routes = static::getRoutes();
        $registered = static::getRegistered();

        if (!count($routes) || !count($registered) || !array_key_exists('chunks_' . count($request), $routes)) {
            return null;
        }
        $routes = $routes['chunks_' . count($request)];
        //
        foreach ($routes as $key => $val) {
            if (strpos($key, '/') !== false) {
                $chunks = array_map(
                    function ($el) {
                        return "(" . str_replace('{id}', '\d+', $el) . ")";
                    },
                    array_values(explode("/", $key))
                );
                $regxp = '/^' . join('\/', $chunks) . '/';
            } else {
                $regxp = '/^' . $key . '/';
            }
            preg_match($regxp, join('/', $request), $matches);

            if (count($matches)) {
                $classLoader = "\Yodorada\Controller\\" . $registered[$val['name']]['name'] . "Controller";
                return array(
                    'registerKey' => $val['name'],
                    'primaryKey' => $val['primaryKey'],
                    'parentKey' => $val['parentKey'],
                    'methods' => $val['methods'],
                    'roleset' => $val['roleset'],
                    'authorization' => $val['authorization'],
                    'system' => $val['system'],
                    'controller' => $registered[$val['name']]['name'],
                    'resources' => $registered[$val['name']]['resources'],
                    'locations' => $registered[$val['name']]['locations'],
                    'match' => $key,
                    'route' => $matches,
                    'version' => $classLoader::$version,
                );
            }
        }
        // no controller found
        return null;
    }

    /**
     * get available resources for output
     *
     * @return array
     */
    public static function availableResources()
    {
        $routes = static::$routes;
        $host = Utils::getHostAndApiPath();
        $flat = array();
        foreach ($routes as $key => $arr) {
            $flat = array_merge($flat, array_keys($arr));
        }
        sort($flat);

        return array_map(function ($value) use ($host) {
            return $host . '/' . $value;
        }, $flat);
    }

    /**
     * get registered controller
     *
     * @return array
     */
    public static function getRegistered()
    {
        return static::$registered;
    }

    /**
     * get custom controller
     *
     * @return array
     */
    public static function getCustom()
    {
        $arr = array();
        foreach (static::$custom as $key) {
            $arr[$key] = static::$registered[$key];
        }
        return $arr;
    }

    /**
     * has custom controller
     *
     * @return bool
     */
    public static function hasCustom()
    {
        return count(static::$custom) > 0;
    }

    /**
     * get parent with child resource uris
     *
     * @return array
     */
    public static function getParents()
    {
        return static::$parents;
    }

    /**
     * get child resource uris
     *
     * @return array
     */
    public static function getChildCollectionResources($name)
    {
        $name = strtolower($name);
        $arr = array();
        if (array_key_exists($name, static::$parents)) {
            foreach (static::$parents[$name] as $key => $value) {
                $childname = strtolower($value);
                if (array_key_exists($childname, static::$byname) && count(static::$byname[$childname]['locations']['collection'])) {
                    $arr[$key] = static::$byname[$childname]['locations']['collection'];
                }
            }
        }
        return (count($arr) ? $arr : null);
    }

    /**
     * get registered resource uris
     *
     * @return array
     */
    public static function getRoutes()
    {
        return static::$routes;
    }

    /**
     * get registered resource uris
     *
     * @return array
     */
    public static function getNames()
    {
        return static::$byname;
    }

    /**
     * get registered routes by name
     *
     * @return array
     */
    public static function getByName($name)
    {
        if (isset(static::$byname[$name])) {
            return static::$byname[$name];
        }
        return null;
    }

    /**
     * get new resource location
     *
     * @param  $name string : if defined do search for specific controller else use current controller
     * @return string
     */
    public static function getResourceLocation($name = '')
    {
        $name = strtolower($name);
        if (strlen($name) && array_key_exists($name, static::$byname)) {
            return static::$byname[$name]['locations']['resource'];
        } elseif (!strlen($name)) {
            return static::$controller['locations']['resource'];
        }
        return null;
    }

    /**
     * get new collection location
     *
     * @param  $name string : if defined do search for specific controller else use current controller
     * @return string
     */
    public static function getCollectionLocation($name = '')
    {
        $name = strtolower($name);
        if (strlen($name) && array_key_exists($name, static::$byname)) {
            return static::$byname[$name]['locations']['collection'];
        } elseif (!strlen($name)) {
            return static::$controller['locations']['collection'];
        }
        return null;
    }

}
