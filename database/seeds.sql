INSERT INTO roles (id, name, label) VALUES
(1, 'admin', 'مدیر'),
(2, 'user', 'عضو')
ON DUPLICATE KEY UPDATE name=VALUES(name), label=VALUES(label);

INSERT INTO permissions (id, name, label) VALUES
(1, 'admin.access', 'دسترسی به داشبورد مدیریت'),
(2, 'forms.manage', 'مدیریت فرم‌ساز پویا'),
(3, 'users.view', 'مشاهده اعضا'),
(4, 'onboarding.complete', 'تکمیل شناخت‌نامه')
ON DUPLICATE KEY UPDATE name=VALUES(name), label=VALUES(label);

INSERT INTO role_permissions (role_id, permission_id) VALUES
(1,1),(1,2),(1,3),(1,4),(2,4)
ON DUPLICATE KEY UPDATE role_id=VALUES(role_id);

INSERT INTO users (id, role_id, email, password_hash, first_name, last_name, is_active) VALUES
(1, 1, 'admin@example.com', '$2y$12$Dr7Y4hNZVsXCmTruZUYYS.JzUbGNFP19QRkVJ04wy2G3Ek9BaxD/2', 'مدیر', 'کاربر', 1)
ON DUPLICATE KEY UPDATE role_id=VALUES(role_id), email=VALUES(email), first_name=VALUES(first_name), last_name=VALUES(last_name), is_active=VALUES(is_active);

INSERT INTO goals (id, title, description, is_active, sort_order) VALUES
(1, 'رابطه بلندمدت', 'به دنبال رابطه‌ای جدی و متعهدانه هستم.', 1, 10),
(2, 'با نگاه به ازدواج', 'آشنایی را با نیت ازدواج دنبال می‌کنم.', 1, 20),
(3, 'اول دوستی و شناخت', 'ترجیح می‌دهم پیش از رابطه عاطفی، دوستی و شناخت شکل بگیرد.', 1, 30),
(4, 'همراه برای فعالیت مشترک', 'مایلم برای علاقه‌ها و رویدادهای مشترک با افراد مناسب آشنا شوم.', 1, 40)
ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), is_active=VALUES(is_active), sort_order=VALUES(sort_order), deleted_at=NULL;

INSERT INTO provinces (id, name, country_code, is_active, sort_order) VALUES
(1, 'تهران', 'IR', 1, 10),
(2, 'اصفهان', 'IR', 1, 20),
(3, 'فارس', 'IR', 1, 30)
ON DUPLICATE KEY UPDATE name=VALUES(name), country_code=VALUES(country_code), is_active=VALUES(is_active), sort_order=VALUES(sort_order), deleted_at=NULL;

INSERT INTO cities (id, province_id, name, is_active, sort_order) VALUES
(1, 1, 'تهران', 1, 10),
(2, 1, 'کرج', 1, 20),
(3, 1, 'قم', 1, 30),
(4, 2, 'اصفهان', 1, 10),
(5, 2, 'کاشان', 1, 20),
(6, 3, 'شیراز', 1, 10),
(7, 3, 'مرودشت', 1, 20),
(8, 3, 'جهرم', 1, 30)
ON DUPLICATE KEY UPDATE province_id=VALUES(province_id), name=VALUES(name), is_active=VALUES(is_active), sort_order=VALUES(sort_order), deleted_at=NULL;

INSERT INTO form_steps (id, title, description, sort_order, is_active) VALUES
(1, 'اطلاعات پایه', 'اطلاعات ضروری را وارد کنید تا معرفی‌ها زمینه شما را بهتر درک کنند.', 10, 1),
(2, 'سازگاری', 'ارزش‌ها و ترجیحاتی را بنویسید که برای معرفی خصوصی استفاده می‌شوند.', 20, 1),
(3, 'سبک زندگی', 'با علاقه‌ها و جزئیات روزمره، شناخت‌نامه را کامل‌تر کنید.', 30, 1)
ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), sort_order=VALUES(sort_order), is_active=VALUES(is_active), deleted_at=NULL;

