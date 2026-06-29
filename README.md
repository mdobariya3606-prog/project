# Private Cloud-Based Document Sharing Platform

A secure, invite-only document sharing platform built with Core PHP and MySQL. Provides private document storage, role-based access control, and strict invite-only sharing — with zero public access to any file or user data.

## Overview

This application lets users securely upload and manage documents with strict access control enforced at every layer. Documents are private by default and can only be accessed by the owner or explicitly authorized users.

The platform covers authentication, document management, invite-only sharing, administrative controls, audit logging, and storage monitoring — all built without a framework using Core PHP and MySQL.

---

## Features

### Authentication & Security

* User registration (email + password)
* Email verification
* User login
* Forgot password
* OTP-based password reset
* Change password
* Session-based authentication
* Password hashing (bcrypt via `password_hash`)
* Authorization checks on every request
* Protection against unauthorized file URL access
* Input validation and sanitization
* Prepared statements (SQL injection protection)
* XSS protection (output escaping)

### User Account Management

* Update profile (name, email)
* Change password
* View personal documents
* View documents shared by others
* View personal storage usage

### Document Management

* Upload documents (PDF, DOCX, XLSX, PPT, PPTX)
* Download documents
* Rename documents
* Delete documents
* View document details (file size, upload date, shared users list)
* Optional folder organization
* Files stored securely — not publicly accessible via direct URL

### Invite-Only Sharing System

* Share files with specific registered users only
* Select users from the existing registered user list
* Revoke access at any time
* View the list of users who have access to a file
* File visibility is private by default
* No public sharing links
* Shared users cannot reshare a file further
* Permission validated against the database before every file operation

### Access Control

* Owner access (full control)
* Shared-user access (download, share, full access)
* Admin override permissions
* Role-based authorization
* Permission middleware applied before every file operation

### Admin Panel

#### User Management

* View all registered users
* Activate / deactivate users
* Delete users
* Reset user passwords
* Disable sharing capability for specific users

#### Storage Monitoring

* View total system storage usage
* View per-user storage usage
* Identify heavy storage users

#### Document Administration

* Grant document access manually
* Revoke document access manually
* Force delete any document
* Manage sharing permissions across all users

#### Dashboard

* Total users
* Total documents
* Total storage used

### Audit Logging

* Document uploads
* Document downloads
* Sharing activity (who shared with whom)
* File access history (who accessed which file)

---

## System Roles

### Guest (Unauthenticated)

**Can:**

* Register (email + password)
* Login
* Request password reset
* Verify email

**Cannot:**

* View any documents
* View any user information
* Access any file URL directly

---

### Registered User (Document Owner / Shared User)

#### Account Management

* Update profile (name, email)
* Change password
* View personal storage usage

#### Document Management (Owner Side)

* Upload documents (PDF, DOCX, XLSX, PPT, PPTX)
* Create sub folders
* Rename files
* Delete files
* Download files
* View file details (size, upload date, shared users)

#### Invite-Only Sharing

* Share files with specific registered users
* Revoke access at any time
* View who currently has access to a file

#### Shared With Me

* View list of files shared by others
* Download permitted files
* Cannot reshare files withour permission

---

### Administrator

#### User Management

* View all registered users
* Activate / deactivate users
* Delete users
* Reset user passwords
* Disable sharing capability for specific users

#### Storage Monitoring

* View total system storage usage
* View per-user storage usage
* Identify heavy storage users

#### Access Control Override

* Grant document access manually
* Revoke document access manually
* Force delete any document
* Disable sharing for specific users

#### Audit & Dashboard

* View all audit logs (uploads, downloads, shares, admin actions)
* Dashboard overview: total users, total documents, total storage

---

## Out of Scope

* Public REST API / separated frontend layer
* Social login / OAuth
* Public file sharing links

---

## Technology Stack

### Backend

* PHP (Core — no framework)
* MySQL
* PHPMailer (password reset emails, invite notifications)

### Frontend

* HTML
* CSS (responsive — mobile and desktop)

### Server

* Apache
* XAMPP (local development)
* Docker (containerized deployment)

---

## Database Structure
 
### Tables
 
#### `user_info`
| Column | Type | Notes |
|---|---|---|
| `id` | int | Primary key |
| `name` | varchar(25) | |
| `email` | varchar(255) | Unique |
| `password` | text | bcrypt hashed |
| `role` | enum | `USER`, `ADMIN` |
| `status` | enum | `ACTIVE`, `INACTIVE` |
| `can_share` | enum | `YES`, `NO` |
| `created_at` | timestamp | |
 
