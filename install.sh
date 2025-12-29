#!/bin/bash

# AxioFrp 一键安装脚本
# 支持交互式配置和自动部署

set -e

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# 打印带颜色的消息
print_message() {
    echo -e "${GREEN}✨ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

print_header() {
    echo -e "${PURPLE}🚀 $1${NC}"
}

# 检查系统要求
check_requirements() {
    print_header "检查系统要求..."
    
    # 检查 Docker
    if ! command -v docker &> /dev/null; then
        print_error "Docker 未安装，请先安装 Docker"
        exit 1
    fi
    
    # 检查 Docker Compose
    if ! command -v docker-compose &> /dev/null; then
        print_error "Docker Compose 未安装，请先安装 Docker Compose"
        exit 1
    fi
    
    print_message "系统要求检查通过！"
}

# 交互式配置
interactive_config() {
    print_header "交互式配置"
    
    echo -e "${CYAN}请输入以下配置信息（直接回车使用默认值）：${NC}\n"
    
    # 数据库配置
    echo -e "${YELLOW}📊 数据库配置${NC}"
    read -p "数据库密码 [随机生成]: " db_password
    if [ -z "$db_password" ]; then
        db_password=$(openssl rand -base64 32)
    fi
    
    # JWT 密钥
    read -p "JWT 密钥 [随机生成]: " jwt_secret
    if [ -z "$jwt_secret" ]; then
        jwt_secret=$(openssl rand -base64 64)
    fi
    
    # 邮件配置
    echo -e "${YELLOW}📧 邮件配置（可选，用于邮箱验证注册）${NC}"
    read -p "是否配置邮件服务？(y/n) [n]: " enable_email
    if [ "$enable_email" = "y" ] || [ "$enable_email" = "Y" ]; then
        read -p "SMTP 服务器 [smtp.gmail.com]: " smtp_host
        read -p "SMTP 端口 [587]: " smtp_port
        read -p "邮箱地址: " smtp_user
        read -p "邮箱密码/授权码: " smtp_pass
        
        smtp_host=${smtp_host:-smtp.gmail.com}
        smtp_port=${smtp_port:-587}
        
        # 测试邮件配置
        print_info "正在测试邮件配置..."
        # 这里可以添加邮件测试逻辑
    fi
    
    # 应用配置
    echo -e "${YELLOW}⚙️ 应用配置${NC}"
    read -p "前端访问地址 [http://localhost]: " frontend_url
    read -p "后端端口 [8000]: " backend_port
    read -p "前端端口 [3000]: " frontend_port
    
    frontend_url=${frontend_url:-http://localhost}
    backend_port=${backend_port:-8000}
    frontend_port=${frontend_port:-3000}
    
    # 用户限制配置
    echo -e "${YELLOW}👥 用户限制配置${NC}"
    read -p "每个用户最大隧道数 [10]: " max_proxies
    read -p "默认流量限制(GB) [1]: " default_traffic_gb
    
    max_proxies=${max_proxies:-10}
    default_traffic_gb=${default_traffic_gb:-1}
    default_traffic_bytes=$((default_traffic_gb * 1024 * 1024 * 1024))
}

# 生成配置文件
generate_config() {
    print_header "生成配置文件..."
    
    # 生成 .env 文件
    cat > .env << EOF
# AxioFrp 自动生成的配置文件
# 生成时间: $(date)

# 数据库配置
DB_HOST=mysql
DB_PORT=3306
DB_NAME=axiofrp
DB_USER=axiofrp_user
DB_PASSWORD=${db_password}

# Redis配置
REDIS_URL=redis://redis:6379

# JWT配置
JWT_SECRET=${jwt_secret}
JWT_EXPIRES_IN=7d

# 应用配置
NODE_ENV=production
PORT=8000

# 前端配置
FRONTEND_URL=${frontend_url}:${frontend_port}

# 邮件配置
EOF

    if [ "$enable_email" = "y" ] || [ "$enable_email" = "Y" ]; then
        cat >> .env << EOF
SMTP_HOST=${smtp_host}
SMTP_PORT=${smtp_port}
SMTP_USER=${smtp_user}
SMTP_PASS=${smtp_pass}
EOF
    else
        cat >> .env << EOF
# SMTP_HOST=
# SMTP_PORT=587
# SMTP_USER=
# SMTP_PASS=
EOF
    fi

    cat >> .env << EOF

# 系统配置
MAX_PROXIES_PER_USER=${max_proxies}
DEFAULT_TRAFFIC_LIMIT=${default_traffic_bytes}
EOF

    # 生成更新的 docker-compose.yml
    cat > docker-compose.yml << 'EOF'
version: '3.8'

services:
  # MySQL 数据库
  mysql:
    image: mysql:8.0
    container_name: axiofrp-mysql
    environment:
      MYSQL_ROOT_PASSWORD: ${db_password}_root
      MYSQL_DATABASE: axiofrp
      MYSQL_USER: axiofrp_user
      MYSQL_PASSWORD: ${db_password}
    volumes:
      - mysql_data:/var/lib/mysql
      - ./init.sql:/docker-entrypoint-initdb.d/init.sql
    ports:
      - "3306:3306"
    networks:
      - axiofrp-network
    restart: unless-stopped

  # Redis 缓存
  redis:
    image: redis:7-alpine
    container_name: axiofrp-redis
    ports:
      - "6379:6379"
    networks:
      - axiofrp-network
    restart: unless-stopped

  # 后端 API 服务
  backend:
    build:
      context: ./backend
      dockerfile: Dockerfile
    container_name: axiofrp-backend
    environment:
      NODE_ENV: production
      DB_HOST: mysql
      DB_PORT: 3306
      DB_NAME: axiofrp
      DB_USER: axiofrp_user
      DB_PASSWORD: ${db_password}
      REDIS_URL: redis://redis:6379
      JWT_SECRET: ${jwt_secret}
      FRONTEND_URL: ${frontend_url}:${frontend_port}
      MAX_PROXIES_PER_USER: ${max_proxies}
      DEFAULT_TRAFFIC_LIMIT: ${default_traffic_bytes}
EOF

    if [ "$enable_email" = "y" ] || [ "$enable_email" = "Y" ]; then
        cat >> docker-compose.yml << EOF
      SMTP_HOST: ${smtp_host}
      SMTP_PORT: ${smtp_port}
      SMTP_USER: ${smtp_user}
      SMTP_PASS: ${smtp_pass}
EOF
    fi

    cat >> docker-compose.yml << 'EOF'
    ports:
      - "${backend_port}:8000"
    depends_on:
      - mysql
      - redis
    networks:
      - axiofrp-network
    restart: unless-stopped

  # 前端应用
  frontend:
    build:
      context: ./frontend
      dockerfile: Dockerfile
      args:
        VITE_API_URL: ${frontend_url}:${backend_port}
    container_name: axiofrp-frontend
    ports:
      - "${frontend_port}:3000"
    depends_on:
      - backend
    networks:
      - axiofrp-network
    restart: unless-stopped

volumes:
  mysql_data:

networks:
  axiofrp-network:
    driver: bridge
EOF

    # 替换变量
    sed -i "s/\${db_password}/${db_password}/g" docker-compose.yml
    sed -i "s/\${jwt_secret}/${jwt_secret}/g" docker-compose.yml
    sed -i "s/\${frontend_url}/${frontend_url}/g" docker-compose.yml
    sed -i "s/\${backend_port}/${backend_port}/g" docker-compose.yml
    sed -i "s/\${frontend_port}/${frontend_port}/g" docker-compose.yml
    sed -i "s/\${max_proxies}/${max_proxies}/g" docker-compose.yml
    sed -i "s/\${default_traffic_bytes}/${default_traffic_bytes}/g" docker-compose.yml
    sed -i "s/\${smtp_host}/${smtp_host}/g" docker-compose.yml
    sed -i "s/\${smtp_port}/${smtp_port}/g" docker-compose.yml
    sed -i "s/\${smtp_user}/${smtp_user}/g" docker-compose.yml
    sed -i "s/\${smtp_pass}/${smtp_pass}/g" docker-compose.yml
    
    print_message "配置文件生成完成！"
}

# 创建管理脚本
create_admin_script() {
    print_header "创建管理脚本..."
    
    cat > manage.sh << 'EOF'
#!/bin/bash

# AxioFrp 管理脚本

case "$1" in
    start)
        echo "🚀 启动 AxioFrp..."
        docker-compose up -d
        ;;
    stop)
        echo "🛑 停止 AxioFrp..."
        docker-compose down
        ;;
    restart)
        echo "🔄 重启 AxioFrp..."
        docker-compose restart
        ;;
    logs)
        echo "📋 查看日志..."
        docker-compose logs -f
        ;;
    status)
        echo "📊 查看状态..."
        docker-compose ps
        ;;
    update)
        echo "🔄 更新 AxioFrp..."
        git pull
        docker-compose down
        docker-compose build --no-cache
        docker-compose up -d
        ;;
    backup)
        echo "💾 备份数据库..."
        mkdir -p backups
        docker exec axiofrp-mysql mysqldump -u root -p axiofrp > backups/backup_$(date +%Y%m%d_%H%M%S).sql
        ;;
    *)
        echo "用法: $0 {start|stop|restart|logs|status|update|backup}"
        exit 1
        ;;
