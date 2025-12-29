# AxioFrp 配置指南

本文档详细介绍如何配置 AxioFrp 项目的各项设置，包括数据库、邮件验证、系统参数等。

## 📋 目录

- [数据库配置](#数据库配置)
- [邮件配置](#邮件配置)
- [邮箱验证注册](#邮箱验证注册)
- [系统配置](#系统配置)
- [环境变量完整列表](#环境变量完整列表)

## 🗄️ 数据库配置

### 1. 基础配置

编辑 `.env` 文件中的数据库配置：

```bash
# 数据库配置
DB_HOST=localhost          # 数据库服务器地址
DB_PORT=3306              # 数据库端口
DB_NAME=axiofrp           # 数据库名称
DB_USER=root              # 数据库用户名
DB_PASSWORD=your_password  # 数据库密码
```

### 2. 数据库初始化

1. 确保已安装 MySQL 8.0+
2. 执行数据库初始化脚本：
```bash
mysql -u root -p < init.sql
```

### 3. Redis 配置

```bash
# Redis配置
REDIS_URL=redis://localhost:6379
```

## 📧 邮件配置

### 1. SMTP 服务器设置

在 `.env` 文件中配置邮件服务：

```bash
# 邮件配置（可选）
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your_email@gmail.com
SMTP_PASS=your_app_password
```

### 2. 常用邮件服务商配置

#### Gmail 配置
```bash
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your_email@gmail.com
SMTP_PASS=your_app_password  # 使用应用专用密码
```

#### Outlook 配置
```bash
SMTP_HOST=smtp-mail.outlook.com
SMTP_PORT=587
SMTP_USER=your_email@outlook.com
SMTP_PASS=your_password
```

#### QQ邮箱配置
```bash
SMTP_HOST=smtp.qq.com
SMTP_PORT=587
SMTP_USER=your_email@qq.com
SMTP_PASS=your_authorization_code  # 授权码
```

#### 163邮箱配置
```bash
SMTP_HOST=smtp.163.com
SMTP_PORT=587
SMTP_USER=your_email@163.com
SMTP_PASS=your_authorization_code  # 授权码
```

### 3. 获取邮箱应用密码

**Gmail 应用密码获取步骤：**
1. 登录 Google 账户
2. 进入"安全性"设置
3. 开启"两步验证"
4. 生成"应用专用密码"
5. 使用生成的16位密码作为 `SMTP_PASS`

**QQ邮箱授权码获取步骤：**
1. 登录 QQ邮箱
2. 进入"设置" → "账户"
3. 开启"POP3/SMTP服务"
4. 获取授权码作为 `SMTP_PASS`

## ✅ 邮箱验证注册

### 1. 开启邮箱验证

在数据库中修改系统设置：

```sql
UPDATE settings SET setting_value = 'true' 
WHERE setting_key = 'require_email_verification';
```

### 2. 或者直接修改 init.sql

在 `init.sql` 文件中找到这行：
```sql
('require_email_verification', 'false', '是否需要邮箱验证'),
```

改为：
```sql
('require_email_verification', 'true', '是否需要邮箱验证'),
```

然后重新执行初始化脚本。

### 3. 相关设置项

在数据库的 `settings` 表中可以控制以下功能：

```sql
-- 是否允许用户注册
INSERT INTO settings VALUES 
('allow_registration', 'true', '是否允许注册');

-- 是否需要邮箱验证
INSERT INTO settings VALUES 
('require_email_verification', 'true', '是否需要邮箱验证');
```

## ⚙️ 系统配置

### 1. 用户限制配置

```bash
# 系统配置
MAX_PROXIES_PER_USER=10                    # 每个用户最大隧道数
DEFAULT_TRAFFIC_LIMIT=1073741824          # 默认流量限制 (1GB)
```

### 2. JWT 安全配置

```bash
# JWT配置
JWT_SECRET=your_jwt_secret_key              # JWT 密钥（建议使用复杂字符串）
JWT_EXPIRES_IN=7d                           # Token 有效期
```

### 3. 前端地址配置

```bash
# 前端配置
FRONTEND_URL=http://localhost:3000           # 前端访问地址
```

### 4. 应用基础配置

```bash
# 应用配置
NODE_ENV=development                         # 运行环境 (development/production)
PORT=8000                                   # 后端服务端口
```

## 📝 环境变量完整列表

以下是 `.env.example` 中的所有环境变量及其说明：

| 变量名 | 必需 | 默认值 | 说明 |
|--------|------|--------|------|
| `DB_HOST` | ✅ | localhost | 数据库服务器地址 |
| `DB_PORT` | ✅ | 3306 | 数据库端口 |
| `DB_NAME` | ✅ | axiofrp | 数据库名称 |
| `DB_USER` | ✅ | root | 数据库用户名 |
| `DB_PASSWORD` | ✅ | - | 数据库密码 |
| `REDIS_URL` | ✅ | redis://localhost:6379 | Redis 连接地址 |
| `JWT_SECRET` | ✅ | axiofrp_jwt_secret_key | JWT 签名密钥 |
| `JWT_EXPIRES_IN` | ❌ | 7d | Token 有效期 |
| `NODE_ENV` | ❌ | development | 运行环境 |
| `PORT` | ❌ | 8000 | 后端服务端口 |
| `SMTP_HOST` | ❌ | - | SMTP 服务器地址 |
| `SMTP_PORT` | ❌ | 587 | SMTP 端口 |
| `SMTP_USER` | ❌ | - | 邮箱用户名 |
| `SMTP_PASS` | ❌ | - | 邮箱密码/授权码 |
| `MAX_PROXIES_PER_USER` | ❌ | 10 | 用户最大隧道数 |
| `DEFAULT_TRAFFIC_LIMIT` | ❌ | 1073741824 | 默认流量限制(字节) |
| `FRONTEND_URL` | ❌ | http://localhost:3000 | 前端地址 |

## 🚀 快速配置步骤

### 1. 复制环境配置
```bash
cp .env.example .env
```

### 2. 编辑配置文件
```bash
nano .env
```

### 3. 修改必要配置
```bash
# 修改数据库密码
DB_PASSWORD=your_secure_password

# 修改JWT密钥
JWT_SECRET=your_very_secure_jwt_secret_key_here

# 配置邮件（可选）
SMTP_HOST=smtp.gmail.com
SMTP_USER=your_email@gmail.com
SMTP_PASS=your_app_password
```

### 4. 初始化数据库
```bash
mysql -u root -p < init.sql
```

### 5. 启动服务
```bash
npm run dev
```

## 🛠️ 生产环境配置

### 1. 环境变量
```bash
NODE_ENV=production
DB_PASSWORD=strong_production_password
JWT_SECRET=very_secure_production_secret
```

### 2. 数据库安全
- 使用强密码
- 限制数据库访问权限
- 定期备份数据库

### 3. 邮件服务
- 使用企业邮箱
- 配置DNS记录（SPF、DKIM）
- 监控邮件发送状态

## 🔧 故障排除

### 1. 数据库连接失败
- 检查 MySQL 服务是否启动
- 验证数据库用户名和密码
- 确认数据库已创建

### 2. 邮件发送失败
- 验证SMTP配置
- 检查邮箱授权码
- 确认网络连接

### 3. 注册验证邮件收不到
- 检查垃圾邮件文件夹
- 确认邮箱验证已开启
- 验证邮件服务配置

## 📞 技术支持

如果在配置过程中遇到问题，可以通过以下方式获取帮助：

- 🐛 **Bug反馈**: [GitHub Issues](https://github.com/frccyan/AxioFrp/issues)
- 💡 **配置问题**: [GitHub Discussions](https://github.com/frccyan/AxioFrp/discussions)
- 📧 **商务合作**: frccyan@outlook.com

---

**⚠️ 安全提醒**: 在生产环境中，请务必使用强密码，并定期更换敏感配置！