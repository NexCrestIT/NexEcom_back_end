# Razorpay Payment Integration API

## Overview
Complete Razorpay payment gateway integration for the NexEcom e-commerce application.

## Configuration

### Environment Variables
Add the following to your `.env` file:
```
RAZORPAY_KEY_ID=your_razorpay_key_id
RAZORPAY_KEY_SECRET=your_razorpay_key_secret
RAZORPAY_CURRENCY=INR
```

## API Endpoints

### 1. Create Razorpay Order
**Endpoint:** `POST /api/v1/razorpay/create-order`  
**Authentication:** Required (Bearer Token)

Creates a new order with Razorpay and stores it in the database.

**Request Body:**
```json
{
  "address_id": 1,
  "notes": "Optional order notes"
}
```

**Validation Rules:**
- `address_id`: Required, must exist in addresses table and belong to authenticated customer
- `notes`: Optional, string, max 500 characters

**Success Response (201):**
```json
{
  "success": true,
  "message": "Order created successfully",
  "data": {
    "order_id": 1,
    "order_number": "ORD-ABC123",
    "razorpay_order_id": "order_Abc123XYZ",
    "razorpay_key_id": "rzp_test_xxxxx",
    "amount": 2499.00,
    "currency": "INR"
  }
}
```

**Error Response (400):**
```json
{
  "success": false,
  "message": "Cart is empty"
}
```

**Error Response (500):**
```json
{
  "success": false,
  "message": "Failed to create order",
  "error": "Error details"
}
```

**Flow:**
1. Validates address belongs to customer
2. Retrieves cart items
3. Calculates total amount
4. Creates order in database with pending status
5. Creates order items from cart
6. Creates Razorpay order
7. Updates order with Razorpay order ID
8. Returns order details and Razorpay key for frontend payment

---

### 2. Verify Payment
**Endpoint:** `POST /api/v1/razorpay/verify-payment`  
**Authentication:** Required (Bearer Token)

Verifies the Razorpay payment signature after payment completion.

**Request Body:**
```json
{
  "razorpay_order_id": "order_Abc123XYZ",
  "razorpay_payment_id": "pay_Xyz789ABC",
  "razorpay_signature": "signature_string"
}
```

**Validation Rules:**
- `razorpay_order_id`: Required, string
- `razorpay_payment_id`: Required, string
- `razorpay_signature`: Required, string

**Success Response (200):**
```json
{
  "success": true,
  "message": "Payment verified successfully",
  "data": {
    "order_id": 1,
    "order_number": "ORD-ABC123",
    "payment_status": "paid",
    "status": "confirmed"
  }
}
```

**Error Response (404):**
```json
{
  "success": false,
  "message": "Order not found"
}
```

**Error Response (400):**
```json
{
  "success": false,
  "message": "Payment signature verification failed",
  "error": "Error details"
}
```

**Flow:**
1. Finds order by Razorpay order ID
2. Verifies payment signature using Razorpay API
3. Updates order status to 'confirmed' and payment status to 'paid'
4. Clears customer's cart
5. Returns updated order details

---

### 3. Payment Failed
**Endpoint:** `POST /api/v1/razorpay/payment-failed`  
**Authentication:** Required (Bearer Token)

Handles payment failure scenarios.

**Request Body:**
```json
{
  "razorpay_order_id": "order_Abc123XYZ",
  "error_code": "BAD_REQUEST_ERROR",
  "error_description": "Payment processing failed"
}
```

**Validation Rules:**
- `razorpay_order_id`: Required, string
- `error_code`: Optional, string
- `error_description`: Optional, string

**Success Response (200):**
```json
{
  "success": false,
  "message": "Payment failed",
  "data": {
    "order_id": 1,
    "error_description": "Payment processing failed"
  }
}
```

**Flow:**
1. Finds order by Razorpay order ID
2. Updates order status to 'cancelled' and payment status to 'failed'
3. Stores error description
4. Returns failure details

---

### 4. Get Payment Status
**Endpoint:** `GET /api/v1/razorpay/payment-status/{orderId}`  
**Authentication:** Required (Bearer Token)

Retrieves the payment status of a specific order.

**URL Parameters:**
- `orderId`: The database order ID (not Razorpay order ID)

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "order_id": 1,
    "order_number": "ORD-ABC123",
    "payment_status": "paid",
    "payment_method": "razorpay",
    "status": "confirmed",
    "total_amount": 2499.00
  }
}
```

**Error Response (404):**
```json
{
  "success": false,
  "message": "Order not found"
}
```

---

## Order Status Values

### Payment Status
- `pending`: Payment not yet initiated
- `paid`: Payment successful and verified
- `failed`: Payment failed or rejected

### Order Status
- `pending`: Order created, payment pending
- `confirmed`: Payment verified, order confirmed
- `cancelled`: Payment failed or order cancelled
- `completed`: Order fulfilled and delivered

---

## Frontend Integration Example

```javascript
// 1. Create Order
const createOrder = async (addressId) => {
  const response = await fetch('/api/v1/razorpay/create-order', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({ address_id: addressId })
  });
  
  const data = await response.json();
  return data;
};

