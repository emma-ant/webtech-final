CREATE TABLE IF NOT EXISTS `a_user` (
  `userID` int(11) NOT NULL AUTO_INCREMENT,
  `firstName` varchar(100) NOT NULL,
  `lastName` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `passwordHash` varchar(255) NOT NULL,
  `birthDate` date DEFAULT NULL,
  `role` enum('subscriber','publisher','admin') DEFAULT 'subscriber',
  `profilePicture` varchar(500) DEFAULT NULL,
  `dateCreated` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`userID`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
);

CREATE TABLE IF NOT EXISTS `a_publisher` (
    `publisherID` int(11) NOT NULL AUTO_INCREMENT,
    `userID` int(11) NOT NULL,
    `publisherName` varchar(100) NOT NULL,
    `description` text DEFAULT NULL,
    `createdAt` datetime DEFAULT current_timestamp(),
    PRIMARY KEY (`publisherID`),
    FOREIGN KEY (`userID`) REFERENCES `a_user`(`userID`) ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS `a_contact` (
    `contactID` int(11) NOT NULL AUTO_INCREMENT,
    `contactName` varchar(100) NOT NULL,
    `contactEmail` varchar(100) NOT NULL,
    `contactMessage` text NOT NULL,
    `createdAt` datetime DEFAULT current_timestamp(),
    PRIMARY KEY (`contactID`)
);

CREATE TABLE IF NOT EXISTS `a_subscriber` (
    `subscriberID` int(11) NOT NULL AUTO_INCREMENT,
    `userID` int(11) NOT NULL,
    `subscriberName` varchar(100) NOT NULL,
    `createdAt` datetime DEFAULT current_timestamp(),
    PRIMARY KEY (`subscriberID`),
    FOREIGN KEY (`userID`) REFERENCES `a_user`(`userID`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `a_subscription` (
    `subscriptionID` int(11) NOT NULL AUTO_INCREMENT,
    `userID` int(11) NOT NULL,
    `publisherID` int(11) NOT NULL,
    `startDate` date NOT NULL,
    `status` enum('Active','Inactive') DEFAULT 'Active',
    `createdAt` datetime DEFAULT current_timestamp(),
    PRIMARY KEY (`subscriptionID`),
    FOREIGN KEY (`userID`) REFERENCES `a_user`(`userID`) ON DELETE CASCADE,
    FOREIGN KEY (`publisherID`) REFERENCES `a_publisher`(`publisherID`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `category` (
    `categoryID` int(11) NOT NULL AUTO_INCREMENT,
    `categoryName` varchar(100) NOT NULL,
    `description` text DEFAULT NULL,
    `parentCategoryID` int(11) DEFAULT NULL,
    PRIMARY KEY (`categoryID`),
    UNIQUE KEY `categoryName` (`categoryName`),
    FOREIGN KEY (`parentCategoryID`) REFERENCES `category`(`categoryID`)
);

CREATE TABLE IF NOT EXISTS `a_post` (
    `postID` int(11) NOT NULL AUTO_INCREMENT,
    `publisherID` int(11) NOT NULL,
    `categoryID` int(11) DEFAULT NULL,
    `title` varchar(100) NOT NULL,
    `content` text NOT NULL,
    `createdAt` datetime DEFAULT current_timestamp(),
    PRIMARY KEY (`postID`),
    FOREIGN KEY (`publisherID`) REFERENCES `a_publisher`(`publisherID`) ON DELETE CASCADE,
    FOREIGN KEY (`categoryID`) REFERENCES `category`(`categoryID`) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS `a_read` (
    `readID` int(11) NOT NULL AUTO_INCREMENT,
    `subscriberID` int(11) NOT NULL,
    `postID` int(11) NOT NULL,
    `readDate` datetime DEFAULT current_timestamp(),
    PRIMARY KEY (`readID`),
    FOREIGN KEY (`subscriberID`) REFERENCES `a_subscriber`(`subscriberID`) ON DELETE CASCADE,
    FOREIGN KEY (`postID`) REFERENCES `a_post`(`postID`) ON DELETE CASCADE,
    UNIQUE KEY `unique_read` (`subscriberID`, `postID`)
);

CREATE TABLE IF NOT EXISTS `a_reviews` (
    `reviewID` int(11) NOT NULL AUTO_INCREMENT,
    `postID` int(11) NOT NULL,
    `subscriberID` int(11) NOT NULL,
    `comment` text NOT NULL,
    `createdAt` datetime DEFAULT current_timestamp(),
    PRIMARY KEY (`reviewID`),
    FOREIGN KEY (`postID`) REFERENCES `a_post`(`postID`) ON DELETE CASCADE,
    FOREIGN KEY (`subscriberID`) REFERENCES `a_subscriber`(`subscriberID`) ON DELETE CASCADE
);