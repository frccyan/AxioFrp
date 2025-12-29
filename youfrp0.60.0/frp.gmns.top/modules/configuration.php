<?php
namespace chhcn;

use chhcn;

$pm = new chhcn\ProxyManager();
//$page_title = "配置文件";
$rs = Database::querySingleLine("users", Array("username" => $_SESSION['user']));

if(!$rs) {
	exit("<script>location='?page=login';</script>");
}

$sel_server = isset($_GET['server']) && preg_match("/^[0-9]+$/", $_GET['server']) ? Intval($_GET['server']) : 0;
if($sel_server <= 0) {
	$sel_server = 1;
}
$ss = Database::toArray(Database::search("nodes", Array("group" => "{$rs['group']};", "status" => "200")));
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
    --border-color: #dee2e6;
    --shadow-color: rgba(0, 0, 0, 0.1);
}

body {
    font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

.fix-text p {
    margin-bottom: 8px;
}

.sub-heading {
    width: calc(100% - 16px);
    height: 0 !important;
    border-top: 1px solid var(--border-color) !important;
    text-align: center !important;
    margin: 1.5rem 0 2rem 8px !important;
    position: relative;
}

.sub-heading span {
    display: inline-block;
    position: relative;
    padding: 0 17px;
    top: -11px;
    font-size: 1.1rem;
    color: var(--primary-color);
    background-color: #fff;
    font-weight: 500;
}

.breadcrumb {
    display: flex;
    padding: 0;
    margin: 0 0 15px 0;
    list-style: none;
    background-color: transparent;
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

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border-color);
}

.page-title {
    font-size: 1.5rem;
    font-weight: 500;
    margin: 0;
    display: flex;
    align-items: center;
}

.page-title i {
    margin-right: 10px;
    color: var(--primary-color);
}

.config-server-select {
    display: block;
    width: 100%;
    padding: 8px 12px;
    font-size: 1rem;
    line-height: 1.5;
    color: var(--dark-color);
    background-color: #fff;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.config-server-select:focus {
    border-color: var(--primary-color);
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
}

.config-server-select option {
    padding: 10px;
}

.config-server-select option:first-child {
    font-weight: 500;
    color: var(--gray-color);
}

.config-viewer {
    position: relative;
    margin-top: 20px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px var(--shadow-color);
}

.config-header {
    background-color: #f5f7fa;
    padding: 10px 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--border-color);
}

.config-title {
    font-weight: 500;
    color: var(--dark-color);
    margin: 0;
    font-size: 1.1rem;
}

.config-actions {
    display: flex;
    gap: 10px;
}

.config-btn {
    background-color: var(--white-color);
    border: 1px solid var(--border-color);
    color: var(--dark-color);
    padding: 6px 12px;
    font-size: 0.9rem;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
}

.config-btn:hover {
    background-color: #f5f7fa;
}

.config-btn i {
    margin-right: 5px;
}

.config-btn.copy {
    color: var(--primary-color);
    border-color: var(--primary-color);
}

.config-btn.copy:hover {
    background-color: rgba(52, 152, 219, 0.1);
}

.config-btn.download {
    color: var(--success-color);
    border-color: var(--success-color);
}

.config-btn.download:hover {
    background-color: rgba(46, 204, 113, 0.1);
}

.config-content {
    max-height: 500px;
    overflow: auto;
}

.config-pre {
    margin: 0;
    padding: 15px;
    font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
    font-size: 0.9rem;
    line-height: 1.5;
    background-color: #2c3e50;
    color: #ecf0f1;
    counter-reset: line;
}

.config-pre code {
    display: block;
    position: relative;
    padding-left: 3.8em;
}

.config-pre code:before {
    counter-increment: line;
    content: counter(line);
    position: absolute;
    left: 0;
    width: 3em;
    text-align: right;
    color: #7f8c8d;
    padding-right: 0.5em;
    border-right: 1px solid #7f8c8d;
    user-select: none;
}

.config-instruction {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px var(--shadow-color);
    margin-top: 20px;
    overflow: hidden;
}

.instruction-header {
    background-color: #f5f7fa;
    padding: 12px 15px;
    border-bottom: 1px solid var(--border-color);
}

.instruction-title {
    margin: 0;
    font-weight: 500;
    color: var(--dark-color);
    font-size: 1.1rem;
    display: flex;
    align-items: center;
}

.instruction-title i {
    margin-right: 8px;
    color: var(--primary-color);
}

.instruction-body {
    padding: 15px;
}

.instruction-section {
    margin-bottom: 20px;
}

.instruction-section h4 {
    font-size: 1rem;
    font-weight: 500;
    margin-bottom: 10px;
    color: var(--dark-color);
    display: flex;
    align-items: center;
}

.instruction-section h4 i {
    margin-right: 8px;
    color: var(--info-color);
}

.instruction-list {
    padding-left: 25px;
    margin-bottom: 0;
}

.instruction-list li {
    margin-bottom: 8px;
}

.instruction-list li:last-child {
    margin-bottom: 0;
}

.instruction-warning {
    margin-top: 20px;
    background-color: rgba(243, 156, 18, 0.1);
    border-left: 4px solid var(--warning-color);
    padding: 12px 15px;
    color: #955305;
    display: flex;
    align-items: center;
    border-radius: 0 4px 4px 0;
}

.instruction-warning i {
    font-size: 1.2rem;
    margin-right: 10px;
    color: var(--warning-color);
}

code {
    font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
    background-color: #f5f7fa;
    color: var(--danger-color);
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 0.9rem;
}

.empty-state {
    padding: 40px 20px;
    text-align: center;
    background-color: #f9f9f9;
    border-radius: 8px;
    color: var(--gray-color);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 15px;
    color: var(--gray-color);
    opacity: 0.5;
}