INSERT INTO question_groups (id, form_step_id, title, description, sort_order, is_active) VALUES
(1, 1, 'اطلاعات پایه شناخت', 'اطلاعات پایه‌ای که نمایش محدود و امن دارد.', 10, 1),
(2, 1, 'مکان', 'محل زندگی و شهرهایی که برای آشنایی در نظر دارید.', 20, 1),
(3, 2, 'هدف‌های ارتباطی', 'نیت‌ها و نشانه‌های سازگاری.', 10, 1),
(4, 2, 'ارزش‌ها', 'ارزش‌های مهم در رابطه.', 20, 1),
(5, 3, 'سبک زندگی notes', 'جزئیات شخصی اختیاری.', 10, 1)
ON DUPLICATE KEY UPDATE form_step_id=VALUES(form_step_id), title=VALUES(title), description=VALUES(description), sort_order=VALUES(sort_order), is_active=VALUES(is_active), deleted_at=NULL;

INSERT INTO questions (id, question_group_id, title, description, help_text, placeholder, answer_type, answer_source, applies_to, visibility_scope, is_required, is_active, is_sensitive, privacy_level, show_in_match_card, match_card_priority, is_matchable, match_weight, match_rule, sort_order) VALUES
(1, 1, 'تیتر کوتاه معرفی', 'معرفی کوتاهی که برای معرفی‌های تأییدشده نمایش داده می‌شود.', 'از نوشتن شماره تلفن، نشانی یا شبکه اجتماعی خودداری کنید.', 'مهربان، اهل شناخت و به دنبال رابطه‌ای واقعی', 'text', 'manual', 'all', 'matches', 1, 1, 0, 'low', 1, 10, 0, 1.00, 'text', 10),
(2, 1, 'درباره من', 'چند جمله درباره خودتان بنویسید.', 'متن را راحت و امن از نظر حریم خصوصی نگه دارید.', 'از چیزهایی که دوست دارم...', 'textarea', 'manual', 'all', 'matches', 1, 1, 0, 'medium', 0, 0, 0, 1.00, 'text', 20),
(3, 2, 'شهر محل زندگی', 'شهر فعلی شما.', NULL, NULL, 'city_single', 'cities', 'all', 'private', 1, 1, 0, 'medium', 1, 20, 1, 2.00, 'distance', 10),
(4, 2, 'شهرهای قابل‌بررسی', 'شهرهایی را انتخاب کنید که برای دیدار یا جابه‌جایی احتمالی در نظر می‌گیرید.', NULL, NULL, 'city_multi', 'cities', 'all', 'private', 0, 1, 0, 'medium', 0, 0, 1, 1.50, 'overlap', 20),
(5, 3, 'به دنبال چه نوع آشنایی هستید؟', 'نیت اصلی خود از آشنایی را انتخاب کنید.', NULL, NULL, 'select', 'options', 'all', 'matches', 1, 1, 0, 'medium', 1, 30, 1, 3.00, 'exact', 10),
(6, 3, 'رابطه از راه دور را بررسی می‌کنید؟', NULL, NULL, NULL, 'boolean', 'manual', 'all', 'private', 1, 1, 0, 'medium', 0, 0, 1, 1.00, 'exact', 20),
(7, 4, 'اهمیت هم‌سویی باورها و ارزش‌ها', 'هم‌سویی ارزشی چقدر برای شما مهم است؟', NULL, NULL, 'scale', 'manual', 'all', 'private', 1, 1, 0, 'medium', 0, 0, 1, 2.50, 'near', 10),
(8, 4, 'بازه سنی ترجیحی', 'چه بازه سنی برای شما مناسب‌تر است؟', 'یک بازه کوتاه مثل ۲۸-۳۶ وارد کنید.', '28-36', 'range', 'manual', 'all', 'private', 0, 1, 1, 'high', 0, 0, 1, 1.00, 'range_overlap', 20),
(9, 5, 'فعالیت دلخواه آخر هفته', NULL, NULL, NULL, 'single_choice', 'options', 'all', 'matches', 0, 1, 0, 'low', 1, 40, 1, 1.00, 'exact', 10),
(10, 5, 'علاقه‌مندی‌ها', 'علاقه‌هایی را انتخاب کنید که به شما نزدیک است.', NULL, NULL, 'multi_select', 'options', 'all', 'matches', 0, 1, 0, 'low', 1, 50, 1, 1.00, 'overlap', 20),
(11, 5, 'تاریخ تولد', 'برای بررسی سن و سازگاری استفاده می‌شود و مستقیم نمایش داده نمی‌شود.', NULL, NULL, 'date', 'manual', 'all', 'private', 1, 1, 1, 'high', 0, 0, 0, 1.00, 'none', 30),
(12, 5, 'قد به سانتی‌متر', NULL, NULL, '68', 'number', 'manual', 'all', 'private', 0, 1, 0, 'medium', 0, 0, 0, 1.00, 'near', 40),
(13, 5, 'سبک گفت‌وگو', NULL, NULL, NULL, 'multi_choice', 'options', 'all', 'matches', 0, 1, 0, 'low', 0, 0, 1, 1.00, 'overlap', 50),
(14, 5, 'نوع مکان دلخواه', NULL, NULL, NULL, 'single_choice', 'options', 'all', 'matches', 0, 1, 0, 'low', 0, 0, 1, 1.00, 'exact', 60)
ON DUPLICATE KEY UPDATE question_group_id=VALUES(question_group_id), title=VALUES(title), description=VALUES(description), help_text=VALUES(help_text), placeholder=VALUES(placeholder), answer_type=VALUES(answer_type), answer_source=VALUES(answer_source), applies_to=VALUES(applies_to), visibility_scope=VALUES(visibility_scope), is_required=VALUES(is_required), is_active=VALUES(is_active), is_sensitive=VALUES(is_sensitive), privacy_level=VALUES(privacy_level), show_in_match_card=VALUES(show_in_match_card), match_card_priority=VALUES(match_card_priority), is_matchable=VALUES(is_matchable), match_weight=VALUES(match_weight), match_rule=VALUES(match_rule), sort_order=VALUES(sort_order), deleted_at=NULL;

