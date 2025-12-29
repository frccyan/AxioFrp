<?php
namespace chhcn;

use chhcn;

//$page_title = "流量统计";
$um = new chhcn\UserManager();
$rs = Database::querySingleLine("users", Array("username" => $_SESSION['user']));

if(!$rs || $rs['group'] !== "admin") {
	exit("<script>location='?page=panel';</script>");
}

if(isset($_GET['getinfo']) && preg_match("/^[0-9]{1,10}$/", $_GET['getinfo'])) {
	ob_clean();
	chhcn\Utils::checkCsrf();
	$nm = new chhcn\NodeManager();
	$rs = $nm->getNodeInfo($_GET['getinfo']);
	if(is_array($rs)) {
		$hs = chhcn\Utils::http("http://admin:{$rs['admin_pass']}@{$rs['ip']}:{$rs['admin_port']}/api/serverinfo");
		if(isset($hs['status']) && $hs['status'] == 200) {
			$js = json_decode($hs['body'], true);

			// 兼容 frps 0.60.0 返回的 camelCase 字段
			$pick = function($arr, $candidates, $default = null) {
				foreach($candidates as $k) {
					if(isset($arr[$k]) && $arr[$k] !== "") return $arr[$k];
				}
				return $default;
			};

			$bind_port       = $pick($js, ['bind_port', 'bindPort'], '-');
			// UDP 端口大小写兼容 + KCP 兜底
			$bind_udp_port   = $pick($js, ['bind_udp_port', 'bindUDPPort', 'bindUdpPort', 'kcp_bind_port', 'kcpBindPort'], '-');
			// HTTP/HTTPS 端口大小写兼容（部分版本为 vhostHTTPPort / vhostHTTPSPort）
			$vhost_http_port  = $pick($js, ['vhost_http_port', 'vhostHTTPPort', 'vhostHttpPort'], '-');
			$vhost_https_port = $pick($js, ['vhost_https_port', 'vhostHTTPSPort', 'vhostHttpsPort'], '-');
			$version         = $pick($js, ['version'], '-');
			$total_in_raw    = $pick($js, ['total_traffic_in', 'totalTrafficIn'], 0);
			$total_out_raw   = $pick($js, ['total_traffic_out', 'totalTrafficOut'], 0);
			$cur_conns       = $pick($js, ['cur_conns', 'curConns'], 0);
			$client_counts   = $pick($js, ['client_counts', 'clientCounts'], 0);

			$tf_in  = chhcn\Utils::getFormatTraffic($total_in_raw);
			$tf_out = chhcn\Utils::getFormatTraffic($total_out_raw);
			echo <<<EOF
<div class="card-body">
    <h4 class="text-primary"><i class="fas fa-server mr-2"></i>{$rs['name']} <small class="text-muted">节点信息</small></h4>
    <div class="table-responsive mt-3">
        <table class="table table-bordered table-striped">
            <tr><th><i class="fas fa-code-branch mr-2"></i>服务端版本</th><td>{$version}</td></tr>
            <tr><th><i class="fas fa-network-wired mr-2"></i>监听端口</th><td>{$bind_port}</td></tr>
            <tr><th><i class="fas fa-network-wired mr-2"></i>UDP 监听端口</th><td>{$bind_udp_port}</td></tr>
            <tr><th><i class="fas fa-globe mr-2"></i>HTTP 监听端口</th><td>{$vhost_http_port}</td></tr>
            <tr><th><i class="fas fa-lock mr-2"></i>HTTPS 监听端口</th><td>{$vhost_https_port}</td></tr>
            <tr><th><i class="fas fa-arrow-down mr-2"></i>总共入网流量</th><td><span class="text-info">{$tf_in}</span></td></tr>
            <tr><th><i class="fas fa-arrow-up mr-2"></i>总共出网流量</th><td><span class="text-success">{$tf_out}</span></td></tr>
            <tr><th><i class="fas fa-plug mr-2"></i>连接数量</th><td><span class="badge badge-info">{$cur_conns}</span></td></tr>
            <tr><th><i class="fas fa-users mr-2"></i>客户端数量</th><td><span class="badge badge-primary">{$client_counts}</span></td></tr>
        </table>
    </div>
    <div class="text-right mt-3">
        <button class="btn btn-sm btn-outline-secondary" onclick="refreshServerInfo({$_GET['getinfo']})">
            <i class="fas fa-sync-alt mr-1"></i> 刷新信息
        </button>
    </div>
</div>
EOF;
			exit;
		} else {
			Header("HTTP/1.1 404 Not Found");
			exit("<div class='alert alert-danger'><i class='fas fa-exclamation-circle mr-2'></i>无法连接至服务器，错误代码：{$hs['status']}</div>");
		}
	} else {
		exit("<div class='alert alert-warning'><i class='fas fa-exclamation-triangle mr-2'></i>未找到该节点信息</div>");
	}
}
if(isset($_GET['gettraffic']) && preg_match("/^[0-9]{1,10}$/", $_GET['gettraffic']) && in_array($_GET['type'], ["tcp", "udp", "http", "https", "stcp"])) {
	ob_clean();
	chhcn\Utils::checkCsrf();
	$um = new chhcn\UserManager();
	$nm = new chhcn\NodeManager();
	$rs = $nm->getNodeInfo($_GET['gettraffic']);
	$tokens = $um->getTokensToUsers();
	if(is_array($rs)) {
		$hs = chhcn\Utils::http("http://admin:{$rs['admin_pass']}@{$rs['ip']}:{$rs['admin_port']}/api/proxy/{$_GET['type']}");
		if(isset($hs['status']) && $hs['status'] == 200) {
			$js = json_decode($hs['body'], true);
			echo '<div class="table-responsive">';
			echo '<table class="table table-striped table-bordered table-hover">';
			echo '<thead class="thead-light">
                <tr>
                    <th><i class="fas fa-tag mr-1"></i> 隧道名称</th>
                    <th><i class="fas fa-user mr-1"></i> 所属用户</th>
                    <th><i class="fas fa-plug mr-1"></i> 连接数量</th>
                    <th><i class="fas fa-chart-bar mr-1"></i> 今日流量 (↓/↑)</th>
                    <th><i class="fas fa-info-circle mr-1"></i> 当前状态</th>
                </tr>
            </thead>
            <tbody>';
			
			$totalConnections = 0;
			$totalTrafficIn = 0;
			$totalTrafficOut = 0;
			$activeProxies = 0;
			
			foreach($js['proxies'] as $proxy) {
				$getProxyVal = function($p, $keys, $default = 0) {
					foreach($keys as $k) {
						if(isset($p[$k])) return $p[$k];
					}
					return $default;
				};
				$name = explode(".", $proxy['name']);
				if(count($name) !== 2) continue;
				
				$cur_conns_p = $getProxyVal($proxy, ['cur_conns', 'curConns'], 0);
				$today_in    = $getProxyVal($proxy, ['today_traffic_in', 'todayTrafficIn'], 0);
				$today_out   = $getProxyVal($proxy, ['today_traffic_out', 'todayTrafficOut'], 0);
				$status_p    = $getProxyVal($proxy, ['status'], 'offline');
				
				$totalConnections += $cur_conns_p;
				$totalTrafficIn += $today_in;
				$totalTrafficOut += $today_out;
				
				if($status_p == 'online') $activeProxies++;
				
				$statusClass = $status_p == 'online' ? 'success' : 'secondary';
				$statusIcon = $status_p == 'online' ? 'check-circle' : 'times-circle';
				$statusText = $status_p == 'online' ? '在线' : '离线';
				
				echo "<tr>";
				echo "<td><strong>{$name[1]}</strong></td>";
				echo "<td><span class='badge badge-info'>{$tokens[$name[0]]}</span></td>";
				echo "<td><span class='badge badge-primary'>{$cur_conns_p}</span></td>";
				$tf_in  = chhcn\Utils::getFormatTraffic($today_in);
				$tf_out = chhcn\Utils::getFormatTraffic($today_out);
				echo "<td><span class='text-info'>{$tf_in}</span> / <span class='text-success'>{$tf_out}</span></td>";
				echo "<td><span class='badge badge-{$statusClass}'><i class='fas fa-{$statusIcon} mr-1'></i> {$statusText}</span></td>";
				echo "</tr>";
			}
			echo "</tbody></table>";
			
			// 添加统计信息
			$totalProxies = count($js['proxies']);
			$totalTrafficInFormatted = chhcn\Utils::getFormatTraffic($totalTrafficIn);
			$totalTrafficOutFormatted = chhcn\Utils::getFormatTraffic($totalTrafficOut);
			$activePercentage = $totalProxies > 0 ? round(($activeProxies / $totalProxies) * 100) : 0;
			
			echo "<div class='alert alert-info mt-3'>
                <div class='row'>
                    <div class='col-md-3 text-center'>
                        <div><i class='fas fa-plug mr-1'></i> 总连接数</div>
                        <strong>{$totalConnections}</strong>
                    </div>
                    <div class='col-md-3 text-center'>
                        <div><i class='fas fa-project-diagram mr-1'></i> 隧道数量</div>
                        <strong>{$totalProxies}</strong> <small>({$activeProxies} 在线)</small>
                    </div>
                    <div class='col-md-3 text-center'>
                        <div><i class='fas fa-arrow-down mr-1'></i> 总入网流量</div>
                        <strong>{$totalTrafficInFormatted}</strong>
                    </div>
                    <div class='col-md-3 text-center'>
                        <div><i class='fas fa-arrow-up mr-1'></i> 总出网流量</div>
                        <strong>{$totalTrafficOutFormatted}</strong>
                    </div>
                </div>
            </div>";
            
			echo "<div class='text-right mt-2'>
                <button class='btn btn-sm btn-outline-secondary' onclick=\"gettraffic('{$_GET['type']}')\">
                    <i class='fas fa-sync-alt mr-1'></i> 刷新数据
                </button>
            </div>";
            
			echo "</div>";
			exit;
		} else {
			Header("HTTP/1.1 404 Not Found");
			exit("<div class='alert alert-danger'><i class='fas fa-exclamation-circle mr-2'></i>无法连接至服务器，错误代码：{$hs['status']}</div>");
		}
	} else {
		exit("<div class='alert alert-warning'><i class='fas fa-exclamation-triangle mr-2'></i>未找到该节点信息</div>");
	}
}

