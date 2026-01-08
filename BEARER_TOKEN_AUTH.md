# Bearer Token Authentication Guide

## Overview

The `/api/v1/customers/me` endpoint (and other protected customer endpoints) now use **Bearer Token Authentication** via Laravel Sanctum.

## How It Works

1. **Register/Login** - Customer receives a token
2. **Protected Endpoints** - Include token in Authorization header
3. **Logout** - Token is revoked

## Authentication Flow

### Step 1: Register or Login

**Register:**
```bash
POST /api/v1/customers/register
```

**Response:**
```json
{
    "success": true,
    "message": "Customer registered successfully",
    "data": {
        "customer": { ... },
        "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
    }
}
```

**Login:**
```bash
POST /api/v1/customers/login
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "customer": { ... },
        "token": "2|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
    }
}
```

### Step 2: Use the Token

Store the token from the response and include it in subsequent requests:

**Header:**
```
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

### Step 3: Access Protected Endpoints

**Get Current Customer:**
```bash
GET /api/v1/customers/me
Headers:
  Authorization: Bearer {your_token}
  Accept: application/json
```

**Logout:**
```bash
POST /api/v1/customers/logout
Headers:
  Authorization: Bearer {your_token}
  Accept: application/json
```

## Example Usage

### Using cURL

**1. Register:**
```bash
curl -X POST http://localhost:8000/api/v1/customers/register \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**Response:**
```json
{
    "success": true,
    "data": {
        "customer": { ... },
        "token": "1|abc123def456..."
    }
}
```

**2. Get Current Customer (using token):**
```bash
curl -X GET http://localhost:8000/api/v1/customers/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abc123def456..."
```

**3. Logout:**
```bash
curl -X POST http://localhost:8000/api/v1/customers/logout \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abc123def456..."
```

### Using JavaScript (Fetch API)

```javascript
// 1. Login
const loginResponse = await fetch('http://localhost:8000/api/v1/customers/login', {
  method: 'POST',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    identifier: 'john@example.com',
    password: 'password123'
  })
});

const loginData = await loginResponse.json();
const token = loginData.data.token;

// 2. Get Current Customer
const meResponse = await fetch('http://localhost:8000/api/v1/customers/me', {
  method: 'GET',
  headers: {
    'Accept': 'application/json',
    'Authorization': `Bearer ${token}`
  }
});

const customerData = await meResponse.json();
console.log(customerData);
```

### Using React (Axios)

```javascript
import axios from 'axios';

// Set up axios instance with base URL
const api = axios.create({
  baseURL: 'http://localhost:8000/api/v1',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  }
});

// Login function
const login = async (identifier, password) => {
  try {
    const response = await api.post('/customers/login', {
      identifier,
      password
    });
    
    const token = response.data.data.token;
    
    // Store token (localStorage, sessionStorage, or state management)
    localStorage.setItem('customer_token', token);
    
    // Set default authorization header for future requests
    api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    
    return response.data;
  } catch (error) {
    console.error('Login failed:', error);
    throw error;
  }
};

// Get current customer
const getCurrentCustomer = async () => {
  try {
    const response = await api.get('/customers/me');
    return response.data;
  } catch (error) {
    console.error('Failed to get customer:', error);
    throw error;
  }
};

// Logout function
const logout = async () => {
  try {
    await api.post('/customers/logout');
    
    // Remove token
    localStorage.removeItem('customer_token');
    delete api.defaults.headers.common['Authorization'];
  } catch (error) {
    console.error('Logout failed:', error);
    throw error;
  }
};
```

### Using React with Axios Interceptor

```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api/v1',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  }
});

// Request interceptor - Add token to every request
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('customer_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor - Handle 401 errors (token expired/invalid)
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Token expired or invalid - redirect to login
      localStorage.removeItem('customer_token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default api;
```

## Postman Setup

1. **Import the collection** - `NexCrest_ECommerce_API.postman_collection.json`

2. **Register or Login** to get a token

3. **Set the token variable:**
   - After login, copy the token from the response
   - Go to Collection Variables
   - Set `customer_token` to your token value

4. **Use protected endpoints:**
   - The `Authorization: Bearer {{customer_token}}` header is already configured
   - Just make the request!

## Token Format

Sanctum tokens follow this format:
```
{token_id}|{hashed_token}
```

Example:
```
1|abc123def456ghi789jkl012mno345pqr678stu901vwx234yz
```

## Security Notes

1. **Store tokens securely** - Use secure storage (httpOnly cookies, secure localStorage, etc.)
2. **HTTPS in production** - Always use HTTPS when transmitting tokens
3. **Token expiration** - Sanctum tokens don't expire by default, but you can configure expiration
4. **Logout revokes token** - Always logout to revoke the token
5. **Multiple devices** - Each login creates a new token (multiple devices supported)

## Error Responses

### 401 Unauthenticated
```json
{
    "success": false,
    "message": "Unauthenticated"
}
```
**Cause:** Missing or invalid token

### 403 Forbidden
```json
{
    "success": false,
    "message": "Your account has been deactivated. Please contact support."
}
```
**Cause:** Account is inactive

## Protected Endpoints

All endpoints using `auth:sanctum` middleware require Bearer token:

- `GET /api/v1/customers/me` - Get current customer
- `POST /api/v1/customers/logout` - Logout customer

## Public Endpoints

These endpoints don't require authentication:

- `POST /api/v1/customers/register` - Register new customer
- `POST /api/v1/customers/login` - Login customer

