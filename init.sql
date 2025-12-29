-- AxioFrp 数据库初始化脚本
-- 基于 YouFrp 的现有表结构，适配 Node.js 版本

-- 创建数据库
CREATE DATABASE IF NOT EXISTS axiofrp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE axiofrp;

-- 用户组表
CREATE TABLE IF NOT EXISTS groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    max_proxies INT DEFAULT 10,
    max_traffic BIGINT DEFAULT 1073741824, -- 1GB in bytes
    proxy_type VARCHAR(10) DEFAULT 'tcp',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 用户表
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    group_name VARCHAR(50) DEFAULT 'default',
    traffic_used BIGINT DEFAULT 0,
    traffic_limit BIGINT DEFAULT 1073741824,
    balance DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (group_name) REFERENCES groups(name) ON DELETE SET NULL
);

-- 节点表
CREATE TABLE IF NOT EXISTS nodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address VARCHAR(100) NOT NULL,
    port INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    status ENUM('online', 'offline', 'maintenance') DEFAULT 'online',
    max_connections INT DEFAULT 1000,
    region VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 隧道表
CREATE TABLE IF NOT EXISTS proxies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    proxy_name VARCHAR(100) NOT NULL,
    proxy_type ENUM('tcp', 'udp', 'http', 'https', 'stcp', 'xtcp') DEFAULT 'tcp',
    local_ip VARCHAR(39) NOT NULL,
    local_port INT NOT NULL,
    remote_port INT,
    custom_domain VARCHAR(255),
    node_id INT NOT NULL,
    status ENUM('active', 'inactive', 'error') DEFAULT 'active',
    traffic_in BIGINT DEFAULT 0,
    traffic_out BIGINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (username) REFERENCES users(username) ON DELETE CASCADE,
    FOREIGN KEY (node_id) REFERENCES nodes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_proxy (username, proxy_name)
);

-- 套餐表
CREATE TABLE IF NOT EXISTS packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    duration_days INT NOT NULL,
    traffic_limit BIGINT DEFAULT 0,
    max_proxies INT DEFAULT 10,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 套餐订单表
CREATE TABLE IF NOT EXISTS package_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    package_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'paid', 'expired', 'cancelled') DEFAULT 'pending',
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (username) REFERENCES users(username) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
);

-- 余额变动记录表
CREATE TABLE IF NOT EXISTS balance_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    type ENUM('recharge', 'consume', 'refund') NOT NULL,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (username) REFERENCES users(username) ON DELETE CASCADE
);

-- 兑换码表
CREATE TABLE IF NOT EXISTS redeem_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    amount DECIMAL(10,2) NOT NULL,
    used_by VARCHAR(50) NULL,
    used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (used_by) REFERENCES users(username) ON DELETE SET NULL
);

-- 系统设置表
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 插入默认数据
INSERT IGNORE INTO groups (name, max_proxies, max_traffic, proxy_type) VALUES
('default', 10, 1073741824, 'tcp'),
('vip', 50, 5368709120, 'all'),
('admin', 100, 107374182400, 'all');

INSERT IGNORE INTO packages (name, description, price, duration_days, traffic_limit, max_proxies) VALUES
('免费套餐', '基础免费套餐', 0.00, 30, 1073741824, 5),
('基础套餐', '适合个人用户', 9.99, 30, 5368709120, 20),
('高级套餐', '适合团队使用', 19.99, 30, 21474836480, 50),
('企业套餐', '适合企业用户', 49.99, 30, 107374182400, 100);

INSERT IGNORE INTO settings (setting_key, setting_value, description) VALUES
('site_name', 'AxioFrp', '网站名称'),
('site_description', '现代化内网穿透管理面板', '网站描述'),
('allow_registration', 'true', '是否允许注册'),
('require_email_verification', 'false', '是否需要邮箱验证'),
('default_traffic_limit', '1073741824', '默认流量限制'),
('max_proxies_per_user', '10', '每个用户最大隧道数');

-- 创建索引以优化查询性能
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_proxies_username ON proxies(username);
CREATE INDEX idx_proxies_node_id ON proxies(node_id);
CREATE INDEX idx_package_orders_username ON package_orders(username);
CREATE INDEX idx_balance_logs_username ON balance_logs(username);
CREATE INDEX idx_redeem_codes_code ON redeem_codes(code);