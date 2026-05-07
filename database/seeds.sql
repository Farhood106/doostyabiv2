INSERT INTO roles (id, name, label) VALUES
(1, 'admin', 'Administrator'),
(2, 'user', 'Member')
ON DUPLICATE KEY UPDATE name=VALUES(name), label=VALUES(label);

INSERT INTO permissions (id, name, label) VALUES
(1, 'admin.access', 'Access admin dashboard'),
(2, 'forms.manage', 'Manage dynamic form builder'),
(3, 'users.view', 'View users'),
(4, 'onboarding.complete', 'Complete onboarding')
ON DUPLICATE KEY UPDATE name=VALUES(name), label=VALUES(label);

INSERT INTO role_permissions (role_id, permission_id) VALUES
(1,1),(1,2),(1,3),(1,4),(2,4)
ON DUPLICATE KEY UPDATE role_id=VALUES(role_id);

INSERT INTO users (id, role_id, email, password_hash, first_name, last_name, is_active) VALUES
(1, 1, 'admin@example.com', '$2y$12$Dr7Y4hNZVsXCmTruZUYYS.JzUbGNFP19QRkVJ04wy2G3Ek9BaxD/2', 'Admin', 'User', 1)
ON DUPLICATE KEY UPDATE role_id=VALUES(role_id), email=VALUES(email), first_name=VALUES(first_name), last_name=VALUES(last_name), is_active=VALUES(is_active);

INSERT INTO goals (id, title, description, is_active, sort_order) VALUES
(1, 'Long-term relationship', 'I am looking for a serious committed relationship.', 1, 10),
(2, 'Marriage-minded', 'I am intentionally dating toward marriage.', 1, 20),
(3, 'Friendship first', 'I prefer to build friendship before romance.', 1, 30),
(4, 'Activity partner', 'I want to meet people for shared interests and events.', 1, 40)
ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), is_active=VALUES(is_active), sort_order=VALUES(sort_order), deleted_at=NULL;

INSERT INTO provinces (id, name, country_code, is_active, sort_order) VALUES
(1, 'California', 'US', 1, 10),
(2, 'New York', 'US', 1, 20),
(3, 'Texas', 'US', 1, 30)
ON DUPLICATE KEY UPDATE name=VALUES(name), country_code=VALUES(country_code), is_active=VALUES(is_active), sort_order=VALUES(sort_order), deleted_at=NULL;

INSERT INTO cities (id, province_id, name, is_active, sort_order) VALUES
(1, 1, 'Los Angeles', 1, 10),
(2, 1, 'San Francisco', 1, 20),
(3, 1, 'San Diego', 1, 30),
(4, 2, 'New York City', 1, 10),
(5, 2, 'Buffalo', 1, 20),
(6, 3, 'Austin', 1, 10),
(7, 3, 'Dallas', 1, 20),
(8, 3, 'Houston', 1, 30)
ON DUPLICATE KEY UPDATE province_id=VALUES(province_id), name=VALUES(name), is_active=VALUES(is_active), sort_order=VALUES(sort_order), deleted_at=NULL;

INSERT INTO form_steps (id, title, description, sort_order, is_active) VALUES
(1, 'Basics', 'Tell us the essentials so matches can understand your context.', 10, 1),
(2, 'Compatibility', 'Share values and preferences used by privacy-first matching.', 20, 1),
(3, 'Lifestyle', 'Round out your profile with interests and daily-life details.', 30, 1)
ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), sort_order=VALUES(sort_order), is_active=VALUES(is_active), deleted_at=NULL;

INSERT INTO question_groups (id, form_step_id, title, description, sort_order, is_active) VALUES
(1, 1, 'Profile basics', 'Public-safe basics for your profile.', 10, 1),
(2, 1, 'Location', 'Where you live and where you are open to meeting.', 20, 1),
(3, 2, 'Relationship goals', 'Intentions and compatibility signals.', 10, 1),
(4, 2, 'Values', 'Important relationship values.', 20, 1),
(5, 3, 'Lifestyle notes', 'Optional personal details.', 10, 1)
ON DUPLICATE KEY UPDATE form_step_id=VALUES(form_step_id), title=VALUES(title), description=VALUES(description), sort_order=VALUES(sort_order), is_active=VALUES(is_active), deleted_at=NULL;

