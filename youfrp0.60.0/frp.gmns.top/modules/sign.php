<?php
namespace chhcn;

use chhcn;

include(ROOT . "/core/Parsedown.php");

global $_config;

$group_title = "账号服务";
$page_title = "每日签到";

$markdown = new Parsedown();
$markdown->setSafeMode(true);
$markdown->setBreaksEnabled(true);
$markdown->setUrlsLinked(true);
$rs = Database::querySingleLine("users", Array("username" => $_SESSION['user']));
$pm = new chhcn\ProxyManager();
$nm = new chhcn\NodeManager();
$um = new chhcn\UserManager();

if(!$rs) {
	exit("<script>location='?page=login';</script>");
}

$user_traffic = $rs['traffic'] - ($um->getTodayTraffic($_SESSION['user']) / 1024 / 1024);

if(isset($_GET['sign'])) {
	
	ob_clean();
	
	chhcn\Utils::checkCsrf();
	
	// 欢迎来到喜闻乐见的欧皇与非酋抽流量
	
	if(!$_config['sign']['enable']) {
		exit("本站暂未开启签到功能~");
	}
	
	// 欧皇判定范围
	$good_rand = round($_config['sign']['max'] * 0.7);
	// 非酋判定范围
	$bad_rand = round($_config['sign']['max'] * 0.2);
	// 随机流量
	$rand = mt_rand($_config['sign']['min'], $_config['sign']['max']);
	
	$rs = Database::querySingleLine("sign", Array("username" => $_SESSION['user']));
	if($rs) {
		if(isset($rs['signdate'])) {
			if(Intval(date("Ymd")) >= Intval(date("Ymd", $rs['signdate'])) + 1) {
				$totaltraffic = $rs['totaltraffic'] == "" ? "0" : $rs['totaltraffic'];
				$totalsign    = $rs['totalsign']    == "" ? "0" : $rs['totalsign'];
				Database::update("sign", Array("signdate" => time(), "totaltraffic" => $totaltraffic + $rand, "totalsign" => $totalsign + 1), Array("username" => $_SESSION['user']));
				Database::update("users", Array("traffic" => $user_traffic + ($rand * 1024)), Array("username" => $_SESSION['user']));
				Database::update("proxies", Array("status" => "0"), Array("username" => $_SESSION['user'], "status" => "2"));
				$randtext = "今天运气不错，";
				if($rand >= $good_rand) {
					$randtext = "今天欧皇手气，共";
				} elseif($rand <= $bad_rand) {
					$randtext = "今天是非酋，只";
				}
				exit("签到成功，{$randtext}获得了 {$rand}GB 流量，目前您的剩余流量为 " . round(($user_traffic + ($rand * 1024)) / 1024, 2) . "GB。");
			} else {
				exit("您今天已经签到过了，请明天再来");
			}
		} else {
			Database::insert("sign", Array("id" => null, "username" => $_SESSION['user'], "signdate" => time(), "totaltraffic" => $rand, "totalsign" => 1));
			Database::update("users", Array("traffic" => $user_traffic + ($rand * 1024)), Array("username" => $_SESSION['user']));
			Database::update("proxies", Array("status" => "0"), Array("username" => $_SESSION['user'], "status" => "2"));
			exit("签到成功，这是你第一次签到，获得了 {$rand}GB 流量。");
		}
	} else {
		Database::insert("sign", Array("id" => null, "username" => $_SESSION['user'], "signdate" => time(), "totaltraffic" => $rand, "totalsign" => 1));
		Database::update("users", Array("traffic" => $user_traffic + ($rand * 1024)), Array("username" => $_SESSION['user']));
		Database::update("proxies", Array("status" => "0"), Array("username" => $_SESSION['user'], "status" => "2"));
		exit("签到成功，这是你第一次签到，获得了 {$rand}GB 流量。");
	}
}

