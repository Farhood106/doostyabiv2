<h1>عیب‌یابی ایمنی</h1>
<section class="card">
<p>تعداد معرفی‌های مخفی‌شده: <?= (int)($result['hiddenMatches'] ?? 0) ?></p>
<p>تعداد گفتگوهای بسته‌شده: <?= (int)($result['hiddenChats'] ?? 0) ?></p>
<p>تعداد کارت‌های پنهان‌شده: <?= (int)($result['hiddenCards'] ?? 0) ?></p>
<p>تعداد علاقه‌های دوطرفه نامعتبر اصلاح‌شده: <?= (int)($result['mutualInvalidated'] ?? 0) ?></p>
<h3>دسته‌بندی دلایل منع نمایش</h3>
<ul>
<li>مسدودسازی: <?= (int)(($result['reasonCounts']['blocked'] ?? 0)) ?></li>
<li>سپر حریم خصوصی: <?= (int)(($result['reasonCounts']['privacy_shield'] ?? 0)) ?></li>
<li>کاربر غیرفعال/نامعتبر: <?= (int)(($result['reasonCounts']['inactive_user'] ?? 0)) ?></li>
<li>داده ناسازگار/کهنه: <?= (int)(($result['reasonCounts']['stale_state'] ?? 0)) ?></li>
</ul>
</section>