INSERT INTO question_options (id, question_id, label, value, sort_order, is_active) VALUES
(1, 5, 'رابطه بلندمدت', 'long_term', 10, 1),
(2, 5, 'با نگاه به ازدواج', 'marriage', 20, 1),
(3, 5, 'اول دوستی و شناخت', 'friendship', 30, 1),
(4, 9, 'قهوه و گفت‌وگو', 'coffee', 10, 1),
(5, 9, 'پیاده‌روی یا طبیعت', 'outdoors', 20, 1),
(6, 9, 'موزه یا برنامه فرهنگی', 'culture', 30, 1),
(7, 9, 'آشپزی در خانه', 'cooking', 40, 1),
(8, 10, 'کتاب', 'books', 10, 1),
(9, 10, 'ورزش و تندرستی', 'fitness', 20, 1),
(10, 10, 'سفر', 'travel', 30, 1),
(11, 10, 'کار داوطلبانه', 'volunteering', 40, 1),
(12, 10, 'موسیقی', 'music', 50, 1),
(13, 13, 'گفت‌وگوی عمیق', 'deep_talks', 10, 1),
(14, 13, 'شوخی محترمانه', 'banter', 20, 1),
(15, 13, 'آرام و روشن', 'direct', 30, 1),
(16, 14, 'کافه آرام', 'cafe', 10, 1),
(17, 14, 'پارک', 'park', 20, 1),
(18, 14, 'رویداد جمعی', 'event', 30, 1)
ON DUPLICATE KEY UPDATE question_id=VALUES(question_id), label=VALUES(label), value=VALUES(value), sort_order=VALUES(sort_order), is_active=VALUES(is_active), deleted_at=NULL;


INSERT INTO admin_settings (setting_key, setting_value, updated_by) VALUES
('site_name', 'دوستیابی', 1),
('site_status', 'active', 1),
('registration_enabled', '1', 1),
('default_onboarding_redirect', '/onboarding', 1)
ON DUPLICATE KEY UPDATE setting_value=setting_value;


INSERT INTO reveal_types (id, title, slug, description, reveal_field_key, privacy_level, requires_mutual_approval, is_active, sort_order) VALUES
(1, 'نام نمایشی', 'display-name', 'نام نمایشی شما پس از رضایت دوطرفه نمایش داده می‌شود.', 'display_name', 'low', 1, 1, 10),
(2, 'شهر محل زندگی', 'home-city', 'فقط شهر شما نمایش داده می‌شود؛ نشانی دقیق و اطلاعات تماس هرگز شامل نمی‌شود.', 'city', 'medium', 1, 1, 20),
(3, 'خلاصه هدف‌های انتخاب‌شده', 'selected-goals-summary', 'خلاصه‌ای از هدف‌های ارتباطی انتخاب‌شده شما نمایش داده می‌شود.', 'selected_goals_summary', 'medium', 1, 1, 30)
ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), reveal_field_key=VALUES(reveal_field_key), privacy_level=VALUES(privacy_level), requires_mutual_approval=VALUES(requires_mutual_approval), is_active=VALUES(is_active), sort_order=VALUES(sort_order);
