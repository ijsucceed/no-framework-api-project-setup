--
--  Drop any exist table
--
DROP TABLE IF EXISTS `app_users`;

--
-- Table structure for table `app_users`
--
CREATE TABLE `app_users` (
  `user_id` int(11) UNSIGNED NOT NULL,
  `user_firstname` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `user_lastname` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `user_email` varchar(140) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `user_pin` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `user_phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_country` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_status` int(6) UNSIGNED NOT NULL DEFAULT '0',
  `user_created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for table `app_users`
--
ALTER TABLE `app_users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_phone` (`user_phone`),
  ADD UNIQUE KEY `user_email` (`user_email`);

--
-- AUTO_INCREMENT for table `app_users`
--
ALTER TABLE `app_users`
  MODIFY `user_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
--  Drop any exist table
--
DROP TABLE IF EXISTS `app_user_meta`;

--
-- Table structure for table `app_users`
--
CREATE TABLE `app_user_meta` (
  `ID` int(11) UNSIGNED NOT NULL,
  `key` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `meta` JSON,
  `user_id` int(11) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for table `app_users`
--
ALTER TABLE `app_user_meta`
  ADD PRIMARY KEY (`ID`),
  ADD FOREIGN KEY (`user_id`) REFERENCES `app_users`(`user_id`);

--
-- AUTO_INCREMENT for table `app_users`
--
ALTER TABLE `app_user_meta`
  MODIFY `ID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- Table structure for table `app_user_requests`
--

CREATE TABLE `app_user_requests` (
  `ID` int(20) UNSIGNED NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT '',
  `user` varchar(100) NOT NULL,
  `code` varchar(255) NOT NULL DEFAULT '',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `app_user_requests`
--
ALTER TABLE `app_user_requests`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for table `app_user_requests`
--
ALTER TABLE `app_user_requests`
  MODIFY `ID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

