<?php

namespace App\Http\Controllers\Admin\Tag;

use App\Http\Controllers\Controller;
use App\Models\Admin\Tag\Tag;
use App\Repositories\Admin\Tag\TagRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Throwable;

class TagController extends Controller
{
    use Toast;

    protected $tagRepository;

    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
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

        $tags = $this->tagRepository->getAllTags($filters);

        return Inertia::render('Admin/Tag/Index', [
            'tags' => $tags,
            'statistics' => $this->tagRepository->getStatistics(),
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Tag/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->tagRepository->store($request->all());
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Tag successfully created');
        return redirect()->route('admin.tags.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Tag $tag)
    {
        return Inertia::render('Admin/Tag/Show', [
            'tag' => $tag,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tag $tag)
    {
        return Inertia::render('Admin/Tag/Edit', [
            'tag' => $tag,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tag $tag)
    {
        DB::beginTransaction();
        try {
            $this->tagRepository->update($tag->id, $request->all());
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Tag successfully updated');
        return redirect()->route('admin.tags.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag)
    {
        DB::beginTransaction();
        try {
            $this->tagRepository->delete($tag->id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Tag successfully deleted');
        return redirect()->route('admin.tags.index');
    }

    /**
     * Toggle tag active status.
     */
    public function toggleStatus(Tag $tag)
    {
        DB::beginTransaction();
        try {
            $tag = $this->tagRepository->toggleStatus($tag->id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $status = $tag->is_active ? 'activated' : 'deactivated';
        $this->toast('success', 'Success', "Tag successfully {$status}");
        return back();
    }

    /**
     * Bulk delete tags.
     */
    public function bulkDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:tags,id',
            ]);

            $this->tagRepository->bulkDelete($request->ids);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $count = count($request->ids);
        $this->toast('success', 'Success', "{$count} " . ($count === 1 ? 'tag' : 'tags') . " successfully deleted");
        return redirect()->route('admin.tags.index');
    }

    /**
     * Get tags for dropdown (API endpoint).
     */
    public function dropdown(Request $request)
    {
        $tags = $this->tagRepository->getTagsForDropdown();
        return response()->json([
            'tags' => $tags,
        ]);
    }
}

