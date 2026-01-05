<?php

namespace App\Http\Controllers\Admin\Label;

use App\Http\Controllers\Controller;
use App\Repositories\Admin\Label\LabelRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Throwable;

class LabelController extends Controller
{
    use Toast;

    protected $labelRepository;

    public function __construct(LabelRepository $labelRepository)
    {
        $this->labelRepository = $labelRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Admin/Label/Index', [
            'labels' => $this->labelRepository->getAllLabels(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Label/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->labelRepository->store($request->all());
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Label successfully created');
        return redirect()->route('admin.labels.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $label = $this->labelRepository->getLabelById($id);
        return Inertia::render('Admin/Label/Show', [
            'label' => $label,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $label = $this->labelRepository->getLabelById($id);
        return Inertia::render('Admin/Label/Edit', [
            'label' => $label,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();
        try {
            $this->labelRepository->update($id, $request->all());
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Label successfully updated');
        return redirect()->route('admin.labels.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            $this->labelRepository->delete($id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Label successfully deleted');
        return redirect()->route('admin.labels.index');
    }

    /**
     * Bulk delete labels.
     */
    public function bulkDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:labels,id',
            ]);

            foreach ($request->ids as $id) {
                $this->labelRepository->delete($id);
            }
         
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $count = count($request->ids);
        $this->toast('success', 'Success', "{$count} " . ($count === 1 ? 'label' : 'labels') . " successfully deleted");
        return redirect()->route('admin.labels.index');
    }
}
