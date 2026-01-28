<?php

namespace Tests\Feature\Admin\Order;

use App\Models\Order;
use App\Models\Customer;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    protected $user;
    protected $customer;
    protected $order;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create();

        // Create test customer
        $this->customer = Customer::factory()->create();

        // Create test products
        $products = Product::factory(3)->create();

        // Create test order
        $this->order = Order::factory()
            ->for($this->customer)
            ->create([
                'status' => 'pending',
                'payment_status' => 'pending',
            ]);

        // Add items to order
        foreach ($products as $product) {
            OrderItem::factory()
                ->for($this->order)
                ->for($product)
                ->create();
        }
    }

    /**
     * Test: View all orders
     */
    public function test_index_lists_all_orders()
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/orders');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page
                ->component('Admin/Order/Index')
                ->has('orders')
                ->has('statistics')
                ->has('filters')
        );
    }

    /**
     * Test: View single order
     */
    public function test_show_displays_order_details()
    {
        $response = $this->actingAs($this->user)
            ->get("/admin/orders/{$this->order->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page
                ->component('Admin/Order/Show')
                ->where('order.id', $this->order->id)
                ->has('orderStatuses')
                ->has('paymentStatuses')
        );
    }

    /**
     * Test: Update order status
     */
    public function test_update_status_changes_order_status()
    {
        $response = $this->actingAs($this->user)
            ->post("/admin/orders/{$this->order->id}/update-status", [
                'status' => 'processing',
                'notes' => 'Order is being prepared',
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => 'processing',
            'notes' => 'Order is being prepared',
        ]);

        $response->assertRedirect("/admin/orders/{$this->order->id}");
    }

    /**
     * Test: Invalid status transition is rejected
     */
    public function test_invalid_status_transition_fails()
    {
        $this->order->update(['status' => 'delivered']);

        $response = $this->actingAs($this->user)
            ->post("/admin/orders/{$this->order->id}/update-status", [
                'status' => 'pending',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors();
    }

    /**
     * Test: Update payment status
     */
    public function test_update_payment_status_changes_payment_status()
    {
        $response = $this->actingAs($this->user)
            ->post("/admin/orders/{$this->order->id}/update-payment-status", [
                'payment_status' => 'completed',
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'payment_status' => 'completed',
        ]);

        $response->assertRedirect("/admin/orders/{$this->order->id}");
    }

    /**
     * Test: Update order notes
     */
    public function test_update_notes_via_ajax()
    {
        $response = $this->actingAs($this->user)
            ->post("/admin/orders/{$this->order->id}/update-notes", [
                'notes' => 'This is a test note',
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'notes' => 'This is a test note',
        ]);

        $response->assertJson([
            'success' => true,
            'message' => 'Notes updated successfully',
        ]);
    }

    /**
     * Test: Process refund
     */
    public function test_process_refund()
    {
        $this->order->update(['payment_status' => 'completed']);

        $response = $this->actingAs($this->user)
            ->post("/admin/orders/{$this->order->id}/process-refund", [
                'amount' => 50.00,
                'reason' => 'Customer requested refund',
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'payment_status' => 'refunded',
        ]);

        $response->assertRedirect("/admin/orders/{$this->order->id}");
    }

    /**
     * Test: Bulk update status
     */
    public function test_bulk_update_status()
    {
        $order1 = Order::factory()->for($this->customer)->create(['status' => 'pending']);
        $order2 = Order::factory()->for($this->customer)->create(['status' => 'pending']);

        $response = $this->actingAs($this->user)
            ->post('/admin/orders/bulk-update-status', [
                'order_ids' => [$order1->id, $order2->id],
                'status' => 'processing',
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order1->id,
            'status' => 'processing',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order2->id,
            'status' => 'processing',
        ]);
    }

    /**
     * Test: Delete order
     */
    public function test_destroy_deletes_order()
    {
        $response = $this->actingAs($this->user)
            ->delete("/admin/orders/{$this->order->id}");

        $this->assertModelMissing($this->order);
        $response->assertRedirect('/admin/orders');
    }

    /**
     * Test: Filter orders by status
     */
    public function test_index_filters_by_status()
    {
        Order::factory(5)->for($this->customer)->create(['status' => 'pending']);
        Order::factory(3)->for($this->customer)->create(['status' => 'processing']);

        $response = $this->actingAs($this->user)
            ->get('/admin/orders?status=pending');

        $response->assertInertia(fn ($page) =>
            $page->where('filters.status', 'pending')
        );
    }

    /**
     * Test: Search orders
     */
    public function test_index_filters_by_search()
    {
        $response = $this->actingAs($this->user)
            ->get("/admin/orders?search={$this->order->order_number}");

        $response->assertInertia(fn ($page) =>
            $page->where('filters.search', $this->order->order_number)
        );
    }

    /**
     * Test: Export orders as CSV
     */
    public function test_export_orders_as_csv()
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/orders/export/csv');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=utf-8');
    }

    /**
     * Test: Get dashboard statistics
     */
    public function test_get_dashboard_stats()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/admin/orders/dashboard/stats');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'statistics',
                'statusDistribution',
                'recentOrders',
                'topCustomers',
            ],
        ]);
    }

    /**
     * Test: Unauthorized access returns error
     */
    public function test_unauthorized_user_cannot_access_orders()
    {
        $response = $this->get('/admin/orders');

        $response->assertRedirect('/login');
    }
}
