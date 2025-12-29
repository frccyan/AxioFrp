<?php
namespace chhcn;

use chhcn;

global $_config;

$group_title = "内网穿透";
$page_title = "创建隧道";

$rs = Database::querySingleLine("users", Array("username" => $_SESSION['user']));

if(!$rs) {
	exit("<script>location='?page=login';</script>");
}

$nm = new chhcn\NodeManager();
$pm = new chhcn\ProxyManager();
$un = $nm->getUserNode($rs['group']);

$proxies_max = $rs['proxies'] == "-1" ? "无限制" : $rs['proxies'];

// 分类节点
$nodes = [
    '中国大陆' => [],
    '港澳台区' => [],
    '非中国区' => []
];

foreach($un as $server) {
    $nodeName = $server[1]; // 假设节点名称在数组的第二个位置
    if (strpos($nodeName, '香港') !== false || strpos($nodeName, '澳门') !== false || strpos($nodeName, '台湾') !== false) {
        $nodes['港澳台区'][] = $server;
    } elseif (strpos($nodeName, '中国') !== false || strpos($nodeName, '北京') !== false || strpos($nodeName, '上海') !== false || strpos($nodeName, '广州') !== false) {
        $nodes['中国大陆'][] = $server;
    } else {
        $nodes['非中国区'][] = $server;
    }
}