// 2. Open Razorpay Checkout
const initiatePayment = async (addressId) => {
  const orderData = await createOrder(addressId);
  
  const options = {
    key: orderData.data.razorpay_key_id,
    amount: orderData.data.amount * 100, // Amount in paise
    currency: orderData.data.currency,
    name: 'NexEcom',
    description: `Order ${orderData.data.order_number}`,
    order_id: orderData.data.razorpay_order_id,
    handler: async function (response) {
      // 3. Verify Payment
      await verifyPayment(response);
    },
    modal: {
      ondismiss: async function() {
        // Handle payment cancellation
        await handlePaymentFailed(orderData.data.razorpay_order_id);
      }
    }
  };
  
  const rzp = new Razorpay(options);
  rzp.open();
};

// 3. Verify Payment
const verifyPayment = async (razorpayResponse) => {
  const response = await fetch('/api/v1/razorpay/verify-payment', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({
      razorpay_order_id: razorpayResponse.razorpay_order_id,
      razorpay_payment_id: razorpayResponse.razorpay_payment_id,
      razorpay_signature: razorpayResponse.razorpay_signature
    })
  });
  
  const data = await response.json();
  
  if (data.success) {
    // Payment successful
    window.location.href = '/order-success';
  } else {
    // Payment verification failed
    alert('Payment verification failed');
  }
};

// 4. Handle Payment Failed
const handlePaymentFailed = async (razorpayOrderId) => {
  await fetch('/api/v1/razorpay/payment-failed', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({
      razorpay_order_id: razorpayOrderId,
      error_description: 'Payment cancelled by user'
    })
  });
};
```

---

## Database Schema

### Orders Table
```php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('customer_id');
    $table->foreignId('address_id');
    $table->string('order_number')->unique();
    $table->decimal('total_amount', 10, 2);
    $table->string('status'); // pending, confirmed, cancelled, completed
    $table->string('payment_status'); // pending, paid, failed
    $table->string('payment_method')->nullable(); // razorpay, cod, etc.
    $table->string('razorpay_order_id')->nullable();
    $table->string('razorpay_payment_id')->nullable();
    $table->text('payment_error')->nullable();
    $table->timestamp('paid_at')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

### Order Items Table
```php
Schema::create('order_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id');
    $table->foreignId('product_id');
    $table->integer('quantity');
    $table->decimal('price', 10, 2);
    $table->decimal('total', 10, 2);
    $table->timestamps();
});
```

---

## Testing with Postman

### Test Credentials (Razorpay Test Mode)
```
Key ID: rzp_test_xxxxx
Key Secret: xxxxx
```

### Test Card Details
```
Card Number: 4111 1111 1111 1111
Expiry: Any future date
CVV: Any 3 digits
```

### Testing Flow

1. **Login Customer**
   - POST `/api/v1/customers/login`
   - Copy the Bearer token

2. **Create Address** (if not exists)
   - POST `/api/v1/addresses`
   - Note the address_id

3. **Add Items to Cart**
   - POST `/api/v1/cart`
   - Add multiple products

4. **Create Razorpay Order**
   - POST `/api/v1/razorpay/create-order`
   - Use address_id from step 2
   - Note the razorpay_order_id and razorpay_key_id

5. **Simulate Payment** (Use Razorpay Checkout UI or test payment)
   - Get razorpay_payment_id and razorpay_signature

6. **Verify Payment**
   - POST `/api/v1/razorpay/verify-payment`
   - Send all three: razorpay_order_id, razorpay_payment_id, razorpay_signature

7. **Check Order Status**
   - GET `/api/v1/orders/{orderId}`
   - Verify payment_status is 'paid' and status is 'confirmed'

---

## Error Handling

All endpoints follow consistent error response format:

```json
{
  "success": false,
  "message": "Error message",
  "error": "Detailed error description"
}
```

Common HTTP status codes:
- `200`: Success
- `201`: Created
- `400`: Bad Request (validation failed, cart empty, etc.)
- `404`: Not Found
- `500`: Internal Server Error

---

## Security Notes

1. **Never expose Razorpay Key Secret** - Keep it only on the server side
2. **Always verify payment signature** - Don't trust frontend success callbacks alone
3. **Validate address ownership** - Ensure address belongs to authenticated customer
4. **Use HTTPS** - All payment transactions must use secure connections
5. **Store sensitive data securely** - Use encrypted database fields if needed

---

## Additional Order Endpoints

### Get All Orders
**Endpoint:** `GET /api/v1/orders`  
**Authentication:** Required

Returns all orders for authenticated customer with order items and address details.

### Get Pending Orders
**Endpoint:** `GET /api/v1/orders/pending`  
**Authentication:** Required

Returns all pending orders for authenticated customer.

### Get Completed Orders
**Endpoint:** `GET /api/v1/orders/completed`  
**Authentication:** Required

Returns all completed orders for authenticated customer.

### Get Single Order
**Endpoint:** `GET /api/v1/orders/{id}`  
**Authentication:** Required

Returns detailed information about a specific order including items and address.

---

## Support

For Razorpay API documentation: https://razorpay.com/docs/api/
For test credentials and testing: https://razorpay.com/docs/payment-gateway/test-card-details/
