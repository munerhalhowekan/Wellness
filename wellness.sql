-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 04, 2025 at 04:43 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `phpmyadmin`
--
CREATE DATABASE IF NOT EXISTS `phpmyadmin` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE `phpmyadmin`;

-- --------------------------------------------------------

--
-- Table structure for table `pma__bookmark`
--

CREATE TABLE `pma__bookmark` (
  `id` int(10) UNSIGNED NOT NULL,
  `dbase` varchar(255) NOT NULL DEFAULT '',
  `user` varchar(255) NOT NULL DEFAULT '',
  `label` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `query` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Bookmarks';

-- --------------------------------------------------------

--
-- Table structure for table `pma__central_columns`
--

CREATE TABLE `pma__central_columns` (
  `db_name` varchar(64) NOT NULL,
  `col_name` varchar(64) NOT NULL,
  `col_type` varchar(64) NOT NULL,
  `col_length` text DEFAULT NULL,
  `col_collation` varchar(64) NOT NULL,
  `col_isNull` tinyint(1) NOT NULL,
  `col_extra` varchar(255) DEFAULT '',
  `col_default` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Central list of columns';

-- --------------------------------------------------------

--
-- Table structure for table `pma__column_info`
--

CREATE TABLE `pma__column_info` (
  `id` int(5) UNSIGNED NOT NULL,
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `column_name` varchar(64) NOT NULL DEFAULT '',
  `comment` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `mimetype` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `transformation` varchar(255) NOT NULL DEFAULT '',
  `transformation_options` varchar(255) NOT NULL DEFAULT '',
  `input_transformation` varchar(255) NOT NULL DEFAULT '',
  `input_transformation_options` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Column information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__designer_settings`
--

CREATE TABLE `pma__designer_settings` (
  `username` varchar(64) NOT NULL,
  `settings_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Settings related to Designer';

-- --------------------------------------------------------

--
-- Table structure for table `pma__export_templates`
--

CREATE TABLE `pma__export_templates` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL,
  `export_type` varchar(10) NOT NULL,
  `template_name` varchar(64) NOT NULL,
  `template_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved export templates';

-- --------------------------------------------------------

--
-- Table structure for table `pma__favorite`
--

CREATE TABLE `pma__favorite` (
  `username` varchar(64) NOT NULL,
  `tables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Favorite tables';

-- --------------------------------------------------------

--
-- Table structure for table `pma__history`
--

CREATE TABLE `pma__history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `db` varchar(64) NOT NULL DEFAULT '',
  `table` varchar(64) NOT NULL DEFAULT '',
  `timevalue` timestamp NOT NULL DEFAULT current_timestamp(),
  `sqlquery` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='SQL history for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__navigationhiding`
--

CREATE TABLE `pma__navigationhiding` (
  `username` varchar(64) NOT NULL,
  `item_name` varchar(64) NOT NULL,
  `item_type` varchar(64) NOT NULL,
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Hidden items of navigation tree';

-- --------------------------------------------------------

--
-- Table structure for table `pma__pdf_pages`
--

CREATE TABLE `pma__pdf_pages` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `page_nr` int(10) UNSIGNED NOT NULL,
  `page_descr` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='PDF relation pages for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__recent`
--

CREATE TABLE `pma__recent` (
  `username` varchar(64) NOT NULL,
  `tables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Recently accessed tables';

--
-- Dumping data for table `pma__recent`
--

INSERT INTO `pma__recent` (`username`, `tables`) VALUES
('root', '[{\"db\":\"wellness\",\"table\":\"diet_pcos\"}]');

-- --------------------------------------------------------

--
-- Table structure for table `pma__relation`
--

CREATE TABLE `pma__relation` (
  `master_db` varchar(64) NOT NULL DEFAULT '',
  `master_table` varchar(64) NOT NULL DEFAULT '',
  `master_field` varchar(64) NOT NULL DEFAULT '',
  `foreign_db` varchar(64) NOT NULL DEFAULT '',
  `foreign_table` varchar(64) NOT NULL DEFAULT '',
  `foreign_field` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Relation table';

-- --------------------------------------------------------

--
-- Table structure for table `pma__savedsearches`
--

CREATE TABLE `pma__savedsearches` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `search_name` varchar(64) NOT NULL DEFAULT '',
  `search_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved searches';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_coords`
--

CREATE TABLE `pma__table_coords` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `pdf_page_number` int(11) NOT NULL DEFAULT 0,
  `x` float UNSIGNED NOT NULL DEFAULT 0,
  `y` float UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table coordinates for phpMyAdmin PDF output';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_info`
--

CREATE TABLE `pma__table_info` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `display_field` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_uiprefs`
--

CREATE TABLE `pma__table_uiprefs` (
  `username` varchar(64) NOT NULL,
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `prefs` text NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Tables'' UI preferences';

-- --------------------------------------------------------

--
-- Table structure for table `pma__tracking`
--

CREATE TABLE `pma__tracking` (
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `version` int(10) UNSIGNED NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `schema_snapshot` text NOT NULL,
  `schema_sql` text DEFAULT NULL,
  `data_sql` longtext DEFAULT NULL,
  `tracking` set('UPDATE','REPLACE','INSERT','DELETE','TRUNCATE','CREATE DATABASE','ALTER DATABASE','DROP DATABASE','CREATE TABLE','ALTER TABLE','RENAME TABLE','DROP TABLE','CREATE INDEX','DROP INDEX','CREATE VIEW','ALTER VIEW','DROP VIEW') DEFAULT NULL,
  `tracking_active` int(1) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Database changes tracking for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__userconfig`
--

CREATE TABLE `pma__userconfig` (
  `username` varchar(64) NOT NULL,
  `timevalue` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `config_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User preferences storage for phpMyAdmin';

--
-- Dumping data for table `pma__userconfig`
--

INSERT INTO `pma__userconfig` (`username`, `timevalue`, `config_data`) VALUES
('root', '2025-11-04 13:22:11', '{\"Console\\/Mode\":\"collapse\",\"NavigationWidth\":0}');

-- --------------------------------------------------------

--
-- Table structure for table `pma__usergroups`
--

CREATE TABLE `pma__usergroups` (
  `usergroup` varchar(64) NOT NULL,
  `tab` varchar(64) NOT NULL,
  `allowed` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User groups with configured menu items';

-- --------------------------------------------------------

--
-- Table structure for table `pma__users`
--

CREATE TABLE `pma__users` (
  `username` varchar(64) NOT NULL,
  `usergroup` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Users and their assignments to user groups';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pma__central_columns`
--
ALTER TABLE `pma__central_columns`
  ADD PRIMARY KEY (`db_name`,`col_name`);

--
-- Indexes for table `pma__column_info`
--
ALTER TABLE `pma__column_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `db_name` (`db_name`,`table_name`,`column_name`);

--
-- Indexes for table `pma__designer_settings`
--
ALTER TABLE `pma__designer_settings`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_user_type_template` (`username`,`export_type`,`template_name`);

--
-- Indexes for table `pma__favorite`
--
ALTER TABLE `pma__favorite`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__history`
--
ALTER TABLE `pma__history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`,`db`,`table`,`timevalue`);

--
-- Indexes for table `pma__navigationhiding`
--
ALTER TABLE `pma__navigationhiding`
  ADD PRIMARY KEY (`username`,`item_name`,`item_type`,`db_name`,`table_name`);

--
-- Indexes for table `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  ADD PRIMARY KEY (`page_nr`),
  ADD KEY `db_name` (`db_name`);

--
-- Indexes for table `pma__recent`
--
ALTER TABLE `pma__recent`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__relation`
--
ALTER TABLE `pma__relation`
  ADD PRIMARY KEY (`master_db`,`master_table`,`master_field`),
  ADD KEY `foreign_field` (`foreign_db`,`foreign_table`);

--
-- Indexes for table `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_savedsearches_username_dbname` (`username`,`db_name`,`search_name`);

--
-- Indexes for table `pma__table_coords`
--
ALTER TABLE `pma__table_coords`
  ADD PRIMARY KEY (`db_name`,`table_name`,`pdf_page_number`);

--
-- Indexes for table `pma__table_info`
--
ALTER TABLE `pma__table_info`
  ADD PRIMARY KEY (`db_name`,`table_name`);

--
-- Indexes for table `pma__table_uiprefs`
--
ALTER TABLE `pma__table_uiprefs`
  ADD PRIMARY KEY (`username`,`db_name`,`table_name`);

--
-- Indexes for table `pma__tracking`
--
ALTER TABLE `pma__tracking`
  ADD PRIMARY KEY (`db_name`,`table_name`,`version`);

--
-- Indexes for table `pma__userconfig`
--
ALTER TABLE `pma__userconfig`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__usergroups`
--
ALTER TABLE `pma__usergroups`
  ADD PRIMARY KEY (`usergroup`,`tab`,`allowed`);

--
-- Indexes for table `pma__users`
--
ALTER TABLE `pma__users`
  ADD PRIMARY KEY (`username`,`usergroup`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__column_info`
--
ALTER TABLE `pma__column_info`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__history`
--
ALTER TABLE `pma__history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  MODIFY `page_nr` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Database: `test`
--
CREATE DATABASE IF NOT EXISTS `test` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `test`;
--
-- Database: `wellness`
--
CREATE DATABASE IF NOT EXISTS `wellness` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `wellness`;

-- --------------------------------------------------------

--
-- Table structure for table `diet_glutenfree`
--

CREATE TABLE `diet_glutenfree` (
  `GlutenfreeID` int(11) NOT NULL,
  `day` enum('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `breakfast` text NOT NULL,
  `b_calories` smallint(5) UNSIGNED NOT NULL,
  `lunch` text NOT NULL,
  `l_calories` smallint(5) UNSIGNED NOT NULL,
  `dinner` text NOT NULL,
  `d_calories` smallint(5) UNSIGNED NOT NULL,
  `total_calories_per_day` smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diet_glutenfree`
--

INSERT INTO `diet_glutenfree` (`GlutenfreeID`, `day`, `breakfast`, `b_calories`, `lunch`, `l_calories`, `dinner`, `d_calories`, `total_calories_per_day`) VALUES
(1, 'Sunday', 'Eggs + Vegetables + Gluten-Free Bread', 420, 'Grilled Chicken + Brown Rice + Salad', 610, 'Lentil Soup + Tuna Salad', 450, 1480),
(2, 'Monday', 'Gluten-Free Oats + Plant Milk + Banana', 430, 'Grilled Salmon + Mashed Potatoes + Veggies', 600, 'Omelet + Avocado', 430, 1410),
(3, 'Tuesday', 'Gluten-Free Pancakes + Honey + Fruits', 400, 'Brown Rice Kabsa + Chicken', 620, 'Vegetable Soup + GF Toast', 410, 1430),
(4, 'Wednesday', 'Greek Yogurt + Nuts + Berries', 390, 'Grilled Beef + Potatoes + Salad', 640, 'Tuna + Chickpea Salad', 420, 1450),
(5, 'Thursday', 'Fried Eggs + Gluten-Free Bread', 430, 'GF Pasta + Tomato Sauce + Chicken', 630, 'Pumpkin Soup + Toast', 400, 1460),
(6, 'Friday', 'Fresh Juice + Oats', 370, 'Turkey Burger on GF Bun', 600, 'Chicken Soup + Green Salad', 390, 1360),
(7, 'Saturday', 'Eggs + Cheese + Gluten-Free Bread', 410, 'Brown Rice + Curry Chicken + Veggies', 640, 'Tuna + Lentil Soup', 420, 1470);

-- --------------------------------------------------------

--
-- Table structure for table `diet_insulin_resist`
--

CREATE TABLE `diet_insulin_resist` (
  `InsulinID` int(11) NOT NULL,
  `day` enum('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `breakfast` text NOT NULL,
  `b_calories` smallint(5) UNSIGNED NOT NULL,
  `lunch` text NOT NULL,
  `l_calories` smallint(5) UNSIGNED NOT NULL,
  `dinner` text NOT NULL,
  `d_calories` smallint(5) UNSIGNED NOT NULL,
  `total_calories_per_day` smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diet_insulin_resist`
--

INSERT INTO `diet_insulin_resist` (`InsulinID`, `day`, `breakfast`, `b_calories`, `lunch`, `l_calories`, `dinner`, `d_calories`, `total_calories_per_day`) VALUES
(1, 'Sunday', 'Eggs + Avocado + Whole-Grain Bread', 410, 'Grilled Chicken + Brown Rice + Veggies', 590, 'Tuna Salad + Yogurt', 430, 1430),
(2, 'Monday', 'Oats + Skim Milk + Nuts', 380, 'Grilled Salmon + Quinoa + Broccoli', 600, 'Eggs + Green Salad', 420, 1400),
(3, 'Tuesday', 'Whole-Grain Toast + Peanut Butter + Apple', 400, 'Grilled Beef + Brown Rice + Salad', 610, 'Lentil Soup + Low-Fat Cheese', 400, 1410),
(4, 'Wednesday', 'Omelet + Avocado + Tomato', 390, 'Grilled Chicken + Baked Potatoes + Veggies', 580, 'Vegetable Soup + Toast', 380, 1350),
(5, 'Thursday', 'Greek Yogurt + Nuts + Berries', 360, 'Tuna + Salad + Brown Rice', 570, 'Eggs + Sautéed Vegetables', 390, 1320),
(6, 'Friday', 'Eggs + Low-Fat Cheese + Whole-Grain Bread', 420, 'Turkey + Whole-Wheat Pasta + Veggies', 610, 'Lentil Soup + Yogurt', 400, 1430),
(7, 'Saturday', 'Oats + Plant Milk + Banana', 380, 'Brown Rice Kabsa + Chicken', 620, 'Salad + Toast', 390, 1390);

-- --------------------------------------------------------

--
-- Table structure for table `diet_pcos`
--

CREATE TABLE `diet_pcos` (
  `PcosID` int(11) NOT NULL,
  `day` enum('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `breakfast` text NOT NULL,
  `b_calories` smallint(5) UNSIGNED NOT NULL,
  `lunch` text NOT NULL,
  `l_calories` smallint(5) UNSIGNED NOT NULL,
  `dinner` text NOT NULL,
  `d_calories` smallint(5) UNSIGNED NOT NULL,
  `total_calories_per_day` smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diet_pcos`
--

INSERT INTO `diet_pcos` (`PcosID`, `day`, `breakfast`, `b_calories`, `lunch`, `l_calories`, `dinner`, `d_calories`, `total_calories_per_day`) VALUES
(1, 'Sunday', 'Eggs + Avocado + Whole-Grain Toast', 400, 'Grilled Chicken + Sautéed Veggies + Brown Rice', 580, 'Lentil Soup + Salad', 420, 1400),
(2, 'Monday', 'Oats + Almond Milk + Berries', 370, 'Grilled Salmon + Quinoa + Veggies', 600, 'Boiled Eggs + Veggies', 390, 1360),
(3, 'Tuesday', 'Greek Yogurt + Nuts + Fruits', 380, 'Grilled Beef + Vegetables + Potatoes', 620, 'Vegetable Soup + Toast', 410, 1410),
(4, 'Wednesday', 'Eggs + Low-Fat Cheese + Whole-Grain Bread', 410, 'Chicken Curry + Brown Rice + Salad', 590, 'Tuna + Lentil Soup', 420, 1420),
(5, 'Thursday', 'Fresh Juice + Oats', 370, 'Turkey + Whole-Wheat Pasta', 600, 'Eggs + Vegetables', 400, 1370),
(6, 'Friday', 'Protein Pancakes + Honey', 400, 'Brown Rice Kabsa + Chicken', 620, 'Pumpkin Soup + Salad', 410, 1430),
(7, 'Saturday', 'Eggs + Cheese + Toast', 390, 'Grilled Salmon + Veggies + Potatoes', 580, 'Tuna + Salad', 390, 1380);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `UserID` int(11) NOT NULL,
  `Name` varchar(120) NOT NULL,
  `email` varchar(255) NOT NULL,
  `fitness_level` enum('beginner','intermediate','advanced') DEFAULT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `password_hash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`UserID`, `Name`, `email`, `fitness_level`, `role`, `password_hash`) VALUES
(1, 'Amal', 'amal@gmail.com', 'beginner', 'admin', '1234'),
(2, 'Ahmed', 'ahmed@example.com', 'intermediate', 'user', '4321'),
(3, 'Sara', 'sara@example.com', 'advanced', 'user', '1122'),
(4, 'Abeer', 'abeer@example.com', 'beginner', 'user', '2233'),
(5, 'Omar', 'omar@example.com', 'intermediate', 'user', '3344');

-- --------------------------------------------------------

--
-- Table structure for table `workout_advanced`
--

CREATE TABLE `workout_advanced` (
  `AdvancedID` int(11) NOT NULL,
  `workout_group` varchar(50) NOT NULL,
  `exercise` varchar(100) NOT NULL,
  `sets` tinyint(3) UNSIGNED NOT NULL,
  `reps` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `workout_advanced`
--

INSERT INTO `workout_advanced` (`AdvancedID`, `workout_group`, `exercise`, `sets`, `reps`) VALUES
(1, 'Back & Biceps', 'Lat Pulldown', 5, '6–10'),
(2, 'Back & Biceps', 'Seated Cable Row', 5, '6–10'),
(3, 'Back & Biceps', 'Dumbbell Biceps Curl', 4, '8–10'),
(4, 'Push Day', 'Barbell Bench Press', 5, '5–8'),
(5, 'Push Day', 'Dumbbell Shoulder Press', 4, '8–10'),
(6, 'Push Day', 'Triceps Pushdown', 4, '8–12'),
(7, 'Leg Day', 'Back Squat', 5, '5–8'),
(8, 'Leg Day', 'Romanian Deadlift', 4, '6–10'),
(9, 'Leg Day', 'Walking Lunge', 4, '10–12'),
(10, 'Core Focus', 'Plank', 5, '60–90 s hold'),
(11, 'Core Focus', 'Hanging Knee Raise', 4, '12–15'),
(12, 'Core Focus', 'Cable Woodchop', 4, '12–15 / side');

-- --------------------------------------------------------

--
-- Table structure for table `workout_beginner`
--

CREATE TABLE `workout_beginner` (
  `BeginnerID` int(11) NOT NULL,
  `workout_group` varchar(50) NOT NULL,
  `exercise` varchar(100) NOT NULL,
  `sets` tinyint(3) UNSIGNED NOT NULL,
  `reps` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `workout_beginner`
--

INSERT INTO `workout_beginner` (`BeginnerID`, `workout_group`, `exercise`, `sets`, `reps`) VALUES
(1, 'Back & Biceps', 'Lat Pulldown', 3, '10–12'),
(2, 'Back & Biceps', 'Seated Cable Row', 3, '10–12'),
(3, 'Back & Biceps', 'Dumbbell Biceps Curl', 3, '10–12'),
(4, 'Push Day', 'Barbell Bench Press', 3, '10–12'),
(5, 'Push Day', 'Dumbbell Shoulder Press', 3, '10–12'),
(6, 'Push Day', 'Triceps Pushdown', 3, '10–12'),
(7, 'Leg Day', 'Back Squat', 3, '10–12'),
(8, 'Leg Day', 'Romanian Deadlift', 3, '10–12'),
(9, 'Leg Day', 'Walking Lunge', 3, '10–12'),
(10, 'Core Focus', 'Plank', 3, '30–45 s hold'),
(11, 'Core Focus', 'Hanging Knee Raise', 3, '10–12'),
(12, 'Core Focus', 'Cable Woodchop', 3, '10–12 / side');

-- --------------------------------------------------------

--
-- Table structure for table `workout_intermediate`
--

CREATE TABLE `workout_intermediate` (
  `IntermediateID` int(11) NOT NULL,
  `workout_group` varchar(50) NOT NULL,
  `exercise` varchar(100) NOT NULL,
  `sets` tinyint(3) UNSIGNED NOT NULL,
  `reps` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `workout_intermediate`
--

INSERT INTO `workout_intermediate` (`IntermediateID`, `workout_group`, `exercise`, `sets`, `reps`) VALUES
(1, 'Back & Biceps', 'Lat Pulldown', 4, '8–12'),
(2, 'Back & Biceps', 'Seated Cable Row', 4, '8–12'),
(3, 'Back & Biceps', 'Dumbbell Biceps Curl', 4, '8–12'),
(4, 'Push Day', 'Barbell Bench Press', 4, '8–12'),
(5, 'Push Day', 'Dumbbell Shoulder Press', 4, '8–12'),
(6, 'Push Day', 'Triceps Pushdown', 4, '10–12'),
(7, 'Leg Day', 'Back Squat', 4, '8–12'),
(8, 'Leg Day', 'Romanian Deadlift', 4, '8–12'),
(9, 'Leg Day', 'Walking Lunge', 4, '10–12'),
(10, 'Core Focus', 'Plank', 4, '45–60 s hold'),
(11, 'Core Focus', 'Hanging Knee Raise', 4, '12–15'),
(12, 'Core Focus', 'Cable Woodchop', 4, '12–15 / side');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `diet_glutenfree`
--
ALTER TABLE `diet_glutenfree`
  ADD PRIMARY KEY (`GlutenfreeID`);

--
-- Indexes for table `diet_insulin_resist`
--
ALTER TABLE `diet_insulin_resist`
  ADD PRIMARY KEY (`InsulinID`);

--
-- Indexes for table `diet_pcos`
--
ALTER TABLE `diet_pcos`
  ADD PRIMARY KEY (`PcosID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `unique_email` (`email`);

--
-- Indexes for table `workout_advanced`
--
ALTER TABLE `workout_advanced`
  ADD PRIMARY KEY (`AdvancedID`);

--
-- Indexes for table `workout_beginner`
--
ALTER TABLE `workout_beginner`
  ADD PRIMARY KEY (`BeginnerID`);

--
-- Indexes for table `workout_intermediate`
--
ALTER TABLE `workout_intermediate`
  ADD PRIMARY KEY (`IntermediateID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `diet_glutenfree`
--
ALTER TABLE `diet_glutenfree`
  MODIFY `GlutenfreeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `diet_insulin_resist`
--
ALTER TABLE `diet_insulin_resist`
  MODIFY `InsulinID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `diet_pcos`
--
ALTER TABLE `diet_pcos`
  MODIFY `PcosID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `workout_advanced`
--
ALTER TABLE `workout_advanced`
  MODIFY `AdvancedID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `workout_beginner`
--
ALTER TABLE `workout_beginner`
  MODIFY `BeginnerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `workout_intermediate`
--
ALTER TABLE `workout_intermediate`
  MODIFY `IntermediateID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