INSERT INTO questions (id, question_group_id, title, description, help_text, placeholder, answer_type, answer_source, applies_to, visibility_scope, is_required, is_active, is_sensitive, privacy_level, show_in_match_card, match_card_priority, is_matchable, match_weight, match_rule, sort_order) VALUES
(1, 1, 'Profile headline', 'A short introduction shown to approved matches.', 'Avoid sharing phone numbers, addresses, or social handles.', 'Kind, curious, and looking for something real', 'text', 'manual', 'all', 'matches', 1, 1, 0, 'low', 1, 10, 0, 1.00, 'text', 10),
(2, 1, 'About me', 'Write a few sentences about yourself.', 'Keep it comfortable and privacy-safe.', 'I enjoy...', 'textarea', 'manual', 'all', 'matches', 1, 1, 0, 'medium', 0, 0, 0, 1.00, 'text', 20),
(3, 2, 'Home city', 'Your current city.', NULL, NULL, 'city_single', 'cities', 'all', 'private', 1, 1, 0, 'medium', 1, 20, 1, 2.00, 'distance', 10),
(4, 2, 'Cities you would consider', 'Select any cities where you would consider meeting or relocating.', NULL, NULL, 'city_multi', 'cities', 'all', 'private', 0, 1, 0, 'medium', 0, 0, 1, 1.50, 'overlap', 20),
(5, 3, 'What are you looking for?', 'Choose your primary dating intention.', NULL, NULL, 'select', 'options', 'all', 'matches', 1, 1, 0, 'medium', 1, 30, 1, 3.00, 'exact', 10),
(6, 3, 'Open to long distance?', NULL, NULL, NULL, 'boolean', 'manual', 'all', 'private', 1, 1, 0, 'medium', 0, 0, 1, 1.00, 'exact', 20),
(7, 4, 'Faith / values alignment importance', 'How important is shared values alignment?', NULL, NULL, 'scale', 'manual', 'all', 'private', 1, 1, 0, 'medium', 0, 0, 1, 2.50, 'near', 10),
(8, 4, 'Preferred age range', 'What age range feels right for you?', 'Enter a short range such as 28-36.', '28-36', 'range', 'manual', 'all', 'private', 0, 1, 1, 'high', 0, 0, 1, 1.00, 'range_overlap', 20),
(9, 5, 'Favorite weekend activity', NULL, NULL, NULL, 'single_choice', 'options', 'all', 'matches', 0, 1, 0, 'low', 1, 40, 1, 1.00, 'exact', 10),
(10, 5, 'Interests', 'Pick any interests that describe you.', NULL, NULL, 'multi_select', 'options', 'all', 'matches', 0, 1, 0, 'low', 1, 50, 1, 1.00, 'overlap', 20),
(11, 5, 'Birth date', 'Used for age verification and compatibility. Not shown directly.', NULL, NULL, 'date', 'manual', 'all', 'private', 1, 1, 1, 'high', 0, 0, 0, 1.00, 'none', 30),
(12, 5, 'Height in inches', NULL, NULL, '68', 'number', 'manual', 'all', 'private', 0, 1, 0, 'medium', 0, 0, 0, 1.00, 'near', 40),
(13, 5, 'Conversation style', NULL, NULL, NULL, 'multi_choice', 'options', 'all', 'matches', 0, 1, 0, 'low', 0, 0, 1, 1.00, 'overlap', 50),
(14, 5, 'Favorite place type', NULL, NULL, NULL, 'single_choice', 'options', 'all', 'matches', 0, 1, 0, 'low', 0, 0, 1, 1.00, 'exact', 60)
ON DUPLICATE KEY UPDATE question_group_id=VALUES(question_group_id), title=VALUES(title), description=VALUES(description), help_text=VALUES(help_text), placeholder=VALUES(placeholder), answer_type=VALUES(answer_type), answer_source=VALUES(answer_source), applies_to=VALUES(applies_to), visibility_scope=VALUES(visibility_scope), is_required=VALUES(is_required), is_active=VALUES(is_active), is_sensitive=VALUES(is_sensitive), privacy_level=VALUES(privacy_level), show_in_match_card=VALUES(show_in_match_card), match_card_priority=VALUES(match_card_priority), is_matchable=VALUES(is_matchable), match_weight=VALUES(match_weight), match_rule=VALUES(match_rule), sort_order=VALUES(sort_order), deleted_at=NULL;

INSERT INTO question_options (id, question_id, label, value, sort_order, is_active) VALUES
(1, 5, 'Long-term relationship', 'long_term', 10, 1),
(2, 5, 'Marriage-minded', 'marriage', 20, 1),
(3, 5, 'Friendship first', 'friendship', 30, 1),
(4, 9, 'Coffee and conversation', 'coffee', 10, 1),
(5, 9, 'Hiking or outdoors', 'outdoors', 20, 1),
(6, 9, 'Museum or culture', 'culture', 30, 1),
(7, 9, 'Cooking at home', 'cooking', 40, 1),
(8, 10, 'Books', 'books', 10, 1),
(9, 10, 'Fitness', 'fitness', 20, 1),
(10, 10, 'Travel', 'travel', 30, 1),
(11, 10, 'Volunteering', 'volunteering', 40, 1),
(12, 10, 'Music', 'music', 50, 1),
(13, 13, 'Deep talks', 'deep_talks', 10, 1),
(14, 13, 'Playful banter', 'banter', 20, 1),
(15, 13, 'Calm and direct', 'direct', 30, 1),
(16, 14, 'Quiet cafe', 'cafe', 10, 1),
(17, 14, 'Park', 'park', 20, 1),
(18, 14, 'Community event', 'event', 30, 1)
ON DUPLICATE KEY UPDATE question_id=VALUES(question_id), label=VALUES(label), value=VALUES(value), sort_order=VALUES(sort_order), is_active=VALUES(is_active), deleted_at=NULL;


INSERT INTO admin_settings (setting_key, setting_value, updated_by) VALUES
('site_name', 'Doostyabi', 1),
('site_status', 'active', 1),
('registration_enabled', '1', 1),
('default_onboarding_redirect', '/onboarding', 1)
ON DUPLICATE KEY UPDATE setting_value=setting_value;
