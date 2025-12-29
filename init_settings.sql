-- AxioFrp 管理后台配置系统升级脚本
-- 执行此脚本来添加管理后台配置相关功能

USE axiofrp;

-- 系统配置表升级
ALTER TABLE settings ADD COLUMN IF NOT EXISTS config_type ENUM('system', 'email', 'security', 'limits', 'ui') DEFAULT 'system';
ALTER TABLE settings ADD COLUMN IF NOT EXISTS is_editable BOOLEAN DEFAULT TRUE;
ALTER TABLE settings ADD COLUMN IF NOT EXISTS validation_rule TEXT;
ALTER TABLE settings ADD COLUMN IF NOT EXISTS display_order INT DEFAULT 0;
ALTER TABLE settings ADD COLUMN IF NOT EXISTS category VARCHAR(50) DEFAULT 'general';

-- 配置变更历史表
CREATE TABLE IF NOT EXISTS config_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL,
    old_value TEXT,
    new_value TEXT,
    changed_by VARCHAR(50) NOT NULL,
    change_reason VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key),
    INDEX idx_changed_by (changed_by),
    FOREIGN KEY (changed_by) REFERENCES users(username) ON DELETE CASCADE
);

-- 系统通知表
CREATE TABLE IF NOT EXISTS system_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'warning', 'error', 'success') DEFAULT 'info',
    target_user VARCHAR(50) NULL, -- NULL表示所有用户
    is_read BOOLEAN DEFAULT FALSE,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_target_user (target_user),
    INDEX idx_is_read (is_read),
    FOREIGN KEY (target_user) REFERENCES users(username) ON DELETE CASCADE
);

-- 插入/更新系统配置项
INSERT INTO settings (setting_key, setting_value, description, config_type, category, is_editable, validation_rule, display_order) VALUES
-- 系统基础配置
('site_name', 'AxioFrp', '网站名称', 'system', 'general', TRUE, 'string:2:50', 1),
('site_description', '现代化内网穿透管理面板', '网站描述', 'system', 'general', TRUE, 'string:10:200', 2),
('site_logo', '/logo.png', '网站Logo地址', 'system', 'general', TRUE, 'url', 3),
('site_favicon', '/favicon.ico', '网站图标地址', 'system', 'general', TRUE, 'url', 4),
('admin_email', 'admin@example.com', '管理员邮箱', 'system', 'general', TRUE, 'email', 5),

-- 邮件配置
('smtp_enabled', 'false', '是否启用SMTP服务', 'email', 'smtp', TRUE, 'boolean', 10),
('smtp_host', 'smtp.gmail.com', 'SMTP服务器地址', 'email', 'smtp', TRUE, 'string:5:100', 11),
('smtp_port', '587', 'SMTP端口', 'email', 'smtp', TRUE, 'number:1:65535', 12),
('smtp_secure', 'true', '是否使用SSL/TLS', 'email', 'smtp', TRUE, 'boolean', 13),
('smtp_user', '', 'SMTP用户名', 'email', 'smtp', TRUE, 'email', 14),
('smtp_password', '', 'SMTP密码', 'email', 'smtp', TRUE, 'string:0:100', 15),
('smtp_from_name', 'AxioFrp', '发件人名称', 'email', 'smtp', TRUE, 'string:2:50', 16),

-- 邮件功能配置
('email_verification_required', 'false', '注册是否需要邮箱验证', 'email', 'registration', TRUE, 'boolean', 20),
('password_reset_enabled', 'true', '是否启用密码重置', 'email', 'security', TRUE, 'boolean', 21),
('welcome_email_enabled', 'true', '是否发送欢迎邮件', 'email', 'notification', TRUE, 'boolean', 22),

