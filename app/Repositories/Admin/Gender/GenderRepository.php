<?php

namespace App\Repositories\Admin\Gender;

use App\Models\Admin\Gender\Gender;
use Illuminate\Validation\ValidationException;

class GenderRepository
{
    /**
     * Get all genders.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllGenders()
    {
        return Gender::orderBy('name')->get();
    }

    /**
     * Get a gender by ID.
     *
     * @param int $id
     * @return Gender
     */
    public function getGenderById($id)
    {
        return Gender::findOrFail($id);
    }

    /**
     * Create a new gender.
     *
     * @param array $data
     * @return Gender
     */
    public function store($data)
    {
        $this->validateData($data);
        return Gender::create([
            'name' => $data['name'],
            'status' => $data['status'] ?? true,
        ]);
    }

    /**
     * Update a gender.
     *
     * @param int $id
     * @param array $data
     * @return Gender
     */
    public function update($id, $data)
    {
        $this->validateData($data, true, $id);
        $gender = Gender::findOrFail($id);
        $gender->update([
            'name' => $data['name'],
            'status' => $data['status'] ?? $gender->status,
        ]);
        return $gender->fresh();
    }

    /**
     * Delete a gender.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $gender = Gender::findOrFail($id);
        return $gender->delete();
    }

    /**
     * Toggle gender status.
     *
     * @param int $id
     * @return Gender
     */
    public function toggleStatus($id)
    {
        $gender = Gender::findOrFail($id);
        $gender->update(['status' => !$gender->status]);
        return $gender;
    }

    /**
     * Get genders for dropdown.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getGendersForDropdown()
    {
        return Gender::where('status', true)
            ->orderBy('name')
            ->get()
            ->map(function ($gender) {
                return [
                    'id' => $gender->id,
                    'name' => $gender->name,
                ];
            });
    }

    /**
     * Validate gender data.
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
            'name' => 'required|string|max:255|unique:genders,name',
            'status' => 'boolean',
        ];

        if ($isUpdate && $id) {
            $rules['name'] = 'required|string|max:255|unique:genders,name,' . $id;
        }

        return validator($data, $rules)->validate();
    }
}