#### `document_info`
| Column | Type | Notes |
|---|---|---|
| `document_id` | int | Primary key |
| `original_name` | varchar(255) | Display name |
| `file_name` | text | Stored filename |
| `file_size` | text | |
| `extension` | varchar(10) | |
| `owner_id` | int | FK → `user_info.id` |
| `folder_id` | int | FK → `user_folder.id` |
| `created_at` | timestamp | |
 
#### `document_user_permission`
| Column | Type | Notes |
|---|---|---|
| `id` | int | Primary key |
| `user_id` | int | FK → `user_info.id` |
| `document_id` | int | FK → `document_info.document_id` |
| `type` | enum | `DOWNLOAD`, `SHARE`, `ALL` |
 
#### `user_folder`
| Column | Type | Notes |
|---|---|---|
| `id` | int | Primary key |
| `folder_name` | varchar(255) | |
| `user_id` | int | FK → `user_info.id` |
| `parent_id` | int | FK → `user_folder.id` (nested folders) |
 
#### `audit_log`
| Column | Type | Notes |
|---|---|---|
| `id` | int | Primary key |
| `user_id` | int | FK → `user_info.id` |
| `document_id` | int | FK → `document_info.document_id` (nullable) |
| `action` | enum | `REGISTER`, `LOGIN`, `LOGOUT`, `UPLOAD`, `DOWNLOAD`, `DELETE_FILE`, `DELETE_USER`, `RENAME`, `SHARE`, `PASSWORD_RESET`, `PASSWORD_CHANGE`, `UPDATE_PROFILE` |
| `created_at` | timestamp | |
 
#### `share_log`
| Column | Type | Notes |
|---|---|---|
| `id` | int | Primary key |
| `sender_id` | int | FK → `user_info.id` |
| `receiver_id` | int | FK → `user_info.id` |
| `document_id` | int | FK → `document_info.document_id` |
| `shared_at` | timestamp | |
 
#### `delete_log`
| Column | Type | Notes |
|---|---|---|
| `id` | int | Primary key |
| `user_id` | int | |
| `document_id` | int | Retains deleted document ID for history |
| `deleted_at` | timestamp | |
 
#### `password_reset`
| Column | Type | Notes |
|---|---|---|
| `id` | int | Primary key |
| `user_id` | int | FK → `user_info.id` |
| `otp` | text | |
| `expires_at` | timestamp | |
 
#### `email_queue`
| Column | Type | Notes |
|---|---|---|
| `id` | int | Primary key |
| `recipient` | varchar(255) | |
| `subject` | varchar(255) | |
| `body` | text | |
| `status` | enum | `PENDING`, `SENT` |
| `created_at` | timestamp | |
| `sent_at` | timestamp | Nullable |
 
### Relationships
 
```text
user_info        ──< document_info              (owner_id)
user_info        ──< document_user_permission   (user_id)
user_info        ──< user_folder                (user_id)
user_info        ──< audit_log                  (user_id)
user_info        ──< share_log                  (sender_id, receiver_id)
user_folder      ──< user_folder                (parent_id — nested folders)
document_info    ──< document_user_permission   (document_id)
document_info    ──< audit_log                  (document_id)
document_info    ──< share_log                  (document_id)
```
 
### Default Admin Credentials
 
```
Email:    admin@dds.com
Password: admin123
```
 
> The admin account is seeded automatically when the schema is imported via `database/init.sql`.
 
---

## Security Measures

* Password hashing (bcrypt)
* Prepared statements with PDO / MySQLi
* SQL injection protection
* XSS protection via output escaping
* Session-based authentication
* Middleware-based authorization on every route
* Access validation before every file download
* Private file storage — files are not web-accessible directly
* No public file URLs
* `.htaccess` blocks direct access to the `uploads/` directory

---

## Installation

### Option 1 — XAMPP (Local Development)

#### 1. Clone the Repository

```bash
git clone https://github.com/mdobariya3606-prog/project.git
cd project
```

#### 2. Configure the Database

Create a MySQL database and import the schema:

```bash
mysql -u root -p your_database < database/init.sql
```

#### 3. Configure Environment