-- 安全配置
('registration_enabled', 'true', '是否允许用户注册', 'security', 'access', TRUE, 'boolean', 30),
('max_login_attempts', '5', '最大登录尝试次数', 'security', 'access', TRUE, 'number:1:10', 31),
('login_lockout_time', '15', '登录锁定时间(分钟)', 'security', 'access', TRUE, 'number:5:1440', 32),
('session_timeout', '24', '会话超时时间(小时)', 'security', 'session', TRUE, 'number:1:168', 33),
('password_min_length', '8', '密码最小长度', 'security', 'password', TRUE, 'number:6:32', 34),
('password_require_special', 'true', '密码是否需要特殊字符', 'security', 'password', TRUE, 'boolean', 35),

-- 用户限制配置
('max_proxies_per_user', '10', '每个用户最大隧道数', 'limits', 'proxies', TRUE, 'number:1:1000', 40),
('max_domains_per_user', '5', '每个用户最大域名数', 'limits', 'proxies', TRUE, 'number:1:100', 41),
('default_traffic_limit', '1073741824', '默认流量限制(字节)', 'limits', 'traffic', TRUE, 'number:0', 42),
('traffic_reset_cycle', 'monthly', '流量重置周期', 'limits', 'traffic', TRUE, 'enum:daily,weekly,monthly,yearly', 43),
('max_nodes_per_user', '3', '每个用户最大可用节点数', 'limits', 'nodes', TRUE, 'number:1:50', 44),

-- UI配置
('theme_primary_color', '#6366f1', '主题主色调', 'ui', 'appearance', TRUE, 'color', 50),
('theme_secondary_color', '#8b5cf6', '主题辅助色', 'ui', 'appearance', TRUE, 'color', 51),
('theme_mode', 'dark', '界面主题模式', 'ui', 'appearance', TRUE, 'enum:light,dark,auto', 52),
('sidebar_collapsed', 'false', '侧边栏默认折叠', 'ui', 'layout', TRUE, 'boolean', 53),
('show_system_status', 'true', '显示系统状态栏', 'ui', 'features', TRUE, 'boolean', 54),
('enable_analytics', 'false', '启用用户行为分析', 'ui', 'features', TRUE, 'boolean', 55),

-- 功能开关
('proxy_auto_start', 'true', '隧道是否自动启动', 'system', 'features', TRUE, 'boolean', 60),
('enable_api_docs', 'true', '启用API文档', 'system', 'features', TRUE, 'boolean', 61),
('enable_backup', 'true', '启用数据备份', 'system', 'features', TRUE, 'boolean', 62),
('backup_retention_days', '30', '备份保留天数', 'system', 'features', TRUE, 'number:1:365', 63),
('maintenance_mode', 'false', '维护模式', 'system', 'status', TRUE, 'boolean', 64),

-- 性能配置
('cache_ttl', '3600', '缓存生存时间(秒)', 'system', 'performance', TRUE, 'number:60:86400', 70),
('max_concurrent_requests', '1000', '最大并发请求数', 'system', 'performance', TRUE, 'number:100:10000', 71),
('request_timeout', '30', '请求超时时间(秒)', 'system', 'performance', TRUE, 'number:5:300', 72),
('rate_limit_enabled', 'true', '启用请求频率限制', 'system', 'performance', TRUE, 'boolean', 73),
('rate_limit_window', '60', '频率限制窗口(秒)', 'system', 'performance', TRUE, 'number:10:3600', 74),
('rate_limit_max', '100', '频率限制最大请求数', 'system', 'performance', TRUE, 'number:10:1000', 75)

ON DUPLICATE KEY UPDATE 
    config_type = VALUES(config_type),
    category = VALUES(category),
    is_editable = VALUES(is_editable),
    validation_rule = VALUES(validation_rule),
    display_order = VALUES(display_order);

-- 插入默认系统通知
INSERT INTO system_notifications (title, message, type, target_user) VALUES
('欢迎使用AxioFrp管理后台', '您已成功登录AxioFrp管理后台，系统配置功能已全面升级！', 'success', NULL),
('系统配置升级', '系统配置功能已升级，现支持在线配置管理和实时更新。', 'info', NULL);

-- 创建索引优化查询性能
CREATE INDEX IF NOT EXISTS idx_settings_category_type ON settings(category, config_type);
CREATE INDEX IF NOT EXISTS idx_settings_display_order ON settings(display_order);