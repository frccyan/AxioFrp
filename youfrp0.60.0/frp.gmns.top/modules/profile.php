<?php
namespace chhcn;

use chhcn;

$group_title = "账号管理";
$page_title = "用户信息";

if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(16));
}

$pm = new chhcn\ProxyManager();
$rs = Database::querySingleLine("users", Array("username" => $_SESSION['user']));

if(!$rs) {
	exit("<script>location='?page=login';</script>");
}

$um          = new chhcn\UserManager();
$ls          = $um->getLimit($_SESSION['user']);
$inbound     = round($ls['inbound'] / 1024 * 8);
$outbound    = round($ls['outbound'] / 1024 * 8);
$speed_limit = "{$inbound}Mbps 上行 / {$outbound}Mbps 下行";
$signinfo    = Database::querySingleLine("sign", Array("username" => $_SESSION['user']));
$token       = Database::querySingleLine("tokens", Array("username" => $_SESSION['user']))["token"] ?? "Unknown";
?>
<style type="text/css">
.fix-text p {
	margin-bottom: 4px;
}
.sub-heading {
	width: calc(100% - 16px);
    height: 0!important;
    border-top: 1px solid #e9f1f1!important;
    text-align: center!important;
    margin-top: 32px!important;
    margin-bottom: 40px!important;
	margin-left: 7px;
}
.sub-heading span {
    display: inline-block;
    position: relative;
    padding: 0 17px;
    top: -11px;
    font-size: 16px;
    color: #058;
    background-color: #fff;
}
.profile-user-img {
    width: 100px;
    height: 100px;
    margin: 0 auto;
    display: block;
    border-radius: 50%;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}
.badge-vip {
    background-color: #f39c12;
    color: white;
}
.badge-admin {
    background-color: #dc3545;
    color: white;
}
.badge-default {
    background-color: #28a745;
    color: white;
}
.lighter {
    font-weight: lighter;
}
.list-group-item {
    border-left: none;
    border-right: none;
}
.stats-box {
    border-radius: 5px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    padding: 10px;
    margin-bottom: 20px;
}
.stats-box .stats-icon {
    font-size: 24px;
    margin-bottom: 10px;
}
.stats-box .stats-number {
    font-size: 24px;
    font-weight: bold;
}
</style>

