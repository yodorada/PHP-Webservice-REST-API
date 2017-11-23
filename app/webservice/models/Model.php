<?php

/*
 * Part of PHP-Webservice-REST-API
 *
 * Copyright (c) Maya K. Herrmann | EINS[23].TV
 *
 * @license LGPL-3.0+
 */

namespace Yodorada\Models;

use Yodorada\Classes\Database;
use Yodorada\Classes\Errors;
use Yodorada\Classes\Filters;
use Yodorada\Classes\Input;
use Yodorada\Classes\Query;
use Yodorada\Classes\Registry;
use Yodorada\Classes\Setting;
use Yodorada\Classes\Translate;
use Yodorada\Classes\Utils;
use Yodorada\Modules\ServiceUser;
use \dbObject;

/**
 * database model
 * @package   Yodorada\Webservice
 * @author    EINS[23].TV | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright EINS[23].TV, 2017
 * @version 0.2.0
 */
class Model extends dbObject
{
    protected $dbTable;
    protected $dbChildren;

    public static $parentKey;

    protected static $fieldData = array();

    /**
     * get table name
     *
     * @return  string
     */
    public static function tableName()
    {
        $self = new static;
        return $self->dbTable;
    }

    /**
     * get table name
     *
     * @return  string
     */
    public static function getDataFields()
    {
        return static::$fieldData;
    }

    /**
     * get field specs as defined in static::$fieldData
     *
     * @return  array
     */
    public static function getFieldsInfo()
    {
        $fields = array();
        foreach (static::$fieldData as $key => $arr) {
            if (isset($arr['systemOnly']) && $arr['systemOnly']) {
                continue;
            }
            if (isset($arr['omit']) && is_string($arr['omit'])) {
                if (strtolower($arr['omit']) == 'cru' || strtolower($arr['omit']) == 'crud') {
                    continue;
                }
                $arr['omit'] = Utils::crudToMethods($arr['omit']);
            }
            if (isset($arr['explanation'])) {
                $arr['explanation'] = Translate::get($arr['explanation']);
            }
            unset($arr['defaultFunction']);
            unset($arr['inputCallback']);
            unset($arr['outputCallback']);

            $fields[$key] = $arr;
        }
        return $fields;
    }

    /**
     * compile the array for output to json
     *
     * @return  array the compiled fields
     *
     */
    protected function makeArray()
    {
        $data = $this->toArray();

        $fData = static::$fieldData;
        $fields = array();
        $method = Setting::get('method');

        foreach ($data as $key => $val) {
            if (array_key_exists($key, $fData)) {
                if (isset($fData[$key]['omit']) && is_string($fData[$key]['omit'])) {
                    $methods = Utils::crudToMethods($fData[$key]['omit']);
                    if (in_array($method, $methods)) {
                        continue;
                    }
                }
                if ($key == 'created' || $key == 'changed') {
                    $val = Utils::timestampToOutput($val);
                }

                $val = static::processOutputValue($fData[$key], $val);

                if (isset($fData[$key]['outputCallback'])) {
                    if (is_string($fData[$key]['outputCallback']) && function_exists($fData[$key]['outputCallback'])) {
                        $val = $fData[$key]['outputCallback']($val);
                    } elseif (is_array($fData[$key]['outputCallback'])) {
                        $class = $fData[$key]['outputCallback'][0];
                        $fn = $fData[$key]['outputCallback'][1];
                        $val = $class::$fn($val);
                    }
                }

            }
            $fields[$key] = $val;
        }
        if ($GLOBALS['CONFIG']['APPLICATION']['OUTPUT']['ADD_RESOURCE_LOCATION']) {
            $location = static::locationResource($fields);
            if ($location !== null) {
                $fields['location'] = $location;
            }
        }
        if (!$GLOBALS['CONFIG']['APPLICATION']['FLAT_HIERARCHY'] && $GLOBALS['CONFIG']['APPLICATION']['OUTPUT']['ADD_RESOURCE_LOCATION']) {
            $children = static::childLocationCollection($fields);
            if ($children !== null) {
                $fields['children'] = $children;
            }
        }
        return $fields;
    }

