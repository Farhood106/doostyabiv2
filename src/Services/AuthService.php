<?php
namespace App\Services;

use App\Repositories\UserRepository;

class AuthService
{
    public function login(string $email, string $password): bool
    {
        $user = (new UserRepository())->findByEmail(strtolower(trim($email)));
        if (!$user || !$user['is_active'] || !password_verify($password, $user['password_hash'])) {
            return false;
        }
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        return true;
    }

    public function register(array $data): array
    {
        $errors = [];
        if (!filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) { $errors['email'] = 'لطفاً یک ایمیل معتبر وارد کنید.'; }
        if (strlen($data['password'] ?? '') < 8) { $errors['password'] = 'رمز عبور باید حداقل ۸ کاراکتر باشد.'; }
        if (($data['password'] ?? '') !== ($data['password_confirm'] ?? '')) { $errors['password_confirm'] = 'رمز عبور و تکرار آن باید یکسان باشند.'; }
        if (trim($data['first_name'] ?? '') === '') { $errors['first_name'] = 'وارد کردن نام ضروری است.'; }
        if (!empty($errors)) { return [false, $errors]; }
        try {
            $id = (new UserRepository())->createMember([
                'email' => strtolower(trim($data['email'])),
                'password' => $data['password'],
                'first_name' => trim($data['first_name']),
                'last_name' => trim($data['last_name'] ?? ''),
                'gender' => trim($data['gender'] ?? ''),
                'birthdate' => trim($data['birthdate'] ?? ''),
            ]);
            $_SESSION['user_id'] = $id;
        } catch (\Throwable $e) {
            return [false, ['email' => 'That email may already be registered.']];
        }
        return [true, []];
    }
}