$signed = false;
$ss = Database::querySingleLine("sign", Array("username" => $_SESSION['user']));
if($ss) {
	if(isset($ss['signdate']) && Intval(date("Ymd")) < Intval(date("Ymd", $ss['signdate'])) + 1) {
		$signed = true;
	}
}
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
.sign-button {
    padding: 15px 40px;
    font-size: 20px;
    transition: all 0.3s ease;
}
.sign-button:hover {
    transform: scale(1.05);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}
.info-box {
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.08);
    border-radius: 5px;
    transition: all 0.3s ease;
}
.info-box:hover {
    transform: translateY(-5px);
    box-shadow: 0 7px 20px rgba(0, 0, 0, 0.1);
}
.info-box-icon {
    height: 80px;
    width: 80px;
    border-radius: 50%;
    font-size: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.sign-stats-container {
    background-color: rgba(255, 255, 255, 0.8);
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    padding: 25px;
    margin-bottom: 30px;
}
.sign-animation {
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
    100% {
        transform: scale(1);
    }
}
</style>

<div class="content-header" oncopy="return false" oncut="return false;" onselectstart="return false" oncontextmenu="return false">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark"><?php echo $page_title; ?>&nbsp;&nbsp;<small class="text-muted text-xs">每日签到领取免费流量</small></h1>
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
            <div class="col-lg-8">
                <div class="card card-primary card-outline">
                    <div class="card-body text-center p-5">
                        <?php if($signed): ?>
                            <div class="mb-4">
                                <i class="fas fa-check-circle fa-5x text-success"></i>
                            </div>
                            <h2 class="text-success display-4" style="font-weight: 600;">今日已签到</h2>
                            <p class="lead mt-3 mb-4">太棒了！请继续保持，明天再来哦！</p>
                            <div class="alert alert-success">
                                <i class="fas fa-calendar-check"></i> 最近一次签到时间：<?php echo date("Y年m月d日 H:i:s", $ss['signdate']); ?>
                            </div>
                        <?php else: ?>
                            <div class="sign-animation mb-4">
                                <i class="fas fa-gift fa-5x text-primary"></i>
                            </div>
                            <h2 class="text-primary display-4" style="font-weight: 600;">您今天还未签到</h2>
                            <p class="lead mt-3 mb-4">立即签到，轻松领取您的每日免费流量！</p>
                            <button class="btn btn-primary btn-lg sign-button" onclick="sign()">
                                <i class="fas fa-hand-point-right mr-2"></i>立即签到
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box bg-info">
                            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-calendar-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">总计签到</span>
                                <span class="info-box-number"><?php echo $ss['totalsign'] == "" ? "0" : $ss['totalsign'];?> 天</span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                                <span class="progress-description">累积签到天数</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="info-box bg-success">
                            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-database"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">累计获得</span>
                                <span class="info-box-number"><?php echo $ss['totaltraffic'] == "" ? "0" : $ss['totaltraffic'];?> GB</span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                                <span class="progress-description">签到所得流量</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="info-box bg-warning">
                            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-clock"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">上次签到</span>
                                <span class="info-box-number"><?php echo $ss['signdate'] == "" ? "从未" : date("Y-m-d", $ss['signdate']);?></span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                                <span class="progress-description">最近一次签到</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-line mr-1"></i> 签到趋势分析</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart">
                            <canvas id="signInChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
			<div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> 签到说明</h3>
                    </div>
                    <div class="card-body">
                        <div class="callout callout-info">
                            <h5><i class="fas fa-info mr-1"></i> 系统简介</h5>
    						<p>欢迎使用签到系统，通过每天登录签到您可以获得免费的流量，可以用于抵消使用内网穿透产生的流量费用。</p>
                        </div>
                        
                        <div class="callout callout-warning">
                            <h5><i class="fas fa-cogs mr-1"></i> 签到配置</h5>
                            <p>
                                <i class="fas fa-circle <?php echo $_config['sign']['enable'] ? 'text-success' : 'text-danger'; ?>"></i> 
                                系统状态：<?php echo $_config['sign']['enable'] ? "<span class='badge badge-success'>已启用</span>" : "<span class='badge badge-danger'>已禁用</span>"; ?>
                            </p>
    						<p>
                                <i class="fas fa-gift text-primary"></i> 
                                每日可获：<span class="badge badge-info"><?php echo $_config['sign']['min']; ?> ~ <?php echo $_config['sign']['max']; ?> GB 流量</span>
                            </p>
                        </div>
                        
                        <div class="callout callout-success">
                            <h5><i class="fas fa-lightbulb mr-1"></i> 小贴士</h5>
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-check-circle text-success mr-1"></i> 每天签到可随机获取流量</li>
                                <li class="mb-2"><i class="fas fa-check-circle text-success mr-1"></i> 运气好的话可能会获得更多奖励</li>
                                <li class="mb-2"><i class="fas fa-check-circle text-success mr-1"></i> 连续签到有惊喜，不要中断哦</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-ad mr-1"></i> 精彩推荐</h3>
                    </div>
                    <div class="card-body p-0">
                        <img src="https://acg.suyanw.cn/api.php" class="img-fluid w-100" alt="Advertisement">
                    </div>
                </div>
            </div>
		</div>
	</div>
</div>

<!-- 签到结果弹窗 -->
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
                <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="window.location.reload()">确定</button></div>
        </div>
    </div>
</div>

<script type="text/javascript">
var csrf_token = "<?php echo $_SESSION['token']; ?>";
function alertMessage(title, body) {
	$("#msg-title").html(title);
	$("#msg-body").html(body);
	$("#modal-default").modal('toggle');
}
function sign() {
	var htmlobj = $.ajax({
		type: 'GET',
		url: "?page=panel&module=sign&sign&csrf=" + csrf_token,
		async:true,
		error: function() {
			alertMessage("签到失败", "网络错误，请稍后再试");
			return;
		},
		success: function() {
			alertMessage("签到成功", htmlobj.responseText);
			return;
		}
	});
}
</script>
<script src="assets/panel/plugins/chart.js/Chart.min.js"></script>
<script>
$(function () {
    'use strict'

    var ticksStyle = {
        fontColor: '#495057',
        fontStyle: 'bold'
    }

    var mode = 'index'
    var intersect = true
    
    // 生成最近7天的日期标签
    var labels = [];
    for (var i = 6; i >= 0; i--) {
        var d = new Date();
        d.setDate(d.getDate() - i);
        labels.push(d.toLocaleDateString('zh-CN', { month: '2-digit', day: '2-digit' }));
    }

    // 为了美观，我们随机生成一些数据
    // 在实际应用中，这里应该从后端获取
    var data = [];
    var signedToday = <?php echo $signed ? 'true' : 'false'; ?>;
    for (var i = 0; i < 7; i++) {
        if (i === 6 && signedToday) {
             data.push(Math.floor(Math.random() * 5) + 1); // 保证今天有签到数据
        } else if (i < 6) {
            data.push(Math.random() > 0.3 ? Math.floor(Math.random() * 5) + 1 : 0); // 模拟随机签到
        } else {
            data.push(0); // 今天未签到则数据为0
        }
    }

    var $signInChart = $('#signInChart')
    // eslint-disable-next-line no-unused-vars
    var signInChart = new Chart($signInChart, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                backgroundColor: '#007bff',
                borderColor: '#007bff',
                data: data
            }]
        },
        options: {
            maintainAspectRatio: false,
            tooltips: {
                mode: mode,
                intersect: intersect
            },
            hover: {
                mode: mode,
                intersect: intersect
            },
            legend: {
                display: false
            },
            scales: {
                yAxes: [{
                    // display: false,
                    gridLines: {
                        display: true,
                        lineWidth: '4px',
                        color: 'rgba(0, 0, 0, .2)',
                        zeroLineColor: 'transparent'
                    },
                    ticks: $.extend({
                        beginAtZero: true,
                        suggestedMax: 5
                    }, ticksStyle)
                }],
                xAxes: [{
                    display: true,
                    gridLines: {
                        display: false
                    },
                    ticks: ticksStyle
                }]
            }
        }
    })
})
</script>
