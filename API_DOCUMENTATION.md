# NexCrest E-Commerce API Documentation

## Overview

This API provides endpoints for accessing Brands, Genders, and Scent Families data for the NexCrest E-Commerce application.

## Base URL

```
http://localhost:8000/api/v1
```

**Note:** Update the base URL in your Postman collection or API client to match your server configuration.

## Authentication

Currently, these endpoints are public. If you need to add authentication later, you can:

1. Install Laravel Sanctum: `composer require laravel/sanctum`
2. Add `auth:sanctum` middleware to the routes in `routes/api.php`

## Endpoints

### Brands

#### Get All Brands
```
GET /api/v1/brands
```

**Query Parameters:**
- `is_active` (boolean, optional): Filter by active status. Default: `true`
- `is_featured` (boolean, optional): Filter by featured status
- `search` (string, optional): Search by name, description, or slug

**Example Request:**
```
GET /api/v1/brands?is_active=true&is_featured=true&search=nike
```

**Example Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Brand Name",
            "slug": "brand-name",
            "description": "Brand description",
            "logo": "brands/logo.jpg",
            "logo_url": "http://localhost/storage/brands/logo.jpg",
            "website": "https://example.com",
            "is_active": true,
            "is_featured": false,
            "sort_order": 0,
            "meta_title": null,
            "meta_description": null,
            "meta_keywords": null,
            "created_at": "2025-01-15T10:00:00.000000Z",
            "updated_at": "2025-01-15T10:00:00.000000Z"
        }
    ],
    "message": "Brands retrieved successfully"
}
```

#### Get Brand by ID
```
GET /api/v1/brands/{id}
```

**Example Request:**
```
GET /api/v1/brands/1
```

**Example Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Brand Name",
        "slug": "brand-name",
        "description": "Brand description",
        "logo": "brands/logo.jpg",
        "logo_url": "http://localhost/storage/brands/logo.jpg",
        "website": "https://example.com",
        "is_active": true,
        "is_featured": false,
        "sort_order": 0,
        "meta_title": null,
        "meta_description": null,
        "meta_keywords": null,
        "created_at": "2025-01-15T10:00:00.000000Z",
        "updated_at": "2025-01-15T10:00:00.000000Z"
    },
    "message": "Brand retrieved successfully"
}
```

**Error Response (404):**
```json
{
    "success": false,
    "message": "Brand not found"
}
```

---

### Genders

#### Get All Genders
```
GET /api/v1/genders
```

**Query Parameters:**
- `status` (boolean, optional): Filter by status. Default: `true` (active only)

**Example Request:**
```
GET /api/v1/genders?status=true
```

**Example Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Men",
            "status": true,
            "created_at": "2025-01-15T10:00:00.000000Z",
            "updated_at": "2025-01-15T10:00:00.000000Z"
        },
        {
            "id": 2,
            "name": "Women",
            "status": true,
            "created_at": "2025-01-15T10:00:00.000000Z",
            "updated_at": "2025-01-15T10:00:00.000000Z"
        },
        {
            "id": 3,
            "name": "Unisex",
            "status": true,
            "created_at": "2025-01-15T10:00:00.000000Z",
            "updated_at": "2025-01-15T10:00:00.000000Z"
        }
    ],
    "message": "Genders retrieved successfully"
}
```

#### Get Gender by ID
```
GET /api/v1/genders/{id}
```

**Example Request:**
```
GET /api/v1/genders/1
```

**Example Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Men",
        "status": true,
        "created_at": "2025-01-15T10:00:00.000000Z",
        "updated_at": "2025-01-15T10:00:00.000000Z"
    },
    "message": "Gender retrieved successfully"
}
```

**Error Response (404):**
```json
{
    "success": false,
    "message": "Gender not found"
}
```

---

### Scent Families

#### Get All Scent Families
```
GET /api/v1/scent-families
```

**Query Parameters:**
- `status` (boolean, optional): Filter by status. Default: `true` (active only)

