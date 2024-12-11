# Legal Communication System

The **Legal Communication System** is a RESTful web service built with PHP and MySQL that allows users to agency lawyers and tracing issue status. As well as classification agencies and issues.
There is a recommendation system that shows users highly rated recommended lawyers.

## Table of Contents

-   [Legal Communication System](#legal-communication-system)
    -   [Table of Contents](#table-of-contents)
    -   [Features](#features)
    -   [Getting Started](#getting-started)
        -   [Prerequisites](#prerequisites)
        -   [Installation](#installation)
        -   [Postman Test](#postman-test)

## Features

1. Users

-   Authentication
-   Create new user
-   Retrieve details of all users
-   Retrieve details of a specific user
-   Update user profile
-   Delete user account

2. Lawyers

-   Authentication
-   Create new lawyer
-   Retrieve details of all lawyers
-   Retrieve details of a specific lawyer
-   Choose specialization
-   Update lawyer profile
-   Delete lawyer account

3. Representatives

-   Authentication
-   Create new representative
-   Retrieve details of all representatives
-   Retrieve details of a specific representative
-   Update representative profile
-   Delete representative account

4. Agencies

-   Create new agency
-   Retrieve details of all agencys
-   Retrieve details of a specific agency
-   Agency isolate

5. Issues

-   Create new issue
-   Retrieve details of all issues
-   Retrieve details of a specific issue
-   Update issue status
-   Choose if issue is active or in-active

6. Chat

-   Open secure connection between user and lawyer
-   Real time connection
-   Exchange files

## Getting Started

These instructions will help you set up and run the Legal Communication System on your local machine for development and testing purposes.

### Prerequisites

-   **PHP** (version 7.4 or later)
-   **MySQL** (version 5.7 or later)
-   **Apache** or **Nginx** web server
-   **Composer** (PHP dependency manager, if you are using any PHP libraries)

### Installation

1. **Clone the repository**:

    ```
    git clone https://github.com/osama806/Legal-Communication-System.git
    cd Legal Communication System
    ```

2. **Set up the environment variables:**:

Create a .env file in the root directory and add your database configuration:

```
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=legal-communication-system
DB_USERNAME=root
DB_PASSWORD=password
```

3. **Set up the MySQL database:**:

-   Create a new database in MySQL:
    ```
    CREATE DATABASE legal-communication-system;
    ```
-   Run the provided SQL script to create the necessary tables:
    ```
    mysql -u root -p legal-communication-system < database/schema.sql
    ```

4. **Configure the server**:

-   Ensure your web server (Apache or Nginx) is configured to serve PHP files.
-   Place the project in the appropriate directory (e.g., /var/www/html for Apache on Linux).

5. **Install dependencies (if using Composer)**:

```
composer install
```

6. **Start the server**:

-   For Apache or Nginx, ensure the server is running.
-   The API will be accessible at http://localhost/legal-communication-system.

### Postman Test

-   Link:
    ```
    https://documenter.getpostman.com/view/32954091/2sAY55adm3
    ```
