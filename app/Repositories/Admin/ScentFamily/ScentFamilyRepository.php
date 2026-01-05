<?php

namespace App\Repositories\Admin\ScentFamily;

use App\Models\Admin\ScentFamily\ScentFamily;
use Illuminate\Validation\ValidationException;

class ScentFamilyRepository
{
    /**
     * Get all scent families.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllScentFamilies()
    {
        return ScentFamily::orderBy('name')->get();
    }

    /**
     * Get a scent family by ID.
     *
     * @param int $id
     * @return ScentFamily
     */
    public function getScentFamilyById($id)
    {
        return ScentFamily::findOrFail($id);
    }

    /**
     * Create a new scent family.
     *
     * @param array $data
     * @return ScentFamily
     */
    public function store($data)
    {
        $this->validateData($data);
        return ScentFamily::create([
            'name' => $data['name'],
            'status' => $data['status'] ?? true,
        ]);
    }

    /**
     * Update a scent family.
     *
     * @param int $id
     * @param array $data
     * @return ScentFamily
     */
    public function update($id, $data)
    {
        $this->validateData($data, true, $id);
        $scentFamily = ScentFamily::findOrFail($id);
        $scentFamily->update([
            'name' => $data['name'],
            'status' => $data['status'] ?? $scentFamily->status,
        ]);
        return $scentFamily->fresh();
    }

    /**
     * Delete a scent family.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $scentFamily = ScentFamily::findOrFail($id);
        return $scentFamily->delete();
    }

    /**
     * Toggle scent family status.
     *
     * @param int $id
     * @return ScentFamily
     */
    public function toggleStatus($id)
    {
        $scentFamily = ScentFamily::findOrFail($id);
        $scentFamily->update(['status' => !$scentFamily->status]);
        return $scentFamily;
    }

    /**
     * Get scent families for dropdown.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getScentFamiliesForDropdown()
    {
        return ScentFamily::where('status', true)
            ->orderBy('name')
            ->get()
            ->map(function ($scentFamily) {
                return [
                    'id' => $scentFamily->id,
                    'name' => $scentFamily->name,
                ];
            });
    }

    /**
     * Validate scent family data.
     *
     * @param array $data
     * @param bool $isUpdate
     * @param int|null $id
     * @return array
     * @throws ValidationException
     */
    public function validateData($data, $isUpdate = false, $id = null)
    {
        $rules = [
            'name' => 'required|string|max:255|unique:scent_families,name',
            'status' => 'boolean',
        ];

        if ($isUpdate && $id) {
            $rules['name'] = 'required|string|max:255|unique:scent_families,name,' . $id;
        }

        return validator($data, $rules)->validate();
    }
}