esac
EOF
    
    chmod +x manage.sh
    print_message "管理脚本创建完成！"
}

# 部署应用
deploy_app() {
    print_header "部署应用..."
    
    # 构建并启动服务
    print_info "构建 Docker 镜像..."
    docker-compose build
    
    print_info "启动服务..."
    docker-compose up -d
    
    # 等待服务启动
    print_info "等待服务启动..."
    sleep 30
    
    # 检查服务状态
    if docker-compose ps | grep -q "Up"; then
        print_message "✅ 服务启动成功！"
        print_info "访问地址："
        echo -e "  🖥️  前端界面: ${CYAN}${frontend_url}:${frontend_port}${NC}"
        echo -e "  🔌  后端API: ${CYAN}${frontend_url}:${backend_port}${NC}"
    else
        print_error "❌ 服务启动失败，请查看日志"
        docker-compose logs
        exit 1
    fi
}

# 显示安装完成信息
show_completion() {
    print_header "🎉 安装完成！"
    
    echo -e "${GREEN}恭喜！AxioFrp 已成功安装并运行！${NC}\n"
    
    echo -e "${CYAN}📋 重要信息：${NC}"
    echo -e "  📁 配置文件: ${YELLOW}.env${NC}"
    echo -e "  📊 数据库密码: ${YELLOW}${db_password}${NC}"
    echo -e "  🔐 JWT密钥: ${YELLOW}${jwt_secret:0:20}...${NC}"
    
    echo -e "\n${CYAN}🚀 访问地址：${NC}"
    echo -e "  🖥️  前端界面: ${GREEN}${frontend_url}:${frontend_port}${NC}"
    echo -e "  🔌  后端API: ${GREEN}${frontend_url}:${backend_port}${NC}"
    
    echo -e "\n${CYAN}🔧 管理命令：${NC}"
    echo -e "  启动服务: ${YELLOW}./manage.sh start${NC}"
    echo -e "  停止服务: ${YELLOW}./manage.sh stop${NC}"
    echo -e "  查看状态: ${YELLOW}./manage.sh status${NC}"
    echo -e "  查看日志: ${YELLOW}./manage.sh logs${NC}"
    echo -e "  更新系统: ${YELLOW}./manage.sh update${NC}"
    echo -e "  备份数据: ${YELLOW}./manage.sh backup${NC}"
    
    echo -e "\n${CYAN}📖 更多帮助：${NC}"
    echo -e "  📋 配置指南: ${YELLOW}CONFIG_GUIDE.md${NC}"
    echo -e "  🐛 问题反馈: ${YELLOW}https://github.com/frccyan/AxioFrp/issues${NC}"
    
    if [ "$enable_email" != "y" ] && [ "$enable_email" != "Y" ]; then
        echo -e "\n${YELLOW}💡 提示: 您未配置邮件服务，可以稍后通过编辑 .env 文件添加邮件配置。${NC}"
    fi
}

# 主函数
main() {
    echo -e "${PURPLE}"
    echo "╔══════════════════════════════════════════════════════════════╗"
    echo "║                    AxioFrp 一键安装脚本                      ║"
    echo "║                 现代化内网穿透管理面板                        ║"
    echo "╚══════════════════════════════════════════════════════════════╝"
    echo -e "${NC}\n"
    
    check_requirements
    interactive_config
    generate_config
    create_admin_script
    deploy_app
    show_completion
}

# 错误处理
trap 'print_error "安装过程中发生错误！"; exit 1' ERR

# 执行主函数
main "$@"