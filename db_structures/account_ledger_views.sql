-- phpMyAdmin SQL Dump
-- version 4.8.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 30, 2018 at 09:45 AM
-- Server version: 5.7.24-0ubuntu0.16.04.1
-- PHP Version: 7.0.32-0ubuntu0.16.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `account_ledger`
--

-- --------------------------------------------------------

--
-- Stand-in structure for view `recent_accounts`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `recent_accounts`;
CREATE TABLE `recent_accounts` (
`account_id` int(11)
,`full_name` varchar(250)
,`name` varchar(250)
,`parent_account_id` int(11)
,`account_type` varchar(50)
,`notes` varchar(250)
,`commodity_type` varchar(50)
,`commodity_value` varchar(50)
,`owner_id` int(11)
,`taxable` char(1)
,`place_holder` char(1)
,`insertion_date_time` datetime
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `recent_transactions`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `recent_transactions`;
CREATE TABLE `recent_transactions` (
`id` int(11)
,`event_date_time` datetime
,`particulars` varchar(150)
,`amount` double
,`insertion_date_time` datetime
,`inserter_id` int(11)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `recent_transactionsv2`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `recent_transactionsv2`;
CREATE TABLE `recent_transactionsv2` (
`id` int(11)
,`event_date_time` datetime
,`particulars` varchar(250)
,`amount` double
,`insertion_date_time` datetime
,`inserter_id` int(11)
,`from_account_id` int(11)
,`to_account_id` int(11)
);

-- --------------------------------------------------------

--
-- Structure for view `recent_accounts`
--
DROP TABLE IF EXISTS `recent_accounts`;

CREATE ALGORITHM=UNDEFINED DEFINER=`vfmobo6d`@`localhost` SQL SECURITY DEFINER VIEW `recent_accounts`  AS  select `accounts`.`account_id` AS `account_id`,`accounts`.`full_name` AS `full_name`,`accounts`.`name` AS `name`,`accounts`.`parent_account_id` AS `parent_account_id`,`accounts`.`account_type` AS `account_type`,`accounts`.`notes` AS `notes`,`accounts`.`commodity_type` AS `commodity_type`,`accounts`.`commodity_value` AS `commodity_value`,`accounts`.`owner_id` AS `owner_id`,`accounts`.`taxable` AS `taxable`,`accounts`.`place_holder` AS `place_holder`,`accounts`.`insertion_date_time` AS `insertion_date_time` from `accounts` order by `accounts`.`account_id` desc ;

-- --------------------------------------------------------

--
-- Structure for view `recent_transactions`
--
DROP TABLE IF EXISTS `recent_transactions`;

CREATE ALGORITHM=UNDEFINED DEFINER=`vfmobo6d`@`localhost` SQL SECURITY DEFINER VIEW `recent_transactions`  AS  select `transactions`.`id` AS `id`,`transactions`.`event_date_time` AS `event_date_time`,`transactions`.`particulars` AS `particulars`,`transactions`.`amount` AS `amount`,`transactions`.`insertion_date_time` AS `insertion_date_time`,`transactions`.`inserter_id` AS `inserter_id` from `transactions` order by `transactions`.`id` desc ;

-- --------------------------------------------------------

--
-- Structure for view `recent_transactionsv2`
--
DROP TABLE IF EXISTS `recent_transactionsv2`;

CREATE ALGORITHM=UNDEFINED DEFINER=`vfmobo6d`@`localhost` SQL SECURITY DEFINER VIEW `recent_transactionsv2`  AS  select `transactionsv2`.`id` AS `id`,`transactionsv2`.`event_date_time` AS `event_date_time`,`transactionsv2`.`particulars` AS `particulars`,`transactionsv2`.`amount` AS `amount`,`transactionsv2`.`insertion_date_time` AS `insertion_date_time`,`transactionsv2`.`inserter_id` AS `inserter_id`,`transactionsv2`.`from_account_id` AS `from_account_id`,`transactionsv2`.`to_account_id` AS `to_account_id` from `transactionsv2` order by `transactionsv2`.`id` desc ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
