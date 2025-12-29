<?php
namespace chhcn;

use chhcn;

//$page_title = "用户列表1";
$um = new chhcn\UserManager();
$rs = Database::querySingleLine("users", Array("username" => $_SESSION['user']));

if(!$rs || $rs['group'] !== "admin") {
	exit("<script>location='?page=panel';</script>");
}

// 处理API请求
// 处理API请求
if(isset($_GET['getinfo']) && preg_match("/^[0-9]{1,10}$/", $_GET['getinfo'])) {
	chhcn\Utils::checkCsrf();
	$rs = Database::querySingleLine("users", Array("id" => $_GET['getinfo']));
	if($rs) {
		// 获取用户的限速信息
		$limitRecord = Database::querySingleLine("limits", Array("username" => $rs['username']));
		
		// 初始化默认值
		$inbound = "";
		$outbound = "";
		
		// 如果找到限速记录，使用该记录
		if($limitRecord) {
			$inbound = $limitRecord['inbound'];
			$outbound = $limitRecord['outbound'];
		} else {
			// 尝试从用户组获取默认值
			$gs = Database::querySingleLine("groups", Array("name" => $rs['group']));
			if($gs) {
				if(isset($gs['inbound'])) {
					$inbound = $gs['inbound'];
				}
				if(isset($gs['outbound'])) {
					$outbound = $gs['outbound'];
				}
			}
		}
		
		ob_clean();
		exit(json_encode(Array(
			"id"       => $rs['id'],
			"username" => $rs['username'],
			"traffic"  => $rs['traffic'],
			"proxies"  => $rs['proxies'],
			"inbound"  => $inbound,
			"outbound" => $outbound,
			"group"    => $rs['group'],
			"status"   => $rs['status'],
			"email"    => $rs['email'],
			"regtime"  => date("Y-m-d", $rs['regtime'])
		)));
	} else {
		ob_clean();
		Header("HTTP/1.1 403");
		exit("未找到用户");
	}
}

// 处理更新用户请求
if(isset($_GET['action']) && $_GET['action'] == "updateuser") {
	chhcn\Utils::checkCsrf();
	$status = isset($_POST['status']) ? $_POST['status'] : 0;
	$group = isset($_POST['group']) ? $_POST['group'] : "default";
	$traffic = isset($_POST['traffic']) ? Intval($_POST['traffic']) : "";
	$proxies = isset($_POST['proxies']) ? Intval($_POST['proxies']) : "";
	$inbound = isset($_POST['inbound']) ? Intval($_POST['inbound']) : "";
	$outbound = isset($_POST['outbound']) ? Intval($_POST['outbound']) : "";
	
	if(!isset($_POST['id']) || !preg_match("/^[0-9]{1,10}$/", $_POST['id'])) {
		exit("请求参数有误");
	}
	
	$id = Intval($_POST['id']);
	$rs = Database::querySingleLine("users", Array("id" => $id));
	
	if(!$rs) {
		exit("未找到用户");
	}
	
	if($traffic == "") {
		// 使用用户组的默认流量
		$gs = Database::querySingleLine("groups", Array("name" => $group));
		if($gs) {
			$traffic = $gs['traffic'];
		}
	}
	
	if($proxies == "") {
		// 使用用户组的默认隧道数
		$gs = Database::querySingleLine("groups", Array("name" => $group));
		if($gs) {
			$proxies = $gs['proxies'];
		}
	}
	
	// 设置限速 - 直接使用Database类操作limits表
	if($inbound === "" && $outbound === "") {
		// 使用用户组默认设置，删除用户自定义设置
		Database::delete("limits", Array("username" => $rs['username']));
	} else {
		// 至少有一个值不为空，使用自定义设置
		$inbound = $inbound === "" ? 0 : $inbound;  // 如果为空设为0
		$outbound = $outbound === "" ? 0 : $outbound;  // 如果为空设为0
		
		// 检查用户是否已有限速记录
		$limitRecord = Database::querySingleLine("limits", Array("username" => $rs['username']));
		if($limitRecord) {
			// 更新已有记录
			Database::update("limits", Array("username" => $rs['username']), Array(
				"inbound" => $inbound,
				"outbound" => $outbound
			));
		} else {
			// 创建新记录
			Database::insert("limits", Array(
				"username" => $rs['username'],
				"inbound" => $inbound,
				"outbound" => $outbound
			));
		}
	}
	
	Database::update("users", Array("id" => $id), Array(
		"status" => $status,
		"group" => $group,
		"traffic" => $traffic,
		"proxies" => $proxies
	));
	
	// 输出更详细的结果，包含限速信息
	$result = "用户 {$rs['username']} 信息已更新\n";
	$result .= "流量: " . ($traffic == "" ? "使用组默认值" : $traffic . " MB") . "\n";
	$result .= "隧道数: " . ($proxies == "" ? "使用组默认值" : $proxies . " 个") . "\n";
	
	if($inbound === "" && $outbound === "") {
		$result .= "限速: 使用组默认值";
	} else {
		$result .= "最大上传速度: " . ($inbound == 0 ? "无限制" : $inbound . " KB/s") . "\n";
		$result .= "最大下载速度: " . ($outbound == 0 ? "无限制" : $outbound . " KB/s");
	}
	
	exit($result);
}

