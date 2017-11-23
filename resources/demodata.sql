-- --------------------------------------------------------
-- Execute in your webservice database for demo purposes 
-- --------------------------------------------------------

CREATE TABLE `re_authors` (
  `id` int(11) UNSIGNED NOT NULL,
  `created` int(10) UNSIGNED NOT NULL,
  `changed` int(10) UNSIGNED NOT NULL,
  `authorname` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT charSET=utf8 COLLATE=utf8_unicode_ci;


ALTER TABLE `re_authors`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `re_authors`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;



CREATE TABLE `re_authors_aphorisms` (
  `id` int(11) UNSIGNED NOT NULL,
  `authorsId` int(10) NOT NULL,
  `created` int(10) UNSIGNED NOT NULL,
  `changed` int(10) UNSIGNED NOT NULL,
  `aphorism` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT charSET=utf8 COLLATE=utf8_unicode_ci;


ALTER TABLE `re_authors_aphorisms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `indx_authr` (`authorsId`);

ALTER TABLE `re_authors_aphorisms`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;



--
-- Inserts into `re_authors` and `re_authors_aphorisms`
-- 


INSERT INTO `re_authors` (`id`, `created`, `changed`, `authorname`) VALUES
(1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Buddha');

INSERT INTO `re_authors` (`id`, `created`, `changed`, `authorname`) VALUES
(2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Aristotle');

INSERT INTO `re_authors` (`id`, `created`, `changed`, `authorname`) VALUES
(3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Coco Chanel');

 
INSERT INTO `re_authors_aphorisms` (`id`, `authorsId`, `created`, `changed`, `aphorism`) VALUES
(1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Stay where you are, otherwise you will miss your life.');

INSERT INTO `re_authors_aphorisms` (`id`, `authorsId`, `created`, `changed`, `aphorism`) VALUES
(2, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Every human being is the author of his own health or disease.');

INSERT INTO `re_authors_aphorisms` (`id`, `authorsId`, `created`, `changed`, `aphorism`) VALUES
(3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Work out your own salvation. Do not depend on others.');

INSERT INTO `re_authors_aphorisms` (`id`, `authorsId`, `created`, `changed`, `aphorism`) VALUES
(4, 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Teaching is the highest form of understanding.');

INSERT INTO `re_authors_aphorisms` (`id`, `authorsId`, `created`, `changed`, `aphorism`) VALUES
(5, 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'A common danger unites even the bitterest enemies.');

INSERT INTO `re_authors_aphorisms` (`id`, `authorsId`, `created`, `changed`, `aphorism`) VALUES
(6, 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Elegance does not consist in putting on a new dress.');

INSERT INTO `re_authors_aphorisms` (`id`, `authorsId`, `created`, `changed`, `aphorism`) VALUES
(7, 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'I don\'t know why women want any of the things men have when one of the things that women have is men.');
