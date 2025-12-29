<?php
namespace chhcn;

use chhcn;

// 导出订单记录
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    $pm = new PackageManager();
    $orders = $pm->getAllOrders();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=package_orders_' . date('Ymd') . '.csv');

    $output = fopen('php://output', 'w');
    
    // 添加BOM头，防止UTF-8在Excel中乱码
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // 写入表头
    fputcsv($output, ['订单ID', '用户名', '套餐名称', '价格', '购买时间', '到期时间']);

    // 写入数据
    foreach ($orders as $order) {
        $expireTime = $order['expire_time'] > 0 ? date('Y-m-d H:i:s', intval($order['expire_time'])) : '永久';
        fputcsv($output, [
            $order['id'],
            $order['username'],
            $order['name'] ?? '未知套餐',
            number_format(floatval($order['price']), 2),
            date('Y-m-d H:i:s', intval($order['order_time'])),
            $expireTime
        ]);
    }

    fclose($output);
    exit;
}

//$page_title = "套餐管理";
$pm = new PackageManager();
$rs = Database::querySingleLine("users", Array("username" => $_SESSION['user']));

if(!$rs || $rs['group'] !== "admin") {
	exit("<script>location='?page=panel';</script>");
}

if(isset($_GET['getinfo']) && preg_match("/^[0-9]{1,10}$/", $_GET['getinfo'])) {
	chhcn\Utils::checkCsrf();
	$rs = $pm->getPackageInfo($_GET['getinfo']);
	if($rs) {
		ob_clean();
		exit(json_encode($rs));
	} else {
		ob_clean();
		Header("HTTP/1.1 403");
		exit("未找到套餐");
	}
}

// 获取套餐统计信息
$allPackages = $pm->getAllPackages();
$activePackages = array_filter($allPackages, function($pkg) {
    return intval($pkg['status']) === 1;
});
$totalPackages = count($allPackages);
$activePackagesCount = count($activePackages);

