# AxioFrp 一键安装脚本 (PowerShell版本)
# 支持交互式配置和自动部署

param(
    [switch]$Auto,
    [string]$ConfigFile = "axiofrp-config.json"
)

# 颜色定义
$Colors = @{
    Red = "Red"
    Green = "Green"
    Yellow = "Yellow"
    Blue = "Blue"
    Purple = "Magenta"
    Cyan = "Cyan"
    White = "White"
}

# 打印带颜色的消息
function Write-ColorMessage {
    param(
        [string]$Message,
        [string]$Color = "White"
    )
    Write-Host $Message -ForegroundColor $Colors[$Color]
}

function Write-Success {
    param([string]$Message)
    Write-ColorMessage "✨ $Message" "Green"
}

function Write-Warning {
    param([string]$Message)
    Write-ColorMessage "⚠️  $Message" "Yellow"
}

function Write-Error {
    param([string]$Message)
    Write-ColorMessage "❌ $Message" "Red"
}

function Write-Info {
    param([string]$Message)
    Write-ColorMessage "ℹ️  $Message" "Blue"
}

function Write-Header {
    param([string]$Message)
    Write-ColorMessage "🚀 $Message" "Purple"
}

# 检查系统要求
function Test-Requirements {
    Write-Header "检查系统要求..."
    
    # 检查 Docker
    try {
        docker version | Out-Null
        Write-Success "Docker 已安装"
    } catch {
        Write-Error "Docker 未安装，请先安装 Docker Desktop"
        exit 1
    }
    
    # 检查 Docker Compose
    try {
        docker-compose version | Out-Null
        Write-Success "Docker Compose 已安装"
    } catch {
        Write-Error "Docker Compose 未安装，请先安装 Docker Compose"
        exit 1
    }
    
    Write-Success "系统要求检查通过！"
}

# 生成随机密码
function New-RandomPassword {
    param([int]$Length = 32)
    $chars = 'abcdefghijkmnoprstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%^&*'
    -join ($chars.ToCharArray() | Get-Random -Count $Length)
}

# 交互式配置
function Start-InteractiveConfig {
    Write-Header "交互式配置"
    
    Write-ColorMessage "请输入以下配置信息（直接回车使用默认值）：" "Cyan"
    ""
    
    $config = @{}
    
    # 数据库配置
    Write-ColorMessage "📊 数据库配置" "Yellow"
    $dbPassword = Read-Host "数据库密码 [随机生成]"
    if ([string]::IsNullOrEmpty($dbPassword)) {
        $config.DB_PASSWORD = New-RandomPassword
    } else {
        $config.DB_PASSWORD = $dbPassword
    }
    
    # JWT 密钥
    $jwtSecret = Read-Host "JWT 密钥 [随机生成]"
    if ([string]::IsNullOrEmpty($jwtSecret)) {
        $config.JWT_SECRET = New-RandomPassword 64
    } else {
        $config.JWT_SECRET = $jwtSecret
    }
    
    # 邮件配置
    Write-ColorMessage "📧 邮件配置（可选，用于邮箱验证注册）" "Yellow"
    $enableEmail = Read-Host "是否配置邮件服务？(y/n) [n]"
    if ($enableEmail -eq "y" -or $enableEmail -eq "Y") {
        $config.SMTP_HOST = Read-Host "SMTP 服务器 [smtp.gmail.com]"
        if ([string]::IsNullOrEmpty($config.SMTP_HOST)) {
            $config.SMTP_HOST = "smtp.gmail.com"
        }
        
        $smtpPort = Read-Host "SMTP 端口 [587]"
        if ([string]::IsNullOrEmpty($smtpPort)) {
            $config.SMTP_PORT = "587"
        } else {
            $config.SMTP_PORT = $smtpPort
        }
        
        $config.SMTP_USER = Read-Host "邮箱地址"
        $config.SMTP_PASS = Read-Host "邮箱密码/授权码" -AsSecureString
        $config.EMAIL_ENABLED = $true
    } else {
        $config.EMAIL_ENABLED = $false
    }
    
    # 应用配置
    Write-ColorMessage "⚙️ 应用配置" "Yellow"
    $frontendUrl = Read-Host "前端访问地址 [http://localhost]"
    if ([string]::IsNullOrEmpty($frontendUrl)) {
        $config.FRONTEND_URL = "http://localhost"
    } else {
        $config.FRONTEND_URL = $frontendUrl
    }
    
    $backendPort = Read-Host "后端端口 [8000]"
    if ([string]::IsNullOrEmpty($backendPort)) {
        $config.BACKEND_PORT = "8000"
    } else {
        $config.BACKEND_PORT = $backendPort
    }
    
    $frontendPort = Read-Host "前端端口 [3000]"
    if ([string]::IsNullOrEmpty($frontendPort)) {
        $config.FRONTEND_PORT = "3000"
    } else {
        $config.FRONTEND_PORT = $frontendPort
    }
    
    # 用户限制配置
    Write-ColorMessage "👥 用户限制配置" "Yellow"
    $maxProxies = Read-Host "每个用户最大隧道数 [10]"
    if ([string]::IsNullOrEmpty($maxProxies)) {
        $config.MAX_PROXIES_PER_USER = "10"
    } else {
        $config.MAX_PROXIES_PER_USER = $maxProxies
    }
    
    $defaultTrafficGB = Read-Host "默认流量限制(GB) [1]"
    if ([string]::IsNullOrEmpty($defaultTrafficGB)) {
        $config.DEFAULT_TRAFFIC_LIMIT = [string]1073741824  # 1GB
    } else {
        $config.DEFAULT_TRAFFIC_LIMIT = [string]([int]$defaultTrafficGB * 1024 * 1024 * 1024)
    }
    
    return $config
}

