<?php

namespace App\Http\Controllers\Admin\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Repositories\Admin\Order\OrderRepository;
use App\Services\OrderStatusService;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Throwable;

class OrderController extends Controller
{
    use Toast;

    protected $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = [
            'status' => $request->get('status'),
            'payment_status' => $request->get('payment_status'),
            'search' => $request->get('search'),
            'from_date' => $request->get('from_date'),
            'to_date' => $request->get('to_date'),
            'min_amount' => $request->get('min_amount'),
            'max_amount' => $request->get('max_amount'),
        ];

        $orders = $this->orderRepository->getPaginatedOrders(15, $filters);
        $statistics = $this->orderRepository->getStatistics();
        $orderStatuses = OrderStatusService::getOrderStatuses();
        $paymentStatuses = OrderStatusService::getPaymentStatuses();

        return Inertia::render('Admin/Order/Index', [
            'orders' => $orders,
            'statistics' => $statistics,
            'filters' => $filters,
            'orderStatuses' => $orderStatuses,
            'paymentStatuses' => $paymentStatuses,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $order = $this->orderRepository->getOrderById($id);
            $orderStatuses = OrderStatusService::getOrderStatuses();
            $paymentStatuses = OrderStatusService::getPaymentStatuses();
            $nextPossibleStatuses = OrderStatusService::getNextPossibleStatuses($order->status);

            return Inertia::render('Admin/Order/Show', [
                'order' => $order,
                'orderStatuses' => $orderStatuses,
                'paymentStatuses' => $paymentStatuses,
                'nextPossibleStatuses' => $nextPossibleStatuses,
                'canCancel' => OrderStatusService::canCancel($order),
                'canRefund' => OrderStatusService::canRefund($order),
                'canShip' => OrderStatusService::canShip($order),
            ]);
        } catch (Throwable $th) {
            $this->toast('error', 'Error', 'Order not found');
            return redirect()->route('admin.orders.index');
        }
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, string $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'status' => 'required|string',
                'notes' => 'nullable|string',
            ]);

            $order = $this->orderRepository->getOrderById($id);

            // Check if transition is valid
            if (!OrderStatusService::isValidTransition($order->status, $request->status)) {
                throw new \Exception('Invalid status transition from ' . $order->status . ' to ' . $request->status);
            }

            $this->orderRepository->updateOrderStatus(
                $id,
                $request->status,
                $request->input('notes')
            );

            DB::commit();
            $this->toast('success', 'Success', 'Order status updated successfully');
            return redirect()->route('admin.orders.show', $id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()]);
        }
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(Request $request, string $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'payment_status' => 'required|string|in:pending,completed,failed,refunded',
            ]);

            $this->orderRepository->updatePaymentStatus($id, $request->payment_status);

            DB::commit();
            $this->toast('success', 'Success', 'Payment status updated successfully');
            return redirect()->route('admin.orders.show', $id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()]);
        }
    }

    /**
     * Update order notes
     */
    public function updateNotes(Request $request, string $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'notes' => 'nullable|string',
            ]);

            $this->orderRepository->updateNotes($id, $request->notes);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Notes updated successfully',
            ]);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 400);
        }
    }

    /**
     * Process refund
     */
    public function processRefund(Request $request, string $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'amount' => 'nullable|numeric|min:0',
                'reason' => 'nullable|string',
            ]);

            $order = $this->orderRepository->getOrderById($id);

            if (!OrderStatusService::canRefund($order)) {
                throw new \Exception('Cannot refund order with payment status: ' . $order->payment_status);
            }

            OrderStatusService::processRefund($order, $request->input('amount'));

            DB::commit();
            $this->toast('success', 'Success', 'Refund processed successfully');
            return redirect()->route('admin.orders.show', $id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()]);
        }
    }

    /**
     * Bulk update order status
     */
    public function bulkUpdateStatus(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'order_ids' => 'required|array',
                'order_ids.*' => 'integer',
                'status' => 'required|string',
            ]);

            $this->orderRepository->bulkUpdateStatus($request->order_ids, $request->status);

            DB::commit();
            $this->toast('success', 'Success', 'Orders updated successfully');
            return redirect()->route('admin.orders.index');
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()]);
        }
    }

    /**
     * Delete order
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            $this->orderRepository->delete($id);

            DB::commit();
            $this->toast('success', 'Success', 'Order deleted successfully');
            return redirect()->route('admin.orders.index');
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()]);
        }
    }

    /**
     * Export orders
     */
    public function export(Request $request)
    {
        try {
            $filters = [
                'status' => $request->get('status'),
                'payment_status' => $request->get('payment_status'),
                'search' => $request->get('search'),
                'from_date' => $request->get('from_date'),
                'to_date' => $request->get('to_date'),
            ];

            // Get all orders (not paginated)
            $orders = Order::with(['customer', 'items'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Create CSV
            $headers = [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename=orders-' . date('Y-m-d H:i:s') . '.csv',
            ];

            $callback = function() use ($orders) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Order ID', 'Order Number', 'Customer', 'Email', 'Amount', 'Status', 'Payment Status', 'Date']);

                foreach ($orders as $order) {
                    fputcsv($file, [
                        $order->id,
                        $order->order_number,
                        $order->customer->name ?? 'N/A',
                        $order->customer->email ?? 'N/A',
                        $order->total_amount,
                        $order->status,
                        $order->payment_status,
                        $order->created_at->format('Y-m-d H:i:s'),
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (Throwable $th) {
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
    }

    /**
     * Get dashboard statistics
     */
    public function dashboardStats()
    {
        try {
            $stats = $this->orderRepository->getStatistics();
            $statusDistribution = $this->orderRepository->getStatusDistribution();
            $recentOrders = $this->orderRepository->getRecentOrders(5);
            $topCustomers = $this->orderRepository->getTopCustomers(5);

            return response()->json([
                'success' => true,
                'data' => [
                    'statistics' => $stats,
                    'statusDistribution' => $statusDistribution,
                    'recentOrders' => $recentOrders,
                    'topCustomers' => $topCustomers,
                ],
            ]);
        } catch (Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 400);
        }
    }
}