// 获取订单统计信息
$allOrders = $pm->getAllOrders();
$totalOrders = count($allOrders);
$totalRevenue = 0;
foreach ($allOrders as $order) {
    $totalRevenue += floatval($order['price']);
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-shopping-bag mr-2"></i>套餐管理</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="?page=panel"><i class="fas fa-home"></i> 首页</a></li>
                    <li class="breadcrumb-item active">套餐管理</li>
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
                        <h3><?php echo $totalPackages; ?></h3>
                        <p>套餐总数</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?php echo $activePackagesCount; ?></h3>
                        <p>上架套餐</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?php echo $totalOrders; ?></h3>
                        <p>订单总数</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-receipt"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>¥<?php echo number_format($totalRevenue, 2); ?></h3>
                        <p>总收入</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-coins"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-list-alt mr-1"></i> 套餐列表</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="resetForm()">
                                <i class="fas fa-plus"></i> 新增套餐
                            </button>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
						<table class="table table-hover text-nowrap table-striped">
							<thead>
								<tr>
									<th><i class="fas fa-hashtag mr-1"></i> ID</th>
									<th><i class="fas fa-tag mr-1"></i> 套餐名称</th>
									<th><i class="fas fa-yen-sign mr-1"></i> 价格</th>
									<th><i class="fas fa-calendar-alt mr-1"></i> 有效期</th>
									<th><i class="fas fa-users mr-1"></i> 用户组</th>
									<th class="text-center"><i class="fas fa-toggle-on mr-1"></i> 状态</th>
									<th class="text-center"><i class="fas fa-cogs mr-1"></i> 操作</th>
								</tr>
							</thead>
							<tbody>
								<?php
								if (count($allPackages) > 0) {
									foreach($allPackages as $package) {
										$statusText = intval($package['status']) === 1 ? "上架" : "下架";
										$statusClass = intval($package['status']) === 1 ? "badge-success" : "badge-danger";
										$statusIcon = intval($package['status']) === 1 ? "fa-check-circle" : "fa-times-circle";
										$duration = isset($package['duration']) ? $package['duration'] : '0';
										$durationText = $duration > 0 ? $duration . ' 天' : '<span class="badge badge-info"><i class="fas fa-infinity mr-1"></i> 永久</span>';
										
										echo "<tr>
										<td>#{$package['id']}</td>
										<td><strong>" . htmlspecialchars($package['name']) . "</strong></td>
										<td><span class='text-success font-weight-bold'>¥" . htmlspecialchars($package['price']) . "</span></td>
										<td>{$durationText}</td>
										<td><span class='badge badge-primary'>" . htmlspecialchars($package['group_name']) . "</span></td>
										<td class='text-center'><span class='badge {$statusClass}'><i class='fas {$statusIcon} mr-1'></i> {$statusText}</span></td>
										<td class='text-center'>
											<button class='btn btn-xs btn-info' onclick='edit({$package['id']})' title='编辑套餐'><i class='fas fa-edit'></i> 编辑</button>
											<button class='btn btn-xs btn-danger' onclick='deletePackage({$package['id']})' title='删除套餐'><i class='fas fa-trash'></i> 删除</button>
										</td>
										</tr>";
									}
								} else {
									echo "<tr><td colspan='7' class='text-center py-4'>
										<div class='empty-state'>
											<i class='fas fa-shopping-basket fa-4x text-muted'></i>
											<p class='mt-3'>暂无套餐，请在右侧添加</p>
											<button class='btn btn-sm btn-primary' onclick='resetForm()'>
												<i class='fas fa-plus'></i> 立即添加
											</button>
										</div>
									</td></tr>";
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
				
				<div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-receipt mr-1"></i> 套餐订单记录</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-toggle="dropdown">
                                    <i class="fas fa-download"></i> 导出
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a href="?page=panel&module=packages&export=excel" class="dropdown-item" id="export-excel">
                                        <i class="fas fa-file-excel mr-2"></i> 导出到 Excel (CSV)
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
						<table class="table table-hover text-nowrap table-striped" id="orders-table">
							<thead>
								<tr>
									<th><i class="fas fa-hashtag mr-1"></i> 订单ID</th>
									<th><i class="fas fa-user mr-1"></i> 用户名</th>
									<th><i class="fas fa-tag mr-1"></i> 套餐名称</th>
									<th><i class="fas fa-yen-sign mr-1"></i> 价格</th>
									<th><i class="fas fa-calendar-plus mr-1"></i> 购买时间</th>
									<th><i class="fas fa-calendar-times mr-1"></i> 到期时间</th>
								</tr>
							</thead>
							<tbody>
								<?php
								if (count($allOrders) > 0) {
									foreach($allOrders as $order) {
										$orderTime = date('Y-m-d H:i:s', intval($order['order_time']));
										$expireTime = $order['expire_time'] > 0 ? date('Y-m-d H:i:s', intval($order['expire_time'])) : '<span class="badge badge-success"><i class="fas fa-infinity mr-1"></i> 永久</span>';
										$username = htmlspecialchars($order['username']);
										$packageName = htmlspecialchars($order['name'] ?? '未知套餐');
										$price = number_format(floatval($order['price']), 2);
										
										echo "<tr>
										<td>#{$order['id']}</td>
										<td><span class='badge badge-info'><i class='fas fa-user mr-1'></i> {$username}</span></td>
										<td>{$packageName}</td>
										<td><span class='text-success font-weight-bold'>¥{$price}</span></td>
										<td><i class='far fa-clock mr-1'></i> {$orderTime}</td>
										<td>{$expireTime}</td>
										</tr>";
									}
								} else {
									echo "<tr><td colspan='6' class='text-center py-4'>
										<div class='empty-state'>
											<i class='fas fa-receipt fa-4x text-muted'></i>
											<p class='mt-3'>暂无订单记录</p>
										</div>
									</td></tr>";
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="col-lg-4">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title" id="form-title"><i class="fas fa-plus-circle mr-1"></i> 添加新套餐</h3>
                    </div>
                    <div class="card-body">
						<p id="statusmsg" class="alert alert-info"><i class="fas fa-info-circle"></i> 在此添加新套餐，或点击左侧列表的"编辑"按钮来修改。</p>
						
						<div class="form-group">
							<label for="package_name"><i class="fas fa-tag mr-1"></i> 套餐名称</label>
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text"><i class="fas fa-shopping-bag"></i></span>
								</div>
								<input type="text" class="form-control" id="package_name" placeholder="输入套餐名称">
							</div>
							<small class="form-text text-muted">用于显示的名称，如"基础套餐"、"高级套餐"等</small>
						</div>
						
						<div class="form-group">
							<label for="package_description"><i class="fas fa-align-left mr-1"></i> 套餐描述</label>
							<textarea class="form-control" id="package_description" rows="3" placeholder="输入套餐描述内容"></textarea>
							<small class="form-text text-muted">简要描述套餐内容和特点，支持换行</small>
						</div>
						
						<div class="form-group">
							<label for="package_price"><i class="fas fa-yen-sign mr-1"></i> 套餐价格</label>
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text">¥</span>
								</div>
								<input type="number" step="0.01" class="form-control" id="package_price" placeholder="输入套餐价格">
								<div class="input-group-append">
									<span class="input-group-text">元</span>
								</div>
							</div>
							<small class="form-text text-muted">设置套餐的购买价格，精确到分</small>
						</div>
						
						<div class="form-group">
							<label for="package_duration"><i class="fas fa-calendar-alt mr-1"></i> 有效期</label>
							<div class="input-group">
								<input type="number" class="form-control" id="package_duration" value="0" placeholder="输入有效期天数">
								<div class="input-group-append">
									<span class="input-group-text">天</span>
								</div>
							</div>
							<small class="form-text text-muted">设置套餐的有效期天数，设置为0表示永久有效</small>
						</div>
						
						<div class="form-group">
							<label for="package_group"><i class="fas fa-users mr-1"></i> 对应用户组</label>
							<select class="form-control" id="package_group">
								<?php
								$groupManager = new GroupManager();
								$groups = $groupManager->getAllGroups();
								foreach($groups as $group) {
									$name = isset($group['name']) ? $group['name'] : (isset($group[1]) ? $group[1] : '');
									$friendly = isset($group['friendly_name']) ? $group['friendly_name'] : (isset($group[2]) ? $group[2] : '');
									if($name && $friendly) {
										echo "<option value='{$name}'>{$friendly} ({$name})</option>";
									}
								}
								?>
							</select>
							<small class="form-text text-muted">购买后用户将被分配到的用户组，决定用户的权限和资源限制</small>
						</div>
						
						<div class="form-group">
							<label><i class="fas fa-toggle-on mr-1"></i> 套餐状态</label>
							<div class="custom-control custom-switch">
								<input type="checkbox" class="custom-control-input" id="package_status_switch" checked>
								<label class="custom-control-label" for="package_status_switch">上架销售</label>
								<input type="hidden" id="package_status" value="1">
							</div>
							<small class="form-text text-muted">控制套餐是否在商店页面显示和可购买</small>
						</div>
					</div>
					<div class="card-footer">
						<button class="btn btn-primary float-right" onclick="save()"><i class="fas fa-save mr-1"></i> 保存套餐</button>
						<button class="btn btn-default" onclick="resetForm()"><i class="fas fa-undo mr-1"></i> 重置</button>
					</div>
				</div>
				
				<!-- 帮助卡片 -->
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-question-circle mr-1"></i> 套餐管理帮助</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <p><i class="fas fa-info-circle text-info mr-1"></i> <strong>如何添加新套餐？</strong></p>
                        <p>在右侧表单中填写套餐信息后点击"保存套餐"按钮。</p>
                        
                        <p><i class="fas fa-info-circle text-info mr-1"></i> <strong>如何编辑套餐？</strong></p>
                        <p>在套餐列表中点击对应套餐的"编辑"按钮，修改表单信息后保存。</p>
                        
                        <p><i class="fas fa-info-circle text-info mr-1"></i> <strong>如何下架套餐？</strong></p>
                        <p>编辑套餐时，将状态切换为"下架"即可。下架后的套餐将不会在商店页面显示。</p>
                        
                        <p><i class="fas fa-exclamation-triangle text-warning mr-1"></i> <strong>注意事项</strong></p>
                        <p>删除套餐操作不可恢复，且不会影响已购买该套餐的用户。</p>
                    </div>
                </div>
			</div>
		</div>
	</div>
</div>

<style>
.empty-state {
    text-align: center;
    padding: 30px;
    color: #6c757d;
}
.table th {
    background-color: #f4f6f9;
}
.custom-switch .custom-control-label::before {
    width: 2.25rem;
}
.custom-switch .custom-control-label::after {
    left: calc(-2.25rem + 2px);
}
.custom-switch .custom-control-input:checked ~ .custom-control-label::after {
    transform: translateX(1.2rem);
}
.small-box {
    border-radius: 0.5rem;
    box-shadow: 0 0 15px rgba(0,0,0,.1);
}
.small-box .icon {
    font-size: 70px;
    color: rgba(255,255,255,0.15);
}
.card-primary.card-outline {
    border-top: 3px solid #007bff;
}
.card-info.card-outline {
    border-top: 3px solid #17a2b8;
}
</style>

<script type="text/javascript">
var csrf_token = "<?php echo $_SESSION['token']; ?>";
var packageId = undefined;

function edit(id) {
    $.ajax({
        type: 'GET',
        url: "?page=panel&module=packages&getinfo=" + id + "&csrf=" + csrf_token,
        async: true,
        error: function(xhr) {
            alert("错误：" + xhr.responseText);
        },
        success: function(data) {
            try {
                var json = JSON.parse(data);
                packageId = id;
                $("#package_name").val(json.name);
                $("#package_description").val(json.description);
                $("#package_price").val(json.price);
                $("#package_duration").val(json.duration || 0);
                $("#package_group").val(json.group_name);
                $("#package_status").val(json.status);
                $("#package_status_switch").prop('checked', json.status == 1);
                $("#statusmsg").html("正在编辑套餐 " + json.name);
                $("#form-title").html("<i class='fas fa-edit mr-1'></i> 编辑套餐 #" + id);
            } catch (e) {
                alert("错误：无法解析服务器返回的数据");
                console.error(e);
            }
        }
    });
}

function resetForm() {
    packageId = undefined;
    $("#package_name").val('');
    $("#package_description").val('');
    $("#package_price").val('');
    $("#package_duration").val('0');
    $("#package_group").prop('selectedIndex', 0);
    $("#package_status").val('1');
    $("#package_status_switch").prop('checked', true);
    $("#statusmsg").html("在此添加新套餐，或点击左侧列表的'编辑'按钮来修改。");
    $("#form-title").html("<i class='fas fa-plus-circle mr-1'></i> 添加新套餐");
}

function deletePackage(id) {
    if (!confirm("你确定要删除这个套餐吗？此操作不可恢复！")) {
        return;
    }
    $.ajax({
        type: 'POST',
        url: "?action=deletepackage&page=panel&module=packages&csrf=" + csrf_token,
        async: true,
        data: {
            id: id
        },
        error: function(xhr) {
            alert("错误：" + xhr.responseText);
        },
        success: function(data) {
            alert(data);
            window.location.reload();
        }
    });
}

function save() {
    var url = "?action=updatepackage&page=panel&module=packages";
    if (packageId == undefined) {
        packageId = null;
        url = "?action=addpackage&page=panel&module=packages";
    }
    
    // 在保存前获取最新的状态值
    var packageStatus = $('#package_status_switch').prop('checked') ? '1' : '0';

    $.ajax({
        type: 'POST',
        url: url + "&csrf=" + csrf_token,
        async: true,
        data: {
            id: packageId,
            name: $("#package_name").val(),
            description: $("#package_description").val(),
            price: $("#package_price").val(),
            duration: $("#package_duration").val(),
            group_name: $("#package_group").val(),
            status: packageStatus
        },
        error: function(xhr) {
            alert("错误：" + xhr.responseText);
        },
        success: function(data) {
            alert(data);
            window.location.reload();
        }
    });
}

$(document).ready(function() {
    // 状态开关
    $('#package_status_switch').change(function() {
        $('#package_status').val($(this).prop('checked') ? '1' : '0');
    });
    
    // 导出功能
    $('#export-excel').click(function(e) {
        e.preventDefault();
        
        let table = document.querySelector("#orders-table");
        let ws = XLSX.utils.table_to_sheet(table);
        let wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "套餐订单记录");
        XLSX.writeFile(wb, "package_orders_" + new Date().toISOString().slice(0, 10) + ".xlsx");
    });
    
    $('#export-pdf').click(function(e) {
        e.preventDefault();
        alert('PDF导出功能暂未开放，请使用Excel导出。');
    });
});
</script> 