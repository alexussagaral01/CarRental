-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 06, 2025 at 04:36 AM
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_AddVehicle` (IN `p_staff_id` INT, IN `p_vehicle_type` VARCHAR(50), IN `p_vehicle_brand` VARCHAR(50), IN `p_model` VARCHAR(50), IN `p_year` INT, IN `p_color` CHAR(10), IN `p_license_plate` VARCHAR(20), IN `p_vehicle_description` VARCHAR(50), IN `p_images` VARCHAR(255), IN `p_capacity` VARCHAR(30), IN `p_transmission` VARCHAR(20), IN `p_status` VARCHAR(20), IN `p_amount` DECIMAL(10,2))   BEGIN
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
        AMOUNT
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
        p_amount
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_InsertRentalDetail` (IN `p_vehicle_id` INT, IN `p_pickup_location` VARCHAR(100), IN `p_start_date` DATETIME, IN `p_end_date` DATETIME)   BEGIN
    DECLARE v_duration INT;
    DECLARE v_hourly_rate DECIMAL(10,2);
    DECLARE v_total_amount DECIMAL(10,2);
    DECLARE v_vat_amount DECIMAL(10,2);
    DECLARE v_line_total DECIMAL(10,2);
    
    -- Calculate duration in hours
    SET v_duration = TIMESTAMPDIFF(HOUR, p_start_date, p_end_date);
    
    -- Get vehicle hourly rate directly from vehicle table
    SELECT AMOUNT INTO v_hourly_rate
    FROM VEHICLE 
    WHERE VEHICLE_ID = p_vehicle_id;
    
    -- Calculate total amount
    SET v_total_amount = v_duration * v_hourly_rate;
    
    -- Calculate VAT (12% of total amount)
    SET v_vat_amount = v_total_amount * 0.12;
    
    -- Calculate final total (total amount + VAT)
    SET v_line_total = v_total_amount + v_vat_amount;
    
    -- Validate dates
    IF p_end_date <= p_start_date THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'End date must be after start date';
    END IF;
    
    -- Insert rental detail
    INSERT INTO RENTAL_DTL (
        VEHICLE_ID,
        PICKUP_LOCATION,
        START_DATE,
        END_DATE,
        HOURLY_RATE
    ) VALUES (
        p_vehicle_id,
        p_pickup_location,
        p_start_date,
        p_end_date,
        v_hourly_rate
    );
    
    -- Return rental details
    SELECT 
        LAST_INSERT_ID() as rental_dtl_id,
        p_pickup_location as pickup_location,
        v_duration as duration_hours,
        v_hourly_rate as hourly_rate,
        v_total_amount as total_amount,
        v_vat_amount as vat_amount,
        v_line_total as final_total;
        
    -- Update vehicle status
    UPDATE VEHICLE 
    SET STATUS = 'Rented'
    WHERE VEHICLE_ID = p_vehicle_id;
    
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_insert_customer` (IN `p_customer_type` VARCHAR(20), IN `p_driver_type` VARCHAR(20), IN `p_company_name` VARCHAR(30), IN `p_job_title` VARCHAR(30), IN `p_first_name` VARCHAR(30), IN `p_last_name` VARCHAR(30), IN `p_email` VARCHAR(100), IN `p_contact_num` VARCHAR(20), IN `p_address` VARCHAR(30), IN `p_drivers_license` VARCHAR(30))   BEGIN
    INSERT INTO customer (
        CUSTOMER_TYPE,
        DRIVER_TYPE,
        COMPANY_NAME,
        JOB_TITLE,
        FIRST_NAME,
        LAST_NAME,
        EMAIL,
        CONTACT_NUM,
        CUSTOMER_ADDRESS,
        DRIVERS_LICENSE
    )
    VALUES (
        p_customer_type,
        p_driver_type,
        p_company_name,
        p_job_title,
        p_first_name,
        p_last_name,
        p_email,
        p_contact_num,
        p_address,
        p_drivers_license
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_insert_rental_hdr` (IN `p_rental_dtl_id` INT, IN `p_customer_id` INT, IN `p_payment_id` INT, IN `p_vehicle_id` INT)   BEGIN
    INSERT INTO rental_hdr (
        RENTAL_DTL_ID,
        CUSTOMER_ID,
        PAYMENT_ID,
        VEHICLE_ID,
        DATE_CREATED
    )
    VALUES (
        p_rental_dtl_id,
        p_customer_id,
        p_payment_id,
        p_vehicle_id,
        NOW()
    );
    
    SELECT LAST_INSERT_ID() as rental_hdr_id;
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_update_customer` (IN `p_customer_id` INT, IN `p_customer_type` VARCHAR(20), IN `p_driver_type` VARCHAR(20), IN `p_company_name` VARCHAR(30), IN `p_job_title` VARCHAR(30), IN `p_first_name` VARCHAR(30), IN `p_last_name` VARCHAR(30), IN `p_email` VARCHAR(100), IN `p_contact_num` VARCHAR(20), IN `p_address` VARCHAR(30), IN `p_drivers_license` VARCHAR(30))   BEGIN
    UPDATE customer SET
        CUSTOMER_TYPE = p_customer_type,
        DRIVER_TYPE = p_driver_type,
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
  `DRIVER_TYPE` varchar(20) NOT NULL,
  `DRIVERS_LICENSE` varchar(30) NOT NULL,
  `ASSIGNED_DRIVER_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`CUSTOMER_ID`, `CUSTOMER_TYPE`, `COMPANY_NAME`, `JOB_TITLE`, `FIRST_NAME`, `LAST_NAME`, `EMAIL`, `CONTACT_NUM`, `CUSTOMER_ADDRESS`, `DRIVER_TYPE`, `DRIVERS_LICENSE`, `ASSIGNED_DRIVER_ID`) VALUES
