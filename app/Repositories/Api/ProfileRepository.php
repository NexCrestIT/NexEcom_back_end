<?php

namespace App\Repositories\Api;

use App\Models\Profile;
use Exception;
use Illuminate\Support\Facades\Storage;

class ProfileRepository
{
    protected $model;

    public function __construct(Profile $model)
    {
        $this->model = $model;
    }

    /**
     * Validate profile data
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    private function validateProfileData(array $data): array
    {
        $errors = [];

        // First Name validation
        if (isset($data['first_name'])) {
            if (strlen($data['first_name']) > 255) {
                $errors['first_name'] = 'First name must not exceed 255 characters';
            }
        }

        // Last Name validation
        if (isset($data['last_name'])) {
            if (strlen($data['last_name']) > 255) {
                $errors['last_name'] = 'Last name must not exceed 255 characters';
            }
        }

        // Phone Number validation
        if (isset($data['phone_number'])) {
            if (strlen($data['phone_number']) > 20) {
                $errors['phone_number'] = 'Phone number must not exceed 20 characters';
            }
            if (!preg_match('/^[0-9\s\-\+\(\)\.]+$/', $data['phone_number'])) {
                $errors['phone_number'] = 'Phone number format is invalid';
            }
        }

        // Email Address validation
        if (isset($data['email_address'])) {
            if (!filter_var($data['email_address'], FILTER_VALIDATE_EMAIL)) {
                $errors['email_address'] = 'Email address format is invalid';
            }
        }

        // City validation
        if (isset($data['city'])) {
            if (strlen($data['city']) > 100) {
                $errors['city'] = 'City must not exceed 100 characters';
            }
        }

        // State/County validation
        if (isset($data['state_county'])) {
            if (strlen($data['state_county']) > 100) {
                $errors['state_county'] = 'State/County must not exceed 100 characters';
            }
        }

        // Postcode validation
        if (isset($data['postcode'])) {
            if (strlen($data['postcode']) > 20) {
                $errors['postcode'] = 'Postcode must not exceed 20 characters';
            }
        }

        // Country validation
        if (isset($data['country'])) {
            if (strlen($data['country']) > 100) {
                $errors['country'] = 'Country must not exceed 100 characters';
            }
        }

        if (!empty($errors)) {
            throw new Exception(json_encode($errors));
        }

        return $data;
    }

    /**
     * Handle file upload
     *
     * @param mixed $file
     * @return string|null
     */
    private function handleFileUpload($file): ?string
    {
        if (!$file) {
            return null;
        }

        try {
            $path = Storage::disk('public')->put('profile-images', $file);
            return $path ? Storage::url($path) : null;
        } catch (Exception $e) {
            throw new Exception('Failed to upload profile image: ' . $e->getMessage());
        }
    }

    /**
     * Get all profiles paginated
     *
     * @param int $perPage
     * @return mixed
     */
    public function getAll(int $perPage = 15)
    {
        return $this->model->paginate($perPage);
    }

    /**
     * Get profile by ID
     *
     * @param int $id
     * @return Profile|null
     */
    public function getById(int $id): ?Profile
    {
        return $this->model->find($id);
    }

    /**
     * Get profile by Customer ID
     *
     * @param int $customerId
     * @return Profile|null
     */
    public function getByCustomerId(int $customerId): ?Profile
    {
        return $this->model->where('customer_id', $customerId)->first();
    }

    /**
     * Create a new profile
     *
     * @param array $data
     * @param mixed $profileImage
     * @return Profile
     * @throws Exception
     */
    public function create(array $data, $profileImage = null): Profile
    {
        $validated = $this->validateProfileData($data);
        
        if ($profileImage) {
            $validated['profile_image'] = $this->handleFileUpload($profileImage);
        }
        
        return $this->model->create($validated);
    }

    /**
     * Update a profile
     *
     * @param int $id
     * @param array $data
     * @param mixed $profileImage
     * @return Profile|null
     * @throws Exception
     */
    public function update(int $id, array $data, $profileImage = null): ?Profile
    {
        $profile = $this->getById($id);
        if (!$profile) {
            return null;
        }

        $validated = $this->validateProfileData($data);
        
        if ($profileImage) {
            $validated['profile_image'] = $this->handleFileUpload($profileImage);
        }
        
        $profile->update($validated);
        return $profile->refresh();
    }

    /**
     * Update profile by Customer ID
     *
     * @param int $customerId
     * @param array $data
     * @param mixed $profileImage
     * @return Profile|null
     * @throws Exception
     */
    public function updateByCustomerId(int $customerId, array $data, $profileImage = null): ?Profile
    {
        $profile = $this->getByCustomerId($customerId);
        if (!$profile) {
            return null;
        }

        $validated = $this->validateProfileData($data);
        
        if ($profileImage) {
            $validated['profile_image'] = $this->handleFileUpload($profileImage);
        }
        
        $profile->update($validated);
        return $profile->refresh();
    }

    /**
     * Delete a profile
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $profile = $this->getById($id);
        if (!$profile) {
            return false;
        }
        return $profile->delete();
    }

    /**
     * Delete profile by Customer ID
     *
     * @param int $customerId
     * @return bool
     */
    public function deleteByCustomerId(int $customerId): bool
    {
        $profile = $this->getByCustomerId($customerId);
        if (!$profile) {
            return false;
        }
        return $profile->delete();
    }
}
