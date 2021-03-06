

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `re_access_token`
--

CREATE TABLE `re_access_token` (
  `id` int(11) UNSIGNED NOT NULL,
  `usersId` int(11) DEFAULT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `expiresAt` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `re_files`
--

CREATE TABLE `re_files` (
  `id` int(11) UNSIGNED NOT NULL,
  `usersId` int(11) NOT NULL,
  `created` int(10) UNSIGNED NOT NULL,
  `changed` int(10) UNSIGNED NOT NULL,
  `hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `re_groups`
--

CREATE TABLE `re_groups` (
  `id` int(11) UNSIGNED NOT NULL,
  `created` int(10) UNSIGNED NOT NULL,
  `changed` int(10) UNSIGNED NOT NULL,
  `enabled` tinyint(1) DEFAULT NULL,
  `groupname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `role` int(10) UNSIGNED NOT NULL,
  `isFilemaker` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `re_groups_rights`
--

CREATE TABLE `re_groups_rights` (
  `id` int(11) UNSIGNED NOT NULL,
  `groupsId` int(11) NOT NULL,
  `created` int(10) UNSIGNED NOT NULL,
  `changed` int(10) UNSIGNED NOT NULL,
  `rights` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `re_logs`
--

CREATE TABLE `re_logs` (
  `id` int(11) UNSIGNED NOT NULL,
  `created` int(11) NOT NULL,
  `usersId` int(10) NOT NULL,
  `resource` varchar(120) COLLATE utf8_unicode_ci NOT NULL,
  `scope` varchar(120) COLLATE utf8_unicode_ci NOT NULL,
  `method` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `controller` varchar(24) COLLATE utf8_unicode_ci NOT NULL,
  `version` varchar(24) COLLATE utf8_unicode_ci NOT NULL,
  `status` varchar(24) COLLATE utf8_unicode_ci NOT NULL,
  `httpStatusCode` smallint(6) NOT NULL,
  `httpStatusString` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `message` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `dataTransfer` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `re_users`
--

CREATE TABLE `re_users` (
  `id` int(11) UNSIGNED NOT NULL,
  `groupsId` int(11) NOT NULL,
  `created` int(10) NOT NULL,
  `changed` int(10) NOT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `enabled` tinyint(1) DEFAULT NULL,
  `overrideGroupRights` tinyint(1) DEFAULT NULL,
  `locked` int(10) NOT NULL,
  `lastLogin` int(10) DEFAULT NULL,
  `confirmationToken` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `passwordRequestedAt` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `re_users_rights`
--

CREATE TABLE `re_users_rights` (
  `id` int(11) UNSIGNED NOT NULL,
  `usersId` int(11) NOT NULL,
  `created` int(10) UNSIGNED NOT NULL,
  `changed` int(10) UNSIGNED NOT NULL,
  `rights` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `re_access_token`
--
ALTER TABLE `re_access_token`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_token` (`token`),
  ADD KEY `indx_user` (`usersId`);

--
-- Indizes für die Tabelle `re_files`
--
ALTER TABLE `re_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `indx_user` (`usersId`),
  ADD KEY `indx_hash` (`hash`);

--
-- Indizes für die Tabelle `re_groups`
--
ALTER TABLE `re_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `re_groups_rights`
--
ALTER TABLE `re_groups_rights`
  ADD PRIMARY KEY (`id`),
  ADD KEY `indx_group` (`groupsId`);

--
-- Indizes für die Tabelle `re_logs`
--
ALTER TABLE `re_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `re_users`
--
ALTER TABLE `re_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `indx_group` (`groupsId`);

--
-- Indizes für die Tabelle `re_users_rights`
--
ALTER TABLE `re_users_rights`
  ADD PRIMARY KEY (`id`),
  ADD KEY `indx_user` (`usersId`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `re_access_token`
--
ALTER TABLE `re_access_token`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT für Tabelle `re_files`
--
ALTER TABLE `re_files`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT für Tabelle `re_groups`
--
ALTER TABLE `re_groups`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT für Tabelle `re_groups_rights`
--
ALTER TABLE `re_groups_rights`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT für Tabelle `re_logs`
--
ALTER TABLE `re_logs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT für Tabelle `re_users`
--
ALTER TABLE `re_users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT für Tabelle `re_users_rights`
--
ALTER TABLE `re_users_rights`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;