(3, 'individual', '', '', 'TEST', 'TEST', 'TEST@gmail.com', '12312312312', 'TEST', 'with_driver', 'TEST12345', 2),
(4, 'individual', '', '', 'SAMPLE', 'SAMPLE', 'SAMPLE@gmail.com', '14123123123', 'SAMPLE', 'with_driver', 'SAMPLE-1234', 3),
(5, 'individual', '', '', 'TRY', 'TRY', 'TRY@GMAIL.COM', '21345445445', 'TRY', 'with_driver', 'TRY2131231', 4),
(6, 'individual', '', '', 'TESTING', 'TESTING', 'TESTING@gmail.com', '56454654646', 'TESTING', 'with_driver', 'TESTING21312', 5);

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
(2, 1, 'Alexus Sagaral', 'TEST', '09382470661', 'TEST', '2025-04-28', '0', 'Assigned'),
(3, 1, 'Vince Bryant Cabunilas', 'TEST1', '09878786651', 'TEST1', '2025-04-28', '0', 'Assigned'),
(4, 1, 'Mark Dave Catubig', 'SAMPLE-25213', '83138213123', 'SAMPLE', '2025-01-28', '0', 'Assigned'),
(5, 1, 'Jeff Salimbangon Ginolos Monre', 'TESTING-123', '42544354353', 'TESTING', '2025-05-04', '0', 'Assigned');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `PAYMENT_ID` int(11) NOT NULL,
  `PAYMENT_METHOD` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`PAYMENT_ID`, `PAYMENT_METHOD`) VALUES
(3, 'cash'),
(4, 'cash'),
(5, 'cash'),
(6, 'cash');

-- --------------------------------------------------------

--
-- Table structure for table `rental_dtl`
--

