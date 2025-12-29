<?php
namespace chhcn;

use chhcn;

include(ROOT . "/core/Parsedown.php");

$markdown = new Parsedown();
$markdown->setSafeMode(true);
$markdown->setBreaksEnabled(true);
$markdown->setUrlsLinked(true);
//$page_title = "管理面板";
$rs = Database::querySingleLine("users", Array("username" => $_SESSION['user']));

if(!$rs) {
	exit("<script>location='?page=login';</script>");
}

$um = new chhcn\UserManager();
$ls = $um->getLimit($_SESSION['user']);
$inbound = round($ls['inbound'] / 1024 * 8);
$outbound = round($ls['outbound'] / 1024 * 8);
$speed_limit = "{$inbound}Mbps 上行 / {$outbound}Mbps 下行";
$traffic = $rs['traffic'] - round($um->getTodayTraffic($_SESSION['user']) / 1024 / 1024);
if($traffic < 0) {
	$traffic = 0;
}

// 获取用户密钥
$token = Database::querySingleLine("tokens", Array("username" => $_SESSION['user']))["token"] ?? "未知";

// 计算流量使用百分比
$totalTraffic = $rs['traffic'];
$usedTraffic = round($um->getTodayTraffic($_SESSION['user']) / 1024 / 1024);
$trafficPercentage = ($totalTraffic > 0) ? min(100, round(($usedTraffic / $totalTraffic) * 100)) : 0;

// 获取可用节点列表, 排除状态为401（隐藏）的节点
$query = "SELECT * FROM `nodes` WHERE `group` LIKE '%" . $rs['group'] . ";%' AND `status` != '401'";
$nodes = Database::query("nodes", $query, "AND", true);

// 统计在线节点数量
$onlineNodeCount = 0;
$totalNodeCount = 0;
if ($nodes && $nodes->num_rows > 0) {
    $totalNodeCount = $nodes->num_rows;
    $nodes->data_seek(0);
    while ($node = $nodes->fetch_array()) {
        if ($node['status'] == '200') {
            $onlineNodeCount++;
        }
    }
    $nodes->data_seek(0);
}
?>
<style type="text/css">
.fix-text p {
	margin-bottom: 4px;
}
.fix-text pre {
	background: rgba(0,0,0,0.05);
	border-radius: 4px;
}
.fix-image img {
	max-width: 100%;
}
.nodes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}
.node-card {
    background-color: #fff;
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1.5rem;
    transition: all 0.3s ease-in-out;
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
}
.node-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.07);
}
.node-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: #007bff;
    opacity: 0;
    transition: opacity 0.3s;
}
.node-card:hover::before {
    opacity: 1;
}
.node-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}
.node-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: #343a40;
}
.node-description {
    font-size: 0.9rem;
    color: #6c757d;
    margin-top: auto; /* Push description to the bottom if cards have different heights */
}
.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 500;
}
.status-badge.badge-success {
    background-color: #28a745;
    color: white;
}
.status-badge.badge-secondary {
    background-color: #6c757d;
    color: white;
}
.status-badge.badge-warning {
    background-color: #ffc107;
    color: #212529;
}
.profile-info-row {
    display: flex;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}
