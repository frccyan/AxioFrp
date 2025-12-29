<?php
namespace chhcn;

use chhcn\UserManager;
use chhcn\RedeemCodeManager;

// 检查是否是管理员
$um = new UserManager();
$user = $um->getInfoByUser($_SESSION['user']);
if ($user['group'] !== 'admin') {
    header("Location: ?page=panel");
    exit;
}

$rcm = new RedeemCodeManager();
$page = isset($_GET['p']) && is_numeric($_GET['p']) ? (int)$_GET['p'] : 1;
$status = isset($_GET['status']) && in_array($_GET['status'], ['all', 'used', 'unused']) ? $_GET['status'] : 'all';
$limit = 20;

// 获取兑换码列表
$codeData = $rcm->getCodes($status, $page, $limit);
$codes = $codeData['codes'];
$total = $codeData['total'];
$totalPages = ceil($total / $limit);

// 兑换码生成和删除的后端处理逻辑
if (isset($_GET['action']) && $_GET['action'] === 'generatecode' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 检查CSRF令牌
        if (!isset($_GET['csrf']) || !isset($_SESSION['token']) || $_GET['csrf'] !== $_SESSION['token']) {
            header('Content-Type: application/json');
            echo json_encode(['status' => false, 'message' => 'CSRF校验失败']);
            exit;
        }
        
        $um = new \chhcn\UserManager();
        $user = $um->getInfoByUser($_SESSION['user']);
        if ($user['group'] !== 'admin') {
            header('Content-Type: application/json');
            echo json_encode(['status' => false, 'message' => '无权限操作']);
            exit;
        }
        
        $amount = floatval($_POST['amount']);
        $count = intval($_POST['count']);
        
        if ($amount <= 0 || $count < 1 || $count > 100) {
            header('Content-Type: application/json');
            echo json_encode(['status' => false, 'message' => '参数错误：金额必须大于0，数量必须在1-100之间']);
            exit;
        }
        
        $rcm = new \chhcn\RedeemCodeManager();
        $codes = $rcm->generateCodes($amount, $count);
        
        if ($codes && count($codes) > 0) {
            header('Content-Type: application/json');
            echo json_encode(['status' => true, 'message' => '生成成功：' . implode(', ', $codes), 'codes' => $codes]);
            exit;
        } else {
            header('Content-Type: application/json');
            echo json_encode(['status' => false, 'message' => '生成失败，请查看系统日志']);
            exit;
        }
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['status' => false, 'message' => '生成失败：' . $e->getMessage()]);
        exit;
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'deletecode' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 检查CSRF令牌
        if (!isset($_GET['csrf']) || !isset($_SESSION['token']) || $_GET['csrf'] !== $_SESSION['token']) {
            header('Content-Type: application/json');
            echo json_encode(['status' => false, 'message' => 'CSRF校验失败']);
            exit;
        }
        
        $um = new \chhcn\UserManager();
        $user = $um->getInfoByUser($_SESSION['user']);
        if ($user['group'] !== 'admin') {
            header('Content-Type: application/json');
            echo json_encode(['status' => false, 'message' => '无权限操作']);
            exit;
        }
        
        $code = $_POST['code'];
        $rcm = new \chhcn\RedeemCodeManager();
        if ($rcm->deleteCode($code)) {
            header('Content-Type: application/json');
            echo json_encode(['status' => true, 'message' => '删除成功']);
            exit;
        } else {
            header('Content-Type: application/json');
            echo json_encode(['status' => false, 'message' => '删除失败，可能兑换码不存在或已被使用']);
            exit;
        }
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['status' => false, 'message' => '删除失败：' . $e->getMessage()]);
        exit;
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <?php if(isset($_SESSION['redeem_code_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-check-circle"></i> 操作成功！</h5>
            <?php echo $_SESSION['redeem_code_success']; unset($_SESSION['redeem_code_success']); ?>
        </div>
        <?php endif; ?>
        
        <!-- 添加 clipboard.js 库 -->
        <script src="https://cdn.jsdelivr.net/npm/clipboard@2.0.8/dist/clipboard.min.js"></script>
        
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-ticket-alt mr-2"></i>兑换码管理</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="?page=panel"><i class="fas fa-home"></i> 首页</a></li>
                    <li class="breadcrumb-item active">兑换码管理</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-plus-circle mr-2"></i>生成兑换码</h3>
                    </div>
                    <div class="card-body">
                        <form id="generateForm" method="post" action="?page=panel&module=redeemcodes&action=generatecode&csrf=<?php echo $_SESSION['token']; ?>">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="amount"><i class="fas fa-money-bill-wave mr-1"></i> 充值金额</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-yuan-sign"></i></span>
                                            </div>
                                            <input type="number" step="0.01" min="0.01" class="form-control" id="amount" name="amount" placeholder="请输入充值金额" required>
                                            <div class="input-group-append">
                                                <span class="input-group-text">元</span>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted">兑换码可用于用户账户充值</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="count"><i class="fas fa-list-ol mr-1"></i> 生成数量</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                            </div>
                                            <input type="number" min="1" max="100" class="form-control" id="count" name="count" value="1" required>
                                        </div>
                                        <small class="form-text text-muted">每次最多可生成100个兑换码</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group" style="margin-top: 31px;">
                                        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-magic mr-1"></i> 生成兑换码</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 统计卡片 -->
            <div class="col-md-12">
                <div class="row">
                    <div class="col-lg-4 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?php echo $total; ?></h3>
                                <p>总兑换码数量</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?php 
                                    $unused = 0;
                                    foreach($codes as $code) {
                                        if($code['status'] == 0) $unused++;
                                    }
                                    echo $unused; 
                                ?></h3>
                                <p>未使用兑换码</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-6">
                        <div class="small-box bg-secondary">
                            <div class="inner">
                                <h3><?php echo $total - $unused; ?></h3>
                                <p>已使用兑换码</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-list mr-2"></i>兑换码列表</h3>
                        <div class="card-tools">
                            <div class="btn-group">
                                <a href="?page=panel&module=redeemcodes&status=all" class="btn btn-sm btn-outline-primary <?php echo $status === 'all' ? 'active' : ''; ?>">
                                    <i class="fas fa-list"></i> 全部
                                </a>
                                <a href="?page=panel&module=redeemcodes&status=unused" class="btn btn-sm btn-outline-success <?php echo $status === 'unused' ? 'active' : ''; ?>">
                                    <i class="fas fa-check"></i> 未使用
                                </a>
                                <a href="?page=panel&module=redeemcodes&status=used" class="btn btn-sm btn-outline-secondary <?php echo $status === 'used' ? 'active' : ''; ?>">
                                    <i class="fas fa-times"></i> 已使用
                                </a>
                            </div>
                            <div class="btn-group ml-2">
                                <button type="button" class="btn btn-sm btn-outline-success dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-file-export"></i> 导出
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="?page=panel&module=redeemcodes&export=unused">
                                        <i class="fas fa-file-excel mr-2"></i> 导出未使用
                                    </a>
                                    <a class="dropdown-item" href="?page=panel&module=redeemcodes&export=used">
                                        <i class="fas fa-file-excel mr-2"></i> 导出已使用
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="?page=panel&module=redeemcodes&export=all">
                                        <i class="fas fa-file-excel mr-2"></i> 导出全部
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th><i class="fas fa-key mr-1"></i> 兑换码</th>
                                        <th><i class="fas fa-money-bill-wave mr-1"></i> 金额</th>
                                        <th><i class="fas fa-info-circle mr-1"></i> 状态</th>
                                        <th><i class="fas fa-calendar-plus mr-1"></i> 创建时间</th>
                                        <th><i class="fas fa-calendar-check mr-1"></i> 使用时间</th>
                                        <th><i class="fas fa-user mr-1"></i> 使用者</th>
                                        <th><i class="fas fa-cogs mr-1"></i> 操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(count($codes) > 0): ?>
                                        <?php foreach($codes as $code): ?>
                                            <tr>
                                                <td>
                                                    <div class="code-display">
                                                        <span class="code-text"><?php echo htmlspecialchars($code['code']); ?></span>
                                                        <button class="btn btn-xs btn-outline-primary ml-2" onclick="copyCode(this)" title="复制兑换码">
                                                            <i class="fas fa-copy"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                                <td class="text-right">
                                                    <span class="badge badge-info">
                                                        <?php echo number_format($code['amount'], 2); ?> 元
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if($code['status'] == 0): ?>
                                                        <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> 未使用</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary"><i class="fas fa-times-circle mr-1"></i> 已使用</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><i class="far fa-clock mr-1"></i> <?php echo date('Y-m-d H:i:s', $code['created_at']); ?></td>
                                                <td>
                                                    <?php if($code['used_at']): ?>
                                                        <i class="far fa-calendar-check mr-1"></i> <?php echo date('Y-m-d H:i:s', $code['used_at']); ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if($code['used_by']): ?>
                                                        <span class="badge badge-info">
                                                            <i class="fas fa-user mr-1"></i> <?php echo htmlspecialchars($code['used_by']); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if($code['status'] == 0): ?>
                                                        <form method="post" action="?page=panel&module=redeemcodes&action=deletecode&csrf=<?php echo $_SESSION['token']; ?>" style="display:inline;" onsubmit="return confirm('确定要删除兑换码 <?php echo htmlspecialchars($code['code']); ?> 吗？');">
                                                            <input type="hidden" name="code" value="<?php echo htmlspecialchars($code['code']); ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger">
                                                                <i class="fas fa-trash-alt"></i> 删除
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="empty-state">
                                                    <i class="fas fa-ticket-alt fa-4x text-muted"></i>
                                                    <p class="mt-3">暂无兑换码数据</p>
                                                    <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('amount').focus(); return false;">
                                                        <i class="fas fa-plus-circle"></i> 立即创建
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php if($totalPages > 1): ?>
                    <div class="card-footer clearfix">
                        <ul class="pagination pagination-sm m-0 float-right">
                            <?php if($page > 1): ?>
                                <li class="page-item"><a class="page-link" href="?page=panel&module=redeemcodes&p=1&status=<?php echo $status; ?>" title="首页"><i class="fas fa-angle-double-left"></i></a></li>
                                <li class="page-item"><a class="page-link" href="?page=panel&module=redeemcodes&p=<?php echo $page - 1; ?>&status=<?php echo $status; ?>" title="上一页"><i class="fas fa-angle-left"></i></a></li>
                            <?php endif; ?>
                            
                            <?php
                            $start = max(1, $page - 2);
                            $end = min($totalPages, $page + 2);
                            
                            for($i = $start; $i <= $end; $i++): 
                            ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=panel&module=redeemcodes&p=<?php echo $i; ?>&status=<?php echo $status; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if($page < $totalPages): ?>
                                <li class="page-item"><a class="page-link" href="?page=panel&module=redeemcodes&p=<?php echo $page + 1; ?>&status=<?php echo $status; ?>" title="下一页"><i class="fas fa-angle-right"></i></a></li>
                                <li class="page-item"><a class="page-link" href="?page=panel&module=redeemcodes&p=<?php echo $totalPages; ?>&status=<?php echo $status; ?>" title="末页"><i class="fas fa-angle-double-right"></i></a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
$(function() {
    // 初始化 tooltip
    $('[data-toggle="tooltip"]').tooltip();
    
    // 给复制按钮添加 tooltip
    $('.copy-btn').tooltip({
        title: '点击复制',
        placement: 'top',
        trigger: 'hover'
    });
    
    // 生成兑换码
    $('#generateForm').submit(function(event) {
        var amount = $('#amount').val();
        var count = $('#count').val();
        
        if (!amount || amount <= 0) {
            Swal.fire({
                icon: 'error',
                title: '输入错误',
                text: '请输入有效的金额',
                confirmButtonText: '确定'
            });
            event.preventDefault();
            return false;
        }
        
        if (!count || count <= 0 || count > 100) {
            Swal.fire({
                icon: 'error',
                title: '输入错误',
                text: '生成数量必须在1-100之间',
                confirmButtonText: '确定'
            });
            event.preventDefault();
            return false;
        }
        
        // 显示加载中
        Swal.fire({
            title: '正在生成',
            html: '正在生成兑换码，请稍候...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    });
    
    // 初始化剪贴板功能
    // 高亮显示表格行
    $('.table-hover tr').hover(
        function() { $(this).addClass('bg-light'); },
        function() { $(this).removeClass('bg-light'); }
    );
});

function copyCode(button) {
    // 找到按钮旁边的兑换码文本
    var codeText = $(button).siblings('.code-text').text();

    navigator.clipboard.writeText(codeText).then(function() {
        // 复制成功
        var originalTitle = $(button).attr('data-original-title');
        $(button).attr('data-original-title', '已复制!').tooltip('show');
        $(button).removeClass('btn-outline-primary').addClass('btn-success');

        setTimeout(function() {
            $(button).tooltip('hide').attr('data-original-title', originalTitle);
            $(button).removeClass('btn-success').addClass('btn-outline-primary');
        }, 1000);
    }, function() {
        // 复制失败
        alert('复制失败，您的浏览器可能不支持此功能或页面不是通过HTTPS访问的。');
    });
}
</script>

<style>
.empty-state {
    text-align: center;
    padding: 30px;
    color: #6c757d;
}

.code-display {
    display: flex;
    align-items: center;
}

.badge-info {
    font-size: 0.9rem;
    font-weight: normal;
}

.table th, .table td {
    vertical-align: middle;
}

.card-outline {
    border-top: 3px solid;
}

.small-box .icon {
    font-size: 70px;
    color: rgba(0,0,0,0.15);
}

.small-box:hover .icon {
    color: rgba(0,0,0,0.3);
}
</style> 