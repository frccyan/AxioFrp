<?php
namespace chhcn;

use chhcn;

//$page_title = "软件下载";
$rs = Database::querySingleLine("users", Array("username" => $_SESSION['user']));

if(!$rs) {
	exit("<script>location='?page=login';</script>");
}
?>
<style type="text/css">
:root {
    --primary-color: #3498db;
    --secondary-color: #2c3e50;
    --success-color: #2ecc71;
    --danger-color: #e74c3c;
    --warning-color: #f39c12;
    --info-color: #1abc9c;
    --light-color: #ecf0f1;
    --dark-color: #34495e;
    --gray-color: #95a5a6;
    --white-color: #ffffff;
    --shadow-color: rgba(0, 0, 0, 0.1);
}

body {
    font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

.content-header {
    padding: 15px;
    background-color: var(--white-color);
    border-radius: 8px;
    box-shadow: 0 1px 3px var(--shadow-color);
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.content-header h1 {
    margin: 0;
    font-size: 1.5rem;
    color: var(--dark-color);
    display: flex;
    align-items: center;
}

.content-header h1 i {
    margin-right: 10px;
    color: var(--primary-color);
}

.system-img {
    height: 48px;
    width: 48px;
    object-fit: contain;
    transition: transform 0.2s ease;
}

.download tr:hover .system-img {
    transform: scale(1.1);
}

.download tr td {
    vertical-align: middle;
    padding: 12px;
}

.download th {
    white-space: nowrap;
    font-weight: 500;
    color: var(--dark-color);
    border-bottom: 2px solid var(--primary-color);
}

.download-link {
    display: inline-block;
    color: var(--white-color);
    background-color: var(--success-color);
    border: none;
    padding: 8px 12px;
    border-radius: 4px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(46, 204, 113, 0.2);
}

.download-link:hover {
    background-color: #27ae60;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(46, 204, 113, 0.3);
}

.download-link i {
    margin-right: 5px;
}

.code-block {
    font-family: 'Courier New', monospace;
    background-color: var(--light-color);
    color: var(--dark-color);
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 0.9rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 300px;
    position: relative;
}

.code-block:hover {
    white-space: normal;
    word-break: break-all;
    cursor: pointer;
}

.copy-link {
    position: absolute;
    top: 4px;
    right: 4px;
    color: var(--gray-color);
    background-color: rgba(255, 255, 255, 0.8);
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    opacity: 0;
}

.code-block:hover .copy-link {
    opacity: 1;
}

.copy-link:hover {
    color: var(--primary-color);
    background-color: var(--white-color);
    transform: scale(1.1);
}

.download-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.download-card {
    background-color: var(--white-color);
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px var(--shadow-color);
    transition: all 0.3s ease;
    position: relative;
}

.download-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
}

.download-card-header {
    background-color: var(--light-color);
    padding: 20px;
    display: flex;
    align-items: center;
}

.download-card-header img {
    width: 48px;
    height: 48px;
    object-fit: contain;
    margin-right: 15px;
}

.download-card-header h3 {
    margin: 0;
    color: var(--dark-color);
    font-size: 1.2rem;
    font-weight: 500;
}

.download-card-body {
    padding: 15px 20px;
}

.architecture-item {
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.architecture-item:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.architecture-name {
    font-weight: 500;
    color: var(--dark-color);
}

.breadcrumb {
    display: flex;
    padding: 0;
    margin: 0 0 20px 0;
    list-style: none;
    background-color: transparent;
    border-radius: 4px;
}

.breadcrumb-item {
    font-size: 0.9rem;
    color: #6c757d;
}

.breadcrumb-item a {
    color: var(--primary-color);
    text-decoration: none;
}

.breadcrumb-item a:hover {
    text-decoration: underline;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: "/";
    display: inline-block;
    padding: 0 8px;
    color: #6c757d;
}

.version-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
    color: var(--white-color);
    background-color: var(--primary-color);
    margin-left: 10px;
}

.system-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    overflow-x: auto;
    padding-bottom: 5px;
}

.system-tab {
    display: flex;
    align-items: center;
    padding: 8px 15px;
    background-color: var(--light-color);
    border-radius: 30px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-weight: 500;
    border: 2px solid transparent;
}

.system-tab img {
    height: 24px;
    width: 24px;
    margin-right: 8px;
}

.system-tab:hover {
    background-color: rgba(52, 152, 219, 0.1);
}

.system-tab.active {
    background-color: rgba(52, 152, 219, 0.15);
    border-color: var(--primary-color);
    color: var(--primary-color);
}

.tab-content .tab-pane {
    display: none;
}