// 获取节点列表
$nodes = Database::toArray(Database::query("users", "SELECT * FROM `nodes`", true));

// 计算统计信息
$totalNodes = count($nodes);
$onlineNodes = 0;
$offlineNodes = 0;
$disabledNodes = 0;

foreach($nodes as $node) {
    switch(intval($node[10])) {
        case 200: $onlineNodes++; break;
        case 500: $offlineNodes++; break;
        case 403: $disabledNodes++; break;
    }
}
?>
<style type="text/css">
.fix-text p {
	margin-bottom: 4px;
}
.sinfotable th {
	width: 40%;
}
.node-card {
    transition: all 0.3s ease;
    cursor: pointer;
    border-left: 4px solid transparent;
}
.node-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}
.node-card.selected {
    border-left-color: #007bff;
    background-color: rgba(0, 123, 255, 0.05);
}
.node-status-badge {
    padding: 5px 8px;
    border-radius: 50px;
    font-size: 12px;
    font-weight: normal;
}
.btn-traffic-type {
    margin-bottom: 8px;
    border-radius: 20px;
    padding: 6px 15px;
    transition: all 0.2s;
}
.btn-traffic-type:hover {
    transform: translateY(-2px);
}
.btn-traffic-type.active {
    box-shadow: 0 3px 8px rgba(0,0,0,0.15);
}
.table th {
    white-space: nowrap;
}
.small-box .icon {
    font-size: 70px;
    color: rgba(0,0,0,0.15);
    right: 10px;
    top: 10px;
}
.small-box:hover .icon {
    color: rgba(0,0,0,0.3);
}
.small-box {
    border-radius: 5px;
    transition: all 0.3s ease;
    overflow: hidden;
}
.small-box:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.traffic-stats {
    display: flex;
    flex-wrap: wrap;
    margin-top: 1rem;
    border-top: 1px solid #f0f0f0;
    padding-top: 1rem;
}
.stat-item {
    flex: 1;
    text-align: center;
    padding: 0.5rem;
    border-right: 1px solid #f0f0f0;
}
.stat-item:last-child {
    border-right: none;
}
.stat-value {
    font-size: 1.2rem;
    font-weight: bold;
    color: #007bff;
    margin-top: 5px;
}
.stat-label {
    font-size: 0.8rem;
    color: #6c757d;
}
.loader {
    display: inline-block;
    border: 3px solid #f3f3f3;
    border-radius: 50%;
    border-top: 3px solid #3498db;
    width: 20px;
    height: 20px;
    margin-right: 5px;
    animation: spin 1s linear infinite;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-chart-area mr-2"></i>流量统计</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="?page=panel"><i class="fas fa-home"></i> 首页</a></li>
                    <li class="breadcrumb-item active">流量统计</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <!-- 状态卡片 -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?php echo $totalNodes; ?></h3>
                        <p>节点总数</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-server"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?php echo $onlineNodes; ?></h3>
                        <p>在线节点</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?php echo $disabledNodes; ?></h3>
                        <p>禁用节点</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-ban"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?php echo $offlineNodes; ?></h3>
                        <p>离线节点</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-server mr-2"></i>服务器节点</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center"><i class="fas fa-hashtag mr-1"></i> ID</th>
                                        <th><i class="fas fa-tag mr-1"></i> 名称</th>
                                        <th><i class="fas fa-globe mr-1"></i> 主机名</th>
                                        <th><i class="fas fa-network-wired mr-1"></i> IP地址</th>
                                        <th class="text-center"><i class="fas fa-plug mr-1"></i> 端口</th>
                                        <th class="text-center"><i class="fas fa-info-circle mr-1"></i> 状态</th>
                                        <th class="text-center"><i class="fas fa-cogs mr-1"></i> 操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 0;
                                    foreach($nodes as $node) {
                                        $i++;
                                        $statusMap = Array(
                                            200 => ["正常", "success", "check-circle"], 
                                            403 => ["禁用", "warning", "ban"], 
                                            500 => ["离线", "danger", "times-circle"], 
                                            401 => ["隐藏", "secondary", "eye-slash"]
                                        );
                                        
                                        $statusInfo = $statusMap[intval($node[10])] ?? ["未知", "secondary", "question-circle"];
                                        $statusText = $statusInfo[0];
                                        $statusClass = $statusInfo[1];
                                        $statusIcon = $statusInfo[2];
                                        
                                        echo "<tr class='node-row' data-node-id='{$node[0]}'>
                                        <td class='text-center'>{$node[0]}</td>
                                        <td><strong>{$node[1]}</strong></td>
                                        <td>{$node[3]}</td>
                                        <td>{$node[4]}</td>
                                        <td class='text-center'>{$node[5]}</td>
                                        <td class='text-center'>
                                            <span class='badge badge-{$statusClass}'>
                                                <i class='fas fa-{$statusIcon} mr-1'></i> {$statusText}
                                            </span>
                                        </td>
                                        <td class='text-center'>
                                            <button class='btn btn-sm btn-primary select-server' onclick='selectserver({$node[0]})'>
                                                <i class='fas fa-check mr-1'></i> 选择
                                            </button>
                                        </td>
                                        </tr>";
                                    }
                                    
                                    if($i == 0) {
                                        echo "<tr><td colspan='7' class='text-center py-4'>
                                            <div class='empty-state'>
                                                <i class='fas fa-server fa-4x text-muted'></i>
                                                <p class='mt-3 mb-0'>没有找到符合条件的节点</p>
                                            </div>
                                        </td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-bar mr-2"></i>流量排行</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i> 
                            <span id="traffic-instruction">请先从上方选择一个服务器节点，然后再选择要查询的映射类型。</span>
                        </div>
                        
                        <div class="text-center mb-3">
                            <div class="btn-group" id="traffic-type-buttons">
                                <button class="btn btn-outline-primary btn-traffic-type" data-type="tcp">
                                    <i class="fas fa-network-wired mr-1"></i> TCP
                                </button>
                                <button class="btn btn-outline-primary btn-traffic-type" data-type="udp">
                                    <i class="fas fa-stream mr-1"></i> UDP
                                </button>
                                <button class="btn btn-outline-primary btn-traffic-type" data-type="http">
                                    <i class="fas fa-globe mr-1"></i> HTTP
                                </button>
                                <button class="btn btn-outline-primary btn-traffic-type" data-type="https">
                                    <i class="fas fa-lock mr-1"></i> HTTPS
                                </button>
                                <button class="btn btn-outline-primary btn-traffic-type" data-type="stcp">
                                    <i class="fas fa-shield-alt mr-1"></i> STCP
                                </button>
                            </div>
                        </div>
                        
                        <div id="traffic-loading" class="text-center py-5 d-none">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">正在加载...</span>
                            </div>
                            <p class="mt-3 text-muted">正在查询流量数据，请稍候...</p>
                        </div>
                        
                        <div id="trafficlist">
                            <div class="text-center py-5">
                                <i class="fas fa-chart-line fa-4x text-muted"></i>
                                <p class="mt-3 text-muted">选择服务器和映射类型后将显示流量数据</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>服务器信息</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div id="serverinfo">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-server fa-4x text-muted"></i>
                            <p class="mt-3 text-muted">请从左侧列表选择一个服务器节点</p>
                        </div>
                    </div>
                </div>
                
                <!-- 帮助卡片 -->
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-question-circle mr-2"></i>使用帮助</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="text-primary"><i class="fas fa-info-circle mr-2"></i>如何查看流量数据</h5>
                        <ol class="pl-3">
                            <li>从上方服务器节点列表中选择一个在线节点</li>
                            <li>在流量排行卡片中选择要查看的映射类型（如TCP、HTTP等）</li>
                            <li>系统将显示该节点上对应映射类型的流量排行数据</li>
                        </ol>
                        
                        <div class="alert alert-warning mt-3 mb-0">
                            <i class="fas fa-exclamation-triangle mr-2"></i> 
                            <strong>注意：</strong>流量实时查询需要一定时间，请勿频繁点击按钮，否则容易导致服务器响应缓慢。
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 通知模态框 -->
<div class="modal fade" id="modal-default">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="msg-title"></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="msg-body"></div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                <button type="button" class="btn btn-primary" data-dismiss="modal">确定</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
var csrf_token = "<?php echo $_SESSION['token']; ?>";
var nodeid = undefined;
var currentType = null;

$(document).ready(function() {
    // 初始化工具提示
    $('[data-toggle="tooltip"]').tooltip();
    
    // 美化页面加载
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
    
    // 映射类型按钮点击
    $('.btn-traffic-type').click(function() {
        var type = $(this).data('type');
        $('.btn-traffic-type').removeClass('active');
        $(this).addClass('active');
        gettraffic(type);
    });
    
    // 节点行点击
    $('.node-row').click(function() {
        var nodeId = $(this).data('node-id');
        selectserver(nodeId);
        $('.node-row').removeClass('table-primary');
        $(this).addClass('table-primary');
    });
});

function search() {
    window.location = window.location.href + '&search=' + encodeURIComponent($('#searchdata').val());
}

function selectserver(id) {
    // 更新界面状态
    $('.node-row').removeClass('table-primary');
    $(`.node-row[data-node-id="${id}"]`).addClass('table-primary');
    
    $('#serverinfo').html(`
        <div class="card-body text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">正在加载...</span>
            </div>
            <p class="mt-3 text-muted">正在查询服务器信息，请稍候...</p>
        </div>
    `);
    
    // 更新指引文本
    $('#traffic-instruction').html('已选择节点，请点击下方按钮查看对应的映射类型流量数据。');
    
    $.ajax({
        type: 'GET',
        url: "?page=panel&module=traffic&getinfo=" + id + "&csrf=" + csrf_token,
        async: true,
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: '查询失败',
                text: xhr.responseText || '无法连接到服务器',
                confirmButtonText: '确定'
            });
            $('#serverinfo').html(`
                <div class="card-body text-center py-5">
                    <i class="fas fa-exclamation-circle fa-4x text-danger"></i>
                    <p class="mt-3 text-danger">查询失败：${xhr.responseText || '无法连接到服务器'}</p>
                    <button class="btn btn-outline-primary mt-3" onclick="selectserver(${id})">
                        <i class="fas fa-sync-alt mr-1"></i> 重试
                    </button>
                </div>
            `);
        },
        success: function(response) {
            nodeid = id;
            $('#serverinfo').html(response);
            
            // 如果当前有选中的映射类型，自动刷新
            if(currentType) {
                gettraffic(currentType);
            }
        }
    });
}

