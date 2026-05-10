CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    label VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    label VARCHAR(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NULL,
    gender VARCHAR(30) NULL,
    birthdate DATE NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    email_verified_at DATETIME NULL,
    profile_quality_score DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    profile_quality_level VARCHAR(40) NOT NULL DEFAULT 'not_ready',
    last_quality_calculated_at DATETIME NULL,
    trust_score DECIMAL(5,2) NOT NULL DEFAULT 50.00,
    trust_level VARCHAR(40) NOT NULL DEFAULT 'new',
    trust_flags_json LONGTEXT NULL,
    quality_flags_json LONGTEXT NULL,
    moderation_signals_json LONGTEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_role_created (role_id, created_at),
    INDEX idx_users_quality_trust (profile_quality_score, trust_score),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    deleted_at DATETIME NULL,
    INDEX idx_goals_active_sort (is_active, deleted_at, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_goals (
    user_id INT NOT NULL,
    goal_id INT NOT NULL,
    PRIMARY KEY (user_id, goal_id),
    INDEX idx_user_goals_goal (goal_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (goal_id) REFERENCES goals(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS provinces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    country_code CHAR(2) NOT NULL DEFAULT 'US',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    deleted_at DATETIME NULL,
    INDEX idx_provinces_active_sort (is_active, deleted_at, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    province_id INT NOT NULL,
    name VARCHAR(120) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    deleted_at DATETIME NULL,
    INDEX idx_cities_province_active (province_id, is_active, deleted_at, sort_order),
    FOREIGN KEY (province_id) REFERENCES provinces(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS form_steps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    deleted_at DATETIME NULL,
    INDEX idx_form_steps_active_sort (is_active, deleted_at, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS question_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    form_step_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    deleted_at DATETIME NULL,
    INDEX idx_question_groups_step_active (form_step_id, is_active, deleted_at, sort_order),
    FOREIGN KEY (form_step_id) REFERENCES form_steps(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_group_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    help_text TEXT NULL,
    placeholder VARCHAR(255) NULL,
    answer_type ENUM('text','textarea','number','boolean','single_choice','multi_choice','select','multi_select','scale','range','city_single','city_multi','date') NOT NULL,
    answer_source ENUM('manual','options','cities') NOT NULL DEFAULT 'manual',
    applies_to VARCHAR(100) NOT NULL DEFAULT 'all',
    visibility_scope ENUM('private','matches','admins','public') NOT NULL DEFAULT 'private',
    is_required TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    is_sensitive TINYINT(1) NOT NULL DEFAULT 0,
    privacy_level ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
    show_in_match_card TINYINT(1) NOT NULL DEFAULT 0,
    match_card_priority INT NOT NULL DEFAULT 0,
    is_matchable TINYINT(1) NOT NULL DEFAULT 0,
    match_weight DECIMAL(5,2) NOT NULL DEFAULT 1.00,
    match_rule VARCHAR(100) NOT NULL DEFAULT 'exact',
    sort_order INT NOT NULL DEFAULT 0,
    deleted_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_questions_group_active (question_group_id, is_active, deleted_at, sort_order),
    INDEX idx_questions_match_card (show_in_match_card, match_card_priority),
    FOREIGN KEY (question_group_id) REFERENCES question_groups(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS question_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    label VARCHAR(180) NOT NULL,
    value VARCHAR(180) NOT NULL,
    description TEXT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    deleted_at DATETIME NULL,
    INDEX idx_question_options_question_active (question_id, is_active, deleted_at, sort_order),
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    question_id INT NOT NULL,
    answer_text TEXT NULL,
    answer_number DECIMAL(12,2) NULL,
    answer_boolean TINYINT(1) NULL,
    answer_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_question (user_id, question_id),
    INDEX idx_user_answers_question (question_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_answer_options (
    user_answer_id INT NOT NULL,
    question_option_id INT NOT NULL,
    PRIMARY KEY (user_answer_id, question_option_id),
    INDEX idx_user_answer_options_option (question_option_id),
    FOREIGN KEY (user_answer_id) REFERENCES user_answers(id) ON DELETE CASCADE,
    FOREIGN KEY (question_option_id) REFERENCES question_options(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_answer_cities (
    user_answer_id INT NOT NULL,
    city_id INT NOT NULL,
    PRIMARY KEY (user_answer_id, city_id),
    INDEX idx_user_answer_cities_city (city_id),
    FOREIGN KEY (user_answer_id) REFERENCES user_answers(id) ON DELETE CASCADE,
    FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_onboarding_progress (
    user_id INT PRIMARY KEY,
    current_step_id INT NULL,
    completed_steps INT NOT NULL DEFAULT 0,
    is_complete TINYINT(1) NOT NULL DEFAULT 0,
    skipped_optional_count INT NOT NULL DEFAULT 0,
    fatigue_score DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    started_at DATETIME NULL,
    completed_at DATETIME NULL,
    engagement_metadata_json LONGTEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_onboarding_complete (is_complete),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (current_step_id) REFERENCES form_steps(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    admin_user_id INT NULL,
    action VARCHAR(120) NOT NULL,
    entity_type VARCHAR(80) NOT NULL,
    entity_id INT NULL,
    metadata LONGTEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_entity (entity_type, entity_id),
    INDEX idx_audit_admin_created (admin_user_id, created_at),
    FOREIGN KEY (admin_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS admin_settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT NULL,
    updated_by INT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS blocks (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    blocker_user_id INT NOT NULL,
    blocked_user_id INT NOT NULL,
    reason_text TEXT NULL,
    source VARCHAR(50) NOT NULL DEFAULT 'member',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    UNIQUE KEY uq_blocks_pair (blocker_user_id, blocked_user_id),
    INDEX idx_blocks_blocker (blocker_user_id, deleted_at),
    INDEX idx_blocks_blocked (blocked_user_id, deleted_at),
    FOREIGN KEY (blocker_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (blocked_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS match_recommendation_queue (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    status ENUM('pending','processing','done','failed') NOT NULL DEFAULT 'pending',
    attempts INT NOT NULL DEFAULT 0,
    last_error TEXT NULL,
    run_after DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_match_queue_status (status, run_after, id),
    UNIQUE KEY uq_match_queue_user_status (user_id, status),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS matches (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_one_id INT NOT NULL,
    user_two_id INT NOT NULL,
    match_status ENUM('suggested','mutual','passed','blocked','archived') NOT NULL DEFAULT 'suggested',
    compatibility_score DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    confidence_score DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    generated_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_matches_pair (user_one_id, user_two_id),
    INDEX idx_matches_user_one (user_one_id, match_status),
    INDEX idx_matches_user_two (user_two_id, match_status),
    FOREIGN KEY (user_one_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user_two_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS chats (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    match_id BIGINT NOT NULL,
    status ENUM('open','closed') NOT NULL DEFAULT 'open',
    closed_reason TEXT NULL,
    closed_by_admin_user_id INT NULL,
    closed_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_chats_match (match_id),
    INDEX idx_chats_status_updated (status, updated_at),
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
    FOREIGN KEY (closed_by_admin_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS chat_participants (
    chat_id BIGINT NOT NULL,
    user_id INT NOT NULL,
    last_read_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (chat_id, user_id),
    INDEX idx_chat_participants_user (user_id, chat_id),
    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS messages (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    chat_id BIGINT NOT NULL,
    sender_user_id INT NOT NULL,
    body TEXT NOT NULL,
    moderation_status ENUM('visible','flagged','hidden') NOT NULL DEFAULT 'visible',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    INDEX idx_messages_chat_created (chat_id, created_at, id),
    INDEX idx_messages_sender (sender_user_id, created_at),
    INDEX idx_messages_moderation (moderation_status, created_at),
    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS message_flags (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    message_id BIGINT NOT NULL,
    reporter_user_id INT NOT NULL,
    reason_text TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_message_flags_reporter (message_id, reporter_user_id),
    INDEX idx_message_flags_reporter (reporter_user_id, created_at),
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    FOREIGN KEY (reporter_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE IF NOT EXISTS reports (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    reporter_user_id INT NOT NULL,
    reported_user_id INT NULL,
    match_id BIGINT NULL,
    chat_id BIGINT NULL,
    message_id BIGINT NULL,
    report_type VARCHAR(50) NOT NULL,
    report_reason VARCHAR(100) NOT NULL,
    description TEXT NULL,
    status ENUM('open','reviewing','resolved','dismissed') NOT NULL DEFAULT 'open',
    priority ENUM('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
    assigned_admin_id INT NULL,
    admin_resolution_note TEXT NULL,
    resolved_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_reports_status_priority (status, priority, created_at),
    INDEX idx_reports_type (report_type, created_at),
    INDEX idx_reports_reporter (reporter_user_id, created_at),
    INDEX idx_reports_reported (reported_user_id, status),
    FOREIGN KEY (reporter_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reported_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE SET NULL,
    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE SET NULL,
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_admin_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reveal_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    reveal_field_key VARCHAR(100) NOT NULL,
    privacy_level ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
    requires_mutual_approval TINYINT(1) NOT NULL DEFAULT 1,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_reveal_types_active_sort (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reveal_requests (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    match_id BIGINT NOT NULL,
    chat_id BIGINT NOT NULL,
    requester_user_id INT NOT NULL,
    target_user_id INT NOT NULL,
    reveal_type_id INT NOT NULL,
    status ENUM('pending','approved','rejected','cancelled','expired') NOT NULL DEFAULT 'pending',
    request_message TEXT NULL,
    response_note TEXT NULL,
    requested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    responded_at DATETIME NULL,
    expires_at DATETIME NULL,
    INDEX idx_reveal_requests_chat_status (chat_id, status, requested_at),
    INDEX idx_reveal_requests_target_status (target_user_id, status, requested_at),
    INDEX idx_reveal_requests_requester (requester_user_id, requested_at),
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE,
    FOREIGN KEY (requester_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reveal_type_id) REFERENCES reveal_types(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS match_visibility_snapshots (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    match_id BIGINT NOT NULL,
    chat_id BIGINT NOT NULL,
    reveal_request_id BIGINT NOT NULL,
    viewer_user_id INT NOT NULL,
    subject_user_id INT NOT NULL,
    reveal_type_id INT NOT NULL,
    reveal_field_key VARCHAR(100) NOT NULL,
    revealed_value TEXT NULL,
    status ENUM('approved','revoked','expired') NOT NULL DEFAULT 'approved',
    visible_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NULL,
    UNIQUE KEY uq_visibility_request_viewer (reveal_request_id, viewer_user_id),
    INDEX idx_visibility_viewer_chat (viewer_user_id, chat_id, status),
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE,
    FOREIGN KEY (reveal_request_id) REFERENCES reveal_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (viewer_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reveal_type_id) REFERENCES reveal_types(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS match_scores (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    match_id BIGINT NOT NULL,
    score_type ENUM('goal_fit','location_fit','answer_fit','boundary_fit','confidence','penalty') NOT NULL,
    score_value DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    weight DECIMAL(6,2) NOT NULL DEFAULT 1.00,
    details TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_match_scores_match (match_id, score_type),
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS match_explanations (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    match_id BIGINT NOT NULL,
    why_matched TEXT NULL,
    strongest_points TEXT NULL,
    caution_points TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_match_explanations_match (match_id),
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS match_cards (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    match_id BIGINT NOT NULL,
    viewer_user_id INT NOT NULL,
    target_user_id INT NOT NULL,
    title VARCHAR(180) NOT NULL,
    summary TEXT NULL,
    strengths_text TEXT NULL,
    cautions_text TEXT NULL,
    compatibility_label VARCHAR(80) NOT NULL,
    privacy_level VARCHAR(40) NOT NULL DEFAULT 'anonymous',
    last_shown_at DATETIME NULL,
    shown_count INT NOT NULL DEFAULT 0,
    hidden_until DATETIME NULL,
    freshness_score DECIMAL(5,2) NOT NULL DEFAULT 50.00,
    generated_payload_json LONGTEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_match_cards_viewer (match_id, viewer_user_id),
    INDEX idx_match_cards_viewer (viewer_user_id, privacy_level),
    INDEX idx_match_cards_freshness (viewer_user_id, hidden_until, freshness_score, last_shown_at),
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
    FOREIGN KEY (viewer_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS match_actions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    match_id BIGINT NOT NULL,
    actor_user_id INT NOT NULL,
    target_user_id INT NOT NULL,
    action ENUM('interested','pass','block') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_match_actions_actor (match_id, actor_user_id),
    INDEX idx_match_actions_target (target_user_id, action),
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
    FOREIGN KEY (actor_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