.tab-content .tab-pane.active {
    display: block;
    animation: fadeIn 0.4s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* 响应式调整 */
@media (max-width: 768px) {
    .download-cards {
        grid-template-columns: 1fr;
    }
    
    .code-block {
        max-width: 200px;
    }
}
</style>

<div class="content">
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="?page=panel">控制面板</a></li>
        <li class="breadcrumb-item">软件下载</li>
    </ul>
    
    <div class="content-header">
        <h1><i class="fas fa-download"></i> 客户端软件下载</h1>
        <span class="version-badge">v0.29.0</span>
    </div>
    
    <div class="container-fluid">
        <!-- 系统选项卡 -->
        <div class="system-tabs">
            <div class="system-tab active" onclick="showSystem('windows')">
                <img src="assets/download/windows.png" alt="Windows"> Windows
            </div>
            <div class="system-tab" onclick="showSystem('linux')">
                <img src="assets/download/linux.png" alt="Linux"> Linux
            </div>
            <div class="system-tab" onclick="showSystem('macos')">
                <img src="assets/download/macos.png" alt="MacOS"> MacOS
            </div>
            <div class="system-tab" onclick="showSystem('freebsd')">
                <img src="assets/download/freebsd.png" alt="FreeBSD"> FreeBSD
            </div>
        </div>
        
        <div class="tab-content">
            <!-- Windows 下载选项 -->
            <div class="tab-pane active" id="windows">
                <div class="download-cards">
                    <div class="download-card">
                        <div class="download-card-header">
                            <img src="assets/download/windows.png" class="system-img">
                            <h3>Windows</h3>
                        </div>
                        <div class="download-card-body">
                            <div class="architecture-item">
                                <span class="architecture-name">Windows i386 (32位)</span>
                                <a href="https://github.com/fatedier/frp/releases/download/v0.29.0/frp_0.29.0_windows_386.zip" target="_blank" class="download-link">
                                    <i class="fas fa-download"></i> 下载
                                </a>
                            </div>
                            <div class="architecture-item">
                                <span class="architecture-name">Windows amd64 (64位)</span>
                                <a href="https://github.com/fatedier/frp/releases/download/v0.29.0/frp_0.29.0_windows_amd64.zip" target="_blank" class="download-link">
                                    <i class="fas fa-download"></i> 下载
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Linux 下载选项 -->
            <div class="tab-pane" id="linux">
                <div class="download-cards">
                    <div class="download-card">
                        <div class="download-card-header">
                            <img src="assets/download/linux.png" class="system-img">
                            <h3>Linux 桌面/服务器</h3>
                        </div>
                        <div class="download-card-body">
                            <div class="architecture-item">
                                <span class="architecture-name">Linux i386 (32位)</span>
                                <a href="https://github.com/fatedier/frp/releases/download/v0.29.0/frp_0.29.0_linux_386.tar.gz" target="_blank" class="download-link">
                                    <i class="fas fa-download"></i> 下载
                                </a>
                            </div>
                            <div class="architecture-item">
                                <span class="architecture-name">Linux amd64 (64位)</span>
                                <a href="https://github.com/fatedier/frp/releases/download/v0.29.0/frp_0.29.0_linux_amd64.tar.gz" target="_blank" class="download-link">
                                    <i class="fas fa-download"></i> 下载
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="download-card">
                        <div class="download-card-header">
                            <img src="assets/download/linux.png" class="system-img">
                            <h3>Linux 嵌入式/ARM设备</h3>
                        </div>
                        <div class="download-card-body">
                            <div class="architecture-item">
                                <span class="architecture-name">Linux ARM</span>
                                <a href="https://github.com/fatedier/frp/releases/download/v0.29.0/frp_0.29.0_linux_arm.tar.gz" target="_blank" class="download-link">
                                    <i class="fas fa-download"></i> 下载
                                </a>
                            </div>
                            <div class="architecture-item">
                                <span class="architecture-name">Linux ARM64/AARCH64</span>
                                <a href="https://github.com/fatedier/frp/releases/download/v0.29.0/frp_0.29.0_linux_arm64.tar.gz" target="_blank" class="download-link">
                                    <i class="fas fa-download"></i> 下载
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="download-card">
                        <div class="download-card-header">
                            <img src="assets/download/linux.png" class="system-img">
                            <h3>Linux MIPS架构</h3>
                        </div>
                        <div class="download-card-body">
                            <div class="architecture-item">
                                <span class="architecture-name">Linux MIPS</span>
                                <a href="https://github.com/fatedier/frp/releases/download/v0.29.0/frp_0.29.0_linux_mips.tar.gz" target="_blank" class="download-link">
                                    <i class="fas fa-download"></i> 下载
                                </a>
                            </div>
                            <div class="architecture-item">
                                <span class="architecture-name">Linux MIPS64</span>
                                <a href="https://github.com/fatedier/frp/releases/download/v0.29.0/frp_0.29.0_linux_mips64.tar.gz" target="_blank" class="download-link">
                                    <i class="fas fa-download"></i> 下载
                                </a>
                            </div>
                            <div class="architecture-item">
                                <span class="architecture-name">Linux MIPSLE</span>
                                <a href="https://github.com/fatedier/frp/releases/download/v0.29.0/frp_0.29.0_linux_mipsle.tar.gz" target="_blank" class="download-link">
                                    <i class="fas fa-download"></i> 下载
                                </a>
                            </div>
                            <div class="architecture-item">
                                <span class="architecture-name">Linux MIPS64LE</span>
                                <a href="https://github.com/fatedier/frp/releases/download/v0.29.0/frp_0.29.0_linux_mips64le.tar.gz" target="_blank" class="download-link">
                                    <i class="fas fa-download"></i> 下载
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- MacOS 下载选项 -->
            <div class="tab-pane" id="macos">
                <div class="download-cards">
                    <div class="download-card">
                        <div class="download-card-header">
                            <img src="assets/download/macos.png" class="system-img">
                            <h3>MacOS</h3>
                        </div>
                        <div class="download-card-body">
                            <div class="architecture-item">
                                <span class="architecture-name">MacOS amd64 (Intel)</span>
                                <a href="https://github.com/fatedier/frp/releases/download/v0.29.0/frp_0.29.0_darwin_amd64.tar.gz" target="_blank" class="download-link">
                                    <i class="fas fa-download"></i> 下载
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- FreeBSD 下载选项 -->
            <div class="tab-pane" id="freebsd">
                <div class="download-cards">
                    <div class="download-card">
                        <div class="download-card-header">
                            <img src="assets/download/freebsd.png" class="system-img">
                            <h3>FreeBSD</h3>
                        </div>
                        <div class="download-card-body">
                            <div class="architecture-item">
                                <span class="architecture-name">FreeBSD i386 (32位)</span>
                                <a href="https://github.com/fatedier/frp/releases/download/v0.29.0/frp_0.29.0_freebsd_386.tar.gz" target="_blank" class="download-link">
                                    <i class="fas fa-download"></i> 下载
                                </a>
                            </div>
                            <div class="architecture-item">
                                <span class="architecture-name">FreeBSD amd64 (64位)</span>
                                <a href="https://github.com/fatedier/frp/releases/download/v0.29.0/frp_0.29.0_freebsd_amd64.tar.gz" target="_blank" class="download-link">
                                    <i class="fas fa-download"></i> 下载
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 使用说明 -->
        <div class="card mt-4">
            <div class="card-header border-0">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> 使用说明</h3>
            </div>
            <div class="card-body">
                <h4><i class="fas fa-cogs"></i> 配置安装方法</h4>
                <ol>
                    <li>在"<a href="?page=panel&module=configuration">配置文件获取</a>"页面复制您的配置。</li>
                    <li>在客户端的同级目录创建一个文本文档，命名为 <code>frpc.ini</code></li>
                    <li>使用 Notepad++ 等专业的文本编辑器打开它</li>
                    <li>将复制的内容粘贴到里面并保存。</li>
                </ol>
                
                <h4><i class="fas fa-play-circle"></i> 客户端启动方法</h4>
                <ol>
                    <li>按照上面的方法储存好你的配置文件。</li>
                    <li>在客户端的目录里按住 Shift + 鼠标右键。</li>
                    <li>点击 "在此处打开命令窗口"。</li>
                    <li>输入命令 <code>frpc.exe -c frpc.ini</code> 并按下回车启动。</li>
                    <li>保持命令提示符窗口打开，不要关闭它，否则映射会中断。</li>
                </ol>
                
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>注意：</strong> 请勿泄露配置文件中 user 字段的内容，否则他人可以登录您的账号。如不慎泄露，请立即修改密码。
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 添加Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<script>
// 显示不同系统的下载选项
function showSystem(systemType) {
    // 隐藏所有选项卡内容
    document.querySelectorAll('.tab-pane').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // 取消选中所有选项卡
    document.querySelectorAll('.system-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // 显示选定的系统选项卡
    document.getElementById(systemType).classList.add('active');
    
    // 高亮显示当前选项卡
    document.querySelector(`.system-tab[onclick="showSystem('${systemType}')"]`).classList.add('active');
}

// 复制下载链接到剪贴板
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('链接已复制到剪贴板');
    }).catch(err => {
        console.error('无法复制链接: ', err);
    });
}
</script>