-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 23, 2025 at 07:35 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vehicle_rental`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_delete_customer` (IN `p_customer_id` INT)   BEGIN
    DELETE FROM customer WHERE CUSTOMER_ID = p_customer_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_get_admin_login` (IN `p_username` VARCHAR(30), IN `p_password` VARCHAR(30))   BEGIN
    SELECT * FROM admin 
    WHERE USER_NAME = p_username 
    AND PASSWORD = p_password;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_get_customer_by_id` (IN `p_customer_id` INT)   BEGIN
    SELECT * FROM customer WHERE CUSTOMER_ID = p_customer_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_get_staff_login` (IN `p_username` VARCHAR(30), IN `p_password` VARCHAR(30))   BEGIN
    SELECT * FROM staff 
    WHERE USER_NAME = p_username 
    AND PASSWORD = p_password;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_insert_customer` (IN `p_customer_type` VARCHAR(20), IN `p_company_name` VARCHAR(30), IN `p_job_title` VARCHAR(30), IN `p_first_name` VARCHAR(30), IN `p_last_name` VARCHAR(3), IN `p_email` VARCHAR(100), IN `p_contact_num` VARCHAR(20), IN `p_address` VARCHAR(30), IN `p_drivers_license` VARCHAR(30))   BEGIN
    INSERT INTO customer (
        CUSTOMER_TYPE, COMPANY_NAME, JOB_TITLE, 
        FIRST_NAME, LAST_NAME, EMAIL, 
        CONTACT_NUM, CUSTOMER_ADDRESS, DRIVERS_LICENSE
    )
    VALUES (
        p_customer_type, p_company_name, p_job_title,
        p_first_name, p_last_name, p_email,
        p_contact_num, p_address, p_drivers_license
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_insert_staff` (IN `p_firstname` VARCHAR(30), IN `p_lastname` VARCHAR(30), IN `p_address` VARCHAR(255), IN `p_phone` VARCHAR(20), IN `p_username` VARCHAR(30), IN `p_password` VARCHAR(30))   BEGIN
    INSERT INTO staff (FIRST_NAME, LAST_NAME, ADDRESS, PHONE_NUM, USER_NAME, PASSWORD)
    VALUES (p_firstname, p_lastname, p_address, p_phone, p_username, p_password);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_update_customer` (IN `p_customer_id` INT, IN `p_customer_type` VARCHAR(20), IN `p_company_name` VARCHAR(30), IN `p_job_title` VARCHAR(30), IN `p_first_name` VARCHAR(30), IN `p_last_name` VARCHAR(3), IN `p_email` VARCHAR(100), IN `p_contact_num` VARCHAR(20), IN `p_address` VARCHAR(30), IN `p_drivers_license` VARCHAR(30))   BEGIN
    UPDATE customer SET
        CUSTOMER_TYPE = p_customer_type,
        COMPANY_NAME = p_company_name,
        JOB_TITLE = p_job_title,
        FIRST_NAME = p_first_name,
        LAST_NAME = p_last_name,
        EMAIL = p_email,
        CONTACT_NUM = p_contact_num,
        CUSTOMER_ADDRESS = p_address,
        DRIVERS_LICENSE = p_drivers_license
    WHERE CUSTOMER_ID = p_customer_id;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `ADMIN_ID` int(11) NOT NULL,
  `USER_NAME` varchar(30) NOT NULL DEFAULT 'admin',
  `PASSWORD` varchar(30) NOT NULL DEFAULT 'admin123'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`ADMIN_ID`, `USER_NAME`, `PASSWORD`) VALUES
(1, 'admin', 'admin123');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `CUSTOMER_ID` int(11) NOT NULL,
  `CUSTOMER_TYPE` varchar(20) NOT NULL,
  `COMPANY_NAME` varchar(30) DEFAULT NULL,
  `JOB_TITLE` varchar(30) DEFAULT NULL,
  `FIRST_NAME` varchar(30) NOT NULL,
  `LAST_NAME` varchar(3) NOT NULL,
  `EMAIL` varchar(100) NOT NULL,
  `CONTACT_NUM` varchar(20) NOT NULL,
  `CUSTOMER_ADDRESS` varchar(30) NOT NULL,
  `DRIVERS_LICENSE` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `STAFF_ID` int(11) NOT NULL,
  `FIRST_NAME` varchar(30) NOT NULL,
  `LAST_NAME` varchar(30) NOT NULL,
  `ADDRESS` varchar(255) NOT NULL,
  `PHONE_NUM` varchar(20) NOT NULL,
  `USER_NAME` varchar(30) NOT NULL,
  `PASSWORD` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`STAFF_ID`, `FIRST_NAME`, `LAST_NAME`, `ADDRESS`, `PHONE_NUM`, `USER_NAME`, `PASSWORD`) VALUES
(1, 'Alexus Sundae ', 'Sagaral', 'CEBU CITY', '09382470661', 'alexus', 'alexus123');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`ADMIN_ID`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`CUSTOMER_ID`),
  ADD UNIQUE KEY `DRIVERS_LICENSE` (`DRIVERS_LICENSE`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`STAFF_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `ADMIN_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `CUSTOMER_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `STAFF_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
