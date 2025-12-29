<?php
namespace chhcn;

use chhcn;

//$page_title = "用户列表1";
$um = new chhcn\UserManager();
$rs = Database::querySingleLine("users", Array("username" => $_SESSION['user']));

if(!$rs || $rs['group'] !== "admin") {
	exit("<script>location='?page=panel';</script>");
}

// 包含数据库连接文件
require_once '.../../chh.php';
// 初始化变量
$stats = [];
$error = '';

try {
    // 获取总邀请码数量
    $total_query = "SELECT COUNT(*) as total FROM invitecode";
    $total_result = $conn->query($total_query);
    $stats['total'] = $total_result->fetch_assoc()['total'];

    // 获取已使用的邀请码数量
    $used_query = "SELECT COUNT(*) as used FROM invitecode WHERE user IS NOT NULL";
    $used_result = $conn->query($used_query);
    $stats['used'] = $used_result->fetch_assoc()['used'];

    // 计算未使用的邀请码数量
    $stats['unused'] = $stats['total'] - $stats['used'];

    // 获取所有未使用邀请码
    $unused_query = "SELECT * FROM invitecode WHERE user IS NULL ORDER BY code DESC";
    $unused_result = $conn->query($unused_query);
    $stats['unused_list'] = $unused_result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $error = "操作出错: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>邀请码管理</title>
    <style>
        :root {
            --primary-color: #3498db;
            --success-color: #2ecc71;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --info-color: #1abc9c;
            --light-color: #f8f9fa;
            --dark-color: #2c3e50;
            --border-color: #e0e0e0;
            --shadow-color: rgba(0,0,0,0.1);
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: var(--light-color);
            color: var(--dark-color);
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px var(--shadow-color);
        }
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border-color);
        }
        .page-header h1 {
            color: var(--dark-color);
            margin: 0;
            font-weight: 600;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
        }
        .page-header h1:before {
            content: '\f023';
            font-family: 'Font Awesome 5 Free';
            margin-right: 10px;
            color: var(--primary-color);
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
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 3px 10px var(--shadow-color);
            text-align: center;
            transition: all 0.3s ease;
            border-top: 4px solid transparent;
            position: relative;
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }
        .stat-card.total {
            border-top-color: var(--primary-color);
        }
        .stat-card.used {
            border-top-color: var(--success-color);
        }
        .stat-card.unused {
            border-top-color: var(--danger-color);
        }
        .stat-card:before {
            content: '';
            position: absolute;
            top: -15px;
            right: -15px;
            background: rgba(255,255,255,0.1);
            width: 100px;
            height: 100px;
            border-radius: 50%;
            z-index: 0;
        }
        .stat-card h3 {
            margin-top: 0;
            color: var(--dark-color);
            font-size: 1.3rem;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }
        .stat-value {
            font-size: 2.8rem;
            font-weight: bold;
            margin: 15px 0;
            font-family: 'Arial', sans-serif;
            position: relative;
            z-index: 1;
        }
        .stat-card.total .stat-value { color: var(--primary-color); }
        .stat-card.used .stat-value { color: var(--success-color); }
        .stat-card.unused .stat-value { color: var(--danger-color); }
        .stat-card p {
            color: #666;
            margin: 0;
            font-size: 0.95rem;
            position: relative;
            z-index: 1;
        }
        .stat-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 24px;
            opacity: 0.2;
            color: var(--dark-color);
        }
        .table-container {
            margin-top: 40px;
            overflow-x: auto;
            border-radius: 8px;
            box-shadow: 0 2px 10px var(--shadow-color);
        }
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .btn {
            display: inline-block;
            font-weight: 400;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.25rem;
            transition: all 0.15s ease-in-out;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-primary {
            color: #fff;
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }
        .btn-success {
            color: #fff;
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        .btn-success:hover {
            background-color: #27ae60;
            border-color: #27ae60;
        }
        .btn-danger {
            color: #fff;
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }
        .btn-danger:hover {
            background-color: #c0392b;
            border-color: #c0392b;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.2rem;
        }
        .btn i {
            margin-right: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        th, td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        th {
            background-color: #f5f7fa;
            color: var(--dark-color);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        tr:hover {
            background-color: #f8fafc;
        }
        .code-badge {
            font-family: 'Courier New', monospace;
            background-color: #f5f7fa;
            padding: 4px 8px;
            border-radius: 3px;
            border: 1px solid #ddd;
            font-size: 0.9rem;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-badge.unused {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }
        .section-title {
            margin: 50px 0 20px;
            color: var(--dark-color);
            font-size: 1.5rem;
            font-weight: 500;
            position: relative;
            padding-left: 15px;
            display: flex;
            align-items: center;
        }
        .section-title:before {
            content: '';
            position: absolute;
            left: 0;
            top: 5px;
            bottom: 5px;
            width: 4px;
            background-color: var(--primary-color);
            border-radius: 2px;
        }
        .error {
            color: var(--danger-color);
            background-color: #fdecea;
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 25px;
            border-left: 4px solid var(--danger-color);
            display: flex;
            align-items: center;
        }
        .error:before {
            content: '!';
            display: inline-block;
            width: 24px;
            height: 24px;
            background-color: var(--danger-color);
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 24px;
            margin-right: 10px;
            font-weight: bold;
        }
        .no-data {
            text-align: center;
            padding: 40px 20px;
            color: #95a5a6;
            font-style: italic;
            background-color: #f9f9f9;
            border-radius: 8px;
        }
        .no-data:before {
            content: '🛈';
            display: block;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .actions {
            display: flex;
            gap: 5px;
        }
        .actions a {
            color: var(--primary-color);
            text-decoration: none;
            padding: 4px 8px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }
        .actions a:hover {
            background-color: rgba(52, 152, 219, 0.1);
        }
        .action-icon {
            font-size: 1.1rem;
        }
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
            th, td {
                padding: 12px 15px;
            }
        }
    </style>
    <!-- 添加Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="container">
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="?page=panel">控制面板</a></li>
            <li class="breadcrumb-item">管理员功能</li>
            <li class="breadcrumb-item">邀请码管理</li>
        </ul>
        
        <div class="page-header">
            <h1><i class="fas fa-ticket-alt"></i> 邀请码管理</h1>
            <div class="action-buttons">
                <button class="btn btn-primary" onclick="generateCodes()"><i class="fas fa-plus"></i> 生成邀请码</button>
                <button class="btn btn-success" onclick="exportCodes()"><i class="fas fa-file-export"></i> 导出邀请码</button>
            </div>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-icon"><i class="fas fa-tags"></i></div>
                <h3>总邀请码数量</h3>
                <div class="stat-value"><?= $stats['total'] ?? 0 ?></div>
                <p>系统中所有的邀请码总数</p>
            </div>
            
            <div class="stat-card used">
                <div class="stat-icon"><i class="fas fa-user-check"></i></div>
                <h3>已使用邀请码</h3>
                <div class="stat-value"><?= $stats['used'] ?? 0 ?></div>
                <p>已被用户使用的邀请码数量</p>
            </div>
            
            <div class="stat-card unused">
                <div class="stat-icon"><i class="fas fa-ticket-alt"></i></div>
                <h3>未使用邀请码</h3>
                <div class="stat-value"><?= $stats['unused'] ?? 0 ?></div>
                <p>尚未被使用的可用邀请码数量</p>
            </div>
        </div>
        
        <div class="action-bar">
            <h2 class="section-title"><i class="fas fa-list"></i> 未使用的邀请码列表</h2>
            <div class="action-buttons">
                <button class="btn btn-sm btn-primary" onclick="copyAllCodes()"><i class="fas fa-copy"></i> 复制全部</button>
                <button class="btn btn-sm btn-danger" onclick="deleteAllCodes()"><i class="fas fa-trash"></i> 删除全部</button>
            </div>
        </div>
        
        <div class="table-container">
            <?php if (!empty($stats['unused_list'])): ?>
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all" onchange="toggleAllCodes()"></th>
                            <th>邀请码</th>
                            <th>状态</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['unused_list'] as $item): ?>
                            <tr>
                                <td><input type="checkbox" class="code-checkbox" data-code="<?= htmlspecialchars($item['code']) ?>"></td>
                                <td><span class="code-badge"><?= htmlspecialchars($item['code']) ?></span></td>
                                <td><span class="status-badge unused">未使用</span></td>
                                <td>
                                    <div class="actions">
                                        <a href="javascript:void(0)" onclick="copyToClipboard('<?= htmlspecialchars($item['code']) ?>')" title="复制"><i class="fas fa-copy action-icon"></i></a>
                                        <a href="javascript:void(0)" onclick="deleteCode('<?= htmlspecialchars($item['code']) ?>')" title="删除"><i class="fas fa-trash action-icon"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">当前没有未使用的邀请码</div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // 复制单个邀请码到剪贴板
        function copyToClipboard(code) {
            navigator.clipboard.writeText(code).then(() => {
                alert('邀请码已复制到剪贴板');
            }).catch(err => {
                console.error('无法复制到剪贴板: ', err);
            });
        }
        
        // 复制所有选中的邀请码
        function copyAllCodes() {
            const checkboxes = document.querySelectorAll('.code-checkbox:checked');
            if (checkboxes.length === 0) {
                alert('请先选择要复制的邀请码');
                return;
            }
            
            const codes = Array.from(checkboxes).map(cb => cb.getAttribute('data-code')).join('\n');
            navigator.clipboard.writeText(codes).then(() => {
                alert('所有选中的邀请码已复制到剪贴板');
            }).catch(err => {
                console.error('无法复制到剪贴板: ', err);
            });
        }
        
        // 删除单个邀请码
        function deleteCode(code) {
            if (confirm('确定要删除此邀请码吗？')) {
                alert('删除功能需要后端支持，请联系管理员实现');
                // 这里需要添加AJAX请求来删除邀请码
            }
        }
        
        // 删除所有选中的邀请码
        function deleteAllCodes() {
            const checkboxes = document.querySelectorAll('.code-checkbox:checked');
            if (checkboxes.length === 0) {
                alert('请先选择要删除的邀请码');
                return;
            }
            
            if (confirm(`确定要删除选中的 ${checkboxes.length} 个邀请码吗？`)) {
                alert('批量删除功能需要后端支持，请联系管理员实现');
                // 这里需要添加AJAX请求来批量删除邀请码
            }
        }
        
        // 生成邀请码
        function generateCodes() {
            const count = prompt('请输入要生成的邀请码数量:', '10');
            if (count !== null) {
                alert('生成邀请码功能需要后端支持，请联系管理员实现');
                // 这里需要添加AJAX请求来生成邀请码
            }
        }
        
        // 导出邀请码
        function exportCodes() {
            alert('导出功能需要后端支持，请联系管理员实现');
            // 这里需要添加导出功能
        }
        
        // 全选/取消全选邀请码
        function toggleAllCodes() {
            const selectAll = document.getElementById('select-all');
            const checkboxes = document.querySelectorAll('.code-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = selectAll.checked;
            });
        }
    </script>
</body>
</html>