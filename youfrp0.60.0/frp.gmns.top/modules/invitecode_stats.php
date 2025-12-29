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

    // 获取未使用的邀请码数量
    $unused_query = "SELECT COUNT(*) as unused FROM invitecode WHERE user IS NULL";
    $unused_result = $conn->query($unused_query);
    $stats['unused'] = $unused_result->fetch_assoc()['unused'];

    // 计算已使用的邀请码数量
    $stats['used'] = $stats['total'] - $stats['unused'];

    // 获取所有已使用邀请码
    $used_query = "SELECT * FROM invitecode WHERE user IS NOT NULL ORDER BY code DESC";
    $used_result = $conn->query($used_query);
    $stats['used_list'] = $used_result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $error = "操作出错: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>邀请码使用统计</title>
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
            content: '\f080';
            font-family: 'Font Awesome 5 Free';
            margin-right: 10px;
            color: var(--success-color);
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
        .stat-card.unused {
            border-top-color: var(--danger-color);
        }
        .stat-card.used {
            border-top-color: var(--success-color);
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
        .stat-card.unused .stat-value { color: var(--danger-color); }
        .stat-card.used .stat-value { color: var(--success-color); }
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
            margin-top: 20px;
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
        .search-container {
            display: flex;
            gap: 10px;
            width: 100%;
            max-width: 500px;
        }
        .search-input {
            flex-grow: 1;
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 0.9rem;
        }
        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
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
        .status-badge.used {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--success-color);
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
            background-color: var(--success-color);
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
        .user-link {
            color: var(--primary-color);
            text-decoration: none;
            position: relative;
        }
        .user-link:hover {
            text-decoration: underline;
        }
        .user-link .user-tooltip {
            visibility: hidden;
            position: absolute;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            background-color: rgba(0,0,0,0.8);
            color: white;
            text-align: center;
            border-radius: 4px;
            padding: 5px 10px;
            z-index: 1;
            opacity: 0;
            transition: opacity 0.3s;
            width: 200px;
            font-size: 0.8rem;
        }
        .user-link:hover .user-tooltip {
            visibility: visible;
            opacity: 1;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            list-style: none;
            padding: 0;
        }
        .pagination li {
            margin: 0 5px;
        }
        .pagination a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            color: var(--dark-color);
            text-decoration: none;
            transition: all 0.3s;
        }
        .pagination a:hover {
            background-color: rgba(52, 152, 219, 0.1);
        }
        .pagination .active a {
            background-color: var(--primary-color);
            color: white;
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
            .search-container {
                max-width: 100%;
                flex-direction: column;
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
            <li class="breadcrumb-item">邀请码统计</li>
        </ul>
        
        <div class="page-header">
            <h1><i class="fas fa-chart-bar"></i> 邀请码使用统计</h1>
            <div class="action-buttons">
                <a href="?page=panel&module=invitecode" class="btn btn-primary"><i class="fas fa-ticket-alt"></i> 邀请码管理</a>
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
            
            <div class="stat-card unused">
                <div class="stat-icon"><i class="fas fa-ticket-alt"></i></div>
                <h3>未使用邀请码</h3>
                <div class="stat-value"><?= $stats['unused'] ?? 0 ?></div>
                <p>尚未被使用的可用邀请码数量</p>
            </div>
            
            <div class="stat-card used">
                <div class="stat-icon"><i class="fas fa-user-check"></i></div>
                <h3>已使用邀请码</h3>
                <div class="stat-value"><?= $stats['used'] ?? 0 ?></div>
                <p>已被用户使用的邀请码数量</p>
            </div>
        </div>
        
        <div class="action-bar">
            <h2 class="section-title"><i class="fas fa-list-alt"></i> 已使用的邀请码列表</h2>
            <div class="search-container">
                <input type="text" id="searchInput" class="search-input" placeholder="搜索用户或邀请码..." onkeyup="searchTable()">
                <button class="btn btn-primary" onclick="searchTable()"><i class="fas fa-search"></i> 搜索</button>
            </div>
        </div>
        
        <div class="table-container">
            <?php if (!empty($stats['used_list'])): ?>
                <table id="usedCodesTable">
                    <thead>
                        <tr>
                            <th><i class="fas fa-ticket-alt"></i> 邀请码</th>
                            <th><i class="fas fa-user"></i> 使用者</th>
                            <th><i class="fas fa-info-circle"></i> 状态</th>
                            <th><i class="fas fa-clock"></i> 使用时间</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['used_list'] as $item): ?>
                            <tr>
                                <td><span class="code-badge"><?= htmlspecialchars($item['code']) ?></span></td>
                                <td>
                                    <a href="javascript:void(0)" class="user-link" onclick="viewUserDetails('<?= htmlspecialchars($item['user']) ?>')">
                                        <?= htmlspecialchars($item['user']) ?>
                                        <span class="user-tooltip">点击查看用户详情</span>
                                    </a>
                                </td>
                                <td><span class="status-badge used">已使用</span></td>
                                <td><?= isset($item['used_time']) ? htmlspecialchars($item['used_time']) : '未知时间' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <ul class="pagination">
                    <li><a href="#" onclick="prevPage()"><i class="fas fa-chevron-left"></i></a></li>
                    <li class="active"><a href="#" onclick="goToPage(1)">1</a></li>
                    <li><a href="#" onclick="goToPage(2)">2</a></li>
                    <li><a href="#" onclick="goToPage(3)">3</a></li>
                    <li><a href="#" onclick="nextPage()"><i class="fas fa-chevron-right"></i></a></li>
                </ul>
            <?php else: ?>
                <div class="no-data">当前没有已使用的邀请码</div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // 搜索表格功能
        function searchTable() {
            const input = document.getElementById("searchInput");
            const filter = input.value.toUpperCase();
            const table = document.getElementById("usedCodesTable");
            const tr = table.getElementsByTagName("tr");
            
            let hasResults = false;
            
            // 从索引1开始，跳过表头
            for (let i = 1; i < tr.length; i++) {
                const codeColumn = tr[i].getElementsByTagName("td")[0];
                const userColumn = tr[i].getElementsByTagName("td")[1];
                
                if (codeColumn && userColumn) {
                    const codeText = codeColumn.textContent || codeColumn.innerText;
                    const userText = userColumn.textContent || userColumn.innerText;
                    
                    if (codeText.toUpperCase().indexOf(filter) > -1 || userText.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                        hasResults = true;
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
            
            // 如果没有搜索结果，显示提示信息
            if (!hasResults && filter !== "") {
                // 检查是否已存在"无结果"行
                let noResultsRow = document.getElementById("noResultsRow");
                if (!noResultsRow) {
                    // 创建一个新行显示"无结果"
                    const tbody = table.getElementsByTagName("tbody")[0];
                    noResultsRow = document.createElement("tr");
                    noResultsRow.id = "noResultsRow";
                    const td = document.createElement("td");
                    td.colSpan = 4;
                    td.style.textAlign = "center";
                    td.style.padding = "20px";
                    td.style.fontStyle = "italic";
                    td.style.color = "#95a5a6";
                    td.innerHTML = `未找到与 "${filter}" 相关的结果`;
                    noResultsRow.appendChild(td);
                    tbody.appendChild(noResultsRow);
                }
            } else {
                // 如果有搜索结果或搜索框为空，移除"无结果"行
                const noResultsRow = document.getElementById("noResultsRow");
                if (noResultsRow) {
                    noResultsRow.remove();
                }
            }
        }
        
        // 查看用户详情功能
        function viewUserDetails(username) {
            // 这里可以实现查看用户详情的功能，例如弹窗或跳转到用户详情页
            alert(`查看用户 ${username} 的详情功能需要后端支持，请联系管理员实现`);
        }
        
        // 分页功能
        let currentPage = 1;
        const rowsPerPage = 10;
        
        function showPage(page) {
            const table = document.getElementById("usedCodesTable");
            const rows = table.getElementsByTagName("tr");
            const totalRows = rows.length - 1; // 减去表头
            const startRow = (page - 1) * rowsPerPage + 1;
            const endRow = Math.min(page * rowsPerPage, totalRows);
            
            // 隐藏所有行
            for (let i = 1; i <= totalRows; i++) {
                rows[i].style.display = "none";
            }
            
            // 显示当前页的行
            for (let i = startRow; i <= endRow; i++) {
                rows[i].style.display = "";
            }
            
            // 更新分页UI
            const paginationLinks = document.querySelectorAll(".pagination li");
            paginationLinks.forEach(li => li.classList.remove("active"));
            
            // 如果页码在链接中，则标记为活动
            for (let i = 0; i < paginationLinks.length; i++) {
                const pageNum = paginationLinks[i].textContent;
                if (pageNum == page) {
                    paginationLinks[i].classList.add("active");
                    break;
                }
            }
        }
        
        function prevPage() {
            if (currentPage > 1) {
                currentPage--;
                showPage(currentPage);
            }
            return false;
        }
        
        function nextPage() {
            const table = document.getElementById("usedCodesTable");
            const rows = table.getElementsByTagName("tr");
            const totalRows = rows.length - 1;
            const totalPages = Math.ceil(totalRows / rowsPerPage);
            
            if (currentPage < totalPages) {
                currentPage++;
                showPage(currentPage);
            }
            return false;
        }
        
        function goToPage(page) {
            currentPage = page;
            showPage(currentPage);
            return false;
        }
        
        // 初始化显示第一页
        window.onload = function() {
            if (document.getElementById("usedCodesTable")) {
                showPage(1);
            }
        };
    </script>
</body>
</html>