# 保存配置到文件
function Save-Config {
    param($Config)
    
    $configJson = $Config | ConvertTo-Json -Depth 10
    $configJson | Out-File -FilePath $ConfigFile -Encoding UTF8
    Write-Success "配置已保存到 $ConfigFile"
}

# 从文件加载配置
function Load-Config {
    if (Test-Path $ConfigFile) {
        $configJson = Get-Content -Path $ConfigFile -Raw | ConvertFrom-Json
        Write-Info "从 $ConfigFile 加载配置"
        return $configJson
    }
    return $null
}

# 生成配置文件
function New-ConfigFiles {
    param($Config)
    
    Write-Header "生成配置文件..."
    
    # 生成 .env 文件
    $envContent = @"
# AxioFrp 自动生成的配置文件
# 生成时间: $(Get-Date)

# 数据库配置
DB_HOST=mysql
DB_PORT=3306
DB_NAME=axiofrp
DB_USER=axiofrp_user
DB_PASSWORD=$($Config.DB_PASSWORD)

# Redis配置
REDIS_URL=redis://redis:6379

# JWT配置
JWT_SECRET=$($Config.JWT_SECRET)
JWT_EXPIRES_IN=7d

# 应用配置
NODE_ENV=production
PORT=8000

# 前端配置
FRONTEND_URL=$($Config.FRONTEND_URL):$($Config.FRONTEND_PORT)
"@
    
    if ($Config.EMAIL_ENABLED) {
        $envContent += @"

# 邮件配置
SMTP_HOST=$($Config.SMTP_HOST)
SMTP_PORT=$($Config.SMTP_PORT)
SMTP_USER=$($Config.SMTP_USER)
SMTP_PASS=$($Config.SMTP_PASS)
"@
    } else {
        $envContent += @"

# 邮件配置（未启用）
# SMTP_HOST=
# SMTP_PORT=587
# SMTP_USER=
# SMTP_PASS=
"@
    }
    
    $envContent += @"

# 系统配置
MAX_PROXIES_PER_USER=$($Config.MAX_PROXIES_PER_USER)
DEFAULT_TRAFFIC_LIMIT=$($Config.DEFAULT_TRAFFIC_LIMIT)
"@
    
    $envContent | Out-File -FilePath ".env" -Encoding UTF8
    
    # 生成 docker-compose.yml
    $dockerComposeContent = @"
version: '3.8'

services:
  # MySQL 数据库
  mysql:
    image: mysql:8.0
    container_name: axiofrp-mysql
    environment:
      MYSQL_ROOT_PASSWORD: $($Config.DB_PASSWORD)_root
      MYSQL_DATABASE: axiofrp
      MYSQL_USER: axiofrp_user
      MYSQL_PASSWORD: $($Config.DB_PASSWORD)
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
      DB_PASSWORD: $($Config.DB_PASSWORD)
      REDIS_URL: redis://redis:6379
      JWT_SECRET: $($Config.JWT_SECRET)
      FRONTEND_URL: $($Config.FRONTEND_URL):$($Config.FRONTEND_PORT)
      MAX_PROXIES_PER_USER: $($Config.MAX_PROXIES_PER_USER)
      DEFAULT_TRAFFIC_LIMIT: $($Config.DEFAULT_TRAFFIC_LIMIT)
"@
    
    if ($Config.EMAIL_ENABLED) {
        $dockerComposeContent += @"
      SMTP_HOST: $($Config.SMTP_HOST)
      SMTP_PORT: $($Config.SMTP_PORT)
      SMTP_USER: $($Config.SMTP_USER)
      SMTP_PASS: $($Config.SMTP_PASS)
"@
    }
    
    $dockerComposeContent += @"
    ports:
      - "$($Config.BACKEND_PORT):8000"
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
        VITE_API_URL: $($Config.FRONTEND_URL):$($Config.BACKEND_PORT)
    container_name: axiofrp-frontend
    ports:
      - "$($Config.FRONTEND_PORT):3000"
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
"@
    
    $dockerComposeContent | Out-File -FilePath "docker-compose.yml" -Encoding UTF8
    
    Write-Success "配置文件生成完成！"
}

