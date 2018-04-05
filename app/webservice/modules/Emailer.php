<?php

/*
 * Part of PHP-Webservice-REST-API
 *
 * Copyright (c) Maya K. Herrmann | Yodorada
 *
 * @license LGPL-3.0+
 */

namespace Yodorada\Modules;

use \Swift_Mailer;
use \Swift_MailTransport;
use \Swift_Message;
use \Swift_SmtpTransport;
use \Swift_Validate;

/**
 * class Emailer
 * @package   Yodorada\Webservice
 * @author    Yodorada | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright Yodorada, 2018
 * @version 0.0.1
 */
class Emailer
{

    /**
     * Emailer object
     * @var Emailer
     */
    protected static $mail;

    /**
     * SwiftMessage object
     * @var Swift_Message
     */
    protected $swiftMsg;

    /**
     * SwiftMessage fails/errors
     * @var array
     */
    protected $swiftFailures;

    /**
     * Current data
     * @var array
     */
    protected $arrData = array();

    /**
     * Instantiate the object and load the mailer framework
     */
    public function __construct()
    {

        // Instantiate self mail
        if (self::$mail === null) {
            if (!$GLOBALS['CONFIG']['MAILER']['USE_SMTP']) {
                // Mail via sendmail
                $transport = \Swift_MailTransport::newInstance();
            } else {
                // mail via smtp
                $transport = \Swift_SmtpTransport::newInstance($GLOBALS['CONFIG']['MAILER']['SMTP_HOST'], $GLOBALS['CONFIG']['MAILER']['SMTP_PORT']);

                // use auth ?
                if ($GLOBALS['CONFIG']['MAILER']['SMTP_USERNAME'] != '') {
                    $transport->setUsername($GLOBALS['CONFIG']['MAILER']['SMTP_USERNAME'])->setPassword($GLOBALS['CONFIG']['MAILER']['SMTP_PASSWORD']);
                }
            }

            self::$mail = \Swift_Mailer::newInstance($transport);
        }

        $this->swiftMsg = \Swift_Message::newInstance();
        $this->swiftMsg->getHeaders()->addTextHeader('X-Mailer', 'Yodorada Webservice REST API');

        $this->initialize();
    }

    /**
     * set default object properties
     *
     */
    protected function initialize()
    {
        $this->set('subject', $GLOBALS['CONFIG']['MAILER']['MAIL_SUBJECT']);
        $this->set('from', $GLOBALS['CONFIG']['MAILER']['MAIL_FROM']);
        $this->set('fromName', $GLOBALS['CONFIG']['MAILER']['MAIL_FROM_NAME']);
    }

    /**
     * send email
     *
     */
    public function send()
    {
        if (
            !Swift_Validate::email($this->get('from')) ||
            !Swift_Validate::email($this->get('to')) ||
            ($this->get('cc') && !Swift_Validate::email($this->get('cc'))) ||
            ($this->get('bcc') && !Swift_Validate::email($this->get('bcc')))
        ) {
            throw new \Exception('Yodorada\Emailer could not send email due to faulty email address(es).');
        }
        if ($this->get('fromName') != '') {
            $this->swiftMsg->setFrom(
                array(
                    $this->get('from') => $this->get('fromName'),
                )
            );
        } else {
            $this->swiftMsg->setFrom($this->get('from'));
        }

        foreach ($this->arrData as $key => $value) {
            switch ($key) {
                case 'to':
                    $this->swiftMsg->setTo($value);
                    break;
                case 'cc':
                    $this->swiftMsg->setCc($value);
                    break;
                case 'bcc':
                    $this->swiftMsg->setBcc($value);
                    break;
                case 'replyTo':
                    $this->swiftMsg->setReplyTo($value);
                    break;
                case 'subject':
                    $this->swiftMsg->setSubject($this->get('subject'));
                    break;
                case 'body':
                    $this->swiftMsg->setBody($this->get('body'), 'text/plain');
                    break;

            }
        }

        $this->swiftMsg->setReturnPath($this->get('from'));
        $sent = self::$mail->send($this->swiftMsg, $this->swiftFailures);

        if (!empty($this->swiftFailures)) {
            error_log("Swift Mailer failed! Recipients: " . implode(', ', $this->swiftFailures), 0);
        }

        return $sent;

    }

    /**
     * Return all object properties
     *
     * @return array
     */
    public function getAllData()
    {
        return $this->arrData;

    }

    /**
     * Return bool if has data
     *
     * @return bool
     */
    public function hasData()
    {
        return (count($this->arrData) > 0);

    }

    /**
     * Set an object property
     *
     * @param string $strKey
     * @param mixed  $varValue
     */
    public function set($strKey, $varValue)
    {
        switch ($strKey) {
            case 'subject':
                $this->arrData[$strKey] = preg_replace(array('/[\t]+/', '/[\n\r]+/'), array(' ', ''), $varValue);
                break;

            case 'text':
                $this->arrData[$strKey] = html_entity_decode($varValue, ENT_COMPAT, 'utf-8');
                break;

            default:
                $this->arrData[$strKey] = $varValue;
        }

    }

    /**
     * Return an object prop
     *
     * @param string $strKey The variable name
     *
     * @return mixed The variable value
     */
    public function get($strKey)
    {
        if (isset($this->arrData[$strKey])) {
            return $this->arrData[$strKey];
        }
        if (in_array($strKey, get_class_methods('\Yodorada\Modules\Emailer'))) {
            $this->arrData[$strKey] = $this->$strKey();
        }
        if (!isset($this->arrData[$strKey])) {
            return null;
        }
        return $this->arrData[$strKey];
    }
}
