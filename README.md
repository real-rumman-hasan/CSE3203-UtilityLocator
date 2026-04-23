# Utility Service Provider System

A centralized web-based platform designed to bridge the gap between residents of Dhaka and verified utility service providers (Electricians, Plumbers, Gas technicians, etc.). This project features a robust role-based access control system and secure payment simulation.

## Key Features

* **Four User States:**
    * **Guest:** Browse services and view provider profiles without logging in.
    * **Customer:** Securely book services, manage booking history, and process payments.
    * **Provider:** Dedicated dashboard to view and manage tasks assigned by the Admin.
    * **Admin:** Complete oversight to verify new providers and manually assign paid jobs.
* **Secure Payment Integration:** Integrated with the **SSLCommerz Sandbox** API to demonstrate secure, industry-standard transaction flows.
* **Verification Workflow:** Ensures safety by requiring Admin approval before any service provider goes "Active" on the platform.
* **Responsive Design:** Optimized for both desktop and mobile browsers using CSS media queries.

## Tech Stack

* **Frontend:** HTML5, CSS3, JavaScript (ES6)
* **Backend:** PHP (Server-side logic & Session-based Security)
* **Database:** MySQL (Relational data storage)
* **Design & Management:** Figma (UI/UX Design) and ClickUp (Task Tracking)
* **Payment Gateway:** SSLCommerz API

## Installation & Setup

* **Clone the repository:**  
  `git clone [https://github.com/your-username/utility-service-system.git](https://github.com/your-username/utility-service-system.git)`
* **Setup Local Server:**
    * Move the project folder to your htdocs (XAMPP) or www (WAMP) directory.
    * Start the Apache and MySQL modules in your control panel.
* **Database Configuration:**
    * Open phpMyAdmin and create a new database named utility_found_system.
    * Import the database.sql file from the /sql directory.
* **Access the App:**
    * Visit http://localhost/utility-service-system in your browser.

# System Architecture

The system utilizes a **Role-Based Access Control (RBAC)** model. Permissions are managed via PHP sessions to ensure that data is isolated between the four user states.

# Information Gathering & Research

The development of this project was informed by:
    * Surveys: Data collected from 18–34-year-old urban residents via Google Forms.
    * Field Observation: Analysis of existing informal utility markets in Dhaka.
    * Prototyping: High-fidelity wireframes created in Figma to test user flows.

# Links

* Figma: https://www.figma.com/design/e3unQkaBF1IS1ZtYfzJTVr/DP2-final?node-id=0-1&t=FIXXmEBiLIUWbJqN-1
* ClickUp: https://app.clickup.com/90182397560/v/li/901815620134
