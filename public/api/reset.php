<?php

/**
 * Part of PHP-Webservice-REST-API
 * >>> Reset User Password
 *
 * Copyright (c) Maya K. Herrmann | Yodorada
 *
 * @license LGPL-3.0+
 *
 * @author    Yodorada | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright Yodorada, 2018
 * @version 0.1.0
 */

require __DIR__ . '/../../app/vendor/autoload.php';
/** load database login etc */
include_once __DIR__ . '/../../app/config/initialize.php';

use Yodorada\Classes\Database;
use Yodorada\Classes\Headers;
use Yodorada\Classes\Translate;
use Yodorada\Classes\Utils;
use Yodorada\Modules\ResetPassword;

Headers::contentType('text/html');
Translate::initialize();
Database::initialize();
ResetPassword::initialize();

/*
/ uncomment the following lines and insert your domain name
 */
// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     switch ($_SERVER['HTTP_ORIGIN']) {
//         case 'http://your.domain.tld':
//             $allowed = true;
//         default:
//             die('not allowed');
//     }
//     if (isset($allowed)) {
//         Headers::allowOrigin($_SERVER['HTTP_ORIGIN']);
//         Headers::allowMethods(array('post'));
//     }
// }
?>

<html>
	<head>
        <meta name="format-detection" content="telephone=no" />
        <meta name="msapplication-tap-highlight" content="no" />
        <meta charset="utf-8" />
        <meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width" />
        <link rel="stylesheet" type="text/css" href="<?php echo Utils::getApplicationAbsolutePath('assets/css/theme.css'); ?>">
        <title><?php echo Translate::get('pages.resetpassword.title'); ?></title>
    </head>
    <body>
        <div id="app">
        	<div id="header">
        		<div id="logo">- logo -</div>
        	</div>
            <div id="wrapper">

                <div id="inner">

                	<?php if (ResetPassword::get('error') !== null): ?>
						<div class="errorMsg"><?php echo Translate::get(ResetPassword::get('error')); ?></div>
					<?php endif;?>

                	<?php if (ResetPassword::valid()): ?>
                		<?php if (ResetPassword::resetted()): ?>
                			<h2><?php echo Translate::get('pages.resetpassword.resetted'); ?></h2>
                		<?php else: ?>
							<div class="formwrapper">
								<h2><?php echo Translate::get('pages.resetpassword.headline'); ?></h2>
								<form action="<?php echo $_SERVER['PHP_SELF']; ?>?token=<?php echo ResetPassword::get('user')['confirmationToken']; ?>" method="post" class="form">
									<input type="hidden" name="action" value="resetpassword">
									<div class="inner">
										<div class="inputfield">
											<input type="password" name="password" id="password" value="">
											<label for="password"><?php echo Translate::get('pages.resetpassword.label_newpassword'); ?></label>
										</div>
										<div class="inputfield">
											<input type="password" name="passwordConfirm" id="passwordConfirm" value="">
											<label for="passwordConfirm"><?php echo Translate::get('pages.resetpassword.label_confirm'); ?></label>
										</div>
										<input type="submit" class="submit" name="submit" value="<?php echo Translate::get('pages.resetpassword.submit'); ?>">
										<div class="hintMsg"><?php echo Translate::get('pages.resetpassword.hint'); ?></div>
									</div>
								</form>
							</div>
                		<?php endif;?>
					<?php endif;?>
                </div>

            </div>
        </div>


	</body>
</html>
