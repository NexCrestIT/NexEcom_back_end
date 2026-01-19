<?php

namespace App\Http\Controllers\Admin\Carousel;

use App\Http\Controllers\Controller;
use App\Models\Carousel;
use App\Repositories\Admin\Carousel\CarouselRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Throwable;

class CarouselController extends Controller
{
    use Toast;

    protected $carouselRepository;

    public function __construct(CarouselRepository $carouselRepository)
    {
        $this->carouselRepository = $carouselRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $filters = [
            'is_active' => $request->get('is_active'),
            'search' => $request->get('search'),
        ];

        $carousels = $this->carouselRepository->getAllCarousels($filters);

        return Inertia::render('Admin/Carousel/Index', [
            'carousels' => $carousels,
            'statistics' => $this->carouselRepository->getStatistics(),
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Carousel/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->carouselRepository->store($request->all());
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Carousel successfully created');
        return redirect()->route('admin.carousels.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Carousel $carousel)
    {
        return Inertia::render('Admin/Carousel/Show', [
            'carousel' => $carousel,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Carousel $carousel)
    {
        return Inertia::render('Admin/Carousel/Edit', [
            'carousel' => $carousel,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Carousel $carousel)
    {
        DB::beginTransaction();
        try {
            $this->carouselRepository->update($carousel->id, $request->all());
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Carousel successfully updated');
        return redirect()->route('admin.carousels.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Carousel $carousel)
    {
        DB::beginTransaction();
        try {
            $this->carouselRepository->delete($carousel->id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Carousel successfully deleted');
        return redirect()->route('admin.carousels.index');
    }

    /**
     * Toggle carousel active status.
     */
    public function toggleStatus(Carousel $carousel)
    {
        DB::beginTransaction();
        try {
            $carousel = $this->carouselRepository->toggleStatus($carousel->id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $status = $carousel->is_active ? 'activated' : 'deactivated';
        $this->toast('success', 'Success', "Carousel successfully {$status}");
        return back();
    }

    /**
     * Bulk delete carousels.
     */
    public function bulkDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:carousels,id',
            ]);

            $this->carouselRepository->bulkDelete($request->ids);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $count = count($request->ids);
        $this->toast('success', 'Success', "{$count} " . ($count === 1 ? 'carousel' : 'carousels') . " successfully deleted");
        return redirect()->route('admin.carousels.index');
    }
}
