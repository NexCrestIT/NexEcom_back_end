<?php

namespace App\Http\Controllers\Admin\ScentFamily;

use App\Http\Controllers\Controller;
use App\Repositories\Admin\ScentFamily\ScentFamilyRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Throwable;

class ScentFamilyController extends Controller
{
    use Toast;

    protected $scentFamilyRepository;

    public function __construct(ScentFamilyRepository $scentFamilyRepository)
    {
        $this->scentFamilyRepository = $scentFamilyRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Admin/ScentFamily/Index', [
            'scentFamilies' => $this->scentFamilyRepository->getAllScentFamilies(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/ScentFamily/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->scentFamilyRepository->store($request->all());
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Scent Family successfully created');
        return redirect()->route('admin.scent-families.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $scentFamily = $this->scentFamilyRepository->getScentFamilyById($id);
        return Inertia::render('Admin/ScentFamily/Show', [
            'scentFamily' => $scentFamily,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $scentFamily = $this->scentFamilyRepository->getScentFamilyById($id);
        return Inertia::render('Admin/ScentFamily/Edit', [
            'scentFamily' => $scentFamily,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();
        try {
            $this->scentFamilyRepository->update($id, $request->all());
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Scent Family successfully updated');
        return redirect()->route('admin.scent-families.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            $this->scentFamilyRepository->delete($id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Scent Family successfully deleted');
        return redirect()->route('admin.scent-families.index');
    }

    /**
     * Toggle scent family status.
     */
    public function toggleStatus(string $id)
    {
        DB::beginTransaction();
        try {
            $scentFamily = $this->scentFamilyRepository->toggleStatus($id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $status = $scentFamily->status ? 'activated' : 'deactivated';
        $this->toast('success', 'Success', "Scent Family successfully {$status}");
        return back();
    }

    /**
     * Bulk delete scent families.
     */
    public function bulkDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:scent_families,id',
            ]);

            foreach ($request->ids as $id) {
                $this->scentFamilyRepository->delete($id);
            }
         
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $count = count($request->ids);
        $this->toast('success', 'Success', "{$count} " . ($count === 1 ? 'scent family' : 'scent families') . " successfully deleted");
        return redirect()->route('admin.scent-families.index');
    }
}

