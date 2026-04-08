# Contact Sync API

A Laravel backend system to store users, sync contacts, and identify which contacts are already on the platform.

## Requirements

- PHP >= 8.1
- Composer
- MySQL >= 8.0
- Laravel >= 10.x

## Setup Instructions

### 1. Clone the Repository

```bash
git clone https://github.com/your-username/your-repo-name.git
cd your-repo-name
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Environment


Open `.env` and update your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=contact_sync
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Generate App Key

```bash
php artisan key:generate
```

### 5. Run Migrations

```bash
php artisan migrate
```

### 6. Start the Server

```bash
php artisan serve
```

The API will be available at `http://127.0.0.1:8000`

---

## API Endpoints

### Register a User

**POST** `/api/register`

**Request Body:**
```json
{
  "name": "John Doe",
  "phone": "07911123456"
}
```

**Response:**
```json
{
    "name": "John Doe",
    "phone": "07911123456",
    "normalized_phone": "+447911123456",
    "updated_at": "2026-04-08T20:17:20.000000Z",
    "created_at": "2026-04-08T20:17:20.000000Z",
    "id": 21
}
```

---

### Sync Contacts

**POST** `/api/contacts/sync`

**Request Body:**
```json
{
  "contacts": [
    { "name": "Angel", "phone": "+44 7123 456789" },
    { "name": "Ruby", "phone": "1123 456789" }
 ] 
}
```

**Response:**
```json
{
    "total_uploaded": 2,
    "matched": [
        {
            "name": "Angel",
            "phone": "+447123456789",
            "user_id": 1
        }
    ],
    "unmatched": [
        {
            "name": "Ruby",
            "phone": "+1123456789"
        }
    ]
}
```

---

## Phone Normalization

All phone numbers are automatically normalized to UK format:

| Input | Normalized |
|---|---|
| `07911 123456` | `+447911123456` |
| `07911-123-456` | `+447911123456` |
| `+44 7911 123456` | `+447911123456` |

---
