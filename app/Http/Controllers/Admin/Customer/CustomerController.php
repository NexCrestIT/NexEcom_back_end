<?php

namespace App\Http\Controllers\Admin\Customer;

use App\Http\Controllers\Controller;
use App\Repositories\Admin\Customer\CustomerRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Throwable;

class CustomerController extends Controller
{
    use Toast;

    protected $customerRepository;

    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = [
            'is_active' => $request->get('is_active'),
            'is_verified' => $request->get('is_verified'),
            'search' => $request->get('search'),
        ];

        $customers = $this->customerRepository->getAllCustomers($filters);

        return Inertia::render('Admin/Customer/Index', [
            'customers' => $customers,
            'statistics' => $this->customerRepository->getStatistics(),
            'filters' => $filters,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $customer = $this->customerRepository->getCustomerById($id);

        return Inertia::render('Admin/Customer/Show', [
            'customer' => $customer,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $customer = $this->customerRepository->getCustomerById($id);

        return Inertia::render('Admin/Customer/Edit', [
            'customer' => $customer,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255|unique:customers,email,' . $id,
                'phone_number' => 'nullable|string|max:20|unique:customers,phone_number,' . $id,
                'city' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'postcode' => 'nullable|string|max:20',
                'country' => 'nullable|string|max:255',
                'date_of_birth' => 'nullable|date|before:today',
                'gender' => 'nullable|in:male,female,other',
                'is_active' => 'boolean',
                'is_verified' => 'boolean',
                'password' => 'nullable|string|min:8|confirmed',
            ]);

            $this->customerRepository->update($id, $request->all());
            DB::commit();
            $this->toast('success', 'Success', 'Customer successfully updated');
            return redirect()->route('admin.customers.index');
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            $this->customerRepository->delete($id);
            DB::commit();
            $this->toast('success', 'Success', 'Customer successfully deleted');
            return redirect()->route('admin.customers.index');
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
    }

    /**
     * Toggle customer active status.
     */
    public function toggleStatus(string $id)
    {
        DB::beginTransaction();
        try {
            $customer = $this->customerRepository->toggleStatus($id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $status = $customer->is_active ? 'activated' : 'deactivated';
        $this->toast('success', 'Success', "Customer successfully {$status}");
        return back();
    }

    /**
     * Bulk delete customers.
     */
    public function bulkDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:customers,id',
            ]);

            $this->customerRepository->bulkDelete($request->ids);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $count = count($request->ids);
        $this->toast('success', 'Success', "{$count} " . ($count === 1 ? 'customer' : 'customers') . " successfully deleted");
        return redirect()->route('admin.customers.index');
    }
}
