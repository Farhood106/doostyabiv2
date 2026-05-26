<h1>عیب‌یابی ایمنی</h1>
<section class="card">
<p>تعداد معرفی‌های مخفی‌شده: <?= (int)($result['hiddenMatches'] ?? 0) ?></p>
<p>تعداد گفتگوهای بسته‌شده: <?= (int)($result['hiddenChats'] ?? 0) ?></p>
<p>تعداد کارت‌های پنهان‌شده: <?= (int)($result['hiddenCards'] ?? 0) ?></p>
<p>تعداد علاقه‌های دوطرفه نامعتبر اصلاح‌شده: <?= (int)($result['mutualInvalidated'] ?? 0) ?></p>
</section>
