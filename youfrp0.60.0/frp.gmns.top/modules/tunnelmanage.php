<?php
namespace chhcn;

use chhcn;

// 检查是否为管理员
$rs = Database::querySingleLine("users", Array("username" => $_SESSION['user']));
if($rs['group'] != "admin") {
    die('<div class="alert alert-danger"><i class="fas fa-exclamation-circle mr-2"></i>您没有权限访问此页面</div>');
}

// 处理删除隧道请求
if(isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);
    $csrf = $_GET['csrf'] ?? '';
    
    if($csrf != $_SESSION['token']) {
        echo '<script>
            alert("CSRF 验证失败");
            window.location.href = "?page=panel&module=tunnelmanage";
        </script>';
    } else {
        $result = Database::query("proxies", "DELETE FROM proxies WHERE id = $id", "", true);
        // 直接重定向，不显示任何提示
        header("Location: ?page=panel&module=tunnelmanage");
        exit;
    }
}

// 获取搜索参数
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$searchField = isset($_GET['field']) ? $_GET['field'] : 'username';

// 构建查询条件
$whereClause = '';
if(!empty($search)) {
    switch($searchField) {
        case 'username':
            $whereClause = "WHERE p.username LIKE '%$search%'";
            break;
        case 'name':
            $whereClause = "WHERE p.proxy_name LIKE '%$search%'";
            break;
        case 'protocol':
            $whereClause = "WHERE p.proxy_type LIKE '%$search%'";
            break;
        case 'local_port':
            $whereClause = "WHERE p.local_port LIKE '%$search%'";
            break;
        case 'remote_port':
            $whereClause = "WHERE p.remote_port LIKE '%$search%'";
            break;
    }
}

// 分页设置
$page = isset($_GET['p']) ? intval($_GET['p']) : 1;
$perPage = 15;
$offset = ($page - 1) * $perPage;

// 获取总记录数
$countResult = Database::query("proxies", "SELECT COUNT(*) as total FROM proxies p $whereClause", "", true);
$totalRows = 0;
$totalPages = 0;

// 检查查询是否成功
if(is_object($countResult)) {
    $row = mysqli_fetch_assoc($countResult);
    if($row) {
        $totalRows = $row['total'];
        $totalPages = ceil($totalRows / $perPage);
    }
} else {
    echo '<div class="alert alert-danger"><i class="fas fa-exclamation-circle mr-2"></i>查询错误: ' . htmlspecialchars($countResult) . '</div>';
}

// 获取隧道列表
$query = "SELECT p.*, u.email FROM proxies p 
          LEFT JOIN users u ON p.username = u.username 
          $whereClause 
          ORDER BY p.id DESC 
          LIMIT $offset, $perPage";
$result = Database::query("proxies", $query, "", true);

// 检查查询是否成功
if(!is_object($result)) {
    echo '<div class="alert alert-danger"><i class="fas fa-exclamation-circle mr-2"></i>查询错误: ' . htmlspecialchars($result) . '</div>';
}

// 统计信息
$totalQuery = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = '0' THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN status = '1' THEN 1 ELSE 0 END) as disabled,
    COUNT(DISTINCT username) as users,
    COUNT(DISTINCT node) as nodes
FROM proxies";
$statsResult = Database::query("proxies", $totalQuery, "", true);
$stats = mysqli_fetch_assoc($statsResult);

// 协议类型分布统计
$protocolQuery = "SELECT proxy_type, COUNT(*) as count FROM proxies GROUP BY proxy_type";
$protocolResult = Database::query("proxies", $protocolQuery, "", true);
$protocols = [];
if(is_object($protocolResult)) {
    while($row = mysqli_fetch_assoc($protocolResult)) {
        $protocols[$row['proxy_type']] = $row['count'];
    }
}
?>

<style>
.search-box {
    background-color: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}
