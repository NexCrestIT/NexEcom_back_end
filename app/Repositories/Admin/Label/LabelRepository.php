<?php

namespace App\Repositories\Admin\Label;

use App\Models\Admin\label\Label;
use Illuminate\Validation\ValidationException;

class LabelRepository
{
    /**
     * Get all labels.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllLabels()
    {
        return Label::orderBy('name')->get();
    }

    /**
     * Get a label by ID.
     *
     * @param int $id
     * @return Label
     */
    public function getLabelById($id)
    {
        return Label::findOrFail($id);
    }

    /**
     * Create a new label.
     *
     * @param array $data
     * @return Label
     */
    public function store($data)
    {
        $this->validateData($data);
        return Label::create([
            'name' => $data['name'],
        ]);
    }

    /**
     * Update a label.
     *
     * @param int $id
     * @param array $data
     * @return Label
     */
    public function update($id, $data)
    {
        $this->validateData($data, true, $id);
        $label = Label::findOrFail($id);
        $label->update([
            'name' => $data['name'],
        ]);
        return $label->fresh();
    }

    /**
     * Delete a label.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $label = Label::findOrFail($id);
        return $label->delete();
    }

    /**
     * Validate label data.
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
            'name' => 'required|string|max:255|unique:labels,name',
        ];

        if ($isUpdate && $id) {
            $rules['name'] = 'required|string|max:255|unique:labels,name,' . $id;
        }

        return validator($data, $rules)->validate();
    }
}
