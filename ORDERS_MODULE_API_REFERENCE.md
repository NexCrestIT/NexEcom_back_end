# Orders Module - API Reference

## Base URL
```
http://localhost:8000/admin
```

## Authentication
All endpoints require authentication. Include bearer token in headers:
```
Authorization: Bearer {token}
```

---

## Admin Endpoints

### 1. List Orders
**Endpoint:** `GET /orders`

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| page | integer | Page number (default: 1) |
| status | string | Filter by order status |
| payment_status | string | Filter by payment status |
| search | string | Search by order number or customer |
| from_date | date | From date (YYYY-MM-DD) |
| to_date | date | To date (YYYY-MM-DD) |
| min_amount | float | Minimum order amount |
| max_amount | float | Maximum order amount |

**Example Request:**
```bash
GET /admin/orders?status=pending&page=1
```

**Example Response:**
```json
{
  "orders": {
    "data": [
      {
        "id": 1,
        "order_number": "ORD-2024-001",
        "customer": {
          "id": 1,
          "name": "John Doe",
          "email": "john@example.com"
        },
        "total_amount": 299.99,
        "status": "pending",
        "payment_status": "pending",
        "created_at": "2024-01-20T10:30:00Z"
      }
    ],
    "links": {},
    "meta": {
      "current_page": 1,
      "total": 150,
      "per_page": 15
    }
  },
  "statistics": {
    "total_orders": 150,
    "pending_orders": 20,
    "processing_orders": 15,
    "shipped_orders": 35,
    "delivered_orders": 70,
    "cancelled_orders": 5,
    "total_revenue": 45000.00,
    "pending_revenue": 6000.00
  },
  "filters": {},
  "orderStatuses": {
    "pending": "Pending",
    "processing": "Processing",
    "shipped": "Shipped",
    "delivered": "Delivered",
    "cancelled": "Cancelled",
    "returned": "Returned"
  },
  "paymentStatuses": {
    "pending": "Pending",
    "completed": "Completed",
    "failed": "Failed",
    "refunded": "Refunded"
  }
}
```

---

### 2. Get Order Details
**Endpoint:** `GET /orders/{id}`

**URL Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Order ID |

**Example Request:**
```bash
GET /admin/orders/1
```

**Example Response:**
```json
{
  "order": {
    "id": 1,
    "order_number": "ORD-2024-001",
    "customer": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone_number": "+1234567890"
    },
    "items": [
      {
        "id": 1,
        "product": {
          "id": 1,
          "name": "Premium Perfume",
          "sku": "PERF-001",
          "price": 99.99
        },
        "quantity": 3,
        "price": 99.99
      }
    ],
    "address": {
      "first_name": "John",
      "last_name": "Doe",
      "address_line_1": "123 Main St",
      "address_line_2": "Apt 4B",
      "city": "New York",
      "state": "NY",
      "postal_code": "10001",
      "country": "USA"
    },
    "total_amount": 299.97,
    "status": "pending",
    "payment_status": "pending",
    "payment_method": "razorpay",
    "razorpay_order_id": "order_1234567890",
    "razorpay_payment_id": "pay_1234567890",
    "notes": "Handle with care",
    "created_at": "2024-01-20T10:30:00Z",
    "paid_at": null
  },
  "orderStatuses": {},
  "paymentStatuses": {},
  "nextPossibleStatuses": {
    "processing": "Processing",
    "cancelled": "Cancelled"
  },
  "canCancel": true,
  "canRefund": false,
  "canShip": false
}
```

---

### 3. Update Order Status
**Endpoint:** `POST /orders/{id}/update-status`

**URL Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Order ID |

**Request Body:**
```json
{
  "status": "processing",
  "notes": "Order is being prepared for shipment"
}
```

**Status Values:**
- `pending` - Initial status
- `processing` - Being prepared
- `shipped` - In transit
- `delivered` - Delivered to customer
- `cancelled` - Cancelled
- `returned` - Returned by customer

**Valid Transitions:**
- pending → processing, cancelled
- processing → shipped, cancelled
- shipped → delivered, returned
- delivered → returned
- cancelled → (no transitions)
- returned → processing

**Example Request:**
```bash
POST /admin/orders/1/update-status
Content-Type: application/json

{
  "status": "processing",
  "notes": "Packing order for shipment"
}
```

**Example Response:**
```json
{
  "success": true,
  "message": "Order status updated successfully",
  "data": {
    "id": 1,
    "status": "processing",
    "notes": "Packing order for shipment"
  }
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Invalid status transition from pending to delivered",
  "errors": {
    "error": ["Invalid status transition..."]
  }
}
```

---

### 4. Update Payment Status
**Endpoint:** `POST /orders/{id}/update-payment-status`

