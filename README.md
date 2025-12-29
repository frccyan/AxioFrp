# AxioFrp

<div align="center">

![AxioFrp Logo](https://via.placeholder.com/200x80/6366f1/ffffff?text=AxioFrp)

**新一代内网穿透管理分享及商业面板**

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Node.js Version](https://img.shields.io/badge/node-%3E%3D16.0.0-brightgreen.svg)](https://nodejs.org/)
[![React](https://img.shields.io/badge/react-18.2.0-blue.svg)](https://reactjs.org/)
[![TypeScript](https://img.shields.io/badge/typescript-5.1+-blue.svg)](https://www.typescriptlang.org/)

🚀 **现代化** | 🎨 **美观界面** | 🔒 **安全可靠** | 💼 **商业级**

</div>

## ✨ 项目简介

AxioFrp 是一个全新的内网穿透管理分享及商业面板，采用现代化设计理念和技术栈构建。我们摒弃了传统内网穿透工具的复杂界面，提供了直观、美观、易用的用户体验，同时具备强大的商业化功能。

### 🎯 设计理念

- **零数据保留**：全新版本不保留任何历史个人数据
- **现代化UI**：采用深色主题和玻璃拟态设计
- **商业化导向**：内置完整的计费、套餐、用户管理功能
- **高性能**：基于最新技术栈，优化性能表现
- **易部署**：容器化部署，一键启动

## 🛠 技术栈

### 后端技术
- **框架**: Node.js + Express.js + TypeScript
- **数据库**: MySQL 8.0 + Redis
- **认证**: JWT + bcrypt
- **文档**: OpenAPI/Swagger
- **安全**: CORS, SQL注入防护, Rate Limiting

### 前端技术
- **框架**: React 18 + TypeScript
- **构建**: Vite
- **样式**: Tailwind CSS + CSS-in-JS
- **状态管理**: Zustand
- **路由**: React Router v6
- **HTTP客户端**: Axios + React Query
- **UI组件**: Headless UI + Heroicons
- **动画**: Framer Motion

### 开发工具
- **容器化**: Docker + Docker Compose
- **代码质量**: ESLint + Prettier
- **包管理**: npm workspaces
- **版本控制**: Git

## 🏗 项目架构

```
axiofrp/
├── 📁 backend/              # 后端 API 服务
│   ├── 📁 src/
│   │   ├── 📁 controllers/  # 控制器层
│   │   ├── 📁 services/     # 业务逻辑层
│   │   ├── 📁 models/       # 数据模型
│   │   ├── 📁 middleware/   # 中间件
│   │   ├── 📁 routes/       # 路由定义
│   │   ├── 📁 config/       # 配置文件
│   │   └── 📁 utils/        # 工具函数
│   ├── 📄 Dockerfile
│   └── 📄 package.json
├── 📁 frontend/            # 前端应用
│   ├── 📁 src/
│   │   ├── 📁 components/   # 可复用组件
│   │   │   └── 📁 Layout/   # 布局组件
│   │   ├── 📁 pages/        # 页面组件
│   │   │   ├── 📁 Auth/     # 认证页面
│   │   │   ├── 📁 Dashboard/# 仪表板
│   │   │   ├── 📁 Proxies/   # 隧道管理
│   │   │   ├── 📁 Nodes/     # 节点管理
│   │   │   └── 📁 Packages/  # 套餐管理
│   │   ├── 📁 services/     # API 服务
│   │   ├── 📁 stores/       # 状态管理
│   │   ├── 📁 types/        # 类型定义
│   │   └── 📄 styles/       # 全局样式
│   ├── 📄 Dockerfile
│   └── 📄 package.json
├── 🐳 docker-compose.yml     # 容器编排
├── 🗄️ init.sql              # 数据库初始化
├── ⚙️ .env.example          # 环境变量模板
├── 📋 CONFIG_GUIDE.md       # 详细配置指南
└── 📖 README.md
```

## 🚀 快速开始

### 📋 环境要求

- **Node.js**: >= 16.0.0
- **npm**: >= 8.0.0  
- **Docker**: >= 20.0.0
- **Docker Compose**: >= 2.0.0

### 🔧 开发环境

1. **克隆项目**
```bash
git clone https://github.com/frccyan/AxioFrp.git
cd AxioFrp
```

2. **一键安装依赖**
```bash
npm run install:all
```

3. **环境配置**
```bash
cp .env.example .env
# 编辑 .env 文件配置数据库等信息
```

⚠️ **重要配置项**：
- 📊 **数据库配置**: 修改 `DB_PASSWORD` 为你的MySQL密码
- 📧 **邮件配置**: 配置SMTP服务以启用邮箱验证注册
- 🔐 **安全配置**: 修改 `JWT_SECRET` 为强密钥
- 📖 **详细配置指南**: 查看 [CONFIG_GUIDE.md](./CONFIG_GUIDE.md)

4. **启动开发服务**
```bash
# 同时启动前后端
npm run dev

# 或分别启动
npm run dev:backend   # 后端: http://localhost:8000
npm run dev:frontend  # 前端: http://localhost:3000
```

### 🚢 生产部署

1. **Docker 部署（推荐）**
```bash
# 一键部署
docker-compose up -d

# 查看服务状态
docker-compose ps

# 停止服务
docker-compose down
```

2. **访问应用**
- 🖥️ **前端界面**: http://localhost:3000
- 🔌 **后端 API**: http://localhost:8000  
- 🗄️ **MySQL**: localhost:3306
- ⚡ **Redis**: localhost:6379

## ✨ 功能特性

### 🔥 核心功能

- 🆔 **用户系统**: 注册、登录、权限管理
- 🌐 **隧道管理**: 支持 TCP/UDP/HTTP/HTTPS/STCP/XTCP
- 🖥️ **节点管理**: 多节点分布、状态监控
- 📊 **数据统计**: 流量统计、使用分析
- 💰 **商业功能**: 套餐购买、计费系统
- 🔐 **安全保障**: JWT认证、数据加密
- 📧 **邮件验证**: 可选的邮箱验证注册功能
- ⚙️ **灵活配置**: 详细的系统配置选项

### 🎨 界面特性

- 🌙 **深色主题**: 专业的深色界面设计
- 🔮 **玻璃拟态**: 现代化视觉效果
- 📱 **响应式**: 完美适配桌面端和移动端
- ⚡ **高性能**: 优化动画和交互体验
- 🎯 **直观易用**: 简洁明了的操作界面

### 🔧 技术特性

- ⚡ **高性能**: 基于最新技术栈优化
- 🛡️ **安全性**: 完善的安全防护机制
- 🐳 **容器化**: Docker一键部署
- 🔧 **类型安全**: 全面TypeScript支持
- 📈 **可扩展**: 模块化架构设计

## 📚 API 文档

### 🔌 主要接口

| 方法 | 路径 | 描述 | 认证 |
|------|------|------|------|
| POST | `/api/auth/login` | 用户登录 | ❌ |
| POST | `/api/auth/register` | 用户注册 | ❌ |
| GET | `/api/users/profile` | 获取用户信息 | ✅ |
| GET | `/api/proxies` | 获取隧道列表 | ✅ |
| POST | `/api/proxies` | 创建隧道 | ✅ |
| PUT | `/api/proxies/:id` | 更新隧道 | ✅ |
| DELETE | `/api/proxies/:id` | 删除隧道 | ✅ |
| GET | `/api/nodes` | 获取节点列表 | ✅ |
| GET | `/api/packages` | 获取套餐列表 | ❌ |
| POST | `/api/orders` | 创建订单 | ✅ |

📖 **详细API文档**: 启动后端服务后访问 `/api/docs`

## 🎨 界面预览

### 🏠 仪表板
- 📊 数据概览和统计图表
- 🚀 快速操作入口
- 📈 实时数据更新

### 🌐 隧道管理
- ➕ 创建、编辑、删除隧道
- 🔄 实时状态监控
- 📊 流量使用统计

### 🖥️ 节点监控
- 🗺️ 节点分布可视化
- 📈 性能指标监控
- ⚠️ 异常状态告警

### 💼 商业中心
- 🛒 套餐购买管理
- 💳 订单状态跟踪
- 📊 财务数据分析

## 🤝 贡献指南

我们欢迎所有形式的贡献！

1. **Fork 项目** 到您的GitHub账户
2. **创建分支** (`git checkout -b feature/AmazingFeature`)
3. **提交更改** (`git commit -m '✨ Add AmazingFeature'`)
4. **推送分支** (`git push origin feature/AmazingFeature`)
5. **提交PR** 并等待审核

### 📝 提交规范

- `✨ feat`: 新功能
- `🐛 fix`: 修复bug  
- `📝 docs`: 文档更新
- 💄 `style`: 代码格式
- ♻️ `refactor`: 重构代码
- ⚡ `perf`: 性能优化
- 🧪 `test`: 测试相关

## 📜 更新日志

### v2.0.0 (2024-12-29)
- 🎨 全新UI设计，现代化深色主题
- 🔮 玻璃拟态界面风格
- 💼 商业功能重构
- 📱 响应式设计优化
- ⚡ 性能大幅提升
- 🛡️ 安全性增强

## 📄 许可证

本项目采用 [MIT 许可证](LICENSE) - 允许商业和个人自由使用。

## 📞 技术支持

- 🐛 **Bug反馈**: [GitHub Issues](https://github.com/frccyan/AxioFrp/issues)
- 💡 **功能建议**: [GitHub Discussions](https://github.com/frccyan/AxioFrp/discussions)
- 📧 **商务合作**: frccyan@outlook.com

---

<div align="center">

**⭐ 如果这个项目对您有帮助，请给我们一个星标！**

Made with ❤️ by [frccyan](https://github.com/frccyan)

</div>