# 📚 LibGuard — Library Management System

> A web-based Library Management System built with PHP and MySQL for managing books, users, categories, borrowing transactions, and library inventory.

[![PHP](https://img.shields.io/badge/PHP-7%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-UI-7952B3?logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![jQuery](https://img.shields.io/badge/jQuery-JavaScript-0769AD?logo=jquery&logoColor=white)](https://jquery.com/)

---

## 📖 Overview

**LibGuard** is a web-based library management system designed to simplify the management of library resources and borrowing transactions.

The system provides a centralized interface for viewing and managing books, organizing them by category, tracking their availability, and monitoring expected return dates.

It includes separate workflows for **administrators** and **library users**, with the administrator responsible for managing library records and user accounts.

---

## ✨ Features

### 📚 Book Management

- Add and manage library books
- Track ISBN, title, author, and publisher
- Record publication dates
- Organize books by category
- Track shelf number and shelf row
- Monitor available quantities
- Display current availability status

### 🔎 Book Search

Users can search the library catalog using:

- ISBN
- Book title
- Author

Books can also be filtered by category.

### 🔄 Borrowing & Returns

The system provides borrowing-related information including:

- Book availability
- Borrowing records
- Expected return dates
- Current borrowing status

When a book is unavailable, its expected return date can be displayed directly in the catalog.

### 👨‍💼 Administrator Management

Administrators can manage library records and student/user information through the administration panel.

The system also provides functionality for adding and viewing student records, which can be used to associate users with their library transactions.

### 🗄️ Database Integration

LibGuard uses **MySQL** for persistent storage of:

- Books
- Categories
- Students/users
- Borrowing records
- Library inventory information

---

## 🛠️ Technology Stack

| Technology | Purpose |
|---|---|
| **PHP** | Server-side application logic |
| **MySQL** | Relational database |
| **HTML5** | Application structure |
| **CSS3** | Styling |
| **Bootstrap** | Responsive UI components |
| **JavaScript** | Client-side functionality |
| **jQuery** | DOM manipulation and interactions |
| **AdminLTE** | Administrative interface components |

---

## 🏗️ Project Structure

```text
LibGuard/
│
├── admin/                  # Administrative functionality
├── database/               # Database and SQL files
├── build/                  # Build-related resources
├── dist/                   # Distribution assets
├── images/                 # Images and application assets
├── includes/               # Shared PHP components
├── plugins/                # Third-party plugins
├── bower_components/       # Front-end dependencies
│
├── index.php               # Main library catalog
├── login.php               # Authentication page
├── logout.php              # Logout handler
├── transaction.php         # Borrowing/transaction functionality
│
├── libbooks.png            # Library application graphic
└── README.md               # Project documentation
```

---

## ⚙️ Requirements

Before installing LibGuard, make sure your development environment includes:

- PHP 7.x or later
- MySQL / MariaDB
- Apache or another PHP-compatible web server
- Web browser
- phpMyAdmin or another MySQL management tool

Recommended local development environments include:

- XAMPP
- WAMP
- Laragon
- LAMP

---

## 🚀 Installation

### 1. Clone the repository

```bash
git clone https://github.com/ivankaine27/LibGuard.git
```

Move the project into your web server's document root.

For XAMPP:

```text
htdocs/LibGuard
```

---

### 2. Create the database

Open **phpMyAdmin** or your preferred MySQL client.

Create a database named:

```text
libsystem
```

Example:

```sql
CREATE DATABASE libsystem;
```

---

### 3. Import the database

The SQL/database file is located inside the:

```text
database/
```

directory.

Import the provided SQL file into the `libsystem` database.

---

### 4. Configure the database connection

Locate the database connection configuration inside the project's PHP include files.

Update the database credentials according to your local environment:

```php
$host = "localhost";
$username = "root";
$password = "";
$database = "libsystem";
```

> Configuration may vary depending on your local PHP/MySQL installation.

---

### 5. Start the server

If using XAMPP, start:

- Apache
- MySQL

Then open:

```text
http://localhost/LibGuard/
```

---

## 🔐 Authentication

LibGuard provides authentication workflows for administrators and library users.

### Administrator

The administrator can access the administration panel to manage library and student records.

> **Security note:** Do not use default credentials in a production deployment. Change administrator credentials immediately and store passwords securely.

### Library Users

Users can access the system using their assigned student/user ID.

User records are managed through the administrator interface.

---

## 📊 Library Catalog

The main catalog provides information such as:

| Field | Description |
|---|---|
| ISBN | International Standard Book Number |
| Title | Book title |
| Author | Book author |
| Publisher | Publishing company |
| Publish Date | Publication date |
| Shelf Number | Physical shelf location |
| Shelf Row | Shelf row/location |
| Quantity | Number of available copies |
| Status | Current availability |
| Expected Return Date | Borrowing due date |

---

## 🔄 Application Workflow

```text
                    ┌─────────────────┐
                    │     LibGuard    │
                    └────────┬────────┘
                             │
              ┌──────────────┴──────────────┐
              │                             │
       ┌──────▼──────┐               ┌──────▼──────┐
       │    Admin    │               │    User     │
       └──────┬──────┘               └──────┬──────┘
              │                             │
       ┌──────▼──────────┐          ┌───────▼────────┐
       │ Manage Records  │          │ Browse Catalog │
       └──────┬──────────┘          └───────┬────────┘
              │                             │
       ┌──────▼──────────┐          ┌───────▼────────┐
       │ Books / Users   │          │ Search / Filter│
       │ Categories      │          │ Availability   │
       └──────┬──────────┘          └───────┬────────┘
              │                             │
              └──────────────┬──────────────┘
                             │
                     ┌───────▼────────┐
                     │    MySQL DB    │
                     └────────────────┘
```

---

## 🔒 Security Considerations

This project is intended primarily for educational and development purposes.

Before deploying LibGuard to a production environment, consider implementing:

- Password hashing using `password_hash()`
- Prepared SQL statements / PDO
- Input validation and sanitization
- CSRF protection
- Session security
- Role-based access control
- Secure database credentials
- Environment-based configuration
- Protection against SQL injection
- Protection against XSS
- HTTPS
- Secure error handling

---

## 🧪 Development Notes

The application follows a traditional server-rendered PHP architecture where PHP handles application logic and database interactions while HTML/CSS/JavaScript provide the client-side interface.

The catalog dynamically retrieves book information from the database and supports category filtering and availability information.

---

## 📸 Screenshots

Add application screenshots here to showcase the interface.

Recommended screenshots:

1. Login page
2. Main library catalog
3. Book search
4. Category filtering
5. Administrator dashboard
6. Book management
7. Student/user management
8. Borrowing/transaction page

Example:

```markdown
![LibGuard Dashboard](images/dashboard.png)
```

---

## 🗺️ Roadmap

Potential future improvements include:

- [ ] Modernize the user interface
- [ ] Implement secure password hashing
- [ ] Replace raw SQL queries with prepared statements
- [ ] Add comprehensive role-based authorization
- [ ] Add book cover/image management
- [ ] Add barcode/QR-code scanning
- [ ] Add automated overdue notifications
- [ ] Add email notifications
- [ ] Add borrowing history
- [ ] Add reporting and analytics
- [ ] Add dashboard statistics
- [ ] Add REST API endpoints
- [ ] Add automated tests
- [ ] Dockerize the application
- [ ] Improve mobile responsiveness
- [ ] Add audit logging

---

## 🤝 Contributing

Contributions, suggestions, and improvements are welcome.

### Fork the repository

```bash
git fork https://github.com/ivankaine27/LibGuard
```

### Create a feature branch

```bash
git checkout -b feature/your-feature
```

### Commit your changes

```bash
git commit -m "Add your feature"
```

### Push the branch

```bash
git push origin feature/your-feature
```

Then open a Pull Request.

---

## 📄 License

This repository does not currently specify a license.

If you intend to distribute or modify the project publicly, consider adding an appropriate open-source license such as MIT, Apache-2.0, or GPL-3.0.

---

## 👨‍💻 Author

**Ivan Kaine Bulaun**

GitHub: [@ivankaine27](https://github.com/ivankaine27)

---

## ⭐ Support

If you find this project useful, consider giving the repository a ⭐ on GitHub.

For questions, bug reports, or suggestions, open an issue in the repository.

---

> **LibGuard** — A simple, centralized solution for managing library resources and borrowing operations.
