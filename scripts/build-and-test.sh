#!/bin/bash
# AxioFrp Docker 构建和测试脚本

set -e

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 日志函数
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# 检查依赖
check_dependencies() {
    log_info "检查依赖..."
    
    if ! command -v docker &> /dev/null; then
        log_error "Docker 未安装"
        exit 1
    fi
    
    if ! command -v docker-compose &> /dev/null; then
        log_error "Docker Compose 未安装"
        exit 1
    fi
    
    if ! command -v node &> /dev/null; then
        log_error "Node.js 未安装"
        exit 1
    fi
    
    log_success "依赖检查通过"
}

# 清理旧的镜像和容器
cleanup() {
    log_info "清理旧的镜像和容器..."
    
    # 停止并删除容器
    docker-compose down --remove-orphans 2>/dev/null || true
    
    # 删除旧镜像
    docker images | grep axiofrp | awk '{print $3}' | xargs -r docker rmi -f 2>/dev/null || true
    
    log_success "清理完成"
}

# 安装依赖
install_dependencies() {
    log_info "安装项目依赖..."
    
    # 后端依赖
    log_info "安装后端依赖..."
    cd backend
    npm ci
    cd ..
    
    # 前端依赖
    log_info "安装前端依赖..."
    cd frontend
    npm ci
    cd ..
    
    log_success "依赖安装完成"
}

# 代码质量检查
quality_check() {
    log_info "执行代码质量检查..."
    
    # 后端检查
    log_info "检查后端代码..."
    cd backend
    npm run type-check
    npm run lint
    cd ..
    
    # 前端检查
    log_info "检查前端代码..."
    cd frontend
    npm run type-check
    npm run lint
    cd ..
    
    log_success "代码质量检查通过"
}

# 构建应用
build_app() {
    log_info "构建应用..."
    
    # 构建后端
    log_info "构建后端..."
    cd backend
    npm run build
    cd ..
    
    # 构建前端
    log_info "构建前端..."
    cd frontend
    npm run build
    cd ..
    
    log_success "应用构建完成"
}

# 构建 Docker 镜像
build_docker() {
    log_info "构建 Docker 镜像..."
    
    # 构建后端镜像
    log_info "构建后端 Docker 镜像..."
    docker build -t axiofrp-backend:test ./backend
    
    # 构建前端镜像
    log_info "构建前端 Docker 镜像..."
    docker build -t axiofrp-frontend:test ./frontend
    
    log_success "Docker 镜像构建完成"
}

# 启动测试环境
start_test_env() {
    log_info "启动测试环境..."
    
    # 使用测试配置启动
    docker-compose -f docker-compose.yml -f docker-compose.test.yml up -d
    
    # 等待服务启动
    log_info "等待服务启动..."
    sleep 30
    
    log_success "测试环境启动完成"
}

# 健康检查
health_check() {
    log_info "执行健康检查..."
    
    # 检查数据库
    log_info "检查数据库连接..."
    if docker exec axiofrp-mysql mysql -u root -paxiofrp_root_password -e "SELECT 1" > /dev/null 2>&1; then
        log_success "数据库连接正常"
    else
        log_error "数据库连接失败"
        return 1
    fi
    
    # 检查 Redis
    log_info "检查 Redis 连接..."
    if docker exec axiofrp-redis redis-cli ping > /dev/null 2>&1; then
        log_success "Redis 连接正常"
    else
        log_error "Redis 连接失败"
        return 1
    fi
    
    # 检查后端 API
    log_info "检查后端 API..."
    if curl -f http://localhost:8000/health > /dev/null 2>&1; then
        log_success "后端 API 正常"
    else
        log_error "后端 API 失败"
        return 1
    fi
    
    # 检查前端
    log_info "检查前端应用..."
    if curl -f http://localhost:3000 > /dev/null 2>&1; then
        log_success "前端应用正常"
    else
        log_error "前端应用失败"
        return 1
    fi
    
    log_success "健康检查全部通过"
}

# 运行测试
run_tests() {
    log_info "运行集成测试..."
    
    # 这里可以添加更多的集成测试
    log_info "测试用户注册..."
    curl -X POST http://localhost:8000/api/auth/register \
         -H "Content-Type: application/json" \
         -d '{"username":"testuser","email":"test@example.com","password":"Test123!@#"}' \
         > /dev/null 2>&1
    
    log_info "测试用户登录..."
    response=$(curl -X POST http://localhost:8000/api/auth/login \
                     -H "Content-Type: application/json" \
                     -d '{"username":"testuser","password":"Test123!@#"}' \
                     2>/dev/null)
    
    if echo "$response" | grep -q "success"; then
        log_success "登录测试通过"
    else
        log_error "登录测试失败"
        return 1
    fi
    
    log_success "集成测试完成"
}

# 生成报告
generate_report() {
    log_info "生成测试报告..."
    
    # 获取镜像信息
    log_info "Docker 镜像信息："
    docker images | grep axiofrp
    
    # 获取容器状态
    log_info "容器状态："
    docker-compose ps
    
    # 生成版本信息
    echo "## 构建报告" > build-report.md
    echo "构建时间: $(date)" >> build-report.md
    echo "Git 版本: $(git rev-parse HEAD)" >> build-report.md
    echo "Node.js 版本: $(node --version)" >> build-report.md
    echo "Docker 版本: $(docker --version)" >> build-report.md
    echo "" >> build-report.md
    echo "### 镜像信息" >> build-report.md
    docker images | grep axiofrp >> build-report.md
    
    log_success "测试报告已生成: build-report.md"
}

# 清理测试环境
cleanup_test() {
    log_info "清理测试环境..."
    docker-compose down
    log_success "测试环境清理完成"
}

# 主函数
main() {
    log_info "开始 AxioFrp 构建和测试流程..."
    
    # 错误时自动清理
    trap cleanup_test EXIT
    
    check_dependencies
    cleanup
    install_dependencies
    quality_check
    build_app
    build_docker
    start_test_env
    health_check
    run_tests
    generate_report
    
    log_success "🎉 构建和测试流程全部完成！"
    
    # 显示访问信息
    echo ""
    log_info "🚀 服务访问地址："
    echo "   前端应用: http://localhost:3000"
    echo "   后端API:  http://localhost:8000"
    echo "   API文档:  http://localhost:8000/health"
    echo ""
    log_info "📋 管理员账号："
    echo "   用户名: admin"
    echo "   邮箱:   admin@example.com"
    echo "   密码:   admin123"
    echo ""
    log_info "🛑 停止服务: docker-compose down"
}

# 命令行参数处理
case "${1:-}" in
    "clean")
        cleanup
        ;;
    "build")
        build_docker
        ;;
    "test")
        health_check
        ;;
    "deploy")
        main
        ;;
    *)
        main
        ;;
esac