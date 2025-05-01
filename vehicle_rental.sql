-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 01, 2025 at 05:40 AM
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
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_AddDriver` (IN `p_staff_id` INT, IN `p_driver_name` VARCHAR(30), IN `p_license_number` VARCHAR(30), IN `p_contact_number` VARCHAR(20), IN `p_address` VARCHAR(100), IN `p_birthdate` DATE, IN `p_gender` VARCHAR(10), IN `p_status` VARCHAR(20))   BEGIN
    INSERT INTO driver(
        STAFF_ID,
        DRIVER_NAME,
        LICENSE_NUMBER,
        CONTACT_NUMBER,
        ADDRESS,
        BIRTHDATE,
        GENDER,
        STATUS
    ) VALUES (
        p_staff_id,
        p_driver_name,
        p_license_number,
        p_contact_number,
        p_address,
        p_birthdate,
        p_gender,
        p_status
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_AddVehicle` (IN `p_staff_id` INT, IN `p_vehicle_type` VARCHAR(50), IN `p_vehicle_brand` VARCHAR(50), IN `p_model` VARCHAR(50), IN `p_year` INT, IN `p_color` CHAR(10), IN `p_license_plate` VARCHAR(20), IN `p_vehicle_description` VARCHAR(50), IN `p_images` VARCHAR(255), IN `p_capacity` VARCHAR(30), IN `p_transmission` VARCHAR(20), IN `p_status` VARCHAR(20), IN `p_amount` DECIMAL(10,2), IN `p_quantity` INT)   BEGIN
    INSERT INTO VEHICLE (
        STAFF_ID, 
        VEHICLE_TYPE, 
        VEHICLE_BRAND, 
        MODEL, 
        YEAR, 
        COLOR, 
        LICENSE_PLATE, 
        VEHICLE_DESCRIPTION, 
        IMAGES,
        CAPACITY,
        TRANSMISSION,
        STATUS,
        AMOUNT,
        QUANTITY
    ) VALUES (
        p_staff_id,
        p_vehicle_type,
        p_vehicle_brand,
        p_model,
        p_year,
        p_color,
        p_license_plate,
        p_vehicle_description,
        p_images,
        p_capacity,
        p_transmission,
        p_status,
        p_amount,
        p_quantity
    );
    
    SELECT LAST_INSERT_ID() as vehicle_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_DeleteDriver` (IN `p_driver_id` INT)   BEGIN
    DELETE FROM driver WHERE DRIVER_ID = p_driver_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_delete_customer` (IN `p_customer_id` INT)   BEGIN
    DELETE FROM customer WHERE CUSTOMER_ID = p_customer_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetAllDrivers` ()   BEGIN
    SELECT * FROM driver;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetDriverById` (IN `p_driver_id` INT)   BEGIN
    SELECT * FROM driver WHERE DRIVER_ID = p_driver_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetVehicleDetails` (IN `p_vehicle_id` INT)   BEGIN
    SELECT * FROM VEHICLE 
    WHERE VEHICLE_ID = p_vehicle_id;
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_insert_customer` (IN `p_customer_type` VARCHAR(20), IN `p_company_name` VARCHAR(30), IN `p_job_title` VARCHAR(30), IN `p_first_name` VARCHAR(30), IN `p_last_name` VARCHAR(30), IN `p_email` VARCHAR(100), IN `p_contact_num` VARCHAR(20), IN `p_address` VARCHAR(30), IN `p_drivers_license` VARCHAR(30))   BEGIN
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_UpdateDriver` (IN `p_driver_id` INT, IN `p_staff_id` INT, IN `p_driver_name` VARCHAR(30), IN `p_license_number` VARCHAR(30), IN `p_contact_number` VARCHAR(20), IN `p_address` VARCHAR(100), IN `p_birthdate` DATE, IN `p_gender` VARCHAR(10), IN `p_status` VARCHAR(20))   BEGIN
    UPDATE driver SET
        STAFF_ID = p_staff_id,
        DRIVER_NAME = p_driver_name,
        LICENSE_NUMBER = p_license_number,
        CONTACT_NUMBER = p_contact_number,
        ADDRESS = p_address,
        BIRTHDATE = p_birthdate,
        GENDER = p_gender,
        STATUS = p_status
    WHERE DRIVER_ID = p_driver_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_UpdateVehicleStatus` (IN `p_vehicle_id` INT, IN `p_status` VARCHAR(20))   BEGIN
    UPDATE VEHICLE 
    SET STATUS = p_status 
    WHERE VEHICLE_ID = p_vehicle_id;
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
  `LAST_NAME` varchar(30) NOT NULL,
  `EMAIL` varchar(100) NOT NULL,
  `CONTACT_NUM` varchar(20) NOT NULL,
  `CUSTOMER_ADDRESS` varchar(30) NOT NULL,
  `DRIVERS_LICENSE` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `driver`