    /**
     * update the model with input data
     *
     * @return  bool
     *
     */
    protected function updateData()
    {
        $newData = static::processInputData($this);

        if (Errors::hasError()) {
            Errors::exitWithErrors();
        }
        $newData['changed'] = time();
        if (!$this->wasModified($newData)) {
            // data was not modified, no need to save
            return true;
        }
        $update = $this->save($newData);
        if (count($this->errors)) {
            foreach ($this->errors as $key => $val) {
                Errors::add($key . ' ' . $val);
            }
            Errors::exitWithErrors();
        }
        return true;
    }

    /**
     * delete current record and all related child records
     *
     * @return  bool
     */
    protected function deleteWithChildren()
    {
        $db = Database::getInstance();
        $prefix = $GLOBALS['CONFIG']['DB']['PREFIX'];

        foreach ($this->dbChildren as $model) {
            $classLoader = "\Yodorada\Models\\" . $model . "Model";
            $m = new $classLoader();
            $table = $prefix . $m->dbTable;
            $pKey = $m::$parentKey;
            $sql = "DELETE FROM " . $table . " WHERE " . $table . "." . $pKey . "=?";
            $affected = $db->rawQuery($sql, array($this->id));
            if ($db->getLastErrno() != 0) {
                Errors::add($db->getLastError());
                Errors::exitWithErrors();
            }
        }

        return $this->delete();

    }

    /**
     * get child record by id and parent key
     *
     * @return  array
     */
    protected static function byIdAndParent($id, $pid)
    {
        $data = static::where('id', $id)->where(static::$parentKey, $pid)->get();
        if (count($data)) {
            return $data[0];
        }
        return array();

    }

    /**
     * get filtered list
     *
     * @return  array
     */
    public static function filter()
    {
        $fData = static::$fieldData;
        $db = static::sorting();
        $filters = Filters::getAllData();
        foreach ($filters as $key => $value) {
            if (array_key_exists($key, $fData)) {

                if ($fData['type'] == 'bool') {
                    if (!isset($db)) {
                        $db = static::where($key, ($value == 'false' ? 0 : 1));
                    } else {
                        $db->where($key, ($value == 'false' ? 0 : 1));
                    }
                } else {
                    if (!is_array($value)) {
                        $value = array($value);
                    }
                    if (!isset($db)) {
                        $db = static::where($key, $value, 'IN');
                    } else {
                        $db->where($key, $value, 'IN');
                    }
                }

            }
        }

        $start = Query::get('start');
        $end = Query::get('end');
        if ($start == null && $end == null) {
            $data = $db->get();
        } else {
            $data = $db->get(array($start, ($end - $start)));
        }

        $returnArr = array();
        if (count($data)) {
            foreach ($data as $n) {
                $returnArr[] = $n->makeArray();
            }
        }

        return $returnArr;

    }

    /**
     * get total count
     *
     * @return  array
     */
    public static function total()
    {
        $db = Database::getInstance();
        $fData = static::$fieldData;
        $filters = Filters::getAllData();
        foreach ($filters as $key => $value) {
            if (array_key_exists($key, $fData)) {
                if (!is_array($value)) {
                    $value = array($value);
                }
                $db->where($key, $value, 'IN');
            }
        }
        if (isset(static::$parentKey) && Query::field(static::$parentKey)) {
            $parentId = Query::field(static::$parentKey);
            $db->where(static::$parentKey, $parentId);
        }
        $queryFields = Query::get('fields');
        if (null !== $queryFields) {
            foreach ($queryFields as $key => $value) {
                if (isset(static::$parentKey) && $key == static::$parentKey) {
                    continue;
                }
                if (array_key_exists($key, $fData)) {
                    if ($fData[$key]['type'] == 'bool') {
                        $db->where($key, ($value == 'false' ? 0 : 1));
                    } else {
                        $db->where($key, $value);
                    }
                }
            }
        }

        return $db->getValue(static::tableName(), "count(*)");

    }

    /**
     * apply sorting
     *
     * @return  model
     */
    public static function sorting($defaultField = 'id', $defaultOrder = 'ASC')
    {
        $fData = static::$fieldData;
        $sort = Query::get('sort');
        $order = Query::get('order');

        if ($sort !== null && array_key_exists($sort, $fData)) {
            $db = static::orderBy($sort, ($order !== null ? $order : $defaultOrder));
        } else {
            $db = static::orderBy($defaultField, $defaultOrder);
        }

        return $db;

    }

