<?php
namespace chhcn;

use chhcn;

include(ROOT . "/core/Parsedown.php");

$markdown = new Parsedown();
$markdown->setSafeMode(true);
$markdown->setBreaksEnabled(true);
$markdown->setUrlsLinked(true);
//$page_title = "隧道列表";
$rs = Database::querySingleLine("users", Array("username" => $_SESSION['user']));
$pm = new chhcn\ProxyManager();
$nm = new chhcn\NodeManager();
$um = new chhcn\UserManager();

if(!$rs) {
	exit("<script>location='?page=login';</script>");
}

if(isset($_GET['getproxyinfo']) && preg_match("/^[0-9]{1,10}$/", $_GET['getproxyinfo'])) {
	ob_clean();
	chhcn\Utils::checkCsrf();
	$rs = $pm->getProxyInfo($_GET['getproxyinfo']);
	if($rs) {
		if(isset($rs['username']) && $rs['username'] == $_SESSION['user']) {
			$ns = $nm->getNodeInfo($rs['node']);
			$domain = "";
			$domains = json_decode($rs['domain'], true);
			if($domains && !empty($domains)) {
				for($i = 0;$i < count($domains);$i++) {
					$domain .= $domains[$i];
					if($i < count($domains) - 1) { $domain .= ", "; }
				}
			}
			?>
			<style type="text/css">
			.proxyinfo tr th {
				width: 30%;
				text-align: right;
				padding-right: 16px;
				font-weight: 500;
				color: #555;
			}
			.proxyinfo tr td {
				font-weight: 400;
			}
			</style>
			<div class="proxy-detail-header mb-3">
			    <div class="d-flex align-items-center">
			        <div class="proxy-icon <?php echo strtolower($rs['proxy_type']); ?>-icon mr-3">
			            <?php 
			            $iconMap = [
			                'tcp' => '<i class="fas fa-network-wired"></i>',
			                'udp' => '<i class="fas fa-stream"></i>',
			                'http' => '<i class="fas fa-globe"></i>',
			                'https' => '<i class="fas fa-lock"></i>',
			                'stcp' => '<i class="fas fa-shield-alt"></i>',
			                'xtcp' => '<i class="fas fa-plug"></i>'
			            ];
			            echo $iconMap[strtolower($rs['proxy_type'])] ?? '<i class="fas fa-project-diagram"></i>';
			            ?>
			        </div>
			        <div>
			            <h5 class="mb-1"><?php echo $rs['proxy_name']; ?></h5>
			            <p class="mb-0 text-muted"><span class="badge badge-<?php echo $rs['status'] == "0" ? "success" : "danger"; ?> mr-2"><?php echo $rs['status'] == "0" ? "已启用" : "已禁用"; ?></span> ID: <?php echo $rs['id']; ?></p>
			        </div>
			    </div>
			</div>
			<div class="table-responsive">
    			<table class="table table-bordered table-striped proxyinfo">
				<tr>
    					<th><i class="fas fa-server mr-2"></i>服务器</th>
					<td><?php echo "{$ns['name']} ({$ns['hostname']})"; ?></td>
				</tr>
				<tr>
    					<th><i class="fas fa-tag mr-2"></i>隧道类型</th>
    					<td><span class="badge badge-info"><?php echo strtoupper($rs['proxy_type']); ?> 映射</span></td>
				</tr>
				<tr>
    					<th><i class="fas fa-desktop mr-2"></i>本地地址</th>
    					<td><code><?php echo $rs['local_ip'] == "" ? "127.0.0.1" : $rs['local_ip']; ?></code></td>
				</tr>
				<tr>
    					<th><i class="fas fa-plug mr-2"></i>本地端口</th>
    					<td><code><?php echo $rs['local_port'] == "" ? "80" : $rs['local_port']; ?></code></td>
				</tr>
				<tr>
    					<th><i class="fas fa-ethernet mr-2"></i>远程端口</th>
    					<td><?php echo $rs['remote_port'] == "" ? "<span class='text-muted'>无</span>" : "<code>{$rs['remote_port']}</code>"; ?></td>
				</tr>
				<tr>
    					<th><i class="fas fa-lock mr-2"></i>连接加密</th>
    					<td>
    					    <?php if($rs['use_encryption'] == "true"): ?>
    					    <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> 启用</span>
    					    <?php else: ?>
    					    <span class="badge badge-secondary"><i class="fas fa-times-circle mr-1"></i> 禁用</span>
    					    <?php endif; ?>
    					</td>
				</tr>
				<tr>
    					<th><i class="fas fa-compress-alt mr-2"></i>数据压缩</th>
    					<td>
    					    <?php if($rs['use_compression'] == "true"): ?>
    					    <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> 启用</span>
    					    <?php else: ?>
    					    <span class="badge badge-secondary"><i class="fas fa-times-circle mr-1"></i> 禁用</span>
    					    <?php endif; ?>
    					</td>
				</tr>
				<tr>
    					<th><i class="fas fa-globe mr-2"></i>绑定域名</th>
    					<td><?php echo $domain == "" ? "<span class='text-muted'>无</span>" : "<code>{$domain}</code>"; ?></td>
				</tr>
				<tr>
    					<th><i class="fas fa-link mr-2"></i>URI 绑定</th>
    					<td><?php echo $rs['locations'] == "" ? "<span class='text-muted'>无</span>" : "<code>{$rs['locations']}</code>"; ?></td>
				</tr>
				<tr>
    					<th><i class="fas fa-heading mr-2"></i>Host 重写</th>
    					<td><?php echo $rs['host_header_rewrite'] == "" ? "<span class='text-muted'>无</span>" : "<code>{$rs['host_header_rewrite']}</code>"; ?></td>
				</tr>
				<tr>
    					<th><i class="fas fa-key mr-2"></i>连接密码</th>
    					<td><?php echo $rs['sk'] == "" ? "<span class='text-muted'>无</span>" : "<code>{$rs['sk']}</code>"; ?></td>
				</tr>
				<tr>
    					<th><i class="fas fa-map-marker-alt mr-2"></i>X-From-Where</th>
    					<td><?php echo $rs['header_X-From-Where'] == "" ? "<span class='text-muted'>无</span>" : "<code>{$rs['header_X-From-Where']}</code>"; ?></td>
				</tr>
			</table>
			</div>
			<div class="text-right mt-3">
                <button type="button" class="btn btn-sm btn-outline-info" onclick="copyConfig(<?php echo $rs['id']; ?>)">
                    <i class="fas fa-copy mr-1"></i> 复制配置
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleProxy(<?php echo $rs['id']; ?>)">
                    <?php if($rs['status'] == "0"): ?>
                    <i class="fas fa-ban mr-1"></i> 禁用隧道
                    <?php else: ?>
                    <i class="fas fa-check-circle mr-1"></i> 启用隧道
                    <?php endif; ?>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteProxy(<?php echo $rs['id']; ?>)">
                    <i class="fas fa-trash mr-1"></i> 删除隧道
                </button>
            </div>
			<?php
			exit;
		} else {
			exit("<div class='alert alert-danger'><i class='fas fa-exclamation-circle mr-2'></i>拒绝访问</div>");
		}
	} else {
		exit("<div class='alert alert-warning'><i class='fas fa-exclamation-triangle mr-2'></i>未找到该隧道</div>");
	}
}