--

CREATE TABLE `driver` (
  `DRIVER_ID` int(11) NOT NULL,
  `STAFF_ID` int(11) DEFAULT NULL,
  `DRIVER_NAME` varchar(30) NOT NULL,
  `LICENSE_NUMBER` varchar(30) NOT NULL,
  `CONTACT_NUMBER` varchar(20) NOT NULL,
  `ADDRESS` varchar(100) NOT NULL,
  `BIRTHDATE` date NOT NULL,
  `GENDER` varchar(10) NOT NULL,
  `STATUS` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `driver`
--

INSERT INTO `driver` (`DRIVER_ID`, `STAFF_ID`, `DRIVER_NAME`, `LICENSE_NUMBER`, `CONTACT_NUMBER`, `ADDRESS`, `BIRTHDATE`, `GENDER`, `STATUS`) VALUES
(2, 1, 'Alexus Sagaral', 'TEST', '09382470661', 'TEST', '2025-04-28', 'Male', 'Available'),
(3, 1, 'Vince Bryant Cabunilas', 'TEST1', '09878786651', 'TEST1', '2025-04-28', 'Male', 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `PAYMENT_ID` int(11) NOT NULL,
  `PAYMENT_METHOD` varchar(30) NOT NULL
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

-- --------------------------------------------------------

--
-- Table structure for table `vehicle`
--

CREATE TABLE `vehicle` (
  `VEHICLE_ID` int(11) NOT NULL,
  `STAFF_ID` int(11) DEFAULT NULL,
  `VEHICLE_TYPE` varchar(50) NOT NULL,
  `VEHICLE_BRAND` varchar(50) NOT NULL,
  `MODEL` varchar(50) NOT NULL,
  `YEAR` int(11) NOT NULL,
  `COLOR` char(10) NOT NULL,
  `LICENSE_PLATE` varchar(20) NOT NULL,
  `VEHICLE_DESCRIPTION` varchar(50) NOT NULL,
  `IMAGES` varchar(255) NOT NULL,
  `CAPACITY` varchar(30) NOT NULL,
  `TRANSMISSION` varchar(20) NOT NULL,
  `STATUS` varchar(20) NOT NULL,
  `AMOUNT` decimal(10,2) NOT NULL DEFAULT 0.00,
  `QUANTITY` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Indexes for table `driver`
--
ALTER TABLE `driver`
  ADD PRIMARY KEY (`DRIVER_ID`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`PAYMENT_ID`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`STAFF_ID`);

--
-- Indexes for table `vehicle`
--
ALTER TABLE `vehicle`
  ADD PRIMARY KEY (`VEHICLE_ID`),
  ADD UNIQUE KEY `LICENSE_PLATE` (`LICENSE_PLATE`),
  ADD KEY `STAFF_ID` (`STAFF_ID`);

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
  MODIFY `CUSTOMER_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `driver`
--
ALTER TABLE `driver`
  MODIFY `DRIVER_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `PAYMENT_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `STAFF_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `vehicle`
--
ALTER TABLE `vehicle`
  MODIFY `VEHICLE_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `vehicle`
--
ALTER TABLE `vehicle`
  ADD CONSTRAINT `vehicle_ibfk_1` FOREIGN KEY (`STAFF_ID`) REFERENCES `staff` (`STAFF_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
