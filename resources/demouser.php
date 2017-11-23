<?php

/**
 * Part of ein23-web-service
 *
 * Copyright (c) Maya K. Herrmann | EINS[23].TV
 *
 * @license LGPL-3.0+
 *
 * @author    EINS[23].TV | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright EINS[23].TV, 2017
 * @version 0.1.0
 */

/** @var Composer\Autoload\ClassLoader */
require __DIR__ . '/../../app/vendor/autoload.php';

use Yodorada\Classes\Headers;
use Yodorada\Classes\Utils;

Headers::contentType('text/plain');

$adminPw = 'adminPassword99';
$editorPw = 'editorPassword99';
$userPw = 'userPassword99';

$admin = Utils::hashPassword($adminPw);
$editor = Utils::hashPassword($editorPw);
$user = Utils::hashPassword($userPw);

?>

-- --------------------------------------------------------
-- Execute in your webservice database for demo purposes
-- --------------------------------------------------------

--
-- Inserts into `re_groups` and `re_users`
-- Initial Groups / Users
-- Group: Admins
-- Name: admin
-- Password: <?php echo $adminPw . PHP_EOL; ?>
--
-- Group: Editors
-- Name: editor
-- Password: <?php echo $editorPw . PHP_EOL; ?>
--
-- Group: Users
-- Name: user
-- Password: <?php echo $userPw . PHP_EOL; ?>
--

INSERT INTO `re_groups` (`id`, `created`, `changed`, `enabled`, `groupname`, `role`) VALUES
(1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), '1', 'Admins', 100);

INSERT INTO `re_groups` (`id`, `created`, `changed`, `enabled`, `groupname`, `role`) VALUES
(2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), '1', 'Editors', 200);

INSERT INTO `re_groups` (`id`, `created`, `changed`, `enabled`, `groupname`, `role`) VALUES
(3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), '1', 'Users', 300);

INSERT INTO `re_groups_rights` (`id`, `groupsId`, `created`, `changed`, `rights`) VALUES
(1, 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), ''),
(2, 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), '');


INSERT INTO `re_users` (`id`, `groupsId`, `created`, `changed`, `username`, `email`, `password`, `enabled`, `overrideGroupRights`, `locked`, `lastLogin`, `confirmationToken`, `passwordRequestedAt`) VALUES
(1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'admin', 'admin@hello.world', '<?php echo $admin; ?>', 1, 0, 0, NULL, NULL, NULL);

INSERT INTO `re_users` (`id`, `groupsId`, `created`, `changed`, `username`, `email`, `password`, `enabled`, `overrideGroupRights`, `locked`, `lastLogin`, `confirmationToken`, `passwordRequestedAt`) VALUES
(2, 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'editor', 'editor@hello.world', '<?php echo $editor; ?>', 1, 0, 0, NULL, NULL, NULL);

INSERT INTO `re_users` (`id`, `groupsId`, `created`, `changed`, `username`, `email`, `password`, `enabled`, `overrideGroupRights`, `locked`, `lastLogin`, `confirmationToken`, `passwordRequestedAt`) VALUES
(3, 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'user', 'user@hello.world', '<?php echo $user; ?>', 1, 0, 0, NULL, NULL, NULL);

INSERT INTO `re_users_rights` (`id`, `usersId`, `created`, `changed`, `rights`) VALUES
(1, 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), ''),
(2, 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), '');