**Request Body:**
```json
{
  "payment_status": "completed"
}
```

**Payment Status Values:**
- `pending` - Payment not received
- `completed` - Payment received
- `failed` - Payment failed
- `refunded` - Payment refunded

**Example Request:**
```bash
POST /admin/orders/1/update-payment-status
Content-Type: application/json

{
  "payment_status": "completed"
}
```

**Example Response:**
```json
{
  "success": true,
  "message": "Payment status updated successfully"
}
```

---

### 5. Update Order Notes
**Endpoint:** `POST /orders/{id}/update-notes`

**Request Body:**
```json
{
  "notes": "Customer prefers morning delivery"
}
```

**Example Request:**
```bash
POST /admin/orders/1/update-notes
Content-Type: application/json

{
  "notes": "Customer prefers morning delivery"
}
```

**Example Response:**
```json
{
  "success": true,
  "message": "Notes updated successfully"
}
```

---

### 6. Process Refund
**Endpoint:** `POST /orders/{id}/process-refund`

**Request Body:**
```json
{
  "amount": 299.99,
  "reason": "Customer requested refund"
}
```

**Requirements:**
- Payment status must be "completed"
- Amount must be less than or equal to total_amount
- Amount is optional (defaults to full amount)

**Example Request:**
```bash
POST /admin/orders/1/process-refund
Content-Type: application/json

{
  "amount": 150.00,
  "reason": "Partial refund - customer returned one item"
}
```

**Example Response:**
```json
{
  "success": true,
  "message": "Refund processed successfully",
  "data": {
    "id": 1,
    "payment_status": "refunded",
    "total_amount": 299.99,
    "refund_amount": 150.00
  }
}
```

---

### 7. Bulk Update Order Status
**Endpoint:** `POST /orders/bulk-update-status`

**Request Body:**
```json
{
  "order_ids": [1, 2, 3, 4, 5],
  "status": "processing"
}
```

**Example Request:**
```bash
POST /admin/orders/bulk-update-status
Content-Type: application/json

{
  "order_ids": [1, 2, 3],
  "status": "shipped"
}
```

**Example Response:**
```json
{
  "success": true,
  "message": "Orders updated successfully",
  "updated_count": 3
}
```

---

### 8. Delete Order
**Endpoint:** `DELETE /orders/{id}`

**Example Request:**
```bash
DELETE /admin/orders/1
```

**Example Response:**
```json
{
  "success": true,
  "message": "Order deleted successfully"
}
```

---

### 9. Export Orders to CSV
**Endpoint:** `GET /orders/export/csv`

**Query Parameters:**
Same as list orders endpoint (optional filters)

**Example Request:**
```bash
GET /admin/orders/export/csv?status=pending&from_date=2024-01-01
```

**Response:**
- Content-Type: `text/csv; charset=utf-8`
- Content-Disposition: `attachment; filename=orders-2024-01-27.csv`

**CSV Format:**
```
Order ID,Order Number,Customer,Email,Amount,Status,Payment Status,Date
1,ORD-2024-001,John Doe,john@example.com,299.99,pending,pending,2024-01-20 10:30:00
2,ORD-2024-002,Jane Smith,jane@example.com,599.99,processing,completed,2024-01-21 14:15:00
```

---

### 10. Get Dashboard Statistics
**Endpoint:** `GET /orders/dashboard/stats`

**Example Request:**
```bash
GET /admin/orders/dashboard/stats
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "statistics": {
      "total_orders": 150,
      "pending_orders": 20,
      "processing_orders": 15,
      "shipped_orders": 35,
      "delivered_orders": 70,
      "cancelled_orders": 5,
      "total_revenue": 45000.00,
      "pending_revenue": 6000.00
    },
    "statusDistribution": {
      "pending": 20,
      "processing": 15,
      "shipped": 35,
      "delivered": 70,
      "cancelled": 5,
      "returned": 5
    },
    "recentOrders": [
      {
        "id": 150,
        "order_number": "ORD-2024-150",
        "customer_id": 50,
        "total_amount": 499.99,
        "status": "pending",
        "created_at": "2024-01-27T09:00:00Z"
      }
    ],
    "topCustomers": [
      {
        "customer_id": 1,
        "customer": {
          "id": 1,
          "name": "Premium Customer",
          "email": "premium@example.com"
        },
        "order_count": 10,
        "total_spent": 5000.00
      }
    ]
  }
}
```

---

## Customer API Endpoints

### 1. Get Customer's Orders
**Endpoint:** `GET /api/orders`