<div class="content-header" oncopy="return false" oncut="return false;" onselectstart="return false" oncontextmenu="return false">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark"><?php echo $page_title; ?>&nbsp;&nbsp;<small class="text-muted text-xs">查看和管理您的账号信息</small></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">
                        <a href="?page=panel&module=home">
                            <i class="nav-icon fas fa-home"></i>
                        </a>
                    </li>
                    <li class="breadcrumb-item active">
                        <?php echo $group_title; ?>
                    </li>
                    <li class="breadcrumb-item active">
                        <?php echo $page_title; ?>
                    </li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content" oncopy="return false" oncut="return false;" onselectstart="return false" oncontextmenu="return false">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header border-0">
                        <div class="d-flex justify-content-between">
                            <h3 class="card-title"><i class="fas fa-user-circle"></i> 账号信息</h3>
                        </div>
                    </div>
                    <div class="card-body box-profile">
                        <div class="text-center">
                            <img class="profile-user-img img-fluid img-circle" src="https://www.loliapi.com/acg/pp/" alt="用户头像">
                        </div>
                        <h3 class="profile-username text-center">
                            <?php echo htmlspecialchars($_SESSION['user']); ?>
                        </h3>
                        <p class="text-muted text-center">
                            <?php 
                            $badge_class = "badge-default";
                            $group_display = "普通会员";
                            if ($rs['group'] == "admin") {
                                $badge_class = "badge-admin";
                                $group_display = "管理员";
                            } elseif (strpos($rs['group'], "vip") !== false) {
                                $badge_class = "badge-vip";
                                $group_display = "VIP会员";
                            }
                            ?>
                            <span class="badge <?php echo $badge_class; ?>"><?php echo $group_display; ?></span>
                        </p>
                        
                        <ul class="list-group list-group-unbordered mb-3 lighter">
                            <li class="list-group-item">
                                <b><i class="fas fa-id-badge"></i> 用户 ID</b>
                                <a class="float-right"><?php echo $rs['id']; ?></a>
                            </li>
                            <li class="list-group-item">
                                <b><i class="fas fa-envelope"></i> 注册邮箱</b>
                                <a class="float-right"><?php echo htmlspecialchars($_SESSION['mail']); ?></a>
                            </li>
                            <li class="list-group-item">
                                <b><i class="fas fa-calendar-alt"></i> 注册时间</b>
                                <a class="float-right"><?php echo date("Y年m月d日", $rs['regtime']); ?></a>
                            </li>
                            <li class="list-group-item">
                                <b><i class="fas fa-key"></i> 访问密钥</b>
                                <div>
                                    <a href="javascript:void(0);" id="token-display" onclick="toggleToken()" class="float-right">
                                        <i class="fas fa-eye"></i> 显示密钥
                                    </a>
                                    <span id="token-value" style="display:none;" class="float-right text-monospace"><?php echo htmlspecialchars($token); ?></span>
                                </div>
                            </li>
                        </ul>
                        
                        <a href="javascript:resetToken();" class="btn btn-primary btn-block">
                            <i class="fas fa-sync-alt"></i> 重置访问密钥
                        </a>
                        <a href="?page=logout&csrf=<?php echo $_SESSION['token']; ?>" class="btn btn-danger btn-block mt-2">
                            <i class="fas fa-sign-out-alt"></i> 退出登录
                        </a>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header border-0">
                        <div class="d-flex justify-content-between">
                            <h3 class="card-title"><i class="fas fa-chart-line"></i> 资源统计</h3>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-tachometer-alt text-info"></i> <b>剩余流量</b>
                                </div>
                                <span class="badge badge-info badge-pill"><?php echo htmlspecialchars(round($rs['traffic'] / 1024, 2)); ?> GB</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-chart-area text-warning"></i> <b>今日已用</b>
                                </div>
                                <span class="badge badge-warning badge-pill"><?php echo htmlspecialchars(round($um->getTodayTraffic($_SESSION['user']) / 1024 / 1024 / 1024, 2)); ?> GB</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-project-diagram text-success"></i> <b>隧道数量</b>
                                </div>
                                <span class="badge badge-success badge-pill"><?php echo htmlspecialchars($rs['proxies']); ?> 条</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-bolt text-primary"></i> <b>宽带速度</b>
                                </div>
                                <span class="badge badge-primary badge-pill"><?php echo htmlspecialchars($speed_limit); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-server text-secondary"></i> <b>已建隧道</b>
                                </div>
                                <span class="badge badge-secondary badge-pill"><?php echo htmlspecialchars($pm->getUserProxies($_SESSION['user'])); ?> 条</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="row">
                    <div class="col-md-4">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?php echo htmlspecialchars($signinfo['totalsign'] ?? 0); ?></h3>
                                <p>总计签到天数</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?php echo htmlspecialchars($signinfo['totaltraffic'] ?? 0); ?></h3>
                                <p>签到获得流量 (GB)</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-database"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?php echo date("m-d", $signinfo['signdate'] ?? time()); ?></h3>
                                <p>上次签到日期</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header border-0">
                        <div class="d-flex justify-content-between">
                            <h3 class="card-title"><i class="fas fa-lock"></i> 修改密码</h3>
                        </div>
                    </div>
                    <form method="post" action="?page=panel&module=profile&action=updatepass&csrf=<?php echo $_SESSION['token']; ?>">
                        <div class="card-body">
                            <div class="form-group row">
                                <label for="oldpass" class="col-sm-3 col-form-label">旧密码</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                                        </div>
                                        <input type="password" class="form-control" id="oldpass" name="oldpass" placeholder="请输入您当前使用的密码">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="newpass" class="col-sm-3 col-form-label">新密码</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        </div>
                                        <input type="password" class="form-control" id="newpass" name="newpass" placeholder="请输入新密码">
                                    </div>
                                </div>
                            </div>
                             <div class="form-group row">
                                <label for="newpass1" class="col-sm-3 col-form-label">确认新密码</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        </div>
                                        <input type="password" class="form-control" id="newpass1" name="newpass1" placeholder="请再次输入新密码">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> 请使用包含字母、数字和符号的强密码，保障账号安全
                            </div>
                            <button type="submit" class="btn btn-primary float-right"><i class="fas fa-save"></i> 确认修改</button>
                        </div>
                    </form>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-ad"></i> 广告信息</h3>
                    </div>
                    <div class="card-body p-0 text-center">
						<a href="https://www.example.com" target="_blank">
							<img src="https://api.lqbby.com/api/pc" alt="随机动漫图片" style="max-width: 46%; height: auto; display: ruby;">
						</a>
					</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
var csrf_token = "<?php echo $_SESSION['token']; ?>";
function resetToken() {
	if (!confirm("您确定要重置您的访问密钥吗？旧的密钥将立即失效。")) {
		return;
	}
	var htmlobj = $.ajax({
		type: 'POST',
		url: "?action=resettoken&page=panel&module=profile&csrf=" + csrf_token,
		async: true,
		error: function() {
			alert("错误：" + htmlobj.responseText);
			return;
		},
		success: function() {
			alert(htmlobj.responseText);
			window.location.reload();
			return;
		}
	});
}

function toggleToken() {
    var tokenDisplay = document.getElementById('token-display');
    var tokenValue = document.getElementById('token-value');
    
    if (tokenValue.style.display === 'none') {
        tokenValue.style.display = 'block';
        tokenDisplay.innerHTML = '<i class="fas fa-eye-slash"></i> 隐藏密钥';
    } else {
        tokenValue.style.display = 'none';
        tokenDisplay.innerHTML = '<i class="fas fa-eye"></i> 显示密钥';
    }
}
</script>