function refreshServerInfo(id) {
    selectserver(id);
}

function gettraffic(type) {
    if(nodeid == undefined) {
        Swal.fire({
            icon: 'warning',
            title: '请先选择服务器',
            text: '请先从左侧列表选择一个服务器节点，然后再查询流量数据。',
            confirmButtonText: '确定'
        });
        return;
    }
    
    currentType = type;
    
    // 更新按钮状态
    $('.btn-traffic-type').removeClass('active');
    $(`.btn-traffic-type[data-type="${type}"]`).addClass('active');
    
    // 显示加载中
    $('#traffic-loading').removeClass('d-none');
    $('#trafficlist').html('');
    
    $.ajax({
        type: 'GET',
        url: "?page=panel&module=traffic&gettraffic=" + nodeid + "&type=" + type + "&csrf=" + csrf_token,
        async: true,
        error: function(xhr) {
            $('#traffic-loading').addClass('d-none');
            Swal.fire({
                icon: 'error',
                title: '查询失败',
                text: xhr.responseText || '无法连接到服务器',
                confirmButtonText: '确定'
            });
            $('#trafficlist').html(`
                <div class="text-center py-5">
                    <i class="fas fa-exclamation-circle fa-4x text-danger"></i>
                    <p class="mt-3 text-danger">查询失败：${xhr.responseText || '无法连接到服务器'}</p>
                    <button class="btn btn-outline-primary mt-3" onclick="gettraffic('${type}')">
                        <i class="fas fa-sync-alt mr-1"></i> 重试
                    </button>
                </div>
            `);
        },
        success: function(response) {
            $('#traffic-loading').addClass('d-none');
            $('#trafficlist').html(response);
        }
    });
}
</script>