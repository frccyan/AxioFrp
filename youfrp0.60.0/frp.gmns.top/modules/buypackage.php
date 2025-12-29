<?php
namespace chhcn;

use chhcn\UserManager;
use chhcn\PackageManager;
use chhcn\BalanceManager;
use chhcn\GroupManager;

if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(16));
}

$um = new UserManager();
$user = $um->getInfoByUser($_SESSION['user']);
if(!$user) {
    header("Location: ?page=login");
    exit;
}

$pm = new PackageManager();
$bm = new BalanceManager();
$userBalance = $bm->getBalance($_SESSION['user']);
$packages = $pm->getActivePackages();
$gm = new GroupManager();

// 生成套餐颜色和图标
$packageColors = [
    'bg-primary', 'bg-info', 'bg-success', 'bg-warning', 'bg-danger', 'bg-indigo', 'bg-purple', 'bg-pink', 'bg-teal'
];

$packageIcons = [
    'fas fa-rocket', 'fas fa-star', 'fas fa-crown', 'fas fa-gem', 'fas fa-bolt', 'fas fa-shield-alt', 'fas fa-award'
];
?>

<style>
.package-card {
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: none;
    margin-bottom: 30px;
    border-radius: 15px;
}

.package-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.15);
}

.package-header {
    padding: 30px 20px;
    text-align: center;
    color: #fff;
    border-radius: 15px 15px 0 0;
}

.package-header .package-icon {
    font-size: 48px;
    margin-bottom: 10px;
    opacity: 0.8;
}

.package-header h3 {
    font-weight: 700;
    font-size: 24px;
    margin-bottom: 5px;
}

.package-header .price {
    font-size: 36px;
    font-weight: 700;
}

.package-body {
    padding: 30px;
}

.feature-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.feature-list li {
    padding: 15px 0;
    border-bottom: 1px solid #eee;
    display: flex;
    align-items: center;
}

.feature-list li:last-child {
    border-bottom: none;
}

.feature-list li i {
    width: 24px;
    margin-right: 10px;
    text-align: center;
}

.feature-label {
    font-weight: 500;
    color: #555;
}

.feature-value {
    margin-left: auto;
    font-weight: 600;
    color: #333;
}

.package-footer {
    padding: 20px 30px 30px;
    text-align: center;
}

.popular-badge {
    position: absolute;
    top: 20px;
    right: -35px;
    background: #ff5722;
    color: white;
    padding: 8px 40px;
    transform: rotate(45deg);
    font-size: 12px;
    font-weight: bold;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    z-index: 2;
}

