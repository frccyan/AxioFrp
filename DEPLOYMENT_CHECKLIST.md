# AxioFrp 部署检查清单

## 🚀 GitHub Packages 自动构建检查

### ✅ 代码质量检查
- [ ] 后端 TypeScript 类型检查通过
- [ ] 前端 TypeScript 类型检查通过  
- [ ] ESLint 代码规范检查通过
- [ ] 所有依赖项安全扫描通过

### ✅ Docker 构建检查
- [ ] 后端 Docker 镜像构建成功
- [ ] 前端 Docker 镜像构建成功
- [ ] 镜像推送到 GitHub Packages 成功
- [ ] 镜像标签正确设置

### ✅ 集成测试检查
- [ ] 数据库连接正常
- [ ] Redis 连接正常
- [ ] 后端 API 健康检查通过
- [ ] 前端应用访问正常
- [ ] 用户注册登录功能正常

### ✅ 安全检查
- [ ] Trivy 安全扫描通过
- [ ] 无高危漏洞
- [ ] 权限配置正确
- [ ] 环境变量安全

## 📋 部署前检查

### 1. 环境准备
```bash
# 检查必要工具
docker --version
docker-compose --version
git --version

# 检查端口占用
netstat -an | grep :3000  # 前端端口
netstat -an | grep :8000  # 后端端口
netstat -an | grep :3306  # 数据库端口
netstat -an | grep :6379  # Redis端口
```

### 2. 配置文件检查
```bash
# 检查 docker-compose.yml
- [ ] 数据库密码已修改
- [ ] JWT_SECRET 已设置
- [ ] 环境变量配置正确
- [ ] 端口映射正确

# 检查 .env 文件
- [ ] 数据库配置
- [ ] 邮件配置（可选）
- [ ] 安全配置
```

### 3. 网络和安全检查
```bash
# 防火墙配置
- [ ] 80/443 端口对外开放（如果需要）
- [ ] 3000/8000 端口限制访问（生产环境）
- [ ] 数据库端口不对外开放
- [ ] SSL 证书配置（生产环境）

# 备份配置
- [ ] 数据库备份策略
- [ ] 配置文件备份
- [ ] 容器数据卷备份
```

## 🚢 部署流程

### 快速部署（推荐）
```bash
# 1. 克隆仓库
git clone https://github.com/frccyan/AxioFrp.git
cd AxioFrp

# 2. 运行一键部署脚本
./install.sh  # Linux/macOS
./install.ps1  # Windows

# 3. 验证部署
curl http://localhost:3000
curl http://localhost:8000/health
```

### 手动部署
```bash
# 1. 环境配置
cp .env.example .env
# 编辑 .env 文件

# 2. 启动服务
docker-compose up -d

# 3. 初始化数据库
docker exec axiofrp-mysql mysql -u root -p < init_settings.sql

# 4. 验证服务
docker-compose ps
docker-compose logs
```

## 🔍 部署后验证

### 功能测试
- [ ] 前端页面加载正常
- [ ] 后端 API 响应正常
- [ ] 用户注册功能
- [ ] 用户登录功能
- [ ] 隧道创建功能
- [ ] 管理后台访问
- [ ] 邮件发送测试（如果配置）

### 性能检查
```bash
# 检查容器资源使用
docker stats

# 检查磁盘空间
df -h

# 检查内存使用
free -h
```

### 日志检查
```bash
# 查看所有服务日志
docker-compose logs

# 查看特定服务日志
docker-compose logs backend
docker-compose logs frontend
docker-compose logs mysql
docker-compose logs redis
```

## 🔧 运维管理

### 日常维护
```bash
# 查看服务状态
docker-compose ps

# 重启服务
docker-compose restart

# 更新服务
git pull
docker-compose pull
docker-compose up -d --force-recreate
```

### 备份恢复
```bash
# 数据库备份
docker exec axiofrp-mysql mysqldump -u root -p axiofrp > backup.sql

# 数据库恢复
docker exec -i axiofrp-mysql mysql -u root -p axiofrp < backup.sql
```

### 监控告警
- [ ] 设置容器监控
- [ ] 配置日志收集
- [ ] 设置告警规则
- [ ] 定期安全扫描

## 📊 扩展部署

### 高可用部署
- [ ] 负载均衡配置
- [ ] 多节点部署
- [ ] 数据库主从复制
- [ ] Redis 集群配置

### 性能优化
- [ ] 数据库索引优化
- [ ] Redis 缓存优化
- [ ] Nginx 性能调优
- [ ] 容器资源限制

## 🆘 故障排查

### 常见问题
1. **容器无法启动**
   - 检查端口占用
   - 检查磁盘空间
   - 查看容器日志

2. **数据库连接失败**
   - 检查数据库服务状态
   - 验证连接配置
   - 检查网络连接

3. **前端无法访问后端**
   - 检查 API 代理配置
   - 验证 CORS 设置
   - 查看网络策略

4. **邮件发送失败**
   - 验证 SMTP 配置
   - 检查网络连接
   - 查看邮件服务商限制

### 联系支持
- 📧 技术支持: support@axiofrp.com
- 🐛 问题反馈: [GitHub Issues](https://github.com/frccyan/AxioFrp/issues)
- 📖 文档中心: [项目文档](https://github.com/frccyan/AxioFrp/wiki)

---

## ✅ 部署成功确认

- [ ] 所有服务正常运行
- [ ] 功能测试通过
- [ ] 性能指标正常
- [ ] 监控告警配置完成
- [ ] 备份策略已实施
- [ ] 文档更新完成

**部署完成后，请及时更新相关文档和监控配置！** 🎉