// 获取统计信息
try {
    $totalUsersQuery = Database::query("users", "SELECT COUNT(*) FROM users", true);
    $totalUsers = Database::toArray($totalUsersQuery)[0][0];
} catch (Exception $e) {
    $totalUsers = 0;
    error_log("统计用户总数出错：" . $e->getMessage());
}

try {
    $activeUsersQuery = Database::query("users", "SELECT COUNT(*) FROM users WHERE status = 0", true);
    $activeUsers = Database::toArray($activeUsersQuery)[0][0];
} catch (Exception $e) {
    $activeUsers = 0;
    error_log("统计活跃用户出错：" . $e->getMessage());
}

try {
    $bannedUsersQuery = Database::query("users", "SELECT COUNT(*) FROM users WHERE status = 1", true);
    $bannedUsers = Database::toArray($bannedUsersQuery)[0][0];
} catch (Exception $e) {
    $bannedUsers = 0;
    error_log("统计封禁用户出错：" . $e->getMessage());
}

try {
    $adminUsersQuery = Database::query("users", "SELECT COUNT(*) FROM users WHERE `group` = 'admin'", true);
    $adminUsers = Database::toArray($adminUsersQuery)[0][0];
} catch (Exception $e) {
    $adminUsers = 0;
    error_log("统计管理员用户出错：" . $e->getMessage());
}

// 分页设置
$spage = isset($_GET['p']) && preg_match("/^[0-9]{1,9}$/", $_GET['p']) && Intval($_GET['p']) > 0 ? (Intval($_GET['p'])) : 1;
$itemsPerPage = 10;
$offset = ($spage - 1) * $itemsPerPage;
$_GET['search'] = isset($_GET['search']) ? Database::escape($_GET['search']) : "";
$_GET['p'] = $offset;

// 构建查询
$mainSQL = "SELECT * FROM `users` ";
$mainSQL .= (isset($_GET['search']) && !empty($_GET['search'])) ? 
    "WHERE POSITION('{$_GET['search']}' IN `username`) OR POSITION('{$_GET['search']}' IN `email`) " : "";
$mainSQL .= "ORDER BY id DESC ";
$mainSQL .= "LIMIT {$offset}," . ($itemsPerPage + 1); // 多获取一条记录用于判断是否有下一页

$rs = Database::toArray(Database::query("users", $mainSQL, true));
?>