.balance-card {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.balance-amount {
    font-size: 48px;
    font-weight: 700;
    margin: 0;
    line-height: 1;
}

.balance-label {
    font-size: 16px;
    opacity: 0.8;
    margin-bottom: 20px;
}

.balance-icon {
    position: absolute;
    right: 30px;
    top: 30px;
    font-size: 60px;
    opacity: 0.2;
}

.redeem-form {
    position: relative;
    z-index: 1;
}

.history-table th {
    font-weight: 600;
    color: #555;
}

.history-table .badge {
    font-size: 0.8rem;
    padding: 0.4rem 0.6rem;
}

.empty-message {
    padding: 50px 20px;
    text-align: center;
}

.empty-message i {
    font-size: 64px;
    color: #ddd;
    margin-bottom: 20px;
}

.empty-message p {
    font-size: 18px;
    color: #999;
    margin-bottom: 20px;
}

.packages-title {
    position: relative;
    text-align: center;
    margin-bottom: 40px;
    font-weight: 700;
    color: #333;
}

.packages-title:after {
    content: "";
    position: absolute;
    bottom: -15px;
    left: 50%;
    width: 80px;
    height: 4px;
    background: linear-gradient(to right, #4facfe, #00f2fe);
    transform: translateX(-50%);
    border-radius: 2px;
}
</style>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-shopping-cart mr-2"></i>套餐购买</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="?page=panel"><i class="fas fa-home"></i> 首页</a></li>
                    <li class="breadcrumb-item active">套餐购买</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <?php if(isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-check-circle mr-2"></i> 操作成功！</h5>
            <?php echo htmlspecialchars($_SESSION['success_msg']); unset($_SESSION['success_msg']); ?>
        </div>
        <?php endif; ?>
        <?php if(isset($_SESSION['error_msg'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-exclamation-circle mr-2"></i> 操作失败！</h5>
            <?php echo htmlspecialchars($_SESSION['error_msg']); unset($_SESSION['error_msg']); ?>
        </div>
        <?php endif; ?>

        <!-- 账户余额部分 -->
        <div class="row">
            <div class="col-md-12">
                <div class="balance-card">
                    <div class="card-body py-4">
                        <div class="balance-icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="balance-label">您的账户余额</div>
                                <div class="balance-amount mb-3">￥<?php echo number_format($userBalance, 2); ?></div>
                                <button type="button" class="btn btn-light mr-2" data-toggle="modal" data-target="#redeemModal">
                                    <i class="fas fa-ticket-alt mr-1"></i> 使用兑换码
                                </button>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-light mt-3 mt-md-0">
                                    <i class="fas fa-info-circle mr-2"></i> 余额可用于购买套餐或充值流量，兑换码可从管理员处获取。
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <h2 class="packages-title mt-5 mb-4">选择适合您的套餐</h2>

        <div class="row">
            <?php if(count($packages) > 0): ?>
                <?php foreach($packages as $index => $package): ?>
                    <?php
                    $group = $gm->getGroupInfoByName($package['group_name']);
                    $traffic = isset($group['traffic']) ? round($group['traffic'] / 1024, 2) : '未知';
                    $proxies = $group['proxies'] ?? '未知';
                    $inbound = isset($group['inbound']) ? round($group['inbound'] / 1024, 2) : '未知';
                    $outbound = isset($group['outbound']) ? round($group['outbound'] / 1024, 2) : '未知';
                    $duration = isset($package['duration']) && $package['duration'] > 0 ? $package['duration'] . ' 天' : '永久';
                    
                    // 选择颜色和图标
                    $colorIndex = $index % count($packageColors);
                    $iconIndex = $index % count($packageIcons);
                    $color = $packageColors[$colorIndex];
                    $icon = $packageIcons[$iconIndex];
                    
                    // 判断是否为推荐套餐
                    $isPopular = ($index % 3 == 2);
                    ?>
                    <div class="col-md-4">
                        <div class="package-card">
                            <?php if($isPopular): ?>
                            <div class="popular-badge">推荐</div>
                            <?php endif; ?>
                            <div class="package-header <?php echo $color; ?>">
                                <div class="package-icon">
                                    <i class="<?php echo $icon; ?>"></i>
                                </div>
                                <h3><?php echo htmlspecialchars($package['name']); ?></h3>
                                <div class="price">￥<?php echo htmlspecialchars($package['price']); ?></div>
                            </div>
                            <div class="package-body">
                                <p class="text-center text-muted"><?php echo htmlspecialchars($package['description']); ?></p>
                                
                                <ul class="feature-list">
                                    <li>
                                        <i class="fas fa-calendar-alt text-primary"></i>
                                        <span class="feature-label">有效期</span>
                                        <span class="feature-value"><?php echo $duration; ?></span>
                                    </li>
                                    <li>
                                        <i class="fas fa-hdd text-info"></i>
                                        <span class="feature-label">流量</span>
                                        <span class="feature-value"><?php echo $traffic; ?> GB</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-network-wired text-success"></i>
                                        <span class="feature-label">隧道数</span>
                                        <span class="feature-value"><?php echo $proxies; ?> 条</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-upload text-warning"></i>
                                        <span class="feature-label">上行速率</span>
                                        <span class="feature-value"><?php echo $inbound; ?> MB/s</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-download text-danger"></i>
                                        <span class="feature-label">下行速率</span>
                                        <span class="feature-value"><?php echo $outbound; ?> MB/s</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="package-footer">
                                <form method="post" action="?action=buypackage" onsubmit="return confirmPurchase('<?php echo htmlspecialchars($package['name']); ?>', '<?php echo htmlspecialchars($package['price']); ?>');">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['token']; ?>">
                                    <input type="hidden" name="package_id" value="<?php echo $package['id']; ?>">
                                    <button type="submit" name="buy_package" class="btn btn-lg btn-block <?php echo $isPopular ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                        <i class="fas fa-shopping-cart mr-1"></i> 立即购买
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="empty-message">
                        <i class="fas fa-shopping-basket"></i>
                        <p>当前没有可用的套餐</p>
                        <div class="text-muted">请联系管理员添加套餐或稍后再试</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php
        $userOrders = $pm->getUserOrders($_SESSION['user']);
        ?>
        <div class="row mt-5">
            <div class="col-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-history mr-2"></i> 我的购买记录</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped history-table">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-hashtag mr-1"></i> 订单ID</th>
                                        <th><i class="fas fa-box mr-1"></i> 套餐名称</th>
                                        <th><i class="fas fa-tag mr-1"></i> 价格</th>
                                        <th><i class="fas fa-clock mr-1"></i> 购买时间</th>
                                        <th><i class="fas fa-calendar-times mr-1"></i> 到期时间</th>
                                        <th><i class="fas fa-info-circle mr-1"></i> 状态</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(count($userOrders) > 0): ?>
                                        <?php foreach($userOrders as $order): ?>
                                            <?php
                                            // 判断订单状态
                                            $now = time();
                                            $status = '';
                                            if($order['expire_time'] == 0) {
                                                $status = '<span class="badge badge-success">永久有效</span>';
                                            } elseif($order['expire_time'] < $now) {
                                                $status = '<span class="badge badge-danger">已过期</span>';
                                            } else {
                                                $daysLeft = ceil(($order['expire_time'] - $now) / 86400);
                                                $status = '<span class="badge badge-info">剩余 ' . $daysLeft . ' 天</span>';
                                            }
                                            ?>
                                            <tr>
                                                <td>#<?php echo $order['id']; ?></td>
                                                <td><?php echo htmlspecialchars($order['name']); ?></td>
                                                <td>¥<?php echo number_format($order['price'], 2); ?></td>
                                                <td><span data-toggle="tooltip" title="<?php echo date('Y-m-d H:i:s', $order['order_time']); ?>"><?php echo date('Y-m-d', $order['order_time']); ?></span></td>
                                                <td>
                                                    <?php if($order['expire_time'] > 0): ?>
                                                        <span data-toggle="tooltip" title="<?php echo date('Y-m-d H:i:s', $order['expire_time']); ?>">
                                                            <?php echo date('Y-m-d', $order['expire_time']); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge badge-success">永久</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $status; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <i class="fas fa-shopping-cart text-muted mb-2" style="font-size: 24px;"></i>
                                                <p class="text-muted mb-0">您还没有购买任何套餐</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 兑换码模态框 -->
<div class="modal fade" id="redeemModal" tabindex="-1" role="dialog" aria-labelledby="redeemModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="redeemModalLabel"><i class="fas fa-ticket-alt mr-2"></i> 使用兑换码</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="?action=redeemcode" class="redeem-form">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['token']; ?>">
                    <div class="form-group">
                        <label for="redeem_code">请输入兑换码</label>
                        <input type="text" id="redeem_code" name="redeem_code" class="form-control" placeholder="输入兑换码" required>
                        <small class="form-text text-muted">兑换码可用于充值余额、获取流量或激活特定套餐</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check mr-1"></i> 确认兑换</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // 初始化工具提示
    $('[data-toggle="tooltip"]').tooltip();
    
    // 页面加载动画
    $('.package-card').css('opacity', 0);
    $('.balance-card').css('opacity', 0);
    
    setTimeout(function() {
        $('.balance-card').animate({
            opacity: 1
        }, 500);
        
        setTimeout(function() {
            $('.package-card').each(function(i) {
                $(this).delay(i * 150).animate({
                    opacity: 1
                }, 500);
            });
        }, 200);
    }, 100);
});

function confirmPurchase(packageName, packagePrice) {
    return Swal.fire({
        title: '确认购买',
        html: `您确定要购买 <strong>${packageName}</strong> 套餐吗？<br>费用: <strong>￥${packagePrice}</strong>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: '确认购买',
        cancelButtonText: '取消'
    }).then((result) => {
        return result.isConfirmed;
    });
}
</script> 