<?php
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function fa_label(?string $value): string
{
    $value = (string) $value;
    $labels = [
        'active' => 'فعال', 'inactive' => 'غیرفعال', 'maintenance' => 'در حال نگهداری',
        'open' => 'باز', 'reviewing' => 'در حال بررسی', 'dismissed' => 'بسته‌شده بدون اقدام', 'closed' => 'بسته', 'blocked' => 'مسدود شده',
        'pending' => 'در انتظار پاسخ', 'approved' => 'تأیید شده', 'rejected' => 'رد شده', 'cancelled' => 'لغو شده', 'expired' => 'منقضی شده',
        'mutual' => 'علاقه دوطرفه', 'suggested' => 'پیشنهاد شده', 'interested' => 'مایلم بیشتر بدانم', 'pass' => 'فعلاً نه',
        'incoming' => 'دریافتی', 'outgoing' => 'ارسالی', 'flagged' => 'نیازمند بررسی', 'clean' => 'بدون مورد',
        'low' => 'کم', 'normal' => 'معمولی', 'urgent' => 'فوری', 'medium' => 'حساسیت متوسط', 'high' => 'حساسیت زیاد',
        'private' => 'خصوصی', 'matches' => 'نمایش در معرفی', 'admins' => 'فقط مدیران', 'public' => 'عمومی',
        'manual' => 'دستی', 'options' => 'گزینه‌ها', 'cities' => 'شهرها', 'all' => 'همه',
        'text' => 'متن کوتاه', 'textarea' => 'متن بلند', 'number' => 'عدد', 'date' => 'تاریخ', 'boolean' => 'بله/خیر', 'scale' => 'مقیاس', 'range' => 'بازه', 'select' => 'فهرست', 'single_choice' => 'تک‌گزینه‌ای', 'multi_choice' => 'چندگزینه‌ای', 'multi_select' => 'انتخاب چندتایی', 'city_single' => 'یک شهر', 'city_multi' => 'چند شهر',
        'harassment' => 'آزار یا فشار', 'spam' => 'هرزنامه یا کلاهبرداری', 'spam or scam' => 'هرزنامه یا کلاهبرداری', 'fake_profile' => 'اطلاعات غیرواقعی', 'unsafe_behavior' => 'رفتار ناامن', 'privacy' => 'نگرانی حریم خصوصی', 'inappropriate_content' => 'محتوای نامناسب', 'other' => 'مورد دیگر',
        'match' => 'معرفی', 'user' => 'عضو', 'safety' => 'امنیت', 'chat' => 'گفت‌وگو', 'message' => 'پیام', 'display_name' => 'نام نمایشی', 'city' => 'شهر', 'selected_goals_summary' => 'خلاصه هدف‌ها',
    ];
    return $labels[$value] ?? $labels[strtolower($value)] ?? $value;
}

function fa_count(int $count, string $singular, string $plural = null): string
{
    return $count . ' ' . ($count === 1 ? $singular : ($plural ?? $singular));
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['_csrf'] ?? '';
    if (!hash_equals($_SESSION['_csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('نشست شما منقضی شده یا کد امنیتی معتبر نیست. لطفاً صفحه را تازه‌سازی کنید و دوباره تلاش کنید.');
    }
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}