.tunnel-status {
    width: 12px;
    height: 12px;
    display: inline-block;
    border-radius: 50%;
    margin-right: 5px;
}
.tunnel-status.active {
    background-color: #28a745;
    box-shadow: 0 0 10px rgba(40, 167, 69, 0.5);
    animation: pulse 2s infinite;
}
.tunnel-status.disabled {
    background-color: #dc3545;
}
@keyframes pulse {
    0% { opacity: 0.7; }
    50% { opacity: 1; }
    100% { opacity: 0.7; }
}
.protocol-badge {
    padding: 6px 10px;
    border-radius: 50px;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
}
.protocol-tcp {
    background-color: #007bff;
    color: white;
}
.protocol-http {
    background-color: #28a745;
    color: white;
}
.protocol-https {
    background-color: #17a2b8;
    color: white;
}
.protocol-udp {
    background-color: #6f42c1;
    color: white;
}
.protocol-stcp {
    background-color: #fd7e14;
    color: white;
}
.stat-card {
    border-radius: 0.5rem;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    padding: 1.5rem;
    text-align: center;
    transition: all 0.3s;
    margin-bottom: 1.5rem;
    border-top: 4px solid #007bff;
}
.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.1);
}
.stat-icon {
    font-size: 2rem;
    margin-bottom: 1rem;
    color: #007bff;
}
.stat-value {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: #343a40;
}
.stat-label {
    color: #6c757d;
    font-size: 1rem;
}
.btn-action {
    padding: 0.25rem 0.75rem;
    font-size: 0.8rem;
    margin: 0 2px;
    border-radius: 50px;
}
.action-btns {
    white-space: nowrap;
}
</style>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-project-diagram mr-2"></i>隧道管理</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="?page=panel"><i class="fas fa-home"></i> 首页</a></li>
                    <li class="breadcrumb-item active">隧道管理</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <!-- 统计卡片 -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?php echo $stats['total'] ?? 0; ?></h3>
                        <p>隧道总数</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?php echo $stats['active'] ?? 0; ?></h3>
                        <p>活跃隧道</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?php echo $stats['users'] ?? 0; ?></h3>
                        <p>用户数量</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?php echo $stats['nodes'] ?? 0; ?></h3>
                        <p>节点数量</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-server"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
    <div class="col-md-9">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>隧道列表</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- 搜索表单 -->
                <div class="search-box mb-4">
                    <form method="get">
                        <input type="hidden" name="page" value="panel">
                        <input type="hidden" name="module" value="tunnelmanage">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><i class="fas fa-filter mr-1"></i> 筛选字段</label>
                                    <select name="field" class="form-control">
                                        <option value="username" <?php echo $searchField == 'username' ? 'selected' : ''; ?>><i class="fas fa-user"></i> 用户名</option>
                                        <option value="name" <?php echo $searchField == 'name' ? 'selected' : ''; ?>><i class="fas fa-tag"></i> 隧道名称</option>
                                        <option value="protocol" <?php echo $searchField == 'protocol' ? 'selected' : ''; ?>><i class="fas fa-code-branch"></i> 协议</option>
                                        <option value="local_port" <?php echo $searchField == 'local_port' ? 'selected' : ''; ?>><i class="fas fa-plug"></i> 本地端口</option>
                                        <option value="remote_port" <?php echo $searchField == 'remote_port' ? 'selected' : ''; ?>><i class="fas fa-ethernet"></i> 远程端口</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label><i class="fas fa-search mr-1"></i> 搜索关键词</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        </div>
                                        <input type="text" name="search" class="form-control" placeholder="输入关键词搜索..." value="<?php echo $search; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-search mr-1"></i> 搜索
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- 隧道列表 -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th><i class="fas fa-hashtag mr-1"></i> ID</th>
                                <th><i class="fas fa-user mr-1"></i> 用户名</th>
                                <th><i class="fas fa-tag mr-1"></i> 隧道名称</th>
                                <th><i class="fas fa-server mr-1"></i> 节点</th>
                                <th><i class="fas fa-code-branch mr-1"></i> 协议</th>
                                <th><i class="fas fa-network-wired mr-1"></i> 本地IP</th>
                                <th><i class="fas fa-plug mr-1"></i> 本地端口</th>
                                <th><i class="fas fa-ethernet mr-1"></i> 远程端口</th>
                                <th><i class="fas fa-toggle-on mr-1"></i> 状态</th>
                                <th><i class="fas fa-cogs mr-1"></i> 操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // 检查结果是否为有效的mysqli_result对象
                            if(is_object($result) && mysqli_num_rows($result) > 0) {
                                while($row = mysqli_fetch_assoc($result)) {
                                    $nodeInfo = Database::querySingleLine("nodes", Array("id" => $row['node']));
                                    $nodeName = $nodeInfo ? $nodeInfo['name'] : '未知节点';
                                    
                                    // 协议类型样式
                                    $protocolClass = 'protocol-' . strtolower($row['proxy_type']);
                                    if(!in_array(strtolower($row['proxy_type']), ['tcp', 'udp', 'http', 'https', 'stcp'])) {
                                        $protocolClass = 'bg-secondary';
                                    }
                                    
                                    // 确定状态显示
                                    if($row['status'] == '0') {
                                        $statusIcon = '<span class="tunnel-status active"></span>';
                                        $statusBadge = '<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> 启用</span>';
                                    } else {
                                        $statusIcon = '<span class="tunnel-status disabled"></span>';
                                        $statusBadge = '<span class="badge badge-danger"><i class="fas fa-ban mr-1"></i> 禁用</span>';
                                    }
                                    
                                    echo '<tr>';
                                    echo '<td>' . $row['id'] . '</td>';
                                    echo '<td><span class="badge badge-info">' . htmlspecialchars($row['username']) . '</span></td>';
                                    echo '<td>' . $statusIcon . ' ' . htmlspecialchars($row['proxy_name']) . '</td>';
                                    echo '<td><span class="badge badge-primary">' . htmlspecialchars($nodeName) . '</span></td>';
                                    echo '<td><span class="badge protocol-badge ' . $protocolClass . '">' . strtoupper(htmlspecialchars($row['proxy_type'])) . '</span></td>';
                                    echo '<td>' . htmlspecialchars($row['local_ip']) . '</td>';
                                    echo '<td><code>' . htmlspecialchars($row['local_port']) . '</code></td>';
                                    echo '<td><code>' . htmlspecialchars($row['remote_port']) . '</code></td>';
                                    echo '<td>' . $statusBadge . '</td>';
                                    echo '<td class="action-btns">';
                                    
                                    // 操作按钮
                                    echo '<div class="btn-group">';
                                    
                                    // 切换状态按钮
                                    if($row['status'] == '0') {
                                        echo '<a href="?page=panel&module=tunnelmanage&action=disable&id=' . $row['id'] . '&csrf=' . $_SESSION['token'] . '" class="btn btn-warning btn-action" title="禁用隧道"><i class="fas fa-ban"></i></a>';
                                    } else {
                                        echo '<a href="?page=panel&module=tunnelmanage&action=enable&id=' . $row['id'] . '&csrf=' . $_SESSION['token'] . '" class="btn btn-success btn-action" title="启用隧道"><i class="fas fa-check"></i></a>';
                                    }
                                    
                                    // 删除按钮
                                    echo '<a href="javascript:void(0);" onclick="confirmDelete(' . $row['id'] . ')" class="btn btn-danger btn-action" title="删除隧道"><i class="fas fa-trash"></i></a>';
                                    
                                    // 查看详情按钮
                                    echo '<button type="button" class="btn btn-info btn-action" title="查看详情" onclick="viewDetails(' . $row['id'] . ', \'' . htmlspecialchars($row['proxy_name']) . '\')"><i class="fas fa-info-circle"></i></button>';
                                    
                                    echo '</div>';
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="10" class="text-center py-5">';
                                echo '<div class="empty-state">';
                                echo '<i class="fas fa-project-diagram fa-4x text-muted"></i>';
                                echo '<p class="mt-3 text-muted">没有找到隧道记录</p>';
                                if(!empty($search)) {
                                    echo '<a href="?page=panel&module=tunnelmanage" class="btn btn-outline-primary mt-2">';
                                    echo '<i class="fas fa-undo mr-1"></i> 清除搜索条件</a>';
                                }
                                echo '</div>';
                                echo '</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- 分页 -->
                <?php if($totalPages > 1): ?>
                <div class="mt-4">
                    <nav aria-label="隧道列表分页">
                        <ul class="pagination justify-content-center">
                            <?php if($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=panel&module=tunnelmanage&p=1&field=<?php echo $searchField; ?>&search=<?php echo $search; ?>" title="第一页">
                                    <i class="fas fa-angle-double-left"></i>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?page=panel&module=tunnelmanage&p=<?php echo $page-1; ?>&field=<?php echo $searchField; ?>&search=<?php echo $search; ?>" title="上一页">
                                    <i class="fas fa-angle-left"></i>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php
                            // 显示页码
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);
                            
                            for($i = $startPage; $i <= $endPage; $i++):
                            ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=panel&module=tunnelmanage&p=<?php echo $i; ?>&field=<?php echo $searchField; ?>&search=<?php echo $search; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=panel&module=tunnelmanage&p=<?php echo $page+1; ?>&field=<?php echo $searchField; ?>&search=<?php echo $search; ?>" title="下一页">
                                    <i class="fas fa-angle-right"></i>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?page=panel&module=tunnelmanage&p=<?php echo $totalPages; ?>&field=<?php echo $searchField; ?>&search=<?php echo $search; ?>" title="最后一页">
                                    <i class="fas fa-angle-double-right"></i>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <div class="text-center text-muted">
                        <small>共 <?php echo $totalRows; ?> 条记录，每页 <?php echo $perPage; ?> 条，当前第 <?php echo $page; ?>/<?php echo $totalPages; ?> 页</small>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <!-- 协议分布卡片 -->
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>协议分布</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if(!empty($protocols)): ?>
                    <div class="text-center mb-3">
                        <canvas id="protocolChart" width="100%" height="200"></canvas>
                    </div>
                    <div class="protocol-list">
                        <?php foreach($protocols as $type => $count): ?>
                            <?php 
                                $protocolClass = 'protocol-' . strtolower($type);
                                if(!in_array(strtolower($type), ['tcp', 'udp', 'http', 'https', 'stcp'])) {
                                    $protocolClass = 'bg-secondary';
                                }
                            ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge protocol-badge <?php echo $protocolClass; ?>"><?php echo strtoupper($type); ?></span>
                                <span class="badge badge-light"><?php echo $count; ?> 个</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-chart-pie fa-3x text-muted"></i>
                        <p class="mt-3 text-muted">暂无数据</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- 帮助卡片 -->
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-question-circle mr-2"></i>隧道管理帮助</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h5 class="text-success"><i class="fas fa-lightbulb mr-1"></i> 操作说明</h5>
                    <ul class="pl-3">
                        <li>点击<span class="badge badge-success"><i class="fas fa-check"></i></span>启用隧道</li>
                        <li>点击<span class="badge badge-warning"><i class="fas fa-ban"></i></span>禁用隧道</li>
                        <li>点击<span class="badge badge-danger"><i class="fas fa-trash"></i></span>删除隧道</li>
                        <li>点击<span class="badge badge-info"><i class="fas fa-info-circle"></i></span>查看隧道详情</li>
                    </ul>
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-1"></i> <strong>注意：</strong> 删除操作不可撤销，请谨慎操作！
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 隧道详情模态框 -->
<div class="modal fade" id="tunnelDetailModal" tabindex="-1" role="dialog" aria-labelledby="tunnelDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tunnelDetailModalLabel"><i class="fas fa-info-circle mr-2"></i>隧道详情</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="tunnelDetailContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">加载中...</span>
                    </div>
                    <p class="mt-3">正在加载隧道详情...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>

<!-- 添加启用/禁用隧道功能 -->
<?php
if(isset($_GET['action']) && ($_GET['action'] == 'enable' || $_GET['action'] == 'disable') && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);
    $csrf = $_GET['csrf'] ?? '';
    $action = $_GET['action'];
    
    if($csrf != $_SESSION['token']) {
        echo '<script>
            alert("CSRF 验证失败");
            window.location.href = "?page=panel&module=tunnelmanage";
        </script>';
    } else {
        $status = ($action == 'enable') ? '0' : '1';
        $result = Database::query("proxies", "UPDATE proxies SET status = '$status' WHERE id = $id", "", true);
        
        // 直接重定向，不显示任何提示
        header("Location: ?page=panel&module=tunnelmanage");
        exit;
    }
}
?>

<script>
// 隧道删除确认
function confirmDelete(id) {
    if(confirm('您确定要删除此隧道吗？此操作不可恢复！')) {
        window.location.href = `?page=panel&module=tunnelmanage&action=delete&id=${id}&csrf=<?php echo $_SESSION['token']; ?>`;
    }
}

// 查看隧道详情
function viewDetails(id, name) {
    $('#tunnelDetailModalLabel').html(`<i class="fas fa-info-circle mr-2"></i>隧道详情 - ${name}`);
    $('#tunnelDetailContent').html(`
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">加载中...</span>
            </div>
            <p class="mt-3">正在加载隧道详情...</p>
        </div>
    `);
    $('#tunnelDetailModal').modal('show');
    
    // 这里可以添加AJAX请求获取隧道详细信息的代码
    // 目前只显示模拟数据
    setTimeout(() => {
        $('#tunnelDetailContent').html(`
            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-2"></i> 此功能将在后续版本中实现，敬请期待！
            </div>
            <div class="text-center">
                <img src="assets/images/tunnel_diagram.png" alt="隧道图示" class="img-fluid mb-3" style="max-height: 200px;">
            </div>
            <div class="tunnel-details">
                <h5 class="text-primary">隧道 ID: ${id}</h5>
                <p>更详细的隧道信息将在未来版本中提供，包括流量统计、连接历史等。</p>
            </div>
        `);
    }, 1000);
}

$(document).ready(function() {
    // 初始化工具提示
    $('[data-toggle="tooltip"]').tooltip();
    
    // 页面淡入动画
    $('.card').css('opacity', 0);
    $('.small-box').css('opacity', 0);
    
    setTimeout(function() {
        $('.small-box').each(function(i) {
            $(this).delay(i * 100).animate({
                opacity: 1
            }, 500);
        });
        
        setTimeout(function() {
            $('.card').each(function(i) {
                $(this).delay(i * 150).animate({
                    opacity: 1
                }, 500);
            });
        }, 400);
    }, 200);
    
    <?php if(!empty($protocols)): ?>
    // 绘制协议分布图表
    var ctx = document.getElementById('protocolChart').getContext('2d');
    var protocolChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: [<?php 
                $labels = [];
                foreach($protocols as $type => $count) {
                    $labels[] = "'" . strtoupper($type) . "'";
                }
                echo implode(", ", $labels);
            ?>],
            datasets: [{
                data: [<?php echo implode(", ", array_values($protocols)); ?>],
                backgroundColor: [
                    '#007bff',  // TCP
                    '#6f42c1',  // UDP
                    '#28a745',  // HTTP
                    '#17a2b8',  // HTTPS
                    '#fd7e14',  // STCP
                    '#6c757d'   // Others
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 12
                }
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        var dataset = data.datasets[tooltipItem.datasetIndex];
                        var total = dataset.data.reduce(function(previousValue, currentValue) {
                            return previousValue + currentValue;
                        });
                        var currentValue = dataset.data[tooltipItem.index];
                        var percentage = Math.round(currentValue / total * 100);
                        return data.labels[tooltipItem.index] + ': ' + currentValue + ' (' + percentage + '%)';
                    }
                }
            }
        }
    });
    <?php endif; ?>
});
</script> 