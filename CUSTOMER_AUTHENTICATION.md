# Customer Authentication System

## Overview

This document describes the customer authentication system for the NexCrest E-Commerce application. Customers can register and login using either **email** or **phone number** along with a password.

## Database Structure

### Customers Table

The `customers` table includes the following fields:

- `id` - Primary key
- `name` - Customer full name
- `email` - Email address (nullable, unique)
- `phone` - Phone number (nullable, unique)
- `email_verified_at` - Email verification timestamp
- `phone_verified_at` - Phone verification timestamp
- `password` - Hashed password
- `avatar` - Profile picture path
- `date_of_birth` - Date of birth
- `gender` - Gender (male, female, other)
- `is_active` - Account active status (default: true)
- `is_verified` - Account verification status (default: false)
- `verification_code` - OTP/verification code
- `verification_code_expires_at` - Verification code expiry
- `remember_token` - Remember me token
- `last_login_at` - Last login timestamp
- `last_login_ip` - Last login IP address
- `created_at`, `updated_at` - Timestamps
- `deleted_at` - Soft delete timestamp

### Customer Password Reset Tokens Table

Separate password reset tokens table for customers: `customer_password_reset_tokens`

## Authentication Configuration

### Auth Guards

The application now has two authentication guards:

1. **`web`** - For admin users (existing)
2. **`customer`** - For e-commerce customers (new)

### Auth Providers

- **`users`** - Admin users provider
- **`customers`** - Customer provider (new)

### Password Reset

- **`users`** - Admin password reset
- **`customers`** - Customer password reset (new)

## API Endpoints

### Customer Registration

```
POST /api/v1/customers/register
```

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",        // Optional, but either email or phone required
    "phone": "+1234567890",             // Optional, but either email or phone required
    "password": "password123",
    "password_confirmation": "password123",
    "date_of_birth": "1990-01-01",      // Optional
    "gender": "male"                    // Optional: male, female, other
}
```

**Success Response (201):**
```json
{
    "success": true,
    "message": "Customer registered successfully",
    "data": {
        "customer": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "+1234567890",
            "email_verified_at": "2026-01-06T05:10:00.000000Z",
            "phone_verified_at": "2026-01-06T05:10:00.000000Z",
            "is_active": true,
            "is_verified": false,
            "created_at": "2026-01-06T05:10:00.000000Z",
            "updated_at": "2026-01-06T05:10:00.000000Z"
        },
        "token": null  // Will be populated if Sanctum is installed
    }
}
```

**Validation Errors (422):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["This email is already registered."],
        "phone": ["This phone number is already registered."],
        "password": ["Password must be at least 8 characters."]
    }
}
```

### Customer Login

```
POST /api/v1/customers/login
```

**Request Body:**
```json
{
    "identifier": "john@example.com",  // Can be email or phone
    "password": "password123"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "customer": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "+1234567890",
            "is_active": true,
            "last_login_at": "2026-01-06T05:15:00.000000Z",
            "last_login_ip": "127.0.0.1"
        },
        "token": null  // Will be populated if Sanctum is installed
    }
}
```

**Error Response (401):**
```json
{
    "success": false,
    "message": "Invalid credentials"
}
```

**Error Response (403):**
```json
{
    "success": false,
    "message": "Your account has been deactivated. Please contact support."
}
```

### Get Current Customer

```
GET /api/v1/customers/me
```

**Headers:**
```
Authorization: Bearer {token}  // If using Sanctum
```

**Success Response (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "+1234567890",
        "avatar": null,
        "avatar_url": null,
        "date_of_birth": "1990-01-01",
        "gender": "male",
        "is_active": true,
        "is_verified": false,
        "last_login_at": "2026-01-06T05:15:00.000000Z"
    }
}
```

**Error Response (401):**
```json
{
    "success": false,
    "message": "Unauthenticated"
}
```

### Customer Logout

```
POST /api/v1/customers/logout
```

**Headers:**
```
Authorization: Bearer {token}  // If using Sanctum
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

## Features

### 1. Flexible Login
- Customers can login using either **email** or **phone number**
- The `identifier` field accepts both formats
- System automatically detects and authenticates accordingly

### 2. Registration Requirements
- At least one of email or phone must be provided
- Both can be provided for maximum flexibility
- Password must be at least 8 characters
- Password confirmation required

### 3. Account Status
- `is_active` - Controls if account can login
- `is_verified` - For future email/phone verification
- Inactive accounts cannot login

### 4. Security Features
- Passwords are hashed using bcrypt
- Last login tracking (timestamp and IP)
- Soft deletes for data retention
- Separate password reset tokens table

### 5. Profile Information
- Optional date of birth
- Optional gender selection
- Avatar/profile picture support
- Email and phone verification timestamps

## Model Methods

### Customer Model Helper Methods

```php
// Find customer by email or phone
Customer::findByEmailOrPhone('john@example.com');

// Check if can login with email
$customer->canLoginWithEmail();

// Check if can login with phone
$customer->canLoginWithPhone();

// Get avatar URL
$customer->avatar_url;

// Scope filters
Customer::active()->get();
Customer::verified()->get();
```

## Authentication Flow

### Registration Flow
1. Customer submits registration form (email or phone + password)
2. System validates input (unique email/phone, password strength)
3. Customer record created with hashed password
4. Email/phone automatically verified (can be changed to require verification)
5. Customer automatically logged in
6. Returns customer data and token (if Sanctum installed)

### Login Flow
1. Customer submits identifier (email or phone) + password
2. System finds customer by email or phone
3. Validates password
4. Checks if account is active
5. Updates last login info
6. Logs in customer
7. Returns customer data and token (if Sanctum installed)

## Laravel Sanctum Integration (Optional)

For token-based authentication with React frontend, you can install Laravel Sanctum:

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

Then add to `Customer` model:
```php
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    // ...
}
```

The API controller already supports Sanctum tokens - they will be automatically generated if Sanctum is installed.

## Testing

### Using cURL

**Register:**
```bash
curl -X POST http://localhost:8000/api/v1/customers/register \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**Login:**
```bash
curl -X POST http://localhost:8000/api/v1/customers/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "identifier": "john@example.com",
    "password": "password123"
  }'
```

**Get Current Customer:**
```bash
curl -X GET http://localhost:8000/api/v1/customers/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {token}"
```

## Postman Collection

The Postman collection has been updated to include all customer authentication endpoints. Import `NexCrest_ECommerce_API.postman_collection.json` to test the endpoints.

## Next Steps

1. **Run Migration:**
   ```bash
   php artisan migrate
   ```

2. **Optional - Install Sanctum for Token Auth:**
   ```bash
   composer require laravel/sanctum
   php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
   php artisan migrate
   ```
   Then add `HasApiTokens` trait to Customer model.

3. **Add Email/Phone Verification:**
   - Implement OTP sending for phone verification
   - Implement email verification links
   - Update verification logic in controller

4. **Add Password Reset:**
   - Create password reset request endpoint
   - Create password reset endpoint
   - Implement email/SMS sending

5. **Add Profile Management:**
   - Update profile endpoint
   - Change password endpoint
   - Upload avatar endpoint

