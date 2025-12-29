<?php
namespace chhcn;

use chhcn;

// 优先处理导出请求
if (isset($_GET['module']) && isset($_GET['export'])) {
    $module = $_GET['module'];
    $exportType = $_GET['export'];

    if ($module === 'redeemcodes' && in_array($exportType, ['all', 'used', 'unused'])) {
        $rcm = new RedeemCodeManager();
        $codesToExport = $rcm->getAllCodesByStatus($exportType);

        if (empty($codesToExport)) {
            die("<script>alert('没有可导出的兑换码。');window.history.back();</script>");
        }

        $filename = "redeemcodes_{$exportType}_" . date('Ymd') . ".txt";
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        foreach ($codesToExport as $code) {
            echo $code['code'] . "\n";
        }
        exit;
    }

    if ($module === 'packages' && $exportType === 'excel') {
        $pm = new PackageManager();
        $orders = $pm->getAllOrders();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=package_orders_' . date('Ymd') . '.csv');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // 添加BOM头
        fputcsv($output, ['订单ID', '用户名', '套餐名称', '价格', '购买时间', '到期时间']);

        foreach ($orders as $order) {
            $expireTime = $order['expire_time'] > 0 ? date('Y-m-d H:i:s', intval($order['expire_time'])) : '永久';
            fputcsv($output, [
                $order['id'],
                $order['username'],
                $order['name'] ?? '未知套餐',
                number_format(floatval($order['price']), 2),
                date('Y-m-d H:i:s', intval($order['order_time'])),
                $expireTime
            ]);
        }
        fclose($output);
        exit;
    }
}


global $_config;
$module = $_GET['module'] ?? "";

