<?php

/*
 * Part of PHP-Webservice-REST-API
 *
 * Copyright (c) Maya K. Herrmann | Yodorada
 *
 * @license LGPL-3.0+
 */

/**
 * TOKEN SPECS
 */
$GLOBALS['CONFIG']['TOKEN']['EXPIRES'] = 2592000; // 30 days
$GLOBALS['CONFIG']['TOKEN']['SECRET_KEY'] = 'change-this-2eb0d989122ef95382bfse3fwagf35edf96ecae0c9a086fc0fdfd61db97c';
/**
 * API SPECS
 */
$GLOBALS['CONFIG']['API']['USE_SSL'] = true;
$GLOBALS['CONFIG']['API']['HAS_OWN_DOMAIN'] = false;
$GLOBALS['CONFIG']['API']['DIRECTORY'] = 'api/';
$GLOBALS['CONFIG']['API']['ADMIN_KEY'] = 'lj-l35najvsljq3rpoujg530ubrhAs-fldkh23r'; // base64 >> bGotbDM1bmFqdnNsanEzcnBvdWpnNTMwdWJyaEFzLWZsZGtoMjNy
$GLOBALS['CONFIG']['API']['FRONTEND_KEY'] = 'xEKc-sUVDD2_rZaoTa46gCxsVQi7hwz8gsE-Ei8'; // base64 >> eEVLYy1zVVZERDJfclphb1RhNDZnQ3hzVlFpN2h3ejhnc0UtRWk4
$GLOBALS['CONFIG']['APPLICATION']['LANGUAGE'] = 'de';
/**
 * APPLICATION SPECS
 */
$GLOBALS['CONFIG']['APPLICATION']['HOST'] = ''; // see README.md file
$GLOBALS['CONFIG']['APPLICATION']['AOR_BACKEND'] = false;
$GLOBALS['CONFIG']['APPLICATION']['FLAT_HIERARCHY'] = true; // see README.md file
$GLOBALS['CONFIG']['APPLICATION']['OUTPUT']['FORMAT'] = 'json';
$GLOBALS['CONFIG']['APPLICATION']['OUTPUT']['XML_SELF_CLOSING_TAGS'] = false;
$GLOBALS['CONFIG']['APPLICATION']['OUTPUT']['ADD_RESOURCE_LOCATION'] = false; // see README.md file
$GLOBALS['CONFIG']['APPLICATION']['OUTPUT']['ADD_WEBSERVICE_STATS'] = false; // see README.md file
/**
 * DATA PATH
 */
$GLOBALS['CONFIG']['DATA']['PATH'] = 'assets/data'; // standard is "assets/data" inside dir public/
$GLOBALS['CONFIG']['DATA']['VERSION'] = '1.1';
/**
 * UPLOAD PATH
 */
$GLOBALS['CONFIG']['UPLOADS']['BASE64'] = true; // if true, the upload controller expects base64 encoded media like so: { src: ...base64-string... , title: filename }
$GLOBALS['CONFIG']['UPLOADS']['ALLOWED'] = 'jpg|jpeg|png|gif|svg|pdf'; // separate filetypes with |
$GLOBALS['CONFIG']['UPLOADS']['PATH'] = 'assets/uploads'; // standard is "assets/uploads" inside dir public/
/**
 * EMAIL SPECS
 */
$GLOBALS['CONFIG']['MAILER']['USE_SWIFT'] = true; // using swiftmailer (or sendmail when false)
$GLOBALS['CONFIG']['MAILER']['USE_SMTP'] = false; // using swiftmailers smtp (or sendmail when false)
$GLOBALS['CONFIG']['MAILER']['SMTP_HOST'] = ''; // required when smtp = true
$GLOBALS['CONFIG']['MAILER']['SMTP_USERNAME'] = ''; // required when smtp = true
$GLOBALS['CONFIG']['MAILER']['SMTP_PASSWORD'] = ''; // required when smtp = true
$GLOBALS['CONFIG']['MAILER']['MAIL_FROM'] = ''; // set default sender's address
$GLOBALS['CONFIG']['MAILER']['MAIL_FROM_NAME'] = ''; // set default sender's name
$GLOBALS['CONFIG']['MAILER']['MAIL_REPLYTO'] = ''; // set default replyto address (if empty the sender's address will be used)
$GLOBALS['CONFIG']['MAILER']['MAIL_SUBJECT'] = ''; // set default email subject