if(isset($_GET['portrules'])) {
	ob_clean();
	chhcn\Utils::checkCsrf();
	echo "<p>映射的端口最小为 <code>{$_config['proxies']['min']}</code>，最大为 <code>{$_config['proxies']['max']}</code>。</p>";
	if(!empty($_config['proxies']['protect'])) {
		echo "<p>以下为系统保留的端口范围，不可使用：</p>";
		echo "<ul>";
		foreach($_config['proxies']['protect'] as $key => $value) {
			echo "<li><code>{$key}</code> - <code>{$value}</code></li>";
		}
		echo "</ul>";
		echo "<span>您最多可以使用 {$proxies_max} 个端口</span>";
	}
	exit;
}
if(isset($_GET['randomport'])) {
	ob_clean();
	chhcn\Utils::checkCsrf();
	echo $pm->getRandomPort();
	exit;
}
?>
<style type="text/css">
.fix-text p {
	margin-bottom: 4px;
}
.pdesc {
	margin-left: 8px;
    color: #6c757d;
    font-size: 0.875rem;
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
.form-group label {
    font-weight: 600;
}
.tunnel-type-icon {
    font-size: 2rem;
    margin-bottom: 10px;
}
.tunnel-type-card {
    transition: all 0.3s ease;
    cursor: pointer;
    border: 2px solid transparent;
}
.tunnel-type-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.tunnel-type-card.active {
    border-color: #007bff;
    background-color: rgba(0, 123, 255, 0.05);
}
.field-description {
    color: #6c757d;
    font-size: 0.875rem;
    margin-top: 5px;
}
</style>

<div class="content-header" oncopy="return false" oncut="return false;" onselectstart="return false" oncontextmenu="return false">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark"><?php echo $page_title; ?>&nbsp;&nbsp;<small class="text-muted text-xs">创建一个新的内网穿透隧道</small></h1>
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
            <div class="col-lg-12">
                <div class="callout callout-warning">
                    <h5><i class="fas fa-exclamation-triangle"></i> 注意事项</h5>
                    <p>创建隧道前请先查看节点状态确认节点可用，并确保您的本地服务已正常运行。</p>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="callout callout-danger">
                    <h5><i class="fas fa-ban"></i> 使用限制</h5>
                    <p>隧道未经许可禁止用于内容分发网络、虚拟专线网络或其他须相关行政许可业务。违规使用将被封禁账号。</p>
                </div>
            </div>
            
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-plus-circle"></i> 创建新隧道</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="node"><i class="fas fa-server"></i> 选择节点</label>
                            <select class="form-control select2" id="node" style="width: 100%;">
                                <?php
                                foreach($un as $server) {
                                    echo "<option value='{$server[0]}'>{$server[1]} - {$server[2]} ({$server[3]})</option>";
                                }
                                ?>
                            </select>
                            <div class="field-description">选择一个可用的服务器节点来创建您的隧道</div>
                        </div>
                        
                        <div class="sub-heading">
                            <span>基础设置</span>
                        </div>
                        
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="proxy_name"><i class="fas fa-tag"></i> 隧道名称</label>
                                    <input type="text" class="form-control" id="proxy_name" placeholder="MyProxy">
                                    <div class="field-description">3-15个字符，中英文和数字以及下划线组成</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="proxy_type"><i class="fas fa-exchange-alt"></i> 隧道类型</label>
                                    <select class="form-control" id="proxy_type">
                                        <option value="tcp">TCP 隧道</option>
                                        <option value="udp">UDP 隧道</option>
                                        <option value="http">HTTP 隧道</option>
                                        <option value="https">HTTPS 隧道</option>
                                        <option value="stcp">STCP 隧道</option>
                                        <option value="xtcp">XTCP 隧道</option>
                                    </select>
                                    <div class="field-description">不同类型适用于不同场景，请查看右侧说明</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="local_ip"><i class="fas fa-laptop"></i> 本地地址</label>
                                    <input type="text" class="form-control" id="local_ip" placeholder="127.0.0.1" value="127.0.0.1">
                                    <div class="field-description">要转发的本机IP地址，默认127.0.0.1即可</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="local_port"><i class="fas fa-plug"></i> 本地端口</label>
                                    <input type="text" class="form-control" id="local_port" placeholder="80">
                                    <div class="field-description">本地服务运行的端口，如网站80、游戏特定端口</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="remote_port"><i class="fas fa-globe"></i> 远程端口</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="remote_port" placeholder="10000">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" onclick="randomPort()">
                                                <i class="fas fa-random"></i> 随机
                                            </button>
                                        </div>
                                    </div>
                                    <div class="field-description">
                                        外部访问使用的端口 <a href="javascript:loadPortRules();">查看可用端口规则</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="domain"><i class="fas fa-globe-americas"></i> 内置域名（可选）</label>
                                    <input type="text" class="form-control" id="domain" placeholder="example.com">
                                    <div class="field-description">HTTP/HTTPS 可填写内置域名（系统解析的子域名），与自定义域名二选一</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="customdomains"><i class="fas fa-link"></i> 自定义域名（可选）</label>
                                    <input type="text" class="form-control" id="customdomains" placeholder="www.example.com">
                                    <div class="field-description">HTTP/HTTPS 可填写自定义域名，与内置域名二选一</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="sub-heading">
                            <span>高级设置</span>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 以下设置均为选填，一般用户可保持默认设置。
                        </div>
                        
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="use_encryption"><i class="fas fa-lock"></i> 加密传输</label>
                                    <select class="form-control" id="use_encryption">
                                        <option value="true">启用</option>
                                        <option value="false">关闭</option>
                                    </select>
                                    <div class="field-description">加密可提高数据安全性但会增加消耗</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="use_compression"><i class="fas fa-compress-arrows-alt"></i> 压缩数据</label>
                                    <select class="form-control" id="use_compression">
                                        <option value="true">启用</option>
                                        <option value="false">关闭</option>
                                    </select>
                                    <div class="field-description">压缩可降低流量消耗但会增加处理延迟</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="locations"><i class="fas fa-link"></i> URL 路由</label>
                                    <input type="text" class="form-control" id="locations" placeholder="/">
                                    <div class="field-description">仅限HTTP隧道，指定要转发的URL路由</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="host_header_rewrite"><i class="fas fa-pencil-alt"></i> Host 重写</label>
                                    <input type="text" class="form-control" id="host_header_rewrite" placeholder="frp.example.com">
                                    <div class="field-description">仅限HTTP隧道，重写请求头部的Host字段</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="header_X-From-Where"><i class="fas fa-map-marker-alt"></i> 请求来源</label>
                                    <input type="text" class="form-control" id="header_X-From-Where" placeholder="frp_node_1">
                                    <div class="field-description">仅限HTTP隧道，给后端区分请求来源用</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="sk"><i class="fas fa-key"></i> 访问密码</label>
                                    <input type="text" class="form-control" id="sk" placeholder="1234567890">
                                    <div class="field-description">仅限STCP/XTCP，访客连接时需要的密码</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="button" class="btn btn-success float-right" onclick="addProxy()">
                            <i class="fas fa-check"></i> 完成创建
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle"></i> 隧道类型说明</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> XTCP映射成功率并不高，取决于NAT设备复杂度。
                        </div>
                        
                        <div class="tunnel-type-item mb-4">
                            <h5><i class="fas fa-network-wired text-primary"></i> TCP 映射</h5>
                            <p>基础的TCP映射，适用于大多数服务，例如远程桌面、SSH、Minecraft、泰拉瑞亚等各类游戏服务器。</p>
                        </div>
                        
                        <div class="tunnel-type-item mb-4">
                            <h5><i class="fas fa-satellite-dish text-success"></i> UDP 映射</h5>
                            <p>基础的UDP映射，适用于DNS、语音通话、部分基于UDP协议的游戏等场景。</p>
                        </div>
                        
                        <div class="tunnel-type-item mb-4">
                            <h5><i class="fas fa-globe text-info"></i> HTTP 映射</h5>
                            <p>搭建网站专用映射，通过80端口访问，适合一般的Web应用。可配置域名、URL路由等。</p>
                        </div>
                        
                        <div class="tunnel-type-item mb-4">
                            <h5><i class="fas fa-lock text-warning"></i> HTTPS 映射</h5>
                            <p>带有SSL加密的网站映射，通过443端口访问，服务器需要支持SSL。适合需要安全连接的应用。</p>
                        </div>
                        
                        <div class="tunnel-type-item mb-4">
                            <h5><i class="fas fa-shield-alt text-danger"></i> STCP 映射</h5>
                            <p>安全交换TCP连接协议，基于TCP，访问此服务的用户也需要运行一个客户端才能建立连接，流量由服务器转发。</p>
                        </div>
                        
                        <div class="tunnel-type-item">
                            <h5><i class="fas fa-project-diagram text-secondary"></i> XTCP 映射</h5>
                            <p>客户端之间点对点(P2P)连接协议，流量不经过服务器，适合大流量传输场景，需要两台设备都运行客户端。</p>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header bg-gradient-info text-white">
                        <h3 class="card-title"><i class="fas fa-lightbulb"></i> 使用提示</h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-3"><i class="fas fa-check-circle text-success mr-2"></i> 创建隧道前确保本地服务已启动并正常运行</li>
                            <li class="mb-3"><i class="fas fa-check-circle text-success mr-2"></i> 选择合适的隧道类型可以获得更好的使用体验</li>
                            <li class="mb-3"><i class="fas fa-check-circle text-success mr-2"></i> 使用随机端口可以避免端口冲突问题</li>
                            <li class="mb-3"><i class="fas fa-check-circle text-success mr-2"></i> 遇到连接问题，请检查防火墙和本地服务状态</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 端口规则模态框 -->
<div class="modal fade" id="modal-default" style="display: none;" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="msg-title"></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
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

// 初始化选项
$(document).ready(function() {
    // 监听隧道类型变化
    $("#proxy_type").change(function() {
        var type = $(this).val();
        if (type == "http" || type == "https") {
            $("#domain").prop("disabled", false);
            $("#locations").prop("disabled", false);
            $("#host_header_rewrite").prop("disabled", false);
            $("#header_X-From-Where").prop("disabled", false);
        } else {
            $("#domain").prop("disabled", true);
            $("#locations").prop("disabled", true);
            $("#host_header_rewrite").prop("disabled", true);
            $("#header_X-From-Where").prop("disabled", true);
        }
        
        if (type == "stcp" || type == "xtcp") {
            $("#sk").prop("disabled", false);
        } else {
            $("#sk").prop("disabled", true);
        }
    });
    
    // 触发一次变化事件以初始化表单状态
    $("#proxy_type").trigger("change");
});

function alertMessage(title, body) {
	$("#msg-title").html(title);
	$("#msg-body").html(body);
	$("#modal-default").modal('toggle');
}

function loadPortRules() {
	var htmlobj = $.ajax({
		type: 'GET',
		url: "?page=panel&module=addproxy&portrules&csrf=" + csrf_token,
		async: true,
		error: function() {
			alertMessage("错误", "加载端口规则失败");
			return;
		},
		success: function() {
			alertMessage("端口使用规则", htmlobj.responseText);
			return;
		}
	});
}

function randomPort() {
	var htmlobj = $.ajax({
		type: 'GET',
		url: "?page=panel&module=addproxy&randomport&csrf=" + csrf_token,
		async: true,
		error: function() {
			alertMessage("错误", "生成随机端口失败");
			return;
		},
		success: function() {
			$("#remote_port").val(htmlobj.responseText);
			return;
		}
	});
}

function addProxy() {
	var node                = $("#node").val();
	var proxy_name          = $("#proxy_name").val();
	var proxy_type          = $("#proxy_type").val();
	var local_ip            = $("#local_ip").val();
	var local_port          = $("#local_port").val();
	var remote_port         = $("#remote_port").val();
	var domain              = $("#domain").val();
	var customdomains       = $("#customdomains").val();
	var use_encryption      = $("#use_encryption").val();
	var use_compression     = $("#use_compression").val();
	var locations           = $("#locations").val();
	var host_header_rewrite = $("#host_header_rewrite").val();
	var header_X_From_Where = $("#header_X-From-Where").val();
	var sk                  = $("#sk").val();
	
	// 表单验证
	if (!proxy_name) {
	    alertMessage("提示", "请输入隧道名称");
	    return;
	}
	
	if (!local_port) {
	    alertMessage("提示", "请输入本地端口");
	    return;
	}
	
	if (!remote_port) {
	    alertMessage("提示", "请输入远程端口");
	    return;
	}
	
	if (proxy_type == "http" || proxy_type == "https") {
		if (!domain && !customdomains) {
		    alertMessage("提示", "HTTP/HTTPS隧道需填写内置域名或自定义域名（二选一）");
		    return;
		}
		if (domain && customdomains) {
		    alertMessage("提示", "内置域名和自定义域名只能填写其中一个");
		    return;
		}
	}
	
	var htmlobj = $.ajax({
		type: 'POST',
		url: "?page=panel&module=addproxy&action=addproxy&csrf=" + csrf_token,
		data: {
			node               : node,
			proxy_name         : proxy_name,
			proxy_type         : proxy_type,
			local_ip           : local_ip,
			local_port         : local_port,
			remote_port        : remote_port,
			domain             : domain,
			customdomains      : customdomains,
			use_encryption     : use_encryption,
			use_compression    : use_compression,
			locations          : locations,
			host_header_rewrite: host_header_rewrite,
			header_X_From_Where: header_X_From_Where,
			sk                 : sk
		},
		async: true,
		error: function() {
			alertMessage("错误", "创建隧道时发生错误");
			return;
		},
		success: function() {
			alertMessage("提示", htmlobj.responseText);
			if(htmlobj.responseText.indexOf("成功") != -1) {
				setTimeout(function() {
					window.location = "?page=panel&module=proxies";
				}, 2000);
			}
			return;
		}
	});
}
</script>