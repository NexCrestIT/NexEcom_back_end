<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Repositories\Admin\User\UserRepository;
use App\Repositories\Admin\Role\RoleRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Throwable;

class UserController extends Controller
{
    use Toast;

    protected $userRepository;
    protected $roleRepository;

    public function __construct(UserRepository $userRepository, RoleRepository $roleRepository)
    {
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = $this->userRepository->getAllUsers();
        $roles = $this->roleRepository->getAllRoles();

        return Inertia::render('Admin/User/Index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = $this->roleRepository->getAllRoles();

        return Inertia::render('Admin/User/Create', [
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->userRepository->store($request->all());
            DB::commit();
            $this->toast('success', 'Success', 'User successfully created');
            return redirect()->route('admin.users.index');
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
        $user = $this->userRepository->getUserById($id);
        $roles = $this->roleRepository->getAllRoles();

        return Inertia::render('Admin/User/Show', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = $this->userRepository->getUserById($id);
        $roles = $this->roleRepository->getAllRoles();

        return Inertia::render('Admin/User/Edit', [
            'user' => $user,
            'roles' => $roles,
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
            $this->userRepository->update($id, $data);
            DB::commit();
            $this->toast('success', 'Success', 'User successfully updated');
            return redirect()->route('admin.users.index');
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
            $this->userRepository->delete($id);
            DB::commit();
            $this->toast('success', 'Success', 'User successfully deleted');
            return redirect()->route('admin.users.index');
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request, string $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user = $this->userRepository->getUserById($id);
            $user->update([
                'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            ]);

            DB::commit();
            $this->toast('success', 'Success', 'Password successfully updated');
            return back();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()]);
        }
    }
}