$rs = Database::querySingleLine("users", Array("username" => $_SESSION['user']));
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>管理面板 :: <?php echo $_config['sitename']; ?> - <?php echo $_config['description']; ?></title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AdminLTE 3 CSS -->
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- 自定义样式 -->
    <style>
        :root {
            --primary-color: #4e73df;
            --primary-hover: #2e59d9;
            --secondary-color: #6c757d;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --light-color: #f8f9fc;
            --dark-color: #3a3b45;
            --sidebar-bg: #1a1c29;
            --sidebar-dark: #131523;
            --sidebar-light: #202340;
            --sidebar-hover: rgba(255,255,255,0.15);
            --sidebar-active: var(--primary-color);
            --sidebar-active-hover: #4262bf;
            --sidebar-header: rgba(255,255,255,0.8);
            --card-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 0.95rem;
            background-color: var(--light-color);
        }
        
        /* 主色调调整 */
        .bg-primary, .btn-primary {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-hover) !important;
            border-color: var(--primary-hover) !important;
        }
        
        .text-primary {
            color: var(--primary-color) !important;
        }
        
        /* 侧边栏样式调整 */
        .main-sidebar {
            background: linear-gradient(180deg, var(--sidebar-bg) 0%, var(--sidebar-dark) 100%);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border-right: none;
        }
        
        .nav-sidebar > .nav-item > .nav-link.active {
            background: linear-gradient(90deg, var(--sidebar-active) 0%, var(--sidebar-active-hover) 100%);
            color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            font-weight: 600;
        }
        
        .nav-sidebar .nav-item > .nav-link {
            color: rgba(255, 255, 255, 0.7);
            border-radius: 0.35rem;
            margin: 0.2rem 0.8rem;
            padding: 0.65rem 1rem;
        }
        
        .nav-sidebar .nav-item > .nav-link:hover {
            background-color: var(--sidebar-hover);
            color: rgba(255, 255, 255, 0.9);
        }
        
        .brand-link {
            background: var(--sidebar-dark);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
            padding: 1rem !important;
        }
        
        .brand-link .brand-text {
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .sidebar .nav-header {
            color: var(--sidebar-header);
            font-size: 0.85rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            padding: 1.2rem 1rem 0.6rem 1.3rem;
            opacity: 0.6;
        }
        
        /* 用户面板 */
        .user-panel {
            padding: 0.8rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            margin-bottom: 1rem;
        }
        
        .user-panel img {
            height: 2.1rem;
            width: 2.1rem;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .user-panel .info a {
            color: rgba(255, 255, 255, 0.8);
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        /* 顶部导航栏 */
        .main-header {
            background-color: #fff;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            border: none;
            padding: 0.3rem 0;
        }
        
        .main-header .navbar-nav .nav-link {
            color: var(--dark-color);
            padding: 0.65rem 1rem;
            transition: all 0.2s;
        }
        
        .main-header .navbar-nav .nav-link:hover {
            color: var(--primary-color);
        }
        
        /* 内容区域 */
        .content-wrapper {
            background-color: var(--light-color);
        }
        
        .content-header {
            padding: 1.5rem 1rem 0.5rem;
        }
        
        .content-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0;
        }
        
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin: 0;
        }
        
        .breadcrumb-item a {
            color: var(--primary-color);
        }
        
        .breadcrumb-item.active {
            color: var(--dark-color);
            font-weight: 600;
        }
        
        /* 卡片样式 */
        .card {
            box-shadow: var(--card-shadow);
            border: none;
            border-radius: 0.35rem;
            margin-bottom: 1.5rem;
            transition: all 0.2s;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.25);
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1.25rem 1.25rem;
        }
        
        .card-header h3.card-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0;
            display: flex;
            align-items: center;
        }
        
        .card-header h3.card-title i {
            margin-right: 0.5rem;
            color: var(--primary-color);
        }
        
        .card-body {
            padding: 1.25rem;
        }
        
        .small-box {
            border-radius: 0.35rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
            transition: all 0.2s;
        }
        
        .small-box:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.3rem 2rem rgba(58, 59, 69, 0.2);
        }
        
        .small-box > .small-box-footer {
            background: rgba(0, 0, 0, 0.1);
            color: rgba(255, 255, 255, 0.9);
            font-weight: 600;
        }
        
        .small-box > .small-box-footer:hover {
            background: rgba(0, 0, 0, 0.15);
        }
        
        /* 按钮样式 */
        .btn {
            border-radius: 0.35rem;
            font-weight: 600;
            padding: 0.375rem 0.95rem;
            font-size: 0.9rem;
            box-shadow: 0 0.125rem 0.25rem 0 rgba(58, 59, 69, 0.2);
            transition: all 0.15s;
        }
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 0.2rem 0.35rem 0 rgba(58, 59, 69, 0.25);
        }
        
        .btn-icon-split {
            display: inline-flex;
            align-items: center;
        }
        
        .btn-icon-split .icon {
            display: inline-block;
            padding: .375rem .5rem;
            border-right: 1px solid rgba(255, 255, 255, 0.15);
            margin-right: .5rem;
        }
        
        /* 表格样式 */
        .table {
            color: var(--dark-color);
        }
        
        .table thead th {
            border-bottom: 2px solid rgba(58, 59, 69, 0.1);
            font-weight: 700;
            color: var(--dark-color);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            vertical-align: middle;
            padding: 0.75rem 1rem;
            background-color: #f8f9fc;
        }
        
        .table tbody td {
            vertical-align: middle;
            padding: 0.75rem 1rem;
        }
        
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        /* 页脚 */
        .main-footer {
            background-color: #fff;
            border-top: 1px solid rgba(0,0,0,0.05);
            color: var(--dark-color);
            font-size: 0.85rem;
            padding: 1rem;
        }
        
        /* 返回顶部按钮 */
        .back-to-top {
            position: fixed;
            bottom: 25px;
            right: 25px;
            display: none;
            width: 40px;
            height: 40px;
            text-align: center;
            line-height: 40px;
            background: var(--primary-color);
            color: white;
            cursor: pointer;
            border-radius: 50%;
            z-index: 99;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .back-to-top:hover {
            background: var(--primary-hover);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            transform: translateY(-3px);
        }
        
        /* 美化卡片和徽章 */
        .badge {
            font-weight: 600;
            padding: 0.35em 0.65em;
            font-size: 0.75em;
        }
        
        /* 动画效果 */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .container-fluid > .card {
            animation: fadeIn 0.4s ease-out;
        }
        
        /* 移动设备适配 */
        @media (max-width: 992px) {
            .main-sidebar {
                box-shadow: none;
            }
            
            .content-wrapper, .main-footer {
                margin-left: 0 !important;
            }
            
            .main-header {
                margin-left: 0 !important;
            }
        }
        
        /* 自定义滚动条 */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 6px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- 顶部导航栏 -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- 左侧导航链接 -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="?page=panel&module=home" class="nav-link">
                        <i class="fas fa-home mr-1"></i> 主页
                    </a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="?page=panel&module=addproxy" class="nav-link">
                        <i class="fas fa-plus-circle mr-1"></i> 创建隧道
                    </a>
                </li>
            </ul>
            
            <!-- 右侧导航链接 -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        <?php if(isset($rs['notification_count']) && $rs['notification_count'] > 0): ?>
                        <span class="badge badge-danger navbar-badge"><?php echo $rs['notification_count']; ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <span class="dropdown-item dropdown-header">系统通知</span>
                        <div class="dropdown-divider"></div>
                        <a href="?page=panel&module=profile#notifications" class="dropdown-item">
                            <i class="fas fa-cog mr-2"></i> 查看所有通知
                        </a>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?page=panel&module=profile" title="个人信息">
                        <i class="fas fa-user-circle"></i>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?page=logout&csrf=<?php echo $_SESSION['token']; ?>" title="退出登录">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </li>
            </ul>
        </nav>
        
        <!-- 侧边栏 -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- 品牌标志 -->
            <a href="?page=panel&module=home" class="brand-link text-center">
                <i class="fas fa-network-wired mr-2"></i>
                <span class="brand-text"><?php echo $_config['sitename']; ?></span>
            </a>
            
            <!-- 侧边栏 -->
            <div class="sidebar">
                <!-- 用户面板 -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="https://www.loliapi.com/acg/pp/" class="img-circle elevation-2" alt="用户头像">
                    </div>
                    <div class="info">
                        <a href="?page=panel&module=profile" class="d-block">
                            <?php echo htmlspecialchars($_SESSION['user']); ?>
                            <?php if($rs['group'] == "admin"): ?>
                            <span class="badge badge-warning">管理员</span>
                            <?php endif; ?>
                        </a>
                        <small class="text-muted d-block mt-1">
                            <?php echo $rs['group']; ?>
                        </small>
                    </div>
                </div>
                <!-- 侧边栏菜单 -->
<nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <li class="nav-item">
            <a href="?page=panel&module=home" class="nav-link <?php echo $module == "home" ? "active" : ""; ?>">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>管理面板</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="?page=panel&module=profile" class="nav-link <?php echo $module == "profile" ? "active" : ""; ?>">
                <i class="nav-icon fas fa-user"></i>
                <p>用户信息</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="?page=panel&module=verification" class="nav-link <?php echo $module == "verification" ? "active" : ""; ?>">
                <i class="nav-icon fas fa-id-card"></i>
                <p>实名认证</p>
            </a>
        </li>
        
        <li class="nav-header">内网穿透</li>
        <li class="nav-item">
            <a href="?page=panel&module=proxies" class="nav-link <?php echo $module == "proxies" ? "active" : ""; ?>">
                <i class="nav-icon fas fa-list"></i>
                <p>隧道列表</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="?page=panel&module=addproxy" class="nav-link <?php echo $module == "addproxy" ? "active" : ""; ?>">
                <i class="nav-icon fas fa-plus"></i>
                <p>创建隧道</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="?page=panel&module=sign" class="nav-link <?php echo $module == "sign" ? "active" : ""; ?>">
                <i class="nav-icon fas fa-check-square"></i>
                <p>每日签到</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="?page=panel&module=download" class="nav-link <?php echo $module == "download" ? "active" : ""; ?>">
                <i class="nav-icon fas fa-download"></i>
                <p>软件下载</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="?page=panel&module=configuration" class="nav-link <?php echo $module == "configuration" ? "active" : ""; ?>">
                <i class="nav-icon fas fa-file"></i>
                <p>配置文件</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="?page=panel&module=buypackage" class="nav-link <?php echo $module == "buypackage" ? "active" : ""; ?>">
                <i class="nav-icon fas fa-shopping-cart"></i>
                <p>套餐购买</p>
            </a>
        </li>
        
        <?php if($rs['group'] == "admin"): ?>
        <li class="nav-header">管理员</li>
        <li class="nav-item">
            <a href="?page=panel&module=userlist" class="nav-link <?php echo $module == "userlist" ? "active" : ""; ?>">
                <i class="nav-icon fas fa-users"></i>
                <p>用户管理</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="?page=panel&module=groupmanage" class="nav-link <?php echo $module == "groupmanage" ? "active" : ""; ?>">
                <i class="nav-icon fas fa-user-tag"></i>
                <p>用户组管理</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="?page=panel&module=nodes" class="nav-link <?php echo $module == "nodes" ? "active" : ""; ?>">
                <i class="nav-icon fas fa-server"></i>
                <p>节点管理</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="?page=panel&module=traffic" class="nav-link <?php echo $module == "traffic" ? "active" : ""; ?>">
                <i class="nav-icon fas fa-paper-plane"></i>
                <p>流量统计</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="?page=panel&module=tunnelmanage" class="nav-link <?php echo $module == "tunnelmanage" ? "active" : ""; ?>">
                <i class="nav-icon fas fa-network-wired"></i>
                <p>隧道管理</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="?page=panel&module=packages" class="nav-link <?php echo $module == "packages" ? "active" : ""; ?>">
                <i class="nav-icon fas fa-tags"></i>
                <p>套餐管理</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="?page=panel&module=redeemcodes" class="nav-link <?php echo $module == "redeemcodes" ? "active" : ""; ?>">
                <i class="nav-icon fas fa-money-bill"></i>
                <p>兑换码管理</p>
            </a>
        </li>
        
        <!-- 邀请码管理树形菜单 -->
       <li class="nav-item">
    <a href="?page=panel&module=invitecode" class="nav-link <?= $module == "invitecode" ? "active" : "" ?>">
        <i class="nav-icon fas fa-ticket-alt"></i>
        <p>未用邀请</p>
    </a>
</li>
<li class="nav-item">
    <a href="?page=panel&module=invitecode_stats" class="nav-link <?= $module == "invitecode_stats" ? "active" : "" ?>">
        <i class="nav-icon fas fa-ticket-alt"></i>
        <p>已用邀请</p>
    </a>
</li>
        <li class="nav-item">
            <a href="?page=panel&module=settings" class="nav-link <?php echo $module == "settings" ? "active" : ""; ?>">
                <i class="nav-icon fas fa-wrench"></i>
                <p>站点设置</p>
            </a>
        </li>
     
             
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </aside>
        
        <!-- 内容区域 -->
        <div class="content-wrapper">
            <!-- 内容头部 -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <h1 class="m-0">
                                <?php
                                    $moduleIcon = [
                                        'home' => '<i class="fas fa-tachometer-alt text-primary mr-2"></i>',
                                        'profile' => '<i class="fas fa-user text-info mr-2"></i>',
                                        'verification' => '<i class="fas fa-id-card text-warning mr-2"></i>',
                                        'proxies' => '<i class="fas fa-list text-success mr-2"></i>',
                                        'addproxy' => '<i class="fas fa-plus text-danger mr-2"></i>',
                                        'sign' => '<i class="fas fa-check-square text-primary mr-2"></i>',
                                        'download' => '<i class="fas fa-download text-info mr-2"></i>',
                                        'configuration' => '<i class="fas fa-file text-warning mr-2"></i>',
                                        'buypackage' => '<i class="fas fa-shopping-cart text-success mr-2"></i>',
                                        'userlist' => '<i class="fas fa-users text-primary mr-2"></i>',
                                        'groupmanage' => '<i class="fas fa-user-tag text-info mr-2"></i>',
                                        'nodes' => '<i class="fas fa-server text-warning mr-2"></i>',
                                        'traffic' => '<i class="fas fa-paper-plane text-success mr-2"></i>',
                                        'tunnelmanage' => '<i class="fas fa-network-wired text-danger mr-2"></i>',
                                        'packages' => '<i class="fas fa-tags text-primary mr-2"></i>',
                                        'redeemcodes' => '<i class="fas fa-money-bill text-success mr-2"></i>',
                                        'invitecode' => '<i class="fas fa-ticket-alt text-warning mr-2"></i>',
                                        'invitecode_stats' => '<i class="fas fa-ticket-alt text-info mr-2"></i>',
                                        'settings' => '<i class="fas fa-wrench text-secondary mr-2"></i>'
                                    ];
                                    $icon = isset($moduleIcon[$module]) ? $moduleIcon[$module] : '<i class="fas fa-circle text-primary mr-2"></i>';
                                    echo $icon . ($module ? ucfirst($module) : 'Dashboard');
                                ?>
                            </h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right bg-transparent p-0">
                                <li class="breadcrumb-item">
                                    <a href="?page=panel&module=home">
                                        <i class="fas fa-home mr-1"></i> 首页
                                    </a>
                                </li>
                                <li class="breadcrumb-item active">
                                    <?php echo $module ? ucfirst($module) : 'Dashboard'; ?>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 主内容区 -->
            <section class="content">
                <div class="container-fluid">
                    <?php
                    // 显示系统通知（如果有）
                    if (isset($_SESSION['system_message'])) {
                        $alertType = isset($_SESSION['system_message_type']) ? $_SESSION['system_message_type'] : 'info';
                        $alertIcon = [
                            'success' => 'fas fa-check-circle',
                            'info' => 'fas fa-info-circle',
                            'warning' => 'fas fa-exclamation-triangle',
                            'danger' => 'fas fa-times-circle'
                        ];
                        $icon = isset($alertIcon[$alertType]) ? $alertIcon[$alertType] : $alertIcon['info'];
                        
                        echo '<div class="alert alert-'.$alertType.' alert-dismissible fade show" role="alert">';
                        echo '<i class="'.$icon.' mr-2"></i> '.$_SESSION['system_message'];
                        echo '<button type="button" class="close" data-dismiss="alert" aria-label="关闭">';
                        echo '<span aria-hidden="true">&times;</span>';
                        echo '</button>';
                        echo '</div>';
                        
                        unset($_SESSION['system_message']);
                        unset($_SESSION['system_message_type']);
                    }
                    
                    // 加载模块内容
                    $page = new chhcn\Pages();
                    if(isset($_GET['module']) && preg_match("/^[A-Za-z0-9\_\-]{1,16}$/", $_GET['module'])) {
                        $page->loadModule($_GET['module']);
                    } else {
                        $page->loadModule("home");
                    }
                    ?>
                </div>
            </section>
        </div>
        
        <!-- 控制面板返回顶部按钮 -->
        <a id="back-to-top" href="#" class="btn btn-primary back-to-top" role="button" aria-label="返回顶部">
            <i class="fas fa-chevron-up"></i>
        </a>
        
        <!-- 页脚 -->
        <footer class="main-footer">
            <div class="float-right d-none d-sm-block">
                <b>Version</b> <?php echo isset($_config['version']) ? $_config['version'] : '1.0.0'; ?>
            </div>
            <strong>Copyright &copy; <?php echo date("Y"); ?> <a href="https://ch-h.cn" target="_blank" rel="noopener noreferrer"><?php echo $_config['sitename']; ?></a>.</strong> 保留所有权利.
        </footer>
    </div>
    
    <!-- jQuery -->
    <script src="https://cdn.bootcdn.net/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="https://cdn.bootcdn.net/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.bootcdn.net/ajax/libs/sweetalert2/11.7.12/sweetalert2.all.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.bootcdn.net/ajax/libs/Chart.js/4.3.0/chart.umd.js"></script>

    <!-- Clipboard.js -->
    <script src="https://cdn.bootcdn.net/ajax/libs/clipboard.js/2.0.11/clipboard.min.js"></script>
    
    <!-- jsPDF -->
    <script src="https://cdn.bootcdn.net/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    
    <!-- jsPDF-AutoTable -->
    <script src="https://cdn.bootcdn.net/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>
    
    <!-- SheetJS (XLSX) -->
    <script src="https://cdn.bootcdn.net/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <script type="text/javascript">
        // 页面加载完成后执行
        $(document).ready(function() {
            // 启用侧边栏折叠功能
            $('[data-widget="pushmenu"]').on('click touchstart', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $('body').toggleClass('sidebar-collapse');
                $('body').toggleClass('sidebar-open');
                return false;
            });
            
            // 响应式调整
            $(window).resize(function() {
                if ($(window).width() < 992) {
                    $('body').addClass('sidebar-collapse');
                }
            });
            
            // 初始化移动设备视图
            if ($(window).width() < 992) {
                $('body').addClass('sidebar-collapse');
            }
            
            // 自动隐藏提醒消息
            $('.alert-dismissible').delay(5000).fadeOut(500);
            
            // 返回顶部按钮
            $(window).scroll(function() {
                if ($(this).scrollTop() > 300) {
                    $('#back-to-top').fadeIn();
                } else {
                    $('#back-to-top').fadeOut();
                }
            });
            
            // 点击返回顶部按钮
            $('#back-to-top').click(function(e) {
                e.preventDefault();
                $('html, body').animate({scrollTop: 0}, 800);
                return false;
            });
            
            // 启用提示工具
            $('[data-toggle="tooltip"]').tooltip();
            
            // 启用弹出框
            $('[data-toggle="popover"]').popover();
            
            // 初始化复制到剪贴板功能
            if (typeof ClipboardJS !== 'undefined') {
                new ClipboardJS('.btn-copy');
                
                // 复制成功提示
                $('.btn-copy').on('click', function() {
                    var $this = $(this);
                    var originalTitle = $this.attr('title');
                    $this.attr('title', '复制成功!').tooltip('_fixTitle').tooltip('show');
                    setTimeout(function() {
                        $this.attr('title', originalTitle).tooltip('_fixTitle');
                    }, 1000);
                });
            }
        });
        
        // 确认操作
        function confirmAction(message, url) {
            if (confirm(message || '确定要执行此操作吗？')) {
                window.location.href = url;
            }
            return false;
        }
    </script>
</body>
</html>