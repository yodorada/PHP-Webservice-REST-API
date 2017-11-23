<?php

/*
 * Part of PHP-Webservice-REST-API
 *
 * Copyright (c) Maya K. Herrmann | EINS[23].TV
 *
 * @license LGPL-3.0+
 */

namespace Yodorada\Controller;

use Yodorada\Classes\Errors;
use Yodorada\Classes\Input;
use Yodorada\Classes\Translate;
use Yodorada\Classes\Utils;
use Yodorada\Models\FilesModel;
use Yodorada\Modules\ServiceUser;

/**
 * class FilesController
 * @package   Yodorada\Webservice
 * @author    EINS[23].TV | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright EINS[23].TV, 2017
 * @version 0.0.1
 *
 */
class FilesController extends Controller implements ControllerInterface
{

    protected $selfInfo = 'controller.files.self_info';

    public static $version = '0.0.1';

    protected $files = array();

    /**
     * check Files upload
     *
     *
     */
    protected function processMultiPartUploads()
    {
        if (!isset($_FILES) || !count($_FILES)) {
            Errors::exitWith(Translate::get('controller.files.no_uploads'));
        }
        $uploadPath = PUBLIC_ROOT . '/' . $GLOBALS['CONFIG']['UPLOADS']['PATH'];

        $saves = array();
        foreach ($_FILES as $name => $file) {
            if (is_uploaded_file($file['tmp_name'])) {
                $baseName = Utils::urlFriendly(basename($file['name']));
                $filetype = $this->checkFileUploadedType($baseName);
                if (!$filetype) {
                    Errors::exitWith(Translate::get('controller.files.wrong_filetype', str_replace('|', ', ', $GLOBALS['CONFIG']['UPLOADS']['ALLOWED'])));
                }
                $filenameTooLong = $this->checkFileUploadedNameLength($baseName);
                if ($filenameTooLong) {
                    Errors::exitWith(Translate::get('controller.files.filename_long'));
                }
                $saves[] = array('tmp' => $_FILES[$name]['tmp_name'], 'name' => $baseName);

            }
        }
        if (count($saves)) {
            $files = array();
            foreach ($saves as $save) {
                $new = true;
                if (file_exists($uploadPath . '/' . $save['name'])) {
                    $new = false;
                }
                $saved = move_uploaded_file($save['tmp'], $uploadPath . '/' . $save['name']);
                if ($saved) {
                    static::$logMessages[] = 'Upload: ' . $GLOBALS['CONFIG']['UPLOADS']['PATH'] . '/' . $save['name'];
                    $this->files[] = array('path' => $GLOBALS['CONFIG']['UPLOADS']['PATH'] . '/' . $save['name'], 'name' => $save['name'], 'new' => $new);
                }
            }
        } else {
            Errors::exitWith(Translate::get('controller.files.no_uploads'));
        }

    }

    /**
     * check Files upload
     *
     *
     */
    protected function processBase64Uploads()
    {
        $uploads = Input::get('pictures');

        if (!count($uploads)) {
            Errors::exitWith(Translate::get('controller.files.no_uploads'));
        }

        $uploadPath = PUBLIC_ROOT . '/' . $GLOBALS['CONFIG']['UPLOADS']['PATH'];

        $saves = array();
        foreach ($uploads as $file) {
            $baseName = Utils::urlFriendly(basename($file['name']));
            $filetype = ($this->checkBase64UploadedType($file['src']) || $this->checkFileUploadedType($file['name']));
            if (!$filetype) {
                Errors::exitWith(Translate::get('controller.files.wrong_filetype', str_replace('|', ', ', $GLOBALS['CONFIG']['UPLOADS']['ALLOWED'])));
            }
            $filenameTooLong = $this->checkFileUploadedNameLength($file['name']);
            if ($filenameTooLong) {
                Errors::exitWith(Translate::get('controller.files.filename_long'));
            }
            $tempImg = base64_decode(explode(',', $file['src'], 2)[1]);
            $saves[] = array('tmp' => $tempImg, 'name' => $file['name']);

        }
        if (count($saves)) {
            $files = array();
            foreach ($saves as $save) {
                $new = true;
                if (file_exists($uploadPath . '/' . $save['name'])) {
                    $new = false;
                }
                $saved = file_put_contents($uploadPath . '/' . $save['name'], $save['tmp']);
                if ($saved) {
                    static::$logMessages[] = 'Upload: ' . $GLOBALS['CONFIG']['UPLOADS']['PATH'] . '/' . $save['name'];
                    $this->files[] = array('path' => $GLOBALS['CONFIG']['UPLOADS']['PATH'] . '/' . $save['name'], 'name' => $save['name'], 'new' => $new);
                }
            }
        } else {
            Errors::exitWith(Translate::get('controller.files.no_uploads'));
        }

    }

