# Account Ledger Server PHP

<div align="center">

[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)
[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-777BB4.svg?logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479A1.svg?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED.svg?logo=docker&logoColor=white)](https://www.docker.com/)

**A robust PHP-based REST API server for managing personal and business financial accounts, transactions, and ledger operations.**

[Features](#features) • [Installation](#installation) • [API Reference](#api-reference) • [Docker](#docker-deployment) • [Contributing](#contributing)

</div>

---

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
  - [Local Development Setup](#local-development-setup)
  - [Database Setup](#database-setup)
  - [Configuration](#configuration)
- [Docker Deployment](#docker-deployment)
- [API Reference](#api-reference)
  - [Authentication & Users](#authentication--users)
  - [Accounts Management](#accounts-management)
  - [Transactions (v1)](#transactions-v1)
  - [Transactions (v2)](#transactions-v2)
  - [Configuration](#configuration-endpoints)
- [Database Schema](#database-schema)
- [Development](#development)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)
- [Author](#author)

---

## 🔍 Overview

Account Ledger Server PHP is a backend API server designed to power financial tracking applications. It provides a complete set of RESTful endpoints for managing users, accounts, and transactions in a double-entry accounting style. The server is built with PHP and MySQL, offering a lightweight yet powerful solution for personal finance or small business accounting needs.

### Use Cases

- 💰 Personal finance tracking
- 📊 Business expense management
- 🏦 Account balance monitoring
- 📈 Transaction history and reporting
- 🔄 Multi-user financial management

---

## ✨ Features

### Core Features

- **User Management**: Create and authenticate users with secure credentials
- **Account Hierarchy**: Support for hierarchical account structures (parent/child accounts)
- **Account Types**: Multiple account types including:
  - Assets
  - Equity
  - Expenses
  - Income
  - Liabilities
- **Transaction Tracking**: Record and manage financial transactions
- **Double-Entry Support**: Transactions v2 supports from/to account tracking
- **Multi-Currency**: Commodity type and value support for different currencies
- **Tax Tracking**: Mark accounts as taxable for reporting purposes

### Technical Features

- 📦 **RESTful API** - Clean HTTP endpoints for all operations
- 🐳 **Docker Ready** - Containerized deployment support
- 🔧 **Environment-Based Config** - Database URL via environment variables
- 📅 **Automated Backups** - GitHub Actions workflow for database backups
- 🔄 **Timezone Support** - Automatic timezone conversion for timestamps
- 📝 **JSON Responses** - Consistent JSON format for all API responses

---

## 🛠 Technology Stack

| Component | Technology |
|-----------|------------|
| **Language** | PHP 8.0+ |
| **Web Server** | Apache 2.4 |
| **Database** | MySQL 5.7+ / MariaDB |
| **Container** | Docker with PHP 8.2 Apache |
| **Extensions** | mysqli, gettext, json |
| **Runtime Manager** | mise (with ubi:adwinying/php) |

---

## 📋 Prerequisites

Before you begin, ensure you have the following installed:

- **PHP 8.0.12** or higher
- **MySQL 5.7** or higher (or MariaDB)
- **Apache** or **Nginx** web server
- **Composer** (for dependency management)
- **Git** (for cloning the repository)

### Required PHP Extensions

```bash
# Install required PHP extensions
sudo apt-get install php-mysqli php-gettext php-json
```

Or on Fedora/RHEL:

```bash
sudo dnf install php-mysqli php-gettext php-json
```

---

## 🚀 Installation

### Local Development Setup

1. **Clone the repository**

```bash
git clone https://github.com/Baneeishaque/Account-Ledger-Server-PHP.git
cd Account-Ledger-Server-PHP
```

2. **Install dependencies using Composer**

```bash
composer install
```

3. **Configure your web server**

For Apache, add a virtual host pointing to the `http_API` directory:

```apache
<VirtualHost *:80>
    ServerName account-ledger.local
    DocumentRoot /path/to/Account-Ledger-Server-PHP/http_API
    
    <Directory /path/to/Account-Ledger-Server-PHP/http_API>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

For Nginx:

```nginx
server {
    listen 80;
    server_name account-ledger.local;
    root /path/to/Account-Ledger-Server-PHP/http_API;
    index index.php;
    
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.0-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

### Database Setup

1. **Create the database**

```sql
CREATE DATABASE account_ledger CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. **Create required tables**

```sql
-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Accounts table
CREATE TABLE accounts (
    account_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(250) NOT NULL,
    name VARCHAR(250) NOT NULL,
    parent_account_id INT DEFAULT 0,
    account_type VARCHAR(50) NOT NULL,
    notes VARCHAR(250),
    commodity_type VARCHAR(50),
    commodity_value VARCHAR(50),
    owner_id INT NOT NULL,
    taxable CHAR(1) DEFAULT 'F',
    place_holder CHAR(1) DEFAULT 'F',
    insertion_date_time DATETIME,
    FOREIGN KEY (owner_id) REFERENCES users(id)
);

-- Transactions v1 table
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_date_time DATETIME NOT NULL,
    particulars VARCHAR(150),
    amount DOUBLE NOT NULL,
    insertion_date_time DATETIME,
    inserter_id INT NOT NULL,
    FOREIGN KEY (inserter_id) REFERENCES users(id)
);

-- Transactions v2 table (with from/to accounts)
CREATE TABLE transactionsv2 (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_date_time DATETIME NOT NULL,
    particulars VARCHAR(250),
    amount DOUBLE NOT NULL,
    insertion_date_time DATETIME,
    inserter_id INT NOT NULL,
    from_account_id INT NOT NULL,
    to_account_id INT NOT NULL,
    FOREIGN KEY (inserter_id) REFERENCES users(id),
    FOREIGN KEY (from_account_id) REFERENCES accounts(account_id),
    FOREIGN KEY (to_account_id) REFERENCES accounts(account_id)
);

-- Configuration table
CREATE TABLE configuration (
    id INT AUTO_INCREMENT PRIMARY KEY,
    version_code INT,
    version_name VARCHAR(50),
    system_status VARCHAR(10)
);
```

3. **Create database views** (optional but recommended)

Run the SQL from `db_structures/account_ledger_views.sql`:

```bash
mysql -u your_user -p account_ledger < db_structures/account_ledger_views.sql
```

### Configuration

The application uses environment variables for database configuration. Set the `CLEARDB_DATABASE_URL` environment variable with your MySQL connection URL:

```bash
# Format: mysql://user:password@host/database
export CLEARDB_DATABASE_URL="mysql://root:password@localhost/account_ledger"
```

For Apache, add to your virtual host or `.htaccess`:

```apache
SetEnv CLEARDB_DATABASE_URL "mysql://root:password@localhost/account_ledger"
```

For Nginx with PHP-FPM, add to your PHP-FPM pool configuration:

```ini
env[CLEARDB_DATABASE_URL] = "mysql://root:password@localhost/account_ledger"
```

---

## 🐳 Docker Deployment

### Quick Start with Docker

1. **Build the Docker image**

```bash
docker build -t account-ledger-server .
```

2. **Run the container**

```bash
docker run -d \
    -p 8080:80 \
    -e CLEARDB_DATABASE_URL="mysql://user:pass@host/database" \
    --name account-ledger \
    account-ledger-server
```

3. **Access the API**

The API will be available at `http://localhost:8080/http_API/`

### Docker Compose (Recommended)

Create a `docker-compose.yml`:

```yaml
version: '3.8'

services:
  app:
    build: .
    ports:
      - "8080:80"
    environment:
      - CLEARDB_DATABASE_URL=mysql://ledger_user:ledger_pass@db/account_ledger
    depends_on:
      - db

  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root_password
      MYSQL_DATABASE: account_ledger
      MYSQL_USER: ledger_user
      MYSQL_PASSWORD: ledger_pass
    volumes:
      - mysql_data:/var/lib/mysql
    ports:
      - "3306:3306"

volumes:
  mysql_data:
```

Run with:

```bash
docker-compose up -d
```

---

## 📖 API Reference

All API endpoints return JSON responses. The base URL depends on your deployment configuration.

### Response Status Codes

| Status | Meaning |
|--------|---------|
| `0` | Success |
| `1` | Error / No data found |
| `2` | No data found (specific endpoints) |

### Authentication & Users

#### Get All Users

```http
GET /http_API/getUsers.php
```

**Response:**
```json
{
    "status": 0,
    "users": [
        {
            "id": "1",
            "username": "user1",
            "password": "hashed_password"
        }
    ]
}
```

#### Authenticate User (Login)

```http
GET /http_API/select_User.php?username={username}&password={password}
```

**Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `username` | string | User's username |
| `password` | string | User's password |

**Response:**
```json
{
    "user_count": "1",
    "id": "5"
}
```

#### Register New User

```http
POST /http_API/insertUser.php
```

**Body Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `username` | string | Desired username |
| `passcode` | string | User's password |

**Response:**
```json
{
    "status": "0"
}
```

> **Note:** When a new user is created, default accounts (Assets, Equity, Expenses, Income, Liabilities) are automatically created.

---

### Accounts Management

#### Get All Accounts

```http
GET /http_API/getAccounts.php
```

**Response:**
```json
[
    {"status": "0"},
    {
        "account_id": "1",
        "full_name": "Assets",
        "name": "Assets",
        "parent_account_id": "0",
        "account_type": "ASSET",
        "notes": "",
        "commodity_type": "CURRENCY",
        "commodity_value": "INR",
        "owner_id": "1",
        "taxable": "F",
        "place_holder": "T",
        "insertion_date_time": "2023-01-01 00:00:00"
    }
]
```

#### Get User Accounts

```http
GET /http_API/select_User_Accounts.php?user_id={user_id}&parent_account_id={parent_id}
```

**Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `user_id` | int | User's ID |
| `parent_account_id` | int | Parent account ID (0 for root accounts) |

#### Get User Accounts (Full)

```http
GET /http_API/select_User_Accounts_full.php?user_id={user_id}
```

Returns all accounts for a user without hierarchy filtering.

**Response:**
```json
{
    "status": 0,
    "accounts": [
        {
            "account_id": "1",
            "full_name": "Assets",
            "name": "Assets",
            "parent_account_id": "0",
            "account_type": "ASSET",
            "notes": "",
            "commodity_type": "CURRENCY",
            "commodity_value": "INR",
            "owner_id": "5",
            "taxable": "F",
            "place_holder": "T"
        }
    ]
}
```

#### Get User Accounts v2

```http
GET /http_API/select_User_Accounts_v2.php?user_id={user_id}&parent_account_id={parent_id}
```

Returns accounts with consistent response format.

#### Create New Account

```http
POST /http_API/insert_Account.php
```

**Body Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `full_name` | string | Full hierarchical name |
| `name` | string | Short display name |
| `parent_account_id` | int | Parent account ID |
| `account_type` | string | Type: ASSET, EQUITY, EXPENSE, INCOME, LIABILITY |
| `notes` | string | Optional notes |
| `commodity_type` | string | Type: CURRENCY, STOCK, etc. |
| `commodity_value` | string | Value: INR, USD, etc. |
| `owner_id` | int | User ID who owns this account |
| `taxable` | char | T/F for taxable status |
| `place_holder` | char | T/F for placeholder accounts |

---

### Transactions (v1)

Basic transaction management without account linking.

#### Get All Transactions

```http
GET /http_API/getTransactions.php
```

#### Get User Transactions

```http
POST /http_API/select_User_Transactions.php
```

**Body Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `user_id` | int | User's ID |

#### Create Transaction

```http
POST /http_API/insert_Transaction.php
```

**Body Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `event_date_time` | datetime | Transaction date/time |
| `user_id` | int | User ID |
| `particulars` | string | Transaction description |
| `amount` | double | Transaction amount |

---

### Transactions (v2)

Enhanced transactions with from/to account support (double-entry style).

#### Get All Transactions v2

```http
GET /http_API/getTransactionsV2.php
```

**Response:**
```json
[
    {"status": "0"},
    {
        "id": "1",
        "event_date_time": "2023-01-15 10:30:00",
        "particulars": "Salary deposit",
        "amount": "50000.00",
        "insertion_date_time": "2023-01-15 10:30:00",
        "inserter_id": "1",
        "from_account_id": "10",
        "to_account_id": "5"
    }
]
```

#### Get User Transactions v2

```http
GET /http_API/select_User_Transactions_v2.php?user_id={user_id}&account_id={account_id}
```

**Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `user_id` | int | User's ID |
| `account_id` | int | Filter by account ID |

**Response includes:**
- Transaction details
- From account name and full name
- To account name and full name

#### Get User Transactions v3 (Recursive)

```http
GET /http_API/select_User_Transactions_v3.php?user_id={user_id}&account_id={account_id}
```

Recursively fetches transactions for an account and all its child accounts.

#### Get Transactions After Date

```http
GET /http_API/select_User_Transactions_After_Specified_Date.php?user_id={user_id}&account_id={account_id}&specified_date={date}
```

**Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `user_id` | int | User's ID |
| `account_id` | int | Account ID |
| `specified_date` | datetime | Filter transactions after this date |

#### Create Transaction v2

```http
POST /http_API/insert_Transaction_v2.php
```

**Body Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `event_date_time` | datetime | Transaction date/time |
| `user_id` | int | User ID |
| `particulars` | string | Transaction description |
| `amount` | double | Transaction amount |
| `from_account_id` | int | Source account ID |
| `to_account_id` | int | Destination account ID |

#### Update Transaction v2

```http
POST /http_API/update_Transaction_v2.php
```

**Body Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | int | Transaction ID to update |
| `event_date_time` | datetime | New date/time |
| `particulars` | string | New description |
| `amount` | double | New amount |
| `from_account_id` | int | New source account |
| `to_account_id` | int | New destination account |

#### Delete Transaction v2

```http
POST /http_API/delete_Transaction_v2.php
```

**Body Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | int | Transaction ID to delete |

---

### Configuration Endpoints

#### Get Configuration

```http
GET /http_API/getConfiguration.php
```

Returns full configuration table.

#### Get System Configuration

```http
GET /http_API/select_Configuration.php
```

Returns system status and version information.

**Response:**
```json
[
    {
        "system_status": "1",
        "version_code": "5",
        "version_name": "1.0.5"
    }
]
```

---

## 🗄 Database Schema

### Entity Relationship

```
┌─────────────┐         ┌─────────────────┐
│   users     │         │  configuration  │
├─────────────┤         ├─────────────────┤
│ id (PK)     │         │ id (PK)         │
│ username    │         │ version_code    │
│ password    │         │ version_name    │
└──────┬──────┘         │ system_status   │
       │                └─────────────────┘
       │
       ├────────────────────────────────────┐
       │                                    │
       ▼                                    ▼
┌─────────────────┐              ┌──────────────────────┐
│    accounts     │              │    transactions      │
├─────────────────┤              ├──────────────────────┤
│ account_id (PK) │              │ id (PK)              │
│ full_name       │              │ event_date_time      │
│ name            │              │ particulars          │
│ parent_account_id│             │ amount               │
│ account_type    │              │ insertion_date_time  │
│ notes           │              │ inserter_id (FK)     │
│ commodity_type  │              └──────────────────────┘
│ commodity_value │
│ owner_id (FK)   │              ┌──────────────────────┐
│ taxable         │              │   transactionsv2     │
│ place_holder    │◄─────────────├──────────────────────┤
│ insertion_date_time│           │ id (PK)              │
└─────────────────┘              │ event_date_time      │
       ▲                         │ particulars          │
       │                         │ amount               │
       └─────────────────────────│ from_account_id (FK) │
                                 │ to_account_id (FK)   │
                                 │ inserter_id (FK)     │
                                 │ insertion_date_time  │
                                 └──────────────────────┘
```

### Account Types

| Type | Description |
|------|-------------|
| `ASSET` | Things you own (Bank accounts, Cash, Property) |
| `EQUITY` | Net worth / Owner's equity |
| `EXPENSE` | Money spent (Food, Utilities, Rent) |
| `INCOME` | Money received (Salary, Interest, Dividends) |
| `LIABILITY` | Money owed (Loans, Credit cards) |

---

## 💻 Development

### Project Structure

```
Account-Ledger-Server-PHP/
├── .github/
│   └── workflows/
│       └── database-backup.yml    # Automated backup workflow
├── api_test/                       # HTTP test files for IDE testing
│   ├── *.http                      # JetBrains HTTP client tests
│   └── http-client.env.json        # Environment configuration
├── db_backup_jobs/                 # SQLyog backup configurations
├── db_backup_logs_SQLyog/          # Backup logs
├── db_scripts/                     # Utility SQL scripts
│   ├── account_Search.sql
│   ├── get_transactions.sql
│   ├── merge_user_accounts.sql
│   └── ...
├── db_structures/                  # Database schema files
│   └── account_ledger_views.sql
├── http_API/                       # API endpoint files
│   ├── config.php                  # Database configuration
│   ├── common_functions.php        # Shared functions
│   ├── getAccounts.php
│   ├── getUsers.php
│   ├── insert_*.php                # Create operations
│   ├── select_*.php                # Read operations
│   ├── update_*.php                # Update operations
│   └── delete_*.php                # Delete operations
├── .gitignore
├── composer.json                   # PHP dependencies
├── composer.lock
├── Dockerfile                      # Docker configuration
├── LICENSE                         # GPL-3.0 License
├── mise.toml                       # mise runtime configuration
└── renovate.json                   # Dependency update config
```

### Using mise for Development

This project uses [mise](https://mise.jdx.dev/) for managing PHP runtime:

```bash
# Install mise
curl https://mise.run | sh

# Install PHP runtime
mise install

# Verify PHP version
php --version
```

### API Testing with JetBrains IDEs

The `api_test/` directory contains `.http` files compatible with JetBrains HTTP Client:

1. Open any `.http` file in PhpStorm or IntelliJ IDEA
2. Configure the environment in `http-client.env.json`
3. Run individual requests by clicking the play button

Example test file (`api_test/getAccounts.http`):

```http
GET https://{{host}}/{{http_API_folder}}/getAccounts.{{file_extension}}
Accept: application/json

###
```

---

## 🧪 Testing

### Manual API Testing

Use `curl` for testing endpoints:

```bash
# Test getting users
curl -X GET http://localhost:8080/http_API/getUsers.php

# Test user login
curl -X GET "http://localhost:8080/http_API/select_User.php?username=testuser&password=testpass"

# Test creating a transaction
curl -X POST http://localhost:8080/http_API/insert_Transaction_v2.php \
    -d "event_date_time=2023-01-15 10:30:00" \
    -d "user_id=1" \
    -d "particulars=Test transaction" \
    -d "amount=100.00" \
    -d "from_account_id=5" \
    -d "to_account_id=10"
```

### Using httpie

```bash
# Install httpie
pip install httpie

# Get all accounts
http GET http://localhost:8080/http_API/getAccounts.php

# Create user
http POST http://localhost:8080/http_API/insertUser.php \
    username=newuser passcode=password123
```

---

## 🤝 Contributing

We welcome contributions from the community! Here's how you can help:

### Getting Started

1. **Fork the repository**

```bash
# Clone your fork
git clone https://github.com/YOUR_USERNAME/Account-Ledger-Server-PHP.git
cd Account-Ledger-Server-PHP
```

2. **Create a feature branch**

```bash
git checkout -b feature/your-feature-name
```

3. **Make your changes**

Follow the existing code style and patterns in the codebase.

4. **Test your changes**

Ensure all API endpoints work correctly with your modifications.

5. **Commit your changes**

```bash
git add .
git commit -m "feat: add your feature description"
```

6. **Push and create a Pull Request**

```bash
git push origin feature/your-feature-name
```

### Code Style Guidelines

- Use consistent PHP formatting (PSR-12 recommended)
- Include proper PHPDoc comments for functions
- Use meaningful variable and function names
- Handle errors gracefully with proper status codes
- Validate all input parameters

### Commit Message Format

We follow conventional commits:

- `feat:` - New feature
- `fix:` - Bug fix
- `docs:` - Documentation changes
- `style:` - Code style changes (formatting)
- `refactor:` - Code refactoring
- `test:` - Adding or modifying tests
- `chore:` - Maintenance tasks

### Reporting Issues

1. Check existing issues before creating a new one
2. Use the issue template if available
3. Include:
   - Clear description of the problem
   - Steps to reproduce
   - Expected vs actual behavior
   - Environment details (PHP version, OS, etc.)

### Pull Request Guidelines

- Keep PRs focused on a single feature or fix
- Update documentation if needed
- Ensure backward compatibility
- Add tests for new functionality when applicable

---

## 📄 License

This project is licensed under the **GNU General Public License v3.0** - see the [LICENSE](LICENSE) file for details.

```
Account Ledger Server PHP
Copyright (C) 2023 Banee Ishaque K

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

---

## 👤 Author

**Banee Ishaque K**

- 📧 Email: [Baneeishaque@gmail.com](mailto:Baneeishaque@gmail.com)
- 🐙 GitHub: [@Baneeishaque](https://github.com/Baneeishaque)

---

## 🙏 Acknowledgments

- PHP community for the robust ecosystem
- MySQL for the reliable database engine
- Docker for containerization support
- All contributors who help improve this project

---

<div align="center">

**⭐ Star this repository if you find it useful! ⭐**

Made with ❤️ by Banee Ishaque K

</div>