if(isset($_GET['toggle']) && preg_match("/^[0-9]{1,10}$/", $_GET['toggle'])) {
	ob_clean();
	chhcn\Utils::checkCsrf();
	$rs = $pm->getProxyInfo($_GET['toggle']);
	if($rs) {
		if(isset($rs['username']) && $rs['username'] == $_SESSION['user']) {
			if($rs['status'] == '2') {
				exit("<div class='alert alert-warning'><i class='fas fa-exclamation-triangle mr-2'></i>你的流量已经用完，无法开启隧道，充值或签到获取流量后即可恢复</div>");
			} elseif($rs['status'] == '3') {
				exit("<div class='alert alert-danger'><i class='fas fa-ban mr-2'></i>此隧道已经被管理员封禁，无法恢复</div>");
			} else {
				$newStatus = $rs['status'] == "0" ? "1" : "0";
				Database::update("proxies", Array("status" => $newStatus), Array("id" => $_GET['toggle']));
				$nm->closeClient($rs['node'], $um->getUserToken($_SESSION['user']));
				$statusText = $newStatus == "0" ? "启用" : "禁用";
				exit("<div class='alert alert-success'><i class='fas fa-check-circle mr-2'></i>隧道已成功{$statusText}</div>");
			}
		} else {
			exit("<div class='alert alert-danger'><i class='fas fa-exclamation-circle mr-2'></i>拒绝访问</div>");
		}
	} else {
		exit("<div class='alert alert-warning'><i class='fas fa-exclamation-triangle mr-2'></i>未找到该隧道</div>");
	}
}