    /**
     * apply sorting
     *
     * @return  model
     */
    public static function queryFields()
    {
        $queryFields = Query::get('fields');
        if (null !== $queryFields) {
            foreach ($queryFields as $key => $value) {
                if (isset(static::$parentKey) && $key == static::$parentKey) {
                    continue;
                }
                if (array_key_exists($key, $fData)) {
                    if ($fData[$key]['type'] == 'bool') {
                        $this->where($key, ($value == 'false' ? 0 : 1));
                    } else {
                        $this->where($key, $value);
                    }
                }
            }
        }

    }

    /**
     * get data with limit params
     *
     * @return  model
     */
    public function getWithLimit()
    {
        $start = Query::get('start');
        $end = Query::get('end');
        if ($start == null && $end == null) {
            return $this->get();
        } else {
            return $this->get(array($start, ($end - $start)));
        }

    }

    /**
     * get child records by parent key
     *
     * @return  void
     */
    public function byParent($pid)
    {
        return $this->where(static::$parentKey, $pid);
    }

    /**
     * if there are errors exit
     *
     */
    protected function outputErrors()
    {
        if (count($this->errors)) {
            foreach ($this->errors as $key => $val) {
                Errors::add($key . ' ' . $val);
            }
            Errors::exitWithErrors();
        }
    }

    /**
     * prepare the update of the model with input data
     *
     * @return  array the compiled fields
     *
     */
    protected function prepareUpdate()
    {
        $newData = static::processInputData($this);
        if (Errors::hasError()) {
            Errors::exitWithErrors();
        }
        $newData['changed'] = time();
        return $newData;
    }

    /**
     * compare existing and new input for changes
     *
     * @return  bool
     *
     */
    protected function wasModified($newData)
    {
        $diff = array_diff_assoc($newData, $this->toArray());
        unset($diff['changed']);
        return (count($diff) > 0);
    }

    /**
     * compile the array for input from json
     *
     * @param   $existingObj The Model Object
     * @return  array the compiled fields
     *
     */
    protected static function newResource()
    {
        $fields = static::processInputData();

        $fields['created'] = time();
        $fields['changed'] = time();
        return $fields;
    }

    /**
     * compile the array for input from json
     *
     * @param   $existingObj The Model Object
     * @return  array the compiled fields
     *
     */
    protected static function processInputData(Model $existingObj = null)
    {
        $fields = array();
        $method = Setting::get('method');
        foreach (static::$fieldData as $key => $arr) {
            if (isset($arr['systemOnly']) && $arr['systemOnly']) {
                continue;
            }
            if (isset($arr['primaryKey']) || isset($arr['foreignColumn'])) {
                continue;
            }
            if (isset($arr['omit']) && is_string($arr['omit'])) {
                $methods = Utils::crudToMethods($arr['omit']);
                if (in_array($method, $methods)) {
                    continue;
                }
            }
            $input = Input::get($key);
            if ($input !== null) {

                if (isset($arr['type'])) {
                    $input = static::processInputValue($arr, $input);
                    if ($input === false) {
                        continue;
                    }
                }
                if (isset($arr['inputCallback'])) {
                    if (is_string($arr['inputCallback'])) {
                        if (function_exists($arr['inputCallback'])) {
                            $input = $arr['inputCallback']($input);
                        }
                    } else {
                        $class = $arr['inputCallback'][0];
                        $fn = $arr['inputCallback'][1];
                        $input = $class::$fn($input);
                    }

                }
                if (isset($arr['accepted'])) {
                    if (!in_array($input, $arr['accepted'])) {
                        if (isset($arr['explanation'])) {
                            Errors::add(Translate::get('models.inputcheck.explanation', $key, Translate::get($arr['explanation'])));
                        } else {
                            Errors::add(Translate::get('models.inputcheck.accepted', $key, join(' or ', $arr['accepted'])));
                        }

                    }
                }
                if (isset($arr['unique']) && $arr['unique']) {
                    if ($existingObj == null || !$existingObj instanceof Model) {
                        $className = get_called_class();
                        $existingObj = new $className();
                    }
                    $db = Database::getInstance();
                    if (($method == 'put' || $method == 'patch')) {
                        $db->where('id', $existingObj->data['id'], '!=');
                    }
                    $db->where($key, $input);
                    $duplicate = $db->getValue($existingObj->dbTable, "count(*)");
                    if ($duplicate) {
                        Errors::add(Translate::get('models.inputcheck.duplicate', $key, $GLOBALS['CONFIG']['DB']['PREFIX'] . $existingObj->dbTable));
                    }
                }
                if (isset($arr['required']) && $arr['required']) {
                    if (is_array($input) && !count($input)) {
                        Errors::add(Translate::get('models.inputcheck.required', $key));
                    } elseif (strlen(trim($input)) < 1) {
                        Errors::add(Translate::get('models.inputcheck.required', $key));
                    }
                }
            } elseif ($method == 'post') {
                if (isset($arr['default'])) {
                    $input = $arr['default'];
                } elseif (isset($arr['defaultFunction']) && function_exists($arr['defaultFunction'])) {
                    $input = $arr['defaultFunction']();
                } elseif (isset($arr['required']) && $arr['required']) {
                    Errors::add('Field \'' . $key . '\' must not be empty.');
                } else {
                    continue;
                }
            }
            if (!strlen($input)) {
                continue;
            }
            $fields[$key] = $input;
        }

        return $fields;
    }