# 创建管理脚本
function New-ManagementScript {
    Write-Header "创建管理脚本..."
    
    $manageScript = @"
# AxioFrp 管理脚本 (PowerShell)

param(
    [Parameter(Mandatory=`$true)]
    [ValidateSet("start", "stop", "restart", "logs", "status", "update", "backup")]
    [string]`$Action
)

switch (`$Action) {
    "start" {
        Write-Host "🚀 启动 AxioFrp..." -ForegroundColor Green
        docker-compose up -d
    }
    "stop" {
        Write-Host "🛑 停止 AxioFrp..." -ForegroundColor Yellow
        docker-compose down
    }
    "restart" {
        Write-Host "🔄 重启 AxioFrp..." -ForegroundColor Blue
        docker-compose restart
    }
    "logs" {
        Write-Host "📋 查看日志..." -ForegroundColor Cyan
        docker-compose logs -f
    }
    "status" {
        Write-Host "📊 查看状态..." -ForegroundColor Magenta
        docker-compose ps
    }
    "update" {
        Write-Host "🔄 更新 AxioFrp..." -ForegroundColor Green
        git pull
        docker-compose down
        docker-compose build --no-cache
        docker-compose up -d
    }
    "backup" {
        Write-Host "💾 备份数据库..." -ForegroundColor Yellow
        if (-not (Test-Path "backups")) {
            New-Item -ItemType Directory -Path "backups"
        }
        `$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
        docker exec axiofrp-mysql mysqldump -u root -p`$($Config.DB_PASSWORD)_root axiofrp > "backups/backup_`$timestamp.sql"
        Write-Host "备份完成: backups/backup_`$timestamp.sql" -ForegroundColor Green
    }
    default {
        Write-Host "用法: ./manage.ps1 {start|stop|restart|logs|status|update|backup}" -ForegroundColor Red
        exit 1
    }
}
"@
    
    $manageScript | Out-File -FilePath "manage.ps1" -Encoding UTF8
    Write-Success "管理脚本创建完成！"
}

# 部署应用
function Start-Deployment {
    Write-Header "部署应用..."
    
    Write-Info "构建 Docker 镜像..."
    docker-compose build
    
    Write-Info "启动服务..."
    docker-compose up -d
    
    Write-Info "等待服务启动..."
    Start-Sleep -Seconds 30
    
    # 检查服务状态
    $status = docker-compose ps
    if ($status -match "Up") {
        Write-Success "✅ 服务启动成功！"
        Write-Info "访问地址："
        Write-Host "  🖥️  前端界面: $($Config.FRONTEND_URL):$($Config.FRONTEND_PORT)" -ForegroundColor Cyan
        Write-Host "  🔌  后端API: $($Config.FRONTEND_URL):$($Config.BACKEND_PORT)" -ForegroundColor Cyan
    } else {
        Write-Error "❌ 服务启动失败，请查看日志"
        docker-compose logs
        exit 1
    }
}

# 显示安装完成信息
function Show-Completion {
    Write-Header "🎉 安装完成！"
    
    Write-Success "恭喜！AxioFrp 已成功安装并运行！"
    ""
    
    Write-ColorMessage "📋 重要信息：" "Cyan"
    Write-Host "  📁 配置文件: .env" -ForegroundColor Yellow
    Write-Host "  📊 数据库密码: $($Config.DB_PASSWORD)" -ForegroundColor Yellow
    Write-Host "  🔐 JWT密钥: $($Config.JWT_SECRET.Substring(0,20))..." -ForegroundColor Yellow
    
    ""
    Write-ColorMessage "🚀 访问地址：" "Cyan"
    Write-Host "  🖥️  前端界面: $($Config.FRONTEND_URL):$($Config.FRONTEND_PORT)" -ForegroundColor Green
    Write-Host "  🔌  后端API: $($Config.FRONTEND_URL):$($Config.BACKEND_PORT)" -ForegroundColor Green
    
    ""
    Write-ColorMessage "🔧 管理命令：" "Cyan"
    Write-Host "  启动服务: ./manage.ps1 start" -ForegroundColor Yellow
    Write-Host "  停止服务: ./manage.ps1 stop" -ForegroundColor Yellow
    Write-Host "  查看状态: ./manage.ps1 status" -ForegroundColor Yellow
    Write-Host "  查看日志: ./manage.ps1 logs" -ForegroundColor Yellow
    Write-Host "  更新系统: ./manage.ps1 update" -ForegroundColor Yellow
    Write-Host "  备份数据: ./manage.ps1 backup" -ForegroundColor Yellow
    
    ""
    Write-ColorMessage "📖 更多帮助：" "Cyan"
    Write-Host "  📋 配置指南: CONFIG_GUIDE.md" -ForegroundColor Yellow
    Write-Host "  🐛 问题反馈: https://github.com/frccyan/AxioFrp/issues" -ForegroundColor Yellow
    
    if (-not $Config.EMAIL_ENABLED) {
        ""
        Write-Warning "💡 提示: 您未配置邮件服务，可以稍后通过编辑 .env 文件添加邮件配置。"
    }
}

# 主函数
function Start-Main {
    Write-ColorMessage @"

╔══════════════════════════════════════════════════════════════╗
║                    AxioFrp 一键安装脚本                      ║
║                 现代化内网穿透管理面板                        ║
╚══════════════════════════════════════════════════════════════╝

"@ "Purple"
    
    try {
        Test-Requirements
        
        if ($Auto) {
            $Config = Load-Config
            if (-not $Config) {
                Write-Error "自动模式需要配置文件 $ConfigFile"
                exit 1
            }
        } else {
            $Config = Start-InteractiveConfig
            Save-Config -Config $Config
        }
        
        New-ConfigFiles -Config $Config
        New-ManagementScript
        Start-Deployment
        Show-Completion
    } catch {
        Write-Error "安装过程中发生错误: $($_.Exception.Message)"
        exit 1
    }
}

# 执行主函数
Start-Main