if(isset($_GET['delete']) && preg_match("/^[0-9]{1,10}$/", $_GET['delete'])) {
	ob_clean();
	chhcn\Utils::checkCsrf();
	$rs = $pm->getProxyInfo($_GET['delete']);
	if($rs) {
		if(isset($rs['username']) && $rs['username'] == $_SESSION['user']) {
			if($rs['status'] == '3') {
				exit("<div class='alert alert-danger'><i class='fas fa-ban mr-2'></i>此隧道已经被管理员封禁，无法删除</div>");
			} else {
				Database::delete("proxies", Array("id" => $rs['id']));
				$nm->closeClient($rs['node'], $um->getUserToken($_SESSION['user']));
				exit("<div class='alert alert-success'><i class='fas fa-check-circle mr-2'></i>隧道删除成功</div>");
			}
		} else {
			exit("<div class='alert alert-danger'><i class='fas fa-exclamation-circle mr-2'></i>拒绝访问</div>");
		}
	} else {
		exit("<div class='alert alert-warning'><i class='fas fa-exclamation-triangle mr-2'></i>未找到该隧道</div>");
	}
}

$use_proxies = $pm->getUserProxies($_SESSION['user']);
$max_proxies = Intval($um->getInfoByUser($_SESSION['user'])['proxies']);

// 获取各种类型隧道数量
$proxy_types = [
    'tcp' => 0,
    'udp' => 0,
    'http' => 0,
    'https' => 0,
    'stcp' => 0,
    'xtcp' => 0
];

$active_proxies = 0;
$ps = Database::query("proxies", Array("username" => $_SESSION['user']));
$ps = Database::toArray($ps);
foreach($ps as $pi) {
    $type = strtolower($pi[3]);
    if(isset($proxy_types[$type])) {
        $proxy_types[$type]++;
    }
    
    if($pi[14] == "0") {
        $active_proxies++;
    }
}

