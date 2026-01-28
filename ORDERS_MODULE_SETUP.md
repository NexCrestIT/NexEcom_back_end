# Orders Module - Setup & Quick Start Guide

## Installation & Setup

### 1. Database Preparation

Ensure your `orders` table includes all necessary columns. If migrating from an older schema, you may need to add:

```sql
ALTER TABLE orders ADD COLUMN IF NOT EXISTS razorpay_order_id VARCHAR(255) NULLABLE;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS razorpay_payment_id VARCHAR(255) NULLABLE;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_error TEXT NULLABLE;
ALTER TABLE orders MODIFY COLUMN status VARCHAR(50) DEFAULT 'pending';
ALTER TABLE orders MODIFY COLUMN payment_status VARCHAR(50) DEFAULT 'pending';
```

### 2. Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:cache
```

### 3. Run Tests

```bash
php artisan test tests/Feature/Admin/Order/OrderControllerTest.php
```

## Quick Start

### Admin Panel Usage

#### View All Orders
Navigate to: `/admin/orders`

Features available:
- Filter by status, payment status, date, amount
- Search by order number or customer name
- Pagination (15 items per page)
- Export to CSV
- View statistics

#### View Order Details
Click on any order number or "View" button

Features available:
- Complete order information
- Customer details
- Shipping information
- Order timeline
- Status management
- Payment information
- Invoice generation
- Refund processing
- Notes management

#### Update Order Status

1. Go to order details page
2. In "Order Status" section, select new status from dropdown
3. Add optional notes
4. Click "Update Status"

**Valid Status Transitions:**
- Pending → Processing, Cancelled
- Processing → Shipped, Cancelled
- Shipped → Delivered, Returned
- Delivered → Returned
- Cancelled → (no transitions allowed)
- Returned → Processing

#### Process Refund

1. Go to order details page
2. Scroll to "Refund" section
3. Enter refund amount (defaults to full order amount)
4. Add refund reason
5. Click "Process Refund"

**Requirements:**
- Payment status must be "Completed"

#### Generate Invoice

1. Go to order details page
2. Scroll to "Invoice" section
3. Options:
   - Click "Generate Invoice" to create new invoice
   - Click "Download PDF" to download PDF version
   - Click "View Invoice" to see HTML version
   - Click "Email Invoice" to send to customer

#### Export Orders

1. Go to orders list page
2. Apply desired filters
3. Click "Export to CSV" button
4. CSV file will download with selected/filtered orders

### API Usage (for Frontend/Mobile)

#### Get Customer's Orders
```bash
GET /api/orders
Authorization: Bearer {token}
```

Response:
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
      "created_at": "2024-01-20T10:30:00Z"
    }
  ]
}
```

#### Get Order Details
```bash
GET /api/orders/{orderId}
Authorization: Bearer {token}
```

#### Cancel Order
```bash
POST /api/orders/{orderId}/cancel
Authorization: Bearer {token}
```

**Allowed statuses:** pending, processing

#### Request Return
```bash
POST /api/orders/{orderId}/return
Authorization: Bearer {token}
```

**Conditions:**
- Order must be delivered
- Less than 30 days old

#### Download Invoice
```bash
GET /api/orders/{orderId}/invoice
Authorization: Bearer {token}
```

#### Get Order Statistics
```bash
GET /api/orders/stats
Authorization: Bearer {token}
```

Response:
```json
{
  "success": true,
  "data": {
    "total_orders": 5,
    "pending_orders": 0,
    "completed_orders": 3,
    "cancelled_orders": 1,
    "total_spent": 1299.99
  }
}
```

## Dashboard Statistics

Available statistics via `/admin/orders/dashboard/stats`:

```json
{
  "statistics": {
    "total_orders": 150,
    "pending_orders": 20,
    "processing_orders": 15,
    "shipped_orders": 35,
    "delivered_orders": 70,
    "cancelled_orders": 5,
    "total_revenue": 15000.00,
    "pending_revenue": 2000.00
  },
  "statusDistribution": {
    "pending": 20,
    "processing": 15,
    "shipped": 35,
    "delivered": 70,
    "cancelled": 5
  },
  "recentOrders": [
    {
      "id": 150,
      "order_number": "ORD-2024-150",
      "total_amount": 299.99,
      "status": "pending"
    }
  ],
  "topCustomers": [
    {
      "customer_id": 1,
      "order_count": 10,
      "total_spent": 5000.00
    }
  ]
}
```