    /**
     * process input value according to field specs as defined in static::$fieldData
     *
     * @param $arr current field data object
     * @param $input current field input
     * @return  mixed
     */
    protected static function processInputValue($arr, $input)
    {
        switch ($arr['type']) {
            case "password":
                if ($input == ServiceUser::PASSWORD_RETURN) {
                    return false;
                }
                $input = Utils::hashPassword($input);
                break;
            case "bool":
                $input = strval((boolval($input) ? '1' : '0'));
                break;
            default:
                break;
        }
        return $input;
    }

    /**
     * process output value according to field specs as defined in static::$fieldData
     *
     * @param $arr current field data object
     * @param $output current field output
     * @return  mixed
     */
    protected static function processOutputValue($arr, $output)
    {
        switch ($arr['type']) {
            case "password":
                $output = ServiceUser::PASSWORD_RETURN;
                break;
            case "bool":
                $output = boolval((int) $output);
                break;
            default:
                break;
        }
        return $output;
    }

    /**
     * get db table name referring to model
     *
     * @param array $fields
     * @return  string
     */
    public function getTableName()
    {
        return $GLOBALS['CONFIG']['DB']['PREFIX'] . $this->dbTable;
    }

    /**
     * output resource location
     *
     * @param array $fields
     * @return  array
     */
    public static function outputLocationResource($fields)
    {
        $location = static::locationResource($fields);
        if ($location != null) {
            if (is_array($location) && count($location) == 1) {
                $location = $location[0];
            }
            $fields['location'] = $location;
            return $fields;
        }
    }

    /**
     * output resource location
     *
     * @param array $fields
     * @return  array
     */
    public static function childLocationCollection($fields)
    {
        $className = get_called_class();
        $class = substr($className, strrpos($className, '\\') + 1);
        $name = str_replace('Model', '', $class);

        $locations = Registry::getChildCollectionResources($name);

        if ($locations === null) {
            return null;
        }
        foreach ($locations as $key => $location) {
            $temp = static::replacePlaceholderId($location, $fields);
            if ($temp === null) {
                continue;
            }
            $return[$key] = $temp;
        }
        return (isset($return) && is_array($return) ? $return : null);
    }

    /**
     * find resource location for output by current model name
     *
     * @return  string
     */
    public static function locationResource($fields)
    {
        $className = get_called_class();
        $class = substr($className, strrpos($className, '\\') + 1);
        $name = str_replace('Model', '', $class);

        $locations = Registry::getResourceLocation($name);

        if ($locations === null) {
            return null;
        }
        return static::replacePlaceholderId($locations, $fields);
    }

    /**
     * replace the placeholder strings {id}
     *
     * @param   string $location
     * @param   array $fields
     * @return  array|null
     *
     */
    public static function replacePlaceholderId($locations, $fields)
    {
        foreach ($locations as $location) {
            $ids = substr_count($location, "{id}");
            if ($ids === 0) {
                $location = $location;
            }
            if ($ids > 2) {
                return null;
            }
            if ($ids == 2 && isset($fields[static::$parentKey])) {
                $location = preg_replace("/{id}/", $fields[static::$parentKey], $location, 1);
            }
            if (isset($fields['id'])) {
                $location = preg_replace("/{id}/", $fields['id'], $location, 1);
            }
            if (strpos($location, "{") === false) {
                $return[] = Utils::getHostAndApiPath() . '/' . $location;
            }
        }
        if (isset($return) && is_array($return) && count($return) == 1) {
            $return = $return[0];
        }
        return (isset($return) ? $return : null);
    }

}