// 获取服务器节点列表
$nodes = [];
$nodes_query = Database::query("nodes", "SELECT * FROM `nodes` WHERE status = 200", true);
$nodes_result = Database::toArray($nodes_query);
foreach($nodes_result as $node) {
    $nodes[$node[0]] = $node[1];
}
?>
<style type="text/css">
.fix-text p {
	margin-bottom: 4px;
}
.proxy-card {
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 20px;
    transition: all 0.3s ease;
    border: 1px solid #eaeaea;
}
.proxy-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.proxy-card .proxy-header {
    padding: 15px;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.proxy-card .proxy-body {
    padding: 15px;
}
.proxy-card .proxy-footer {
    padding: 10px 15px;
    background-color: #f8f9fa;
    border-top: 1px solid #f0f0f0;
}
.proxy-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 18px;
    margin-right: 12px;
}
.tcp-icon {
    background: linear-gradient(135deg, #007bff, #0056b3);
}
.udp-icon {
    background: linear-gradient(135deg, #6f42c1, #4527a0);
}
.http-icon {
    background: linear-gradient(135deg, #28a745, #145523);
}
.https-icon {
    background: linear-gradient(135deg, #17a2b8, #0a4b57);
}
.stcp-icon {
    background: linear-gradient(135deg, #fd7e14, #b35600);
}
.xtcp-icon {
    background: linear-gradient(135deg, #6c757d, #343a40);
}
.proxy-name {
    font-size: 18px;
    font-weight: 500;
    margin-bottom: 5px;
}
.proxy-detail {
    font-size: 13px;
    color: #6c757d;
    margin-bottom: 3px;
}
.proxy-badge {
    font-size: 0.7rem;
    padding: 3px 8px;
    border-radius: 50px;
}
.proxy-status-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-right: 5px;
}
.dot-active {
    background-color: #28a745;
    box-shadow: 0 0 5px rgba(40, 167, 69, 0.5);
    animation: pulse 2s infinite;
}
.dot-inactive {
    background-color: #dc3545;
}
.dot-disabled {
    background-color: #6c757d;
}
@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
    }
    70% {
        box-shadow: 0 0 0 6px rgba(40, 167, 69, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
    }
}
.btn-action {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}
.stats-card {
    border-radius: 8px;
    background-color: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    padding: 20px;
    margin-bottom: 20px;
    transition: all 0.3s ease;
}
.stats-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.stats-card .stats-title {
    font-size: 14px;
    color: #6c757d;
    margin-bottom: 10px;
}
.stats-card .stats-value {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 5px;
}
.stats-card .progress {
    height: 6px;
    margin-top: 10px;
}
.stats-card .stats-icon {
    font-size: 24px;
    position: absolute;
    right: 20px;
    top: 20px;
    opacity: 0.2;
}
.tunnel-type-card {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
    background-color: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    transition: all 0.3s;
}
.tunnel-type-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}
.tunnel-type-card .type-icon {
    font-size: 24px;
    margin-right: 15px;
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}
.tunnel-type-card .type-name {
    font-size: 16px;
    font-weight: 600;
}
.tunnel-type-card .type-desc {
    font-size: 13px;
    color: #6c757d;
    margin-top: 5px;
}
.empty-state {
    padding: 30px;
    text-align: center;
}
.empty-state .icon {
    font-size: 64px;
    color: #ddd;
    margin-bottom: 15px;
}
.empty-state .text {
    color: #888;
    margin-bottom: 15px;
}
.tunnel-list {
    max-height: 1000px;
    overflow-y: auto;
}
</style>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-project-diagram mr-2"></i>隧道列表</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="?page=panel"><i class="fas fa-home"></i> 首页</a></li>
                    <li class="breadcrumb-item active">隧道列表</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <!-- 统计信息部分 -->
        <div class="row">
            <div class="col-md-6">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <div class="stats-title">隧道用量</div>
                    <div class="stats-value"><?php echo $use_proxies; ?> / <?php echo $max_proxies == -1 ? "∞" : $max_proxies; ?></div>
                    <div class="text-muted"><?php echo $active_proxies; ?> 个活跃隧道</div>
                    <?php if($max_proxies > 0): ?>
                    <div class="progress">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo min(100, round(($use_proxies / $max_proxies) * 100)); ?>%"></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-server"></i>
                    </div>
                    <div class="stats-title">可用节点</div>
                    <div class="stats-value"><?php echo count($nodes); ?></div>
                    <div class="text-muted">可用于创建新隧道</div>
                    <?php if(!empty($nodes)): ?>
                    <a href="?page=panel&module=addproxy" class="btn btn-sm btn-primary mt-2">
                        <i class="fas fa-plus mr-1"></i> 添加新隧道
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card card-primary card-outline h-100"> <!-- 添加h-100类使卡片高度占满 -->
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-list mr-2"></i>隧道列表</h3>
                        <div class="card-tools">
                            <a href="?page=panel&module=addproxy" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus mr-1"></i> 添加隧道
                            </a>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column"> <!-- 使用flex布局 -->
                        <?php if(empty($ps)): ?>
                        <div class="empty-state flex-grow-1 d-flex align-items-center justify-content-center"> <!-- 调整空状态布局 -->
                            <div class="text-center">
                                <div class="icon">
                                    <i class="fas fa-project-diagram"></i>
                                </div>
                                <div class="text">您还没有创建任何隧道</div>
                                <a href="?page=panel&module=addproxy" class="btn btn-primary">
                                    <i class="fas fa-plus mr-1"></i> 创建第一个隧道
                                </a>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="tunnel-list flex-grow-1"> <!-- 添加flex-grow-1使隧道列表占满剩余空间 -->
                            <?php 
                            // 添加分页功能
                            $page = isset($_GET['p']) ? intval($_GET['p']) : 1;
                            $perPage = 5; // 每页显示5个隧道
                            $totalProxies = count($ps);
                            $totalPages = ceil($totalProxies / $perPage);
                            
                            // 确保页码有效
                            if($page < 1) $page = 1;
                            if($page > $totalPages) $page = $totalPages;
                            
                            // 计算当前页的隧道
                            $startIndex = ($page - 1) * $perPage;
                            $displayProxies = array_slice($ps, $startIndex, $perPage);
                            
                            foreach($displayProxies as $pi):
                            ?>
								<?php
									$domOrPort = "";
									$domains = json_decode($pi[8], true);
									if($domains && !empty($domains)) {
                                    for($i = 0; $i < count($domains); $i++) {
											$domOrPort .= $domains[$i];
											if($i < count($domains) - 1) { $domOrPort .= ", "; }
										}
									} elseif(!empty($pi[11])) {
										$domOrPort = $pi[11];
									}
                                
                                $nodeName = isset($nodes[$pi[16]]) ? $nodes[$pi[16]] : "未知节点";
									$enable = $pi[14] == "0" ? "checked" : "";
                                $disabled = $pi[14] == "2" || $pi[14] == "3" ? "disabled" : "";
                                
                                // 状态标识
                                if($pi[14] == "0") {
                                    $statusDot = "<span class='proxy-status-dot dot-active'></span>";
                                    $statusText = "正常";
                                    $statusClass = "success";
                                } elseif($pi[14] == "1") {
                                    $statusDot = "<span class='proxy-status-dot dot-inactive'></span>";
                                    $statusText = "已禁用";
                                    $statusClass = "secondary";
                                } elseif($pi[14] == "2") {
                                    $statusDot = "<span class='proxy-status-dot dot-disabled'></span>";
                                    $statusText = "流量耗尽";
                                    $statusClass = "warning";
                                } else {
                                    $statusDot = "<span class='proxy-status-dot dot-disabled'></span>";
                                    $statusText = "已封禁";
                                    $statusClass = "danger";
                                }
                                
                                // 类型图标
                                $type = strtolower($pi[3]);
                                $iconMap = [
                                    'tcp' => '<i class="fas fa-network-wired"></i>',
                                    'udp' => '<i class="fas fa-stream"></i>',
                                    'http' => '<i class="fas fa-globe"></i>',
                                    'https' => '<i class="fas fa-lock"></i>',
                                    'stcp' => '<i class="fas fa-shield-alt"></i>',
                                    'xtcp' => '<i class="fas fa-plug"></i>'
                                ];
                                $icon = $iconMap[$type] ?? '<i class="fas fa-project-diagram"></i>';
                            ?>
                            <div class="proxy-card">
                                <div class="proxy-header">
                                    <div class="d-flex align-items-center">
                                        <div class="proxy-icon <?php echo $type; ?>-icon">
                                            <?php echo $icon; ?>
                                        </div>
                                        <div>
                                            <div class="proxy-name">
                                                <?php echo $statusDot; ?> <?php echo $pi[2]; ?>
                                            </div>
                                            <div class="proxy-detail">
                                                <span class="badge badge-info proxy-badge"><?php echo strtoupper($type); ?></span>
                                                <span class="badge badge-<?php echo $statusClass; ?> proxy-badge">
                                                    <i class="fas fa-circle mr-1" style="font-size: 5px; vertical-align: middle;"></i> 
                                                    <?php echo $statusText; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" <?php echo $enable; ?> <?php echo $disabled; ?> id="switchProxy_<?php echo $pi[0]; ?>" onclick="toggleProxy(<?php echo $pi[0]; ?>);">
                                        <label class="custom-control-label" for="switchProxy_<?php echo $pi[0]; ?>"></label>
                                    </div>
                                </div>
                                
                                <div class="proxy-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="proxy-detail">
                                                <i class="fas fa-server text-muted mr-1"></i> 节点：<?php echo $nodeName; ?>
                                            </div>
                                            <?php if(!empty($domOrPort)): ?>
                                            <div class="proxy-detail">
                                                <?php if($type == 'http' || $type == 'https'): ?>
                                                <i class="fas fa-globe text-muted mr-1"></i> 域名：<?php echo $domOrPort; ?>
                                                <?php else: ?>
                                                <i class="fas fa-ethernet text-muted mr-1"></i> 端口：<?php echo $domOrPort; ?>
                                                <?php endif; ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="proxy-detail">
                                                <i class="fas fa-desktop text-muted mr-1"></i> 本地：<?php echo $pi[5] ? $pi[5] : '127.0.0.1'; ?>:<?php echo $pi[6]; ?>
                                            </div>
                                            <div class="proxy-detail">
                                                <i class="fas fa-hashtag text-muted mr-1"></i> ID：<?php echo $pi[0]; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="proxy-footer">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <button class="btn btn-sm btn-outline-info btn-action" onclick="getProxyInfo(<?php echo $pi[0]; ?>)">
                                                <i class="fas fa-info-circle mr-1"></i> 详细信息
                                            </button>
                                        </div>
                                        <div>
                                            <button class="btn btn-sm btn-outline-danger btn-action" onclick="deleteProxy(<?php echo $pi[0]; ?>)">
                                                <i class="fas fa-trash mr-1"></i> 删除
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            
                            <!-- 添加分页导航 -->
                            <?php if($totalPages > 1): ?>
                            <div class="d-flex justify-content-center mt-4">
                                <ul class="pagination pagination-sm">
                                    <?php if($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=panel&module=proxies&p=1" title="首页">
                                            <i class="fas fa-angle-double-left"></i>
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=panel&module=proxies&p=<?php echo $page - 1; ?>" title="上一页">
                                            <i class="fas fa-angle-left"></i>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    
                                    <?php
                                    // 显示页码，最多显示5个页码
                                    $startPage = max(1, min($page - 2, $totalPages - 4));
                                    $endPage = min($totalPages, max($page + 2, 5));
                                    
                                    for($i = $startPage; $i <= $endPage; $i++):
                                    ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=panel&module=proxies&p=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                    <?php endfor; ?>
                                    
                                    <?php if($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=panel&module=proxies&p=<?php echo $page + 1; ?>" title="下一页">
                                            <i class="fas fa-angle-right"></i>
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=panel&module=proxies&p=<?php echo $totalPages; ?>" title="末页">
                                            <i class="fas fa-angle-double-right"></i>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
			</div>
			<div class="col-lg-4">
				<div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>隧道类型介绍</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-lightbulb mr-2"></i> <strong>提示：</strong>XTCP 映射成功率并不高，具体取决于 NAT 设备的复杂度。
                        </div>
                        
                        <div class="tunnel-type-card">
                            <div class="d-flex">
                                <div class="type-icon tcp-icon">
                                    <i class="fas fa-network-wired"></i>
                                </div>
                                <div>
                                    <div class="type-name">TCP 映射</div>
                                    <div class="type-desc">基础的 TCP 映射，适用于大多数服务，例如远程桌面、SSH、Minecraft、泰拉瑞亚等</div>
                                    <div class="mt-2">
                                        <span class="badge badge-light"><?php echo $proxy_types['tcp']; ?> 个隧道</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tunnel-type-card">
                            <div class="d-flex">
                                <div class="type-icon udp-icon">
                                    <i class="fas fa-stream"></i>
                                </div>
                                <div>
                                    <div class="type-name">UDP 映射</div>
                                    <div class="type-desc">基础的 UDP 映射，适用于域名解析、部分基于 UDP 协议的游戏等</div>
                                    <div class="mt-2">
                                        <span class="badge badge-light"><?php echo $proxy_types['udp']; ?> 个隧道</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tunnel-type-card">
                            <div class="d-flex">
                                <div class="type-icon http-icon">
                                    <i class="fas fa-globe"></i>
                                </div>
                                <div>
                                    <div class="type-name">HTTP 映射</div>
                                    <div class="type-desc">搭建网站专用映射，并通过 80 端口访问</div>
                                    <div class="mt-2">
                                        <span class="badge badge-light"><?php echo $proxy_types['http']; ?> 个隧道</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tunnel-type-card">
                            <div class="d-flex">
                                <div class="type-icon https-icon">
                                    <i class="fas fa-lock"></i>
                                </div>
                                <div>
                                    <div class="type-name">HTTPS 映射</div>
                                    <div class="type-desc">带有 SSL 加密的网站映射，通过 443 端口访问，服务器需要支持 SSL</div>
                                    <div class="mt-2">
                                        <span class="badge badge-light"><?php echo $proxy_types['https']; ?> 个隧道</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tunnel-type-card">
                            <div class="d-flex">
                                <div class="type-icon stcp-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div>
                                    <div class="type-name">STCP 映射</div>
                                    <div class="type-desc">安全交换 TCP 连接协议，基于 TCP，访问此服务的用户也需要运行一个客户端，才能建立连接，流量由服务器转发</div>
                                    <div class="mt-2">
                                        <span class="badge badge-light"><?php echo $proxy_types['stcp']; ?> 个隧道</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tunnel-type-card">
                            <div class="d-flex">
                                <div class="type-icon xtcp-icon">
                                    <i class="fas fa-plug"></i>
                                </div>
                                <div>
                                    <div class="type-name">XTCP 映射</div>
                                    <div class="type-desc">客户端之间点对点 (P2P) 连接协议，流量不经过服务器，适合大流量传输的场景，需要两台设备之间都运行一个客户端</div>
                                    <div class="mt-2">
                                        <span class="badge badge-light"><?php echo $proxy_types['xtcp']; ?> 个隧道</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="?page=panel&module=addproxy" class="btn btn-primary">
                                <i class="fas fa-plus mr-1"></i> 添加新隧道
                            </a>
                        </div>
                    </div>
                </div>
            </div>
		</div>
	</div>
</div>

<!-- 隧道详情模态框 -->
<div class="modal fade" id="modal-default">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="msg-title">隧道详情</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="msg-body">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">正在加载...</span>
                    </div>
                    <p class="mt-3">正在加载隧道信息...</p>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                <button type="button" class="btn btn-primary" data-dismiss="modal">确定</button>
            </div>
        </div>
    </div>
</div>

<!-- 删除确认模态框 -->
<div class="modal fade" id="deleteconfirm">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fas fa-exclamation-triangle text-danger mr-2"></i>删除确认</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>您确定要删除此隧道吗？</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i> 删除之后将不能恢复！
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal" onclick="tempdelete = ''">取消</button>
                <button type="button" class="btn btn-danger" data-dismiss="modal" onclick="confirmDeleteProxy()">
                    <i class="fas fa-trash mr-1"></i> 确认删除
                </button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
var tempdelete = "";
var csrf_token = "<?php echo $_SESSION['token']; ?>";

$(document).ready(function() {
    // 初始化工具提示
    $('[data-toggle="tooltip"]').tooltip();
    
    // 页面加载动画
    $('.stats-card').css('opacity', 0);
    $('.proxy-card').css('opacity', 0);
    $('.tunnel-type-card').css('opacity', 0);
    
    setTimeout(function() {
        $('.stats-card').each(function(i) {
            $(this).delay(i * 100).animate({
                opacity: 1
            }, 500);
        });
        
        setTimeout(function() {
            $('.proxy-card').each(function(i) {
                $(this).delay(i * 150).animate({
                    opacity: 1
                }, 500);
            });
            
            setTimeout(function() {
                $('.tunnel-type-card').each(function(i) {
                    $(this).delay(i * 100).animate({
                        opacity: 1
                    }, 500);
                });
            }, 300);
        }, 200);
    }, 100);
});

function alertMessage(title, body) {
	$("#msg-title").html(title);
	$("#msg-body").html(body);
    $("#modal-default").modal('show');
}

function getProxyInfo(id) {
    // 显示加载状态
    $("#msg-title").html('隧道详情');
    $("#msg-body").html(`
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">正在加载...</span>
            </div>
            <p class="mt-3">正在加载隧道信息...</p>
        </div>
    `);
    $("#modal-default").modal('show');
    
    // 请求数据
    $.ajax({
		type: 'GET',
		url: "?page=panel&module=proxies&getproxyinfo=" + id + "&csrf=" + csrf_token,
        async: true,
        error: function(xhr) {
            $("#msg-body").html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle mr-2"></i> 加载失败：${xhr.responseText || '未知错误'}
                </div>
            `);
		},
        success: function(response) {
            $("#msg-body").html(response);
		}
	});
}

function toggleProxy(id) {
    $.ajax({
		type: 'GET',
		url: "?page=panel&module=proxies&toggle=" + id + "&csrf=" + csrf_token,
        async: true,
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: '操作失败',
                text: xhr.responseText || '未知错误',
                confirmButtonText: '确定'
            });
		},
        success: function(response) {
            Swal.fire({
                icon: 'success',
                title: '操作成功',
                html: response,
                confirmButtonText: '确定'
            }).then(() => {
                location.reload();
            });
		}
	});
}

function copyConfig(id) {
    getProxyInfo(id);
}

function deleteProxy(id) {
    // 保存临时ID
	tempdelete = "" + id;
    $("#deleteconfirm").modal('show');
}

function confirmDeleteProxy() {
	if(tempdelete != "") {
        // 显示加载状态
        Swal.fire({
            title: '正在删除',
            html: '正在删除隧道，请稍候...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        $.ajax({
			type: 'GET',
			url: "?page=panel&module=proxies&delete=" + tempdelete + "&csrf=" + csrf_token,
            async: true,
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: '删除失败',
                    text: xhr.responseText || '未知错误',
                    confirmButtonText: '确定'
                });
			},
            success: function(response) {
				tempdelete = "";
                Swal.fire({
                    icon: 'success',
                    title: '删除成功',
                    html: response,
                    confirmButtonText: '确定'
                }).then(() => {
                    location.reload();
                });
			}
		});
	}
}
</script>