.profile-info-row:last-child {
    border-bottom: none;
}
.profile-info-label {
    width: 40%;
    font-weight: 500;
    color: #495057;
    display: flex;
    align-items: center;
}
.profile-info-value {
    width: 60%;
    color: #6c757d;
}
.profile-avatar {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    margin-right: 15px;
    object-fit: cover;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.profile-header {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}
.stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-top: 1rem;
}
.stat-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
    transition: all 0.3s;
    border: 1px solid #e9ecef;
}
.stat-card:hover {
    background: #fff;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}
.stat-icon {
    font-size: 24px;
    margin-bottom: 5px;
    color: #007bff;
}
.stat-value {
    font-size: 1.25rem;
    font-weight: 600;
    color: #343a40;
    margin: 5px 0;
}
.stat-label {
    font-size: 0.85rem;
    color: #6c757d;
}
.traffic-progress {
    height: 10px;
    margin-top: 10px;
    border-radius: 5px;
    background-color: #e9ecef;
    overflow: hidden;
}
.traffic-bar {
    height: 100%;
    background-color: #007bff;
    border-radius: 5px;
}
.token-field {
    position: relative;
}
.token-value {
    padding-right: 40px;
    word-break: break-all;
    font-family: monospace;
}
.token-copy {
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #007bff;
    cursor: pointer;
}
.fancy-title {
    position: relative;
    padding-left: 15px;
    font-weight: 600;
}
.fancy-title::before {
    content: "";
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 5px;
    height: 18px;
    border-radius: 2px;
    background: #007bff;
}
.card-header {
    background-color: #fff;
    border-bottom: 1px solid rgba(0,0,0,.08);
}
.card-header .card-title {
    display: flex;
    align-items: center;
}
.card-header .card-title i {
    margin-right: 7px;
    color: #007bff;
}
.ad-banner {
    border-radius: 8px;
    overflow: hidden;
    transition: transform 0.3s;
}
.ad-banner:hover {
    transform: translateY(-3px);
}
</style>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-home mr-2"></i>控制面板</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="?page=panel"><i class="fas fa-tachometer-alt"></i> 控制面板</a></li>
                    <li class="breadcrumb-item active">首页</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <!-- 欢迎消息 -->
        <div class="alert alert-info alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-info-circle"></i> 欢迎回来！</h5>
            <p>欢迎使用 YouFrpc2 内网穿透系统，您可以在这里管理您的隧道、查看流量使用情况和获取服务器状态。</p>
        </div>
        
        <!-- 状态卡片 -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?php echo htmlspecialchars(round($traffic / 1024, 2)); ?> GB</h3>
                        <p>剩余流量</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-area"></i>
                    </div>
                    <a href="?page=panel&module=traffic" class="small-box-footer">
                        查看详情 <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?php echo htmlspecialchars($rs['proxies']); ?></h3>
                        <p>隧道数量</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <a href="?page=panel&module=tunnelmanage" class="small-box-footer">
                        管理隧道 <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?php echo $onlineNodeCount; ?> <small>/ <?php echo $totalNodeCount; ?></small></h3>
                        <p>在线节点</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-server"></i>
                    </div>
                    <a href="?page=panel&module=nodes" class="small-box-footer">
                        查看节点 <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?php echo htmlspecialchars($speed_limit); ?></h3>
                        <p>带宽限制</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <a href="?page=panel&module=profile" class="small-box-footer">
                        账户详情 <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-4">
                <div class="card card-primary card-outline">
                    <div class="card-header border-0">
                        <h3 class="card-title"><i class="fas fa-user-circle"></i> 用户信息</h3>
                    </div>
                    <div class="card-body">
                        <div class="profile-header">
                            <img src="https://www.loliapi.com/acg/pp/" class="profile-avatar" alt="用户头像">
                            <div>
                                <h3 class='text-primary mb-0'><?php echo htmlspecialchars($_SESSION['user']); ?></h3>
                                <span class="text-muted"><?php echo ucfirst($rs['group']); ?> 会员</span>
                            </div>
                        </div>
                        
                        <div class="profile-info-row">
                            <div class="profile-info-label"><i class="fas fa-envelope mr-2"></i> 注册邮箱</div>
                            <div class="profile-info-value"><?php echo htmlspecialchars($_SESSION['mail']); ?></div>
                        </div>
                        <div class="profile-info-row">
                            <div class="profile-info-label"><i class="fas fa-calendar-alt mr-2"></i> 注册时间</div>
                            <div class="profile-info-value"><?php echo date("Y-m-d", $rs['regtime']); ?></div>
                        </div>
                        <div class="profile-info-row">
                            <div class="profile-info-label"><i class="fas fa-network-wired mr-2"></i> 宽带速率</div>
                            <div class="profile-info-value"><?php echo htmlspecialchars($speed_limit); ?></div>
                        </div>
                        <div class="profile-info-row">
                            <div class="profile-info-label"><i class="fas fa-key mr-2"></i> 访问密钥</div>
                            <div class="profile-info-value token-field">
                                <div class="token-value" id="user-token"><?php echo htmlspecialchars($token); ?></div>
                                <button class="token-copy" onclick="copyToken()" title="复制密钥">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <h6><i class="fas fa-chart-pie mr-2"></i> 流量使用情况</h6>
                            <div class="d-flex justify-content-between">
                                <span>已用：<?php echo round($usedTraffic / 1024, 2); ?> GB</span>
                                <span>总计：<?php echo round($totalTraffic / 1024, 2); ?> GB</span>
                            </div>
                            <div class="traffic-progress">
                                <div class="traffic-bar" style="width: <?php echo $trafficPercentage; ?>%;"></div>
                            </div>
                            <small class="text-muted">使用率：<?php echo $trafficPercentage; ?>%</small>
                        </div>
                        
                        <div class="stats-grid mt-4">
                            <a href="?page=panel&module=sign" class="stat-card">
                                <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                                <div class="stat-value">签到</div>
                                <div class="stat-label">每日获取免费流量</div>
                            </a>
                            <a href="?page=panel&module=addproxy" class="stat-card">
                                <div class="stat-icon"><i class="fas fa-plus-circle"></i></div>
                                <div class="stat-value">创建</div>
                                <div class="stat-label">创建新的隧道</div>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card card-primary card-outline">
                    <div class="card-header border-0">
                        <h3 class="card-title"><i class="fas fa-bullhorn"></i> 站点公告</h3>
                    </div>
                    <div class="card-body fix-text fix-image">
						<?php echo $markdown->text(Settings::get("broadcast", "暂时没有公告信息")); ?>
                    </div>
                </div>
			</div>
            
			<div class="col-lg-8">
				<div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-server"></i> 可用节点</h3>
                        <div class="card-tools">
                            <span class="badge badge-success mr-2">
                                <i class="fas fa-check-circle"></i> 在线：<?php echo $onlineNodeCount; ?>
                            </span>
                            <span class="badge badge-secondary">
                                <i class="fas fa-server"></i> 总计：<?php echo $totalNodeCount; ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="nodes-grid">
                            <?php
                            if ($nodes && $nodes->num_rows > 0) {
                                while ($node = $nodes->fetch_array()) {
                                    $status_badge = '';
                                    $node_icon = '';
                                    
                                    switch ($node['status']) {
                                        case '200':
                                            $status_badge = '<span class="badge status-badge badge-success"><i class="fas fa-check-circle mr-1"></i> 在线</span>';
                                            $node_icon = '<i class="fas fa-server text-success mr-2"></i>';
                                            break;
                                        case '500':
                                            $status_badge = '<span class="badge status-badge badge-secondary"><i class="fas fa-times-circle mr-1"></i> 离线</span>';
                                            $node_icon = '<i class="fas fa-server text-secondary mr-2"></i>';
                                            break;
                                        case '403':
                                            $status_badge = '<span class="badge status-badge badge-warning"><i class="fas fa-exclamation-triangle mr-1"></i> 禁用</span>';
                                            $node_icon = '<i class="fas fa-server text-warning mr-2"></i>';
                                            break;
                                        default:
                                            $status_badge = '<span class="badge status-badge badge-secondary"><i class="fas fa-question-circle mr-1"></i> 未知</span>';
                                            $node_icon = '<i class="fas fa-server text-secondary mr-2"></i>';
                                            break;
                                    }
                                    
                                    echo '
                                    <div class="node-card">
                                        <div class="node-card-header">
                                            <div>
                                                <span style="font-size: 0.8rem; color: #6c757d;"><i class="fas fa-hashtag"></i> ID: ' . $node[0] . '</span>
                                                <h5 class="node-name" style="margin-top: 2px;">' . $node_icon . htmlspecialchars($node[1]) . '</h5>
                                            </div>
                                            ' . $status_badge . '
                                        </div>
                                        <p class="node-description">' . htmlspecialchars($node[2]) . '</p>
                                    </div>';
                                }
                            } else {
                                echo "<div style='grid-column: 1 / -1;' class='text-center py-5'>
                                    <i class='fas fa-server fa-3x text-muted mb-3'></i>
                                    <p>暂无可用节点</p>
                                    </div>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
                
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-question-circle"></i> 使用帮助</h3>
                    </div>
                    <div class="card-body fix-text fix-image">
						<?php echo $markdown->text(Settings::get("helpinfo", "暂时没有帮助信息")); ?>
                    </div>
                </div>
                
				<div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-ad"></i> 推荐服务</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="ad-banner mb-4">
                                    <a href="?page=panel&module=buypackage" class="d-block">
                                        <div class="card bg-gradient-primary mb-0">
                                            <div class="card-body">
                                                <h5><i class="fas fa-crown mr-2"></i> 升级到高级会员</h5>
                                                <p>享受更高的带宽、更多的隧道数量和专属客户支持</p>
                                                <div class="mt-3 text-right">
                                                    <span class="btn btn-outline-light btn-sm">立即升级</span>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="ad-banner">
                                    <a href="?page=panel&module=download" class="d-block">
                                        <div class="card bg-gradient-success mb-0">
                                            <div class="card-body">
                                                <h5><i class="fas fa-download mr-2"></i> 下载客户端</h5>
                                                <p>获取最新版本的客户端，支持Windows、Linux和macOS</p>
                                                <div class="mt-3 text-right">
                                                    <span class="btn btn-outline-light btn-sm">立即下载</span>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
		</div>
	</div>
</div>

<script>
function copyToken() {
    var tokenText = document.getElementById("user-token");
    var textArea = document.createElement("textarea");
    textArea.value = tokenText.textContent;
    document.body.appendChild(textArea);
    textArea.select();
    document.execCommand("Copy");
    textArea.remove();
    
    // 显示复制成功提示
    var tooltip = document.createElement("div");
    tooltip.innerHTML = "密钥已复制！";
    tooltip.style.position = "fixed";
    tooltip.style.background = "rgba(0,0,0,0.7)";
    tooltip.style.color = "white";
    tooltip.style.padding = "5px 10px";
    tooltip.style.borderRadius = "4px";
    tooltip.style.zIndex = "9999";
    tooltip.style.top = "50%";
    tooltip.style.left = "50%";
    tooltip.style.transform = "translate(-50%, -50%)";
    document.body.appendChild(tooltip);
    
    // 1秒后移除提示
    setTimeout(function() {
        tooltip.style.opacity = "0";
        tooltip.style.transition = "opacity 0.5s";
        setTimeout(function() {
            document.body.removeChild(tooltip);
        }, 500);
    }, 1000);
}

function refreshAnimeImage() {
    var img = document.getElementById('anime-image');
    var timestamp = new Date().getTime();
    img.src = 'https://api.lqbby.com/api/pc?' + timestamp;
}

// 页面加载时的动画效果
$(document).ready(function() {
    $('.small-box').css('opacity', 0);
    $('.card').css('opacity', 0);
    
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
});
</script>