## Common Operations

### Bulk Update Order Status

1. Go to orders list
2. Check checkboxes next to orders to update
3. From dropdown in bulk actions, select new status
4. Click "Update Status"

All selected orders will be updated to the new status.

### Filter Orders by Date Range

1. Go to orders list
2. Click "Filters"
3. Enter "From Date" and "To Date"
4. Click "Apply Filters"

### Search by Customer

1. Go to orders list
2. Enter customer name or email in search box
3. Results will update in real-time

### Calculate Shipping Cost

```php
use App\Services\ShipmentService;

$order = Order::find(1);
$cost = ShipmentService::calculateShippingCost($order, 'fedex');
// Returns: float (5.00 + weight + distance adjustments)
```

### Validate Status Transition

```php
use App\Services\OrderStatusService;

$order = Order::find(1);
if (OrderStatusService::isValidTransition($order->status, 'shipped')) {
    // Valid transition
}

$nextStatuses = OrderStatusService::getNextPossibleStatuses($order->status);
// Returns: ['shipped' => 'Shipped', 'cancelled' => 'Cancelled']
```

## Troubleshooting

### Orders Not Showing
1. Check database has orders
2. Verify customer relationships are loaded
3. Check order items exist
4. Review error logs: `storage/logs/laravel.log`

### Status Update Failed
1. Verify status transition is valid
2. Check order exists and is accessible
3. Review error logs
4. Use `OrderStatusService::getNextPossibleStatuses()` to see allowed transitions

### Refund Not Processing
1. Verify payment status is "completed"
2. Check Razorpay integration if applicable
3. Review error logs
4. Ensure refund amount is valid

### Invoice Not Generating
1. Verify order has items
2. Check customer and address information
3. Verify all products have names
4. Review error logs

### Export Not Working
1. Verify PHP memory limit: `memory_limit = 256M`
2. Check file permissions on storage directory
3. Verify orders exist to export
4. Review error logs

## Performance Tips

1. **Use Pagination**: Don't load all orders at once
2. **Index Database**: Ensure proper indexes on orders table
3. **Lazy Load**: Load relationships only when needed
4. **Cache Statistics**: Cache statistics for dashboard
5. **Archive Old Orders**: Move older orders to archive table

Example cache implementation:
```php
$stats = Cache::remember('order_statistics', now()->addHour(), function () {
    return Order::getStatistics();
});
```

## Security Considerations

1. **Authentication**: All routes require authentication
2. **Authorization**: Implement role-based access:
```php
Route::middleware('role:admin')->group(function () {
    Route::resource('orders', OrderController::class);
});
```

3. **Validation**: All inputs are validated before processing
4. **SQL Injection**: Using Eloquent ORM prevents SQL injection
5. **Rate Limiting**: Add rate limiting to API endpoints:
```php
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);
});
```

## Next Steps

1. **Create Inertia Views**: Build the Vue components using the guide in `ORDERS_MODULE_VIEWS_GUIDE.md`
2. **Configure Email Notifications**: Set up order status change notifications
3. **Integrate Payment Gateway**: Connect Razorpay or other payment provider
4. **Add Shipping Integration**: Connect with carrier APIs for tracking
5. **Setup Webhooks**: Handle payment gateway webhooks
6. **Add Audit Logging**: Track all order changes
7. **Create Reports**: Build advanced reporting features

## Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Inertia.js Documentation](https://inertiajs.com/)
- [Razorpay Documentation](https://razorpay.com/docs/)
- [HTTP Status Codes](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status)

## Support

For issues or questions:
1. Check the `ORDERS_MODULE_DOCUMENTATION.md` for detailed info
2. Review the test file: `tests/Feature/Admin/Order/OrderControllerTest.php`
3. Check error logs: `storage/logs/laravel.log`
4. Contact the development team

---
Last Updated: January 2024
Version: 1.0
