<?php

namespace App\Http\Controllers\Admin\Role;

use App\Http\Controllers\Controller;
use App\Repositories\Admin\Role\RoleRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Throwable;

class RoleController extends Controller
{
    use Toast;

    protected $roleRepository;

    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $roles = $this->roleRepository->getAllRoles();
        $permissions = $this->roleRepository->getPermissionsByModule();

        return Inertia::render('Admin/Role/Index', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = $this->roleRepository->getPermissionsByModule();

        return Inertia::render('Admin/Role/Create', [
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->roleRepository->store($request->all());
            DB::commit();
            $this->toast('success', 'Success', 'Role successfully created');
            return redirect()->route('admin.roles.index');
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $role = $this->roleRepository->getRoleById($id);
        $permissions = $this->roleRepository->getPermissionsByModule();

        return Inertia::render('Admin/Role/Show', [
            'role' => $role,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $role = $this->roleRepository->getRoleById($id);
        $permissions = $this->roleRepository->getPermissionsByModule();

        return Inertia::render('Admin/Role/Edit', [
            'role' => $role,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $data['id'] = $id;
            $this->roleRepository->update($id, $data);
            DB::commit();
            $this->toast('success', 'Success', 'Role successfully updated');
            return redirect()->route('admin.roles.index');
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
            $this->roleRepository->delete($id);
            DB::commit();
            $this->toast('success', 'Success', 'Role successfully deleted');
            return redirect()->route('admin.roles.index');
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
    }
}

