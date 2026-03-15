 CREATE DATABASE  db;

USE db; 

CREATE TABLE Role (
    roleID INT AUTO_INCREMENT PRIMARY KEY,
    roleName VARCHAR(50) NOT NULL
);

CREATE TABLE User (
    userID INT AUTO_INCREMENT PRIMARY KEY,
    roleID INT,
    userName VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    passHash VARCHAR(255) NOT NULL,
    profilePicture VARCHAR(255),
    status VARCHAR(20),
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (roleID) REFERENCES Role(roleID)
);

CREATE TABLE UserLocation (
    locationID INT AUTO_INCREMENT PRIMARY KEY,
    userID INT,
    district VARCHAR(100),
    area VARCHAR(100),
    postalCode VARCHAR(20),
    latitude DECIMAL(10,7),
    longitude DECIMAL(10,7),

    FOREIGN KEY (userID) REFERENCES User(userID)
);

CREATE TABLE Customer (
    customerID INT PRIMARY KEY,
    
    FOREIGN KEY (customerID) REFERENCES User(userID)
);

CREATE TABLE Provider (
    providerID INT PRIMARY KEY,
    
    FOREIGN KEY (providerID) REFERENCES User(userID)
);

CREATE TABLE ServiceCategory (
    categoryID INT AUTO_INCREMENT PRIMARY KEY,
    categoryName VARCHAR(100) NOT NULL
);

CREATE TABLE Service (
    serviceID INT AUTO_INCREMENT PRIMARY KEY,
    categoryID INT,
    serviceName VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2),

    FOREIGN KEY (categoryID) REFERENCES ServiceCategory(categoryID)
);

CREATE TABLE ProviderService (
    providerID INT,
    serviceID INT,
    
    PRIMARY KEY (providerID, serviceID),

    FOREIGN KEY (providerID) REFERENCES Provider(providerID),
    FOREIGN KEY (serviceID) REFERENCES Service(serviceID)
);

CREATE TABLE AvailabilitySchedule (
    scheduleID INT AUTO_INCREMENT PRIMARY KEY,
    providerID INT,
    dayOfWeek VARCHAR(20),
    startTime TIME,
    endTime TIME,

    FOREIGN KEY (providerID) REFERENCES Provider(providerID)
);

/*JobRequest table to track service requests from customers to providers, including scheduling and payment details
*/
CREATE TABLE JobRequest (
    requestID INT AUTO_INCREMENT PRIMARY KEY,
    customerID INT,
    providerID INT,
    requestDate DATETIME,
    scheduleDate DATETIME,
    totalAmount DECIMAL(10,2),
    status VARCHAR(50),

    FOREIGN KEY (customerID) REFERENCES Customer(customerID),
    FOREIGN KEY (providerID) REFERENCES Provider(providerID)
);

CREATE TABLE Payment (
    paymentID INT AUTO_INCREMENT PRIMARY KEY,
    jobRequestID INT,
    transactionID VARCHAR(100),
    paymentMethod VARCHAR(50),
    paymentAmount DECIMAL(10,2),
    paymentDate DATETIME,
    status VARCHAR(50),

    FOREIGN KEY (jobRequestID) REFERENCES JobRequest(requestID)
);

CREATE TABLE ReviewRating (
    reviewID INT AUTO_INCREMENT PRIMARY KEY,
    jobRequestID INT,
    rating INT,
    comment TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (jobRequestID) REFERENCES JobRequest(requestID)
);
