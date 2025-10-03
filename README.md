

# PHP Email System with SQL Backend

![PHP](https://img.shields.io/badge/PHP-8%2B-blue)
![SQLite](https://img.shields.io/badge/Database-SQLite-green)


A lightweight, secure PHP-based email management system that simulates POP3-style retrieval. This system allows users to authenticate, fetch their emails, read messages, and automatically deletes emails after being read for enhanced privacy.

## 🌟 Features

- 🔐 **User Authentication** – Secure session-based login system
- 📬 **Email Management** – Fetch user-specific emails securely
- 📖 **Read & Auto-Delete** – Messages are automatically deleted after being opened
- 🗄️ **SQL Database** – Simple, portable emails.db backend
  
## 🛠 Tech Stack

- **PHP 8+** - Backend scripting
- **SQL (PDO)** - Database management
- **Session-based authentication** - User security

## 🔧 API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `?action=login` | POST (JSON) | Log in with username & password |
| `?action=emails` | GET | Fetch all emails for logged-in user |
| `?action=read_email&id=ID` | GET | Read a single email (auto-deletes after reading) |

## 📂 Project Structure

```
├── emails.db         # SQLite database (users + emails)
├── index.php         # Main API file
└── README.md         # Project documentation
```

## ⚙️ Setup Instructions

### Prerequisites

- PHP 8.0 or higher
- SQL extension enabled
- Web server (Apache, or PHP built-in server)

### Installation

1. **Clone this repository:**

```bash
git clone https://github.com/panditchaitra/POP3_SPD.git
cd POP3_SPD
```

2. **Create a SQLite database `emails.db` with the following tables:**

```sql
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE,
    password TEXT
);

CREATE TABLE emails (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    sender TEXT,
    subject TEXT,
    body TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

3. **Start a local PHP server:**

```bash
php -S localhost:8000
```

4. **Test API endpoints via Postman, cURL, or frontend client.**

## 📝 Usage Examples

### Login

```bash
curl -X POST -H "Content-Type: application/json" -d '{"username":"your_username","password":"your_password"}' http://localhost:8000/?action=login
```

### Fetch Emails

```bash
curl -X GET http://localhost:8000/?action=emails
```

### Read an Email

```bash
curl -X GET http://localhost:8000/?action=read_email&id=1
```

## 🔒 Security Considerations

- All passwords should be hashed before storing in the database
- Session tokens should have appropriate expiration times
- Implement HTTPS in production environments
- Validate and sanitize all user inputs
