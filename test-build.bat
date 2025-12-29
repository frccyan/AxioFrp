@echo off
REM AxioFrp Windows 构建测试脚本

echo [INFO] 开始 AxioFrp 构建测试...

REM 检查 Docker
echo [INFO] 检查 Docker...
docker --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Docker 未安装或未运行
    pause
    exit /b 1
)
echo [SUCCESS] Docker 检查通过

REM 检查 Docker Compose
echo [INFO] 检查 Docker Compose...
docker-compose --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Docker Compose 未安装
    pause
    exit /b 1
)
echo [SUCCESS] Docker Compose 检查通过

REM 清理旧的容器和镜像
echo [INFO] 清理旧的容器和镜像...
docker-compose down --remove-orphans 2>nul
docker images | findstr axiofrp >nul 2>&1
if %errorlevel% equ 0 (
    docker images | findstr axiofrp | for /f "tokens=3" %%i in ('more') do docker rmi -f %%i 2>nul
)
echo [SUCCESS] 清理完成

REM 构建 Docker 镜像
echo [INFO] 构建 Docker 镜像...

REM 构建后端镜像
echo [INFO] 构建后端 Docker 镜像...
docker build -t axiofrp-backend:test ./backend
if %errorlevel% neq 0 (
    echo [ERROR] 后端镜像构建失败
    pause
    exit /b 1
)
echo [SUCCESS] 后端镜像构建完成

REM 构建前端镜像
echo [INFO] 构建前端 Docker 镜像...
docker build -t axiofrp-frontend:test ./frontend
if %errorlevel% neq 0 (
    echo [ERROR] 前端镜像构建失败
    pause
    exit /b 1
)
echo [SUCCESS] 前端镜像构建完成

REM 启动测试环境
echo [INFO] 启动测试环境...
docker-compose up -d

REM 等待服务启动
echo [INFO] 等待服务启动...
timeout /t 30 /nobreak >nul

REM 健康检查
echo [INFO] 执行健康检查...

REM 检查数据库
echo [INFO] 检查数据库连接...
docker exec axiofrp-mysql mysql -u root -paxiofrp_root_password -e "SELECT 1" >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] 数据库连接失败
    pause
    exit /b 1
)
echo [SUCCESS] 数据库连接正常

REM 检查 Redis
echo [INFO] 检查 Redis 连接...
docker exec axiofrp-redis redis-cli ping >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Redis 连接失败
    pause
    exit /b 1
)
echo [SUCCESS] Redis 连接正常

REM 检查后端 API
echo [INFO] 检查后端 API...
curl -f http://localhost:8000/health >nul 2>&1
if %errorlevel% neq 0 (
    echo [WARNING] 后端 API 可能还在启动中...
    timeout /t 10 /nobreak >nul
    curl -f http://localhost:8000/health >nul 2>&1
    if %errorlevel% neq 0 (
        echo [ERROR] 后端 API 失败
        pause
        exit /b 1
    fi
)
echo [SUCCESS] 后端 API 正常

REM 检查前端
echo [INFO] 检查前端应用...
curl -f http://localhost:3000 >nul 2>&1
if %errorlevel% neq 0 (
    echo [WARNING] 前端应用可能还在启动中...
    timeout /t 10 /nobreak >nul
    curl -f http://localhost:3000 >nul 2>&1
    if %errorlevel% neq 0 (
        echo [ERROR] 前端应用失败
        pause
        exit /b 1
    fi
)
echo [SUCCESS] 前端应用正常

echo [SUCCESS] 🎉 构建和测试流程全部完成！

REM 显示访问信息
echo.
echo [INFO] 🚀 服务访问地址：
echo    前端应用: http://localhost:3000
echo    后端API:  http://localhost:8000
echo    API文档:  http://localhost:8000/health
echo.
echo [INFO] 📋 管理员账号：
echo    用户名: admin
echo    邮箱:   admin@example.com
echo    密码:   admin123
echo.
echo [INFO] 🛑 停止服务: docker-compose down
echo.

pause