    protected function checkFileUploadedName($filename)
    {
        (bool) ((preg_match("/^[a-zA-Z0-9_.-]+$/", $filename)) ? true : false);
    }

    protected function checkFileUploadedNameLength($filename)
    {
        return (bool) ((mb_strlen($filename, "UTF-8") > 225) ? true : false);
    }

    protected function checkFileUploadedType($filename)
    {
        return (bool) ((preg_match("/^.*\.(" . $GLOBALS['CONFIG']['UPLOADS']['ALLOWED'] . ")$/", $filename)) ? true : false);
    }

    protected function checkBase64UploadedType($str)
    {
        $pos = strpos($str, ';');
        $type = explode(':', substr($str, 0, $pos))[1];
        return (bool) ((preg_match("/^.*\/(" . $GLOBALS['CONFIG']['UPLOADS']['ALLOWED'] . ")$/", $type)) ? true : false);
    }

    /**
     * method GET
     *
     */
    public function get()
    {
        if ($this->resourceId) {
            // show single upload entry
            $entries = FilesModel::byId($this->resourceId);
            if (!count($entries)) {
                Errors::exitNotFound(Translate::get('controller.files.no_resource', $this->resourceId));
            }
            return $entries->makeArray();
        }

        // show collection
        $entries = FilesModel::sorting("filename", "asc")->getWithLimit();
        if (count($entries)) {
            $returnArr = array();
            foreach ($entries as $n) {
                $returnArr[] = $n->makeArray();
            }
            return $returnArr;
        }

        // return empty data
        return array();
    }

    /**
     * method POST
     *
     */
    public function post()
    {
        if ($GLOBALS['CONFIG']['UPLOADS']['BASE64']) {
            $this->processBase64Uploads();
        } else {
            $this->processMultiPartUploads();
        }
        $uploads = array();

        foreach ($this->files as $file) {
            $hash = md5($file['name']);
            if ($file['new']) {
                $newData = array();
                $newData['created'] = time();
                $newData['changed'] = time();
                $newData['usersId'] = ServiceUser::get('id');
                $newData['hash'] = $hash;
                $newData['path'] = $file['path'];
                $newData['filename'] = $file['name'];

                $fileModel = new FilesModel($newData);
                $newData['id'] = $fileModel->save();

                $arr = FilesModel::outputLocationResource($newData);
                $arr['path'] = Utils::getFilePaths($fileModel->path);
                // return just the last of the new uploads
                $uploads = $arr;
            } else {
                $res = FilesModel::where('hash', $hash)->getOne();
                if (count($res)) {
                    $res->changed = time();
                    $res->usersId = ServiceUser::get('id');
                    $res->save();
                    $arr = FilesModel::outputLocationResource($res->toArray());
                    $arr['path'] = Utils::getFilePaths($res->path);
                    $uploads = $arr;
                }
            }

        }
        return $uploads;

    }

    /**
     * method PUT
     *
     */
    public function put()
    {}

    /**
     * method DELETE
     *
     */
    public function delete()
    {
        if ($this->resourceId) {
            $entries = FilesModel::byId($this->resourceId);
            if (!count($entries)) {
                Errors::exitNotFound(Translate::get('controller.files.no_resource', $this->resourceId));
            }
            $path = $entries->path;

            $file = @unlink(PUBLIC_ROOT . '/' . $entries->path);
            $status = $entries->delete();

            if (!$status) {
                $this->outputErrors();
            }
            return $entries->makeArray();
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
        return FilesModel::filter();
    }

    /**
     * method GET collection total count
     *
     */
    public function total()
    {
        return FilesModel::total();
    }

    /***
     * get fields
     *
     */
    public function fields()
    {
        return FilesModel::getFieldsInfo();
    }
}