Create a `.env` file at the project root:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=document_access_management_system
DB_USERNAME=root
DB_PASSWORD=

MAIL_HOST=smtp.mailtrap.io
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM=from_email
MAIL_TO=to_email
MAIL_PORT=587

ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=admin@123
```

#### 4. Start the Server

1. Start Apache and MySQL from the XAMPP Control Panel.
2. Open in browser:

```
http://localhost/project/public
```

---

### Option 2 — Docker

#### Prerequisites

* [Docker Desktop](https://www.docker.com/products/docker-desktop) installed and running

#### 1. Clone the Repository

```bash
git clone https://github.com/mdobariya3606-prog/project.git
cd project
```

#### 2. Start the Application

```bash
docker-compose up --build
```

#### 3. Access the Application

| Service    | URL                   |
|------------|-----------------------|
| Application | http://localhost:8080 |
| phpMyAdmin | http://localhost:8081 |

#### Default Admin Credentials

```
Email:    admin@dds.com
Password: admin@123
```

---

## Project Structure

```text
project/
│
├── config/
│   ├── bootstrap.php
│   ├── conn.php
│   └── email.php
│
├── database/
│   └── init.sql
│
├── public/
│   ├── index.php
│   ├── session.php
│   │
│   ├── admin/
│   │   ├── all-uploaded-files.php
│   │   ├── change-share-access.php
│   │   ├── change-status.php
│   │   ├── change-user-password.php
│   │   ├── dashboard.php
│   │   ├── manage-users.php
│   │   └── search.php
│   │
│   ├── auth/
│   │   ├── login.php
│   │   ├── logout.php
│   │   └── register.php
│   │
│   ├── css/
│   │   └── style.css
│   │
│   ├── files/
│   │   ├── add-file.php
│   │   ├── add-folder.php
│   │   ├── all-files.php
│   │   ├── delete-file.php
│   │   ├── delete-folder.php
│   │   ├── download.php
│   │   ├── open-folder.php
│   │   ├── permissions.php
│   │   ├── rename.php
│   │   ├── revoke-permission.php
│   │   ├── search.php
│   │   ├── send-mail.php
│   │   ├── share-file.php
│   │   └── shared-files.php
│   │
│   ├── functions/
│   │   ├── Helper.php
│   │   ├── forget-password.php
│   │   ├── reset-password.php
│   │   └── verify-otp.php
│   │
│   ├── include/
│   │   └── header.php
│   │
│   ├── middleware/
│   │   ├── admin.php
│   │   ├── auth.php
│   │   ├── file.php
│   │   ├── permission.php
│   │   ├── share-access.php
│   │   └── status.php
│   │
│   └── user/
│       ├── change-password.php
│       ├── dashboard.php
│       ├── profile.php
│       └── update-profile.php
│
├── uploads/
│   ├── .htaccess
│   ├── admin/
│   └── user/
│
├── vendor/
│   └── (composer dependencies)
│
├── .dockerignore
├── .env
├── .env.example
├── .gitignore
├── composer.json
├── composer.lock
├── docker-compose.yml
├── dockerfile
├── php.ini
└── README.md
```

---

## Future Improvements

* Document preview (in-browser rendering)
* File versioning
* Two-factor authentication (2FA)
* Email verification on registration
* Activity analytics dashboard
* Cloud storage integration (AWS S3, Azure Blob Storage)
* REST API layer for mobile or separated frontend

---

## Screenshots

### Login Page

<img width="1365" height="632" alt="Login Page" src="https://github.com/user-attachments/assets/94345dd3-5d5b-4278-bb40-aa1628457882" />

### Admin Dashboard

<img width="1351" height="632" alt="Admin Dashboard" src="https://github.com/user-attachments/assets/664c8267-4b9e-4827-a2f9-8bc6f4f2a2d3" />

### Manage files

<img width="1352" height="633" alt="Manage files" src="https://github.com/user-attachments/assets/6715d660-09ce-4f95-a3f8-295443865c81" />

### Upload Document

<img width="1365" height="630" alt="Upload Document" src="https://github.com/user-attachments/assets/c920ba7b-6408-42a3-800b-694f7ed3489b" />

### Shared Documents

<img width="1364" height="626" alt="Shared Documents" src="https://github.com/user-attachments/assets/4f220970-f5b4-476c-b166-a1cf00d8fea3" />

---

## License

This project is developed for educational and learning purposes.
