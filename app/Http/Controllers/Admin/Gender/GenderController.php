<?php

namespace App\Http\Controllers\Admin\Gender;

use App\Http\Controllers\Controller;
use App\Repositories\Admin\Gender\GenderRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Throwable;

class GenderController extends Controller
{
    use Toast;

    protected $genderRepository;

    public function __construct(GenderRepository $genderRepository)
    {
        $this->genderRepository = $genderRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Admin/Gender/Index', [
            'genders' => $this->genderRepository->getAllGenders(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Gender/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->genderRepository->store($request->all());
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Gender successfully created');
        return redirect()->route('admin.genders.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $gender = $this->genderRepository->getGenderById($id);
        return Inertia::render('Admin/Gender/Show', [
            'gender' => $gender,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $gender = $this->genderRepository->getGenderById($id);
        return Inertia::render('Admin/Gender/Edit', [
            'gender' => $gender,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();
        try {
            $this->genderRepository->update($id, $request->all());
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Gender successfully updated');
        return redirect()->route('admin.genders.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            $this->genderRepository->delete($id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Gender successfully deleted');
        return redirect()->route('admin.genders.index');
    }

    /**
     * Toggle gender status.
     */
    public function toggleStatus(string $id)
    {
        DB::beginTransaction();
        try {
            $gender = $this->genderRepository->toggleStatus($id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $status = $gender->status ? 'activated' : 'deactivated';
        $this->toast('success', 'Success', "Gender successfully {$status}");
        return back();
    }

    /**
     * Bulk delete genders.
     */
    public function bulkDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:genders,id',
            ]);

            foreach ($request->ids as $id) {
                $this->genderRepository->delete($id);
            }
         
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $count = count($request->ids);
        $this->toast('success', 'Success', "{$count} " . ($count === 1 ? 'gender' : 'genders') . " successfully deleted");
        return redirect()->route('admin.genders.index');
    }
}

