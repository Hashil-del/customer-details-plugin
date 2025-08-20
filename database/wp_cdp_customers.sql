-- Table structure
CREATE TABLE `wp_cdp_customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `dob` date NOT NULL,
  `sex` varchar(10) NOT NULL,
  `cr_number` varchar(50) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(50) NOT NULL,
  `country` varchar(50) NOT NULL,
  `status` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dummy Data
INSERT INTO `wp_cdp_customers` (`name`, `email`, `phone`, `dob`, `sex`, `cr_number`, `address`, `city`, `country`, `status`) VALUES
('John Doe', 'john@example.com', '9876543210', '1990-05-12', 'Male', 'CR12345', '123 Street, NY', 'New York', 'USA', 'active'),
('Jane Smith', 'jane@example.com', '9123456780', '1995-03-22', 'Female', 'CR67890', '456 Avenue, LA', 'Los Angeles', 'USA', 'inactive');
