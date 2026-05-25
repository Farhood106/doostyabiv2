<?php
namespace App\Services;

class AdminCatalogService
{
    public function validateTitle(array $input, string $field = 'title'): array
    {
        $errors = [];
        if (trim($input[$field] ?? '') === '') { $errors[$field] = 'تکمیل این فیلد ضروری است.'; }
        return $errors;
    }
    public function validateCity(array $input): array
    {
        $errors = $this->validateTitle($input, 'name');
        if (empty($input['province_id'])) { $errors['province_id'] = 'انتخاب استان ضروری است.'; }
        return $errors;
    }
}