**Example Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "order_number": "ORD-2024-001",
      "total_amount": 299.99,
      "status": "shipped",
      "payment_status": "completed",
      "created_at": "2024-01-20T10:30:00Z",
      "items": [...]
    }
  ]
}
```

---

### 2. Get Order Details (Customer)
**Endpoint:** `GET /api/orders/{id}`

**Example Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "order_number": "ORD-2024-001",
    "total_amount": 299.99,
    "status": "shipped",
    "payment_status": "completed",
    "items": [
      {
        "product_name": "Premium Perfume",
        "quantity": 3,
        "price": 99.99
      }
    ],
    "address": {...},
    "created_at": "2024-01-20T10:30:00Z"
  }
}
```

---

### 3. Cancel Order
**Endpoint:** `POST /api/orders/{id}/cancel`

**Allowed Statuses:** pending, processing

**Example Response:**
```json
{
  "success": true,
  "message": "Order cancelled successfully",
  "data": {
    "id": 1,
    "status": "cancelled"
  }
}
```

---

### 4. Request Return
**Endpoint:** `POST /api/orders/{id}/return`

**Conditions:**
- Order must be delivered
- Less than 30 days old

**Example Response:**
```json
{
  "success": true,
  "message": "Return request submitted",
  "data": {
    "id": 1,
    "status": "returned"
  }
}
```

---

### 5. Download Invoice
**Endpoint:** `GET /api/orders/{id}/invoice`

**Example Response:**
```json
{
  "success": true,
  "data": {
    "invoice_number": "INV-2024-01-000001",
    "invoice_date": "2024-01-27",
    "order_number": "ORD-2024-001",
    "customer": {
      "name": "John Doe",
      "email": "john@example.com"
    },
    "items": [...],
    "subtotal": "272.72",
    "tax": "27.27",
    "total": "299.99"
  }
}
```

---

### 6. Get Order Statistics
**Endpoint:** `GET /api/orders/stats`

**Example Response:**
```json
{
  "success": true,
  "data": {
    "total_orders": 5,
    "pending_orders": 0,
    "completed_orders": 3,
    "cancelled_orders": 1,
    "returned_orders": 1,
    "total_spent": 1299.99
  }
}
```

---

## Error Responses

### 400 Bad Request
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "status": ["The status field is required"],
    "amount": ["The amount must be a number"]
  }
}
```

### 403 Forbidden
```json
{
  "success": false,
  "message": "Unauthorized"
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Order not found"
}
```

### 500 Server Error
```json
{
  "success": false,
  "message": "An error occurred while processing your request",
  "error": "Error details..."
}
```

---

## Rate Limiting

Currently no rate limiting configured. Recommended:
```
60 requests per minute for admin endpoints
100 requests per minute for API endpoints
```

---

## Pagination

Default: 15 items per page

Query: `?page=2`

Response includes:
```json
{
  "current_page": 2,
  "total": 150,
  "per_page": 15,
  "last_page": 10
}
```

---

## Filtering Examples

### Filter by Status
```bash
GET /admin/orders?status=pending
```

### Filter by Date Range
```bash
GET /admin/orders?from_date=2024-01-01&to_date=2024-01-31
```

### Filter by Amount Range
```bash
GET /admin/orders?min_amount=100&max_amount=500
```

### Search
```bash
GET /admin/orders?search=ORD-2024-001
```

### Combine Filters
```bash
GET /admin/orders?status=shipped&payment_status=completed&from_date=2024-01-01&search=customer_name
```

---

## Best Practices

1. **Always include Authorization header**
2. **Use appropriate HTTP methods** (GET for retrieval, POST for creation, etc.)
3. **Check response status** before processing data
4. **Handle errors gracefully**
5. **Cache statistics** to reduce API calls
6. **Use pagination** for large datasets
7. **Validate input** before sending
8. **Use CSV export** for bulk data export

---

## Example cURL Requests

### List Orders
```bash
curl -X GET "http://localhost:8000/admin/orders?status=pending" \
  -H "Authorization: Bearer your_token"
```

### Update Status
```bash
curl -X POST "http://localhost:8000/admin/orders/1/update-status" \
  -H "Authorization: Bearer your_token" \
  -H "Content-Type: application/json" \
  -d '{"status":"processing","notes":"Preparing shipment"}'
```

### Process Refund
```bash
curl -X POST "http://localhost:8000/admin/orders/1/process-refund" \
  -H "Authorization: Bearer your_token" \
  -H "Content-Type: application/json" \
  -d '{"amount":100.00,"reason":"Customer requested"}'
```

### Export CSV
```bash
curl -X GET "http://localhost:8000/admin/orders/export/csv" \
  -H "Authorization: Bearer your_token" \
  -o orders.csv
```

---

**Last Updated:** January 27, 2025
**API Version:** 1.0