CREATE TABLE `rental_dtl` (
  `RENTAL_DTL_ID` int(11) NOT NULL,
  `VEHICLE_ID` int(11) DEFAULT NULL,
  `PICKUP_LOCATION` varchar(100) NOT NULL,
  `START_DATE` datetime NOT NULL,
  `END_DATE` datetime NOT NULL,
  `DURATION` int(11) GENERATED ALWAYS AS (timestampdiff(HOUR,`START_DATE`,`END_DATE`)) STORED,
  `HOURLY_RATE` decimal(10,2) NOT NULL,
  `TOTAL_AMOUNT` decimal(10,2) GENERATED ALWAYS AS (`DURATION` * `HOURLY_RATE`) STORED,
  `VAT_AMOUNT` decimal(10,2) GENERATED ALWAYS AS (`DURATION` * `HOURLY_RATE` * 0.12) STORED,
  `LINE_TOTAL` decimal(10,2) GENERATED ALWAYS AS (`DURATION` * `HOURLY_RATE` + `DURATION` * `HOURLY_RATE` * 0.12) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rental_dtl`
--

INSERT INTO `rental_dtl` (`RENTAL_DTL_ID`, `VEHICLE_ID`, `PICKUP_LOCATION`, `START_DATE`, `END_DATE`, `HOURLY_RATE`) VALUES
(1, 4, 'Cebu City Downtown', '2025-05-04 09:15:00', '2025-05-04 14:15:00', 499.00),
(2, 5, 'Cebu City Downtown', '2025-05-04 09:50:00', '2025-05-07 09:50:00', 399.00),
(3, 6, 'Cebu City Downtown', '2025-05-04 10:04:00', '2025-05-06 14:04:00', 399.00),
(4, 7, 'Cebu City Downtown', '2025-05-04 10:10:00', '2025-05-09 10:10:00', 399.00);

-- --------------------------------------------------------

--
-- Table structure for table `rental_hdr`
--

CREATE TABLE `rental_hdr` (
  `RENTAL_HDR_ID` int(11) NOT NULL,
  `RENTAL_DTL_ID` int(11) NOT NULL,
  `CUSTOMER_ID` int(11) NOT NULL,
  `PAYMENT_ID` int(11) NOT NULL,
  `VEHICLE_ID` int(11) NOT NULL,
  `DATE_CREATED` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rental_hdr`
--

INSERT INTO `rental_hdr` (`RENTAL_HDR_ID`, `RENTAL_DTL_ID`, `CUSTOMER_ID`, `PAYMENT_ID`, `VEHICLE_ID`, `DATE_CREATED`) VALUES
(1, 1, 3, 3, 4, '2025-05-04 02:16:41'),
(2, 2, 4, 4, 5, '2025-05-04 02:51:22'),
(3, 3, 5, 5, 6, '2025-05-04 03:06:04'),
(4, 4, 6, 6, 7, '2025-05-04 03:11:19');

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
  `AMOUNT` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle`
--

INSERT INTO `vehicle` (`VEHICLE_ID`, `STAFF_ID`, `VEHICLE_TYPE`, `VEHICLE_BRAND`, `MODEL`, `YEAR`, `COLOR`, `LICENSE_PLATE`, `VEHICLE_DESCRIPTION`, `IMAGES`, `CAPACITY`, `TRANSMISSION`, `STATUS`, `AMOUNT`) VALUES
(4, 1, 'HATCHBACK', 'TEST', 'TEST', 2025, 'TEST', 'TEST-123', 'TEST', 'VEHICLE_IMAGES/6816cdb441173_1746324916.jpg', '4-5', 'Automatic', 'Rented', 499.00),
(5, 1, 'SEDAN', 'SAMPLE', 'SAMPLE', 2023, 'SAMPLE', 'SAMPLE-123', 'SAMPLE', 'VEHICLE_IMAGES/6816d5b0d46aa_1746326960.jpg', '7-8', 'Automatic', 'Rented', 399.00),
(6, 1, 'HATCHBACK', 'TRY', 'TRY', 2012, 'TRY', 'TRY-2312', 'TRY', 'VEHICLE_IMAGES/6816d950a4e29_1746327888.jpg', '4-5', 'Automatic', 'Rented', 399.00),
(7, 1, 'SUV', 'TESTING', 'TESTING', 2018, 'TESTING', 'TESTING-123', 'TESTING', 'VEHICLE_IMAGES/6816daa2b0b19_1746328226.jpg', '4-5', 'Automatic', 'Rented', 399.00);

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
-- Indexes for table `rental_dtl`
--
ALTER TABLE `rental_dtl`
  ADD PRIMARY KEY (`RENTAL_DTL_ID`),
  ADD KEY `FK_RENTAL_VEHICLE` (`VEHICLE_ID`);

--
-- Indexes for table `rental_hdr`
--
ALTER TABLE `rental_hdr`
  ADD PRIMARY KEY (`RENTAL_HDR_ID`),
  ADD KEY `FK_RENTAL_HDR_DTL` (`RENTAL_DTL_ID`),
  ADD KEY `FK_RENTAL_CUSTOMER` (`CUSTOMER_ID`),
  ADD KEY `FK_RENTAL_PAYMENT` (`PAYMENT_ID`),
  ADD KEY `FK_RENTAL_HDR_VEHICLE` (`VEHICLE_ID`);

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
  MODIFY `CUSTOMER_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `driver`
--
ALTER TABLE `driver`
  MODIFY `DRIVER_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `PAYMENT_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `rental_dtl`
--
ALTER TABLE `rental_dtl`
  MODIFY `RENTAL_DTL_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `rental_hdr`
--
ALTER TABLE `rental_hdr`
  MODIFY `RENTAL_HDR_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `STAFF_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `vehicle`
--
ALTER TABLE `vehicle`
  MODIFY `VEHICLE_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `rental_dtl`
--
ALTER TABLE `rental_dtl`
  ADD CONSTRAINT `FK_RENTAL_VEHICLE` FOREIGN KEY (`VEHICLE_ID`) REFERENCES `vehicle` (`VEHICLE_ID`);

--
-- Constraints for table `rental_hdr`
--
ALTER TABLE `rental_hdr`
  ADD CONSTRAINT `FK_RENTAL_CUSTOMER` FOREIGN KEY (`CUSTOMER_ID`) REFERENCES `customer` (`CUSTOMER_ID`),
  ADD CONSTRAINT `FK_RENTAL_HDR_DTL` FOREIGN KEY (`RENTAL_DTL_ID`) REFERENCES `rental_dtl` (`RENTAL_DTL_ID`),
  ADD CONSTRAINT `FK_RENTAL_HDR_VEHICLE` FOREIGN KEY (`VEHICLE_ID`) REFERENCES `vehicle` (`VEHICLE_ID`),
  ADD CONSTRAINT `FK_RENTAL_PAYMENT` FOREIGN KEY (`PAYMENT_ID`) REFERENCES `payment` (`PAYMENT_ID`);

--
-- Constraints for table `vehicle`
--
ALTER TABLE `vehicle`
  ADD CONSTRAINT `vehicle_ibfk_1` FOREIGN KEY (`STAFF_ID`) REFERENCES `staff` (`STAFF_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