<style type="text/css">
.fix-text p {
	margin-bottom: 4px;
}
.sub-heading {
    width: calc(100% - 16px);
    height: 0 !important;
    border-top: 1px solid #e9f1f1 !important;
    text-align: center !important;
    margin-top: 24px !important;
    margin-bottom: 24px !important;
    margin-left: 7px;
    position: relative;
}
.sub-heading span {
    display: inline-block;
    position: relative;
    padding: 0 17px;
    top: -11px;
    font-size: 15px;
    color: #007bff;
    background-color: #fff;
    font-weight: 500;
}
.page-num {
    margin: 15px 0;
    padding: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #f8f9fa;
    border-radius: 0 0 4px 4px;
}
.user-status {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 5px;
}
.user-status.active {
    background-color: #28a745;
    box-shadow: 0 0 5px rgba(40, 167, 69, 0.5);
}
.user-status.banned {
    background-color: #dc3545;
}
.user-badge {
    padding: 3px 8px;
    border-radius: 50px;
    font-size: 12px;
}
.edit-form label {
    font-weight: 500;
    color: #555;
    font-size: 14px;
    margin-bottom: 5px;
}
.edit-form .form-group {
    margin-bottom: 20px;
}
.edit-form .form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
.edit-form small {
    color: #6c757d;
}
.form-section {
    border-left: 3px solid #007bff;
    padding-left: 15px;
    margin-bottom: 25px;
}
.form-section h5 {
    font-size: 16px;
    color: #007bff;
    margin-bottom: 15px;
}
.stat-card {
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}
.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.stat-card .icon {
    position: absolute;
    right: 15px;
    top: 15px;
    font-size: 28px;
    opacity: 0.2;
}
.stat-card .number {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 5px;
}
.stat-card .title {
    font-size: 14px;
    color: rgba(255,255,255,0.8);
}
.bg-info-gradient {
    background: linear-gradient(to right, #0091ea, #00b0ff);
    color: white;
}
.bg-success-gradient {
    background: linear-gradient(to right, #43a047, #66bb6a);
    color: white;
}
.bg-warning-gradient {
    background: linear-gradient(to right, #fb8c00, #ffa726);
    color: white;
}
.bg-danger-gradient {
    background: linear-gradient(to right, #e53935, #ef5350);
    color: white;
}
.empty-state {
    text-align: center;
    padding: 30px;
}
.empty-state i {
    font-size: 3rem;
    color: #ccc;
    margin-bottom: 15px;
}
.empty-state p {
    color: #999;
    margin-bottom: 15px;
}
.user-avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background-color: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
    font-weight: bold;
    color: #666;
}
.user-row {
    cursor: pointer;
    transition: background-color 0.2s;
}
.user-row:hover {
    background-color: rgba(0, 123, 255, 0.05) !important;
}
.user-row.selected {
    background-color: rgba(0, 123, 255, 0.1) !important;
    border-left: 3px solid #007bff;
}
</style>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-users mr-2"></i>用户管理</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="?page=panel"><i class="fas fa-home"></i> 首页</a></li>
                    <li class="breadcrumb-item active">用户管理</li>
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
                <div class="stat-card bg-info-gradient">
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="number"><?php echo $totalUsers; ?></div>
                    <div class="title">用户总数</div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="stat-card bg-success-gradient">
                    <div class="icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="number"><?php echo $activeUsers; ?></div>
                    <div class="title">活跃用户</div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="stat-card bg-warning-gradient">
                    <div class="icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="number"><?php echo $adminUsers; ?></div>
                    <div class="title">管理员</div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="stat-card bg-danger-gradient">
                    <div class="icon">
                        <i class="fas fa-user-slash"></i>
                    </div>
                    <div class="number"><?php echo $bannedUsers; ?></div>
                    <div class="title">已封禁用户</div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-list mr-2"></i>用户列表</h3>
                        <div class="card-tools">
                            <form class="form-inline">
                                <input type="hidden" name="page" value="panel">
                                <input type="hidden" name="module" value="userlist">
                                <div class="input-group input-group-sm">
                                    <input type="text" name="search" class="form-control" placeholder="搜索用户名或邮箱..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="50" class="text-center"><i class="fas fa-hashtag"></i></th>
                                        <th><i class="fas fa-user mr-1"></i> 用户信息</th>
                                        <th><i class="fas fa-chart-pie mr-1"></i> 资源配额</th>
                                        <th><i class="fas fa-users-cog mr-1"></i> 用户组</th>
                                        <th><i class="fas fa-calendar-alt mr-1"></i> 注册时间</th>
                                        <th class="text-center"><i class="fas fa-cog mr-1"></i> 操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 0;
                                    foreach($rs as $user) {
                                        $i++;
                                        if($i > $itemsPerPage) break;
                                        
                                        $traffic = round($user[4] / 1024, 2) . " GB";
                                        $regtime = date("Y-m-d", $user[7]);
                                        
                                        // 用户状态
                                        if($user[8] == 0) {
                                            $statusDot = '<span class="user-status active"></span>';
                                            $statusBadge = '<span class="badge badge-success user-badge"><i class="fas fa-check-circle mr-1"></i>正常</span>';
                                        } else {
                                            $statusDot = '<span class="user-status banned"></span>';
                                            $statusBadge = '<span class="badge badge-danger user-badge"><i class="fas fa-ban mr-1"></i>已封禁</span>';
                                        }
                                        
                                        // 用户组样式
                                        $groupColor = ($user[6] == 'admin') ? 'badge-danger' : 'badge-info';
                                        $groupIcon = ($user[6] == 'admin') ? 'fas fa-user-shield' : 'fas fa-user';
                                        
                                        // 获取用户名首字母
                                        $initial = strtoupper(substr($user[1], 0, 1));
                                        
                                        echo "<tr class='user-row' data-user-id='{$user[0]}' onclick='selectUser(this, {$user[0]})'>
                                            <td class='text-center align-middle'>{$user[0]}</td>
                                            <td>
                                                <div class='d-flex align-items-center'>
                                                    <div class='user-avatar bg-light'>{$initial}</div>
                                                    <div>
                                                        <div><strong>{$user[1]}</strong> {$statusDot}</div>
                                                        <div class='text-muted small'>{$user[3]}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div><i class='fas fa-chart-area text-primary mr-1'></i> 流量：<span class='badge badge-light'>{$traffic}</span></div>
                                                <div class='mt-1'><i class='fas fa-project-diagram text-info mr-1'></i> 隧道数：<span class='badge badge-light'>{$user[5]}</span></div>
                                            </td>
                                            <td><span class='badge {$groupColor} user-badge'><i class='{$groupIcon} mr-1'></i>{$user[6]}</span></td>
                                            <td><i class='far fa-clock text-muted mr-1'></i>{$regtime}</td>
                                            <td class='text-center'>
                                                <button class='btn btn-sm btn-outline-primary edit-btn' onclick='edit({$user[0]})'>
                                                    <i class='fas fa-edit mr-1'></i>编辑
                                                </button>
                                            </td>
                                        </tr>";
                                    }
                                    
                                    if($i == 0) {
                                        echo "<tr><td colspan='6'>
                                            <div class='empty-state'>
                                                <i class='fas fa-users'></i>
                                                <p>没有找到符合条件的用户</p>";
                                        if(!empty($_GET['search'])) {
                                            echo "<a href='?page=panel&module=userlist' class='btn btn-outline-primary btn-sm'><i class='fas fa-undo mr-1'></i>清除搜索条件</a>";
                                        }
                                        echo "</div>
                                        </td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if($i > 0): ?>
                        <div class="page-num">
                            <div>
                                <span class="text-muted">当前在第 <?php echo $spage; ?> 页</span>
                            </div>
                            <div>
                                <?php
                                $search = isset($_GET['search']) ? "&search=" . urlencode($_GET['search']) : "";
                                $fpage = $spage - 1;
                                $npage = $spage + 1;
                                
                                echo "<div class='btn-group'>";
                                
                                // 首页按钮
                                if($spage > 1) {
                                    echo "<a href='?page=panel&module=userlist{$search}' class='btn btn-sm btn-outline-primary' title='首页'><i class='fas fa-angle-double-left'></i></a>";
                                } else {
                                    echo "<button class='btn btn-sm btn-outline-secondary' disabled><i class='fas fa-angle-double-left'></i></button>";
                                }
                                
                                // 上一页按钮
                                if($spage > 1) {
                                    echo "<a href='?page=panel&module=userlist{$search}&p={$fpage}' class='btn btn-sm btn-outline-primary' title='上一页'><i class='fas fa-angle-left'></i></a>";
                                } else {
                                    echo "<button class='btn btn-sm btn-outline-secondary' disabled><i class='fas fa-angle-left'></i></button>";
                                }
                                
                                // 当前页码
                                echo "<button class='btn btn-sm btn-primary' disabled>{$spage}</button>";
                                
                                // 下一页按钮
                                if($i > $itemsPerPage) {
                                    echo "<a href='?page=panel&module=userlist{$search}&p={$npage}' class='btn btn-sm btn-outline-primary' title='下一页'><i class='fas fa-angle-right'></i></a>";
                                } else {
                                    echo "<button class='btn btn-sm btn-outline-secondary' disabled><i class='fas fa-angle-right'></i></button>";
                                }
                                
                                echo "</div>";
                                ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user-edit mr-2"></i>修改用户信息</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body edit-form">
                        <div id="edit-placeholder" class="text-center py-4">
                            <i class="fas fa-hand-pointer fa-3x text-muted mb-3"></i>
                            <p id="statusmsg" class="text-muted">请从左侧列表选择一个用户进行编辑</p>
                        </div>
                        
                        <div id="edit-form" style="display: none;">
                            <!-- 基本信息部分 -->
                            <div class="form-section">
                                <h5><i class="fas fa-info-circle mr-2"></i>基本信息</h5>
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="username">用户名</label>
                                            <input type="text" class="form-control" id="username" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email">邮箱</label>
                                            <input type="text" class="form-control" id="email" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 资源配额部分 -->
                            <div class="form-section">
                                <h5><i class="fas fa-sliders-h mr-2"></i>资源配额</h5>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-1"></i> 留空将会使用用户组的默认设置
                                </div>
                                
                                <div class="form-group">
                                    <label for="traffic">流量设置 (MB)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-chart-area"></i></span>
                                        </div>
                                        <input type="number" class="form-control" id="traffic">
                                        <div class="input-group-append">
                                            <span class="input-group-text">MB</span>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">修改后即时生效</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="proxies">隧道数量</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-project-diagram"></i></span>
                                        </div>
                                        <input type="number" class="form-control" id="proxies">
                                        <div class="input-group-append">
                                            <span class="input-group-text">个</span>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">用户最多可以添加的隧道数量</small>
                                </div>
                                
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="inbound">最大上传 (KB/s)</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-upload"></i></span>
                                                </div>
                                                <input type="number" class="form-control" id="inbound">
                                            </div>
                                            <small class="form-text text-muted">留空则继承组设定</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="outbound">最大下载 (KB/s)</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-download"></i></span>
                                                </div>
                                                <input type="number" class="form-control" id="outbound">
                                            </div>
                                            <small class="form-text text-muted">留空则继承组设定</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 权限设置部分 -->
                            <div class="form-section">
                                <h5><i class="fas fa-user-shield mr-2"></i>权限设置</h5>
                                
                                <div class="form-group">
                                    <label for="group">用户组</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-users"></i></span>
                                        </div>
                                        <select class="form-control" id="group">
                                            <?php
                                            $gs = Database::toArray(Database::query("groups", "SELECT * FROM `groups`", true));
                                            foreach($gs as $gi) {
                                                echo "<option value='{$gi[1]}'>{$gi[2]}</option>";
                                            }
                                            ?>
                                            <option value="admin">管理员</option>
                                        </select>
                                    </div>
                                    <small class="form-text text-muted">选择需要将用户分配到的用户组</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="status">用户状态</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-toggle-on"></i></span>
                                        </div>
                                        <select class="form-control" id="status">
                                            <option value="0">正常</option>
                                            <option value="1">封号</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer" id="edit-footer" style="display: none;">
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="cancelEdit()">
                                <i class="fas fa-times mr-1"></i> 取消编辑
                            </button>
                            <button type="button" class="btn btn-primary" onclick="save()">
                                <i class="fas fa-save mr-1"></i> 保存设置
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
var csrf_token = "<?php echo $_SESSION['token']; ?>";
var userid = "";
var currentUsername = "";

$(document).ready(function() {
    // 初始化工具提示
    $('[data-toggle="tooltip"]').tooltip();
    
    // 页面加载动画
    $('.stat-card').css('opacity', 0);
    $('.card').css('opacity', 0);
    
    setTimeout(function() {
        $('.stat-card').each(function(i) {
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
        }, 300);
    }, 100);
});

// 搜索功能
function search() {
    var searchTerm = $('#searchdata').val();
    if(searchTerm) {
        window.location = '?page=panel&module=userlist&search=' + encodeURIComponent(searchTerm);
    } else {
        window.location = '?page=panel&module=userlist';
    }
}

// 选择用户行
function selectUser(element, id) {
    $('.user-row').removeClass('selected');
    $(element).addClass('selected');
    edit(id);
}

// 编辑用户
function edit(id) {
    // 显示加载状态
    $('#statusmsg').html("正在加载用户信息...");
    
    var htmlobj = $.ajax({
        type: 'GET',
        url: "?page=panel&module=userlist&getinfo=" + id + "&csrf=" + csrf_token,
        async: true,
        error: function(xhr) {
            alert("错误：" + xhr.responseText);
            return;
        },
        success: function() {
            try {
                var json = JSON.parse(htmlobj.responseText);
                userid = json.id;
                currentUsername = json.username;
                
                // 在控制台记录获取的上传下载数据
                console.log("获取到的用户数据:", json);
                console.log("最大上传速度:", json.inbound);
                console.log("最大下载速度:", json.outbound);
                
                // 填充表单
                $("#username").val(json.username);
                $("#email").val(json.email);
                $("#traffic").val(json.traffic);
                $("#proxies").val(json.proxies);
                // 确保上传下载数据正确填充到表单
                $("#inbound").val(json.inbound);
                $("#outbound").val(json.outbound);
                $("#group").val(json.group);
                $("#status").val(json.status);
                
                $("#statusmsg").html("正在编辑用户 " + json.username + " 的设置");
                
                // 显示编辑表单
                $('#edit-placeholder').hide();
                $('#edit-form').fadeIn();
                $('#edit-footer').fadeIn();
            } catch(e) {
                console.error("解析数据出错:", e);
                alert("错误：无法解析服务器返回的数据");
            }
            return;
        }
    });
}

// 取消编辑
function cancelEdit() {
    $('#edit-form').hide();
    $('#edit-footer').hide();
    $('#edit-placeholder').fadeIn();
    $('#statusmsg').html('请从左侧列表选择一个用户进行编辑');
    $('.user-row').removeClass('selected');
    userid = "";
    currentUsername = "";
}

// 保存设置
function save() {
    if(userid == "") {
        alert("您未编辑任何用户信息。");
        return;
    }
    
    // 获取表单数据
    var trafficVal = $("#traffic").val();
    var proxiesVal = $("#proxies").val();
    var inboundVal = $("#inbound").val();
    var outboundVal = $("#outbound").val();
    var groupVal = $("#group").val();
    var statusVal = $("#status").val();
    
    // 显示保存中状态
    $('#statusmsg').html("正在保存用户 " + currentUsername + " 的设置...");
    
    var htmlobj = $.ajax({
        type: 'POST',
        url: "?action=updateuser&page=panel&module=userlist&csrf=" + csrf_token,
        async: true,
        data: {
            id: userid,
            traffic: trafficVal,
            proxies: proxiesVal,
            inbound: inboundVal,
            outbound: outboundVal,
            group: groupVal,
            status: statusVal
        },
        error: function(xhr) {
            alert("保存失败：" + xhr.responseText);
            $('#statusmsg').html("保存失败，请重试");
            return;
        },
        success: function() {
            alert(htmlobj.responseText);
            $('#statusmsg').html("保存成功，正在刷新页面...");
            setTimeout(function() {
                window.location.reload();
            }, 1000);
            return;
        }
    });
}
</script>