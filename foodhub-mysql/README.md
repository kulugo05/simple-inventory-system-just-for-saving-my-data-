# 🍴 Bron Michael's FoodHub — MySQL Version

PHP + MySQL inventory management system. Upload to server and go!

---

## 📁 Files

```
foodhub-mysql/
├── index.html           ← Frontend (same UI, dark theme)
├── .htaccess            ← Apache routing
├── setup.sql            ← Run this ONCE to create database & tables
├── config/
│   └── database.php     ← MySQL credentials (edit this!)
└── api/
    ├── products.php     ← Products CRUD
    ├── stock.php        ← Stock In / Out / Adjust
    ├── categories.php   ← Categories
    └── reports.php      ← Dashboard & reports
```

---

## 🚀 Setup (3 Steps)

### Step 1 — Create the Database
```bash
mysql -u root -p < setup.sql
```
Or paste the contents of `setup.sql` into phpMyAdmin.

### Step 2 — Edit DB Credentials
Open `config/database.php` and update:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // your MySQL username
define('DB_PASS', '');           // your MySQL password
define('DB_NAME', 'foodhub_db');
```

### Step 3 — Upload & Open
Upload the whole `foodhub-mysql/` folder to your server (e.g. `/var/www/html/foodhub/`) and open:
```
http://yourserver.com/foodhub/
```

---

## ⚙️ Requirements

| Requirement   | Version |
|---------------|---------|
| PHP           | 7.4+    |
| MySQL / MariaDB | 5.7+  |
| Apache        | 2.4+    |
| mod_rewrite   | enabled |
| PDO + pdo_mysql | enabled |

> No Composer needed! Pure PHP with PDO.

---

## 🔌 API Endpoints

| Method | URL | Description |
|--------|-----|-------------|
| GET | `/api/products.php` | List all products |
| GET | `/api/products.php?id=1` | Get one product |
| GET | `/api/products.php?search=rice` | Search |
| GET | `/api/products.php?category=Dairy` | Filter by category |
| GET | `/api/products.php?low_stock=1` | Low stock only |
| POST | `/api/products.php` | Create product |
| PUT | `/api/products.php?id=1` | Update product |
| DELETE | `/api/products.php?id=1` | Delete product |
| GET | `/api/stock.php` | List transactions |
| POST | `/api/stock.php` | Add stock movement |
| GET | `/api/categories.php` | List categories |
| POST | `/api/categories.php` | Add category |
| DELETE | `/api/categories.php?id=1` | Delete category |
| GET | `/api/reports.php?type=dashboard` | Dashboard stats |
| GET | `/api/reports.php?type=categories` | Category report |

---

## 🛠️ Troubleshooting

**"could not find driver"** → Enable `pdo_mysql` in `php.ini`

**500 error** → Check `tail -f /var/log/apache2/error.log`

**API 404** → Enable `mod_rewrite` and set `AllowOverride All` in Apache config

**Access denied for user** → Double-check credentials in `config/database.php`
