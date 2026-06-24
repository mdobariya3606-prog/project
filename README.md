# Private Cloud-Based Document Sharing Platform

A secure document sharing platform built with Core PHP and MySQL that provides private document storage, role-based access control, and invite-only document sharing.

## Overview

This application allows users to securely upload and manage documents while maintaining strict access control. Documents are private by default and can only be accessed by the owner or explicitly authorized users.

The platform includes authentication, document management, file sharing permissions, administrative controls, and storage monitoring.

## Features

### Authentication & Security

* User registration
* User login
* Forgot password
* Password reset
* Change password
* Session-based authentication
* Password hashing
* Authorization checks
* Protection against unauthorized file access
* Input validation and sanitization

### User Account Management

* Update profile information
* Change password
* View personal documents
* View documents shared by others

### Document Management

* Upload documents
* Download documents
* Rename documents
* Delete documents
* View document details
* Store files securely on the server
* Support for:

  * PDF
  * DOCX
  * XLSX
  * PPT
  * PPTX
  * Other supported document formats

### Invite-Only Sharing System

* Share files with selected users
* Revoke access at any time
* View users with access
* Download shared files
* File visibility is private by default
* No public sharing links

### Access Control

* Owner access
* Shared-user access
* Admin override permissions
* Role-based authorization
* Permission validation before every file operation

### Admin Panel

#### User Management

* View all users
* Activate users
* Deactivate users
* Delete users

#### Storage Monitoring

* View total storage usage
* View per-user storage usage
* Identify heavy storage users

#### Document Administration

* Grant document access
* Revoke document access
* Force delete documents
* Manage sharing permissions

#### Dashboard

* Total users
* Total documents
* Total storage used

### Audit Logging

* Document uploads
* Document downloads
* Sharing activity
* Access history
* Administrative actions

## System Roles

### Guest

Unauthenticated users can:

* Register
* Login
* Request password reset

Restrictions:

* Cannot access documents
* Cannot access user information
* Cannot access file URLs directly

### Registered User

Users can:

* Manage profile
* Upload files
* Rename files
* Delete files
* Download files
* Share files with selected users
* Revoke access
* View shared documents

### Administrator

Administrators can:

* Manage users
* Monitor storage usage
* Override document permissions
* Remove documents
* View system statistics

## Technology Stack

### Backend

* PHP
* MySQL
* PHPMailer

### Frontend

* HTML
* CSS

### Server

* Apache
* XAMPP

## Database Structure

### Core Tables

```text
users
documents
document_user_permissions
password_resets
audit_logs
```

Optional tables:

```text
folders
storage_logs
```

## Security Measures

* Password hashing
* Prepared statements
* SQL Injection protection
* XSS protection
* Session-based authentication
* Authorization middleware
* Access validation before file download
* Private document storage
* No public file URLs

## Installation

### Clone Repository

```bash
git clone <repository-url>
cd project
```

### Configure Database

1. Create a MySQL database.
2. Import the SQL schema.
3. Update database credentials in the configuration file.

### Configure Environment

Create a configuration file and update:

```env
DB_HOST=localhost
DB_NAME=your_database
DB_USER=root
DB_PASS=

MAIL_HOST=
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_PORT=
```

### Start Server

Using XAMPP:

1. Start Apache
2. Start MySQL
3. Open:

```text
http://localhost/project
```

## Project Structure

```text
project/
│
├── config/
│   ├── bootstrap.php
│   ├── conn.php
│   └── email.php
│
├── public/
│   ├── helper.php
│   ├── session.php
│   │
│   ├── admin/
│   │   ├── dashboard.php
│   │   ├── manage-users.php
│   │   ├── change-status.php
│   │   ├── change-user-password.php
│   │   ├── change-share-access.php
│   │   └── delete-user.php
│   │
│   ├── auth/
│   │   ├── login.php
│   │   ├── register.php
│   │   └── logout.php
│   │
│   ├── files/
│   │   ├── add-file.php
│   │   ├── all-files.php
│   │   ├── shared-files.php
│   │   ├── share-file.php
│   │   ├── permissions.php
│   │   ├── revoke-permission.php
│   │   ├── download.php
│   │   ├── rename.php
│   │   └── delete-file.php
│   │
│   ├── functions/
│   │   ├── Helper.php
│   │   ├── forget-password.php
│   │   ├── verify-otp.php
│   │   └── reset-password.php
│   │
│   ├── middleware/
│   │   ├── auth.php
│   │   ├── admin.php
│   │   ├── permission.php
│   │   ├── file.php
│   │   ├── share-access.php
│   │   └── status.php
│   │
│   ├── user/
│   │   ├── profile.php
│   │   ├── update-profile.php
│   │   └── change-password.php
│   │
│   ├── include/
│   │   └── header.php
│   │
│   └── css/
│       └── style.css
│
├── uploads/
│   ├── .htaccess
│   ├── admin/
│   └── user/
│       ├── 2/
│       ├── 9/
│       ├── 10/
│       └── 12/
│
└── README.md
```

## Future Improvements

* Folder management
* Document preview
* API integration
* Docker support
* File versioning
* Two-factor authentication
* Email verification
* Activity analytics
* Cloud storage integration (AWS S3, Azure Blob Storage)

## Screenshots

### Login Page

<img width="1365" height="632" alt="image" src="https://github.com/user-attachments/assets/94345dd3-5d5b-4278-bb40-aa1628457882" />

### Admin Dashboard

<img width="1366" height="633" alt="FireShot Capture 001 - Header -  localhost" src="https://github.com/user-attachments/assets/97eb41d7-741d-4519-b5a4-927f81446c18" />

### Upload Document

<img width="1366" height="633" alt="FireShot Capture 002 - Header -  localhost" src="https://github.com/user-attachments/assets/45c9b5a8-f20c-4e84-a0c5-be95fa84d7f2" />

### Shared Documents

<img width="1366" height="633" alt="FireShot Capture 003 - Header -  localhost" src="https://github.com/user-attachments/assets/2fb6d09c-7a6f-455f-be8d-b3bad1bf254a" />

## License

This project is developed for educational and learning purposes.