.empty-state h3 {
    font-size: 1.2rem;
    margin-bottom: 10px;
    color: var(--dark-color);
}
</style>
<link href="assets/configuration/prettify.css" rel="stylesheet">

<div class="content">
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="?page=panel">控制面板</a></li>
        <li class="breadcrumb-item">配置管理</li>
        <li class="breadcrumb-item">配置文件获取</li>
    </ul>

    <div class="page-header">
        <h2 class="page-title"><i class="fas fa-cog"></i> 配置文件获取</h2>
    </div>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <!-- 服务器选择 -->
                <div class="form-group">
                    <label for="server" class="font-weight-bold">
                        <i class="fas fa-server"></i> 选择服务器
                    </label>
                    <select class="form-control config-server-select" id="server" <?php echo count($ss) == 0 ? "disabled" : ""; ?>>
                    <?php
                        echo "<option value=''>-- 选择要连接的服务器 --</option>";
                        foreach($ss as $si) {
                            $selected = $sel_server == $si[0] ? "selected" : "";
                            echo "<option value='{$si[0]}' {$selected}>{$si[1]} ({$si[3]})</option>";
                        }
                        if(count($ss) == 0) {
                            echo "<option>没有可用的服务器</option>";
                        }
                    ?>
                    </select>
                </div>
                
                <!-- 配置文件内容 -->
                <?php if(count($ss) !== 0): ?>
                <div class="config-viewer">
                    <div class="config-header">
                        <h3 class="config-title">配置文件内容</h3>
                        <div class="config-actions">
                            <button class="config-btn copy" onclick="copyConfig()">
                                <i class="fas fa-copy"></i> 复制配置
                            </button>
                            <button class="config-btn download" onclick="downloadConfig()">
                                <i class="fas fa-download"></i> 下载配置文件
                            </button>
                        </div>
                    </div>
                    <div class="config-content">
                        <pre class="config-pre prettyprint linenums" id="configContent"><?php echo $pm->getUserProxiesConfig($_SESSION['user'], $sel_server); ?></pre>
                    </div>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-exclamation-circle"></i>
                    <h3>无可用服务器</h3>
                    <p>当前所有服务器都不可用，请联系管理员。</p>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="col-lg-4">
                <div class="config-instruction">
                    <div class="instruction-header">
                        <h3 class="instruction-title">
                            <i class="fas fa-info-circle"></i> 配置文件说明
                        </h3>
                    </div>
                    <div class="instruction-body">
                        <p>每次创建完映射或删除了映射之后配置文件都会发生变化，请在变更后及时更新您的配置文件。</p>
                        
                        <div class="instruction-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div>
                                <strong>安全提示：</strong> 请勿泄露配置文件中 user 字段的内容，否则他人可以登录您的账号，截图注意打码。如不慎泄露，请立即修改密码。
                            </div>
                        </div>
                        
                        <div class="sub-heading">
                            <span>配置安装方法</span>
                        </div>
                        
                        <div class="instruction-section">
                            <h4><i class="fas fa-wrench"></i> 配置安装步骤</h4>
                            <ol class="instruction-list">
                                <li>点击上方的 <strong>"复制配置"</strong> 按钮复制配置内容。</li>
                                <li>在客户端的同级目录创建一个文本文档，命名为 <code>frpc.ini</code> 。</li>
                                <li>使用 Notepad++ 等专业的文本编辑器打开它。</li>
                                <li>将复制的内容粘贴到里面并保存。</li>
                            </ol>
                        </div>
                        
                        <div class="sub-heading">
                            <span>客户端启动方法</span>
                        </div>
                        
                        <div class="instruction-section">
                            <h4><i class="fas fa-play-circle"></i> 启动步骤</h4>
                            <ol class="instruction-list">
                                <li>按照上面的方法储存好你的配置文件。</li>
                                <li>在客户端的目录里按住 <code>Shift + 鼠标右键</code>。</li>
                                <li>点击 "在此处打开命令窗口"。</li>
                                <li>输入命令 <code>frpc.exe -c frpc.toml</code> 并按下回车启动。</li>
                                <li>保持命令提示符窗口打开，不要关闭它，否则映射会中断。</li>
                            </ol>
                        </div>
                        
                        <div class="sub-heading">
                            <span>常见问题</span>
                        </div>
                        
                        <div class="instruction-section">
                            <h4><i class="fas fa-question-circle"></i> 启动失败怎么办？</h4>
                            <ul class="instruction-list">
                                <li>检查配置文件是否正确保存。</li>
                                <li>检查防火墙设置，确保程序能够访问网络。</li>
                                <li>查看错误消息，根据提示修复问题。</li>
                                <li>如果仍然有问题，请联系客服寻求帮助。</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 添加Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<script type="text/javascript" src="assets/configuration/prettify.js"></script>
<script type="text/javascript">
    prettyPrint();
    window.onload = function() {
        $('#server').change(function() {
            if ($(this).children('option:selected').val() !== '') {
                location = "/?page=panel&module=configuration&server=" + $(this).children('option:selected').val();
            }
        });
    }
    
    // 复制配置到剪贴板
    function copyConfig() {
        const configContent = document.getElementById('configContent').innerText;
        navigator.clipboard.writeText(configContent).then(() => {
            alert('配置文件已复制到剪贴板');
        }).catch(err => {
            console.error('无法复制配置: ', err);
            // 兼容性处理
            const textarea = document.createElement('textarea');
            textarea.value = configContent;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            alert('配置文件已复制到剪贴板');
        });
    }
    
    // 下载配置文件
    function downloadConfig() {
        const configContent = document.getElementById('configContent').innerText;
        const blob = new Blob([configContent], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'frpc.toml';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
</script>