**Example Request:**
```
GET /api/v1/scent-families?status=true
```

**Example Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Floral",
            "status": true,
            "created_at": "2025-01-15T10:00:00.000000Z",
            "updated_at": "2025-01-15T10:00:00.000000Z"
        },
        {
            "id": 2,
            "name": "Woody",
            "status": true,
            "created_at": "2025-01-15T10:00:00.000000Z",
            "updated_at": "2025-01-15T10:00:00.000000Z"
        },
        {
            "id": 3,
            "name": "Citrus",
            "status": true,
            "created_at": "2025-01-15T10:00:00.000000Z",
            "updated_at": "2025-01-15T10:00:00.000000Z"
        }
    ],
    "message": "Scent families retrieved successfully"
}
```

#### Get Scent Family by ID
```
GET /api/v1/scent-families/{id}
```

**Example Request:**
```
GET /api/v1/scent-families/1
```

**Example Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Floral",
        "status": true,
        "created_at": "2025-01-15T10:00:00.000000Z",
        "updated_at": "2025-01-15T10:00:00.000000Z"
    },
    "message": "Scent family retrieved successfully"
}
```

**Error Response (404):**
```json
{
    "success": false,
    "message": "Scent family not found"
}
```

---

## Response Format

All API responses follow a consistent format:

### Success Response
```json
{
    "success": true,
    "data": { ... },
    "message": "Success message"
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error message",
    "error": "Detailed error (only in development)"
}
```

## HTTP Status Codes

- `200 OK` - Request successful
- `404 Not Found` - Resource not found
- `500 Internal Server Error` - Server error

## Headers

All requests should include:
```
Accept: application/json
Content-Type: application/json
```

## Postman Collection

A Postman collection is included in the project root: `NexCrest_ECommerce_API.postman_collection.json`

### Importing the Collection

1. Open Postman
2. Click "Import" button
3. Select the `NexCrest_ECommerce_API.postman_collection.json` file
4. Update the `base_url` variable in the collection to match your server URL

### Using the Collection

1. Set the `base_url` variable (default: `http://localhost:8000`)
2. All endpoints are organized in folders: Brands, Genders, Scent Families
3. Each endpoint includes example requests and responses

## Testing the API

### Using cURL

**Get All Brands:**
```bash
curl -X GET "http://localhost:8000/api/v1/brands" \
  -H "Accept: application/json"
```

**Get Brand by ID:**
```bash
curl -X GET "http://localhost:8000/api/v1/brands/1" \
  -H "Accept: application/json"
```

**Get All Genders:**
```bash
curl -X GET "http://localhost:8000/api/v1/genders" \
  -H "Accept: application/json"
```

**Get All Scent Families:**
```bash
curl -X GET "http://localhost:8000/api/v1/scent-families" \
  -H "Accept: application/json"
```

### Using JavaScript (Fetch API)

```javascript
// Get all brands
fetch('http://localhost:8000/api/v1/brands', {
  headers: {
    'Accept': 'application/json'
  }
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('Error:', error));
```

### Using React (Axios)

```javascript
import axios from 'axios';

// Get all brands
const fetchBrands = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/v1/brands', {
      headers: {
        'Accept': 'application/json'
      }
    });
    console.log(response.data);
  } catch (error) {
    console.error('Error:', error);
  }
};
```

## Notes

1. **Active Items Only**: By default, all endpoints return only active items (brands with `is_active=true`, genders and scent families with `status=true`)

2. **Logo URLs**: Brand logos are returned with full URLs in the `logo_url` attribute

3. **Filtering**: Use query parameters to filter results (e.g., `?is_featured=true` for featured brands)

4. **Search**: The brands endpoint supports search functionality across name, description, and slug fields

5. **Error Handling**: All endpoints include proper error handling with appropriate HTTP status codes

## Future Enhancements

- Add pagination support
- Add rate limiting
- Add API authentication (Sanctum/Passport)
- Add caching for better performance
- Add API versioning
- Add request/response logging

