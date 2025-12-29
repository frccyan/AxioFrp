<?php
namespace chhcn;

use chhcn;

//$page_title = "用户组管理";
$gm = new chhcn\GroupManager();
$rs = Database::querySingleLine("users", Array("username" => $_SESSION['user']));

if(!$rs || $rs['group'] !== "admin") {
	exit("<script>location='?page=panel';</script>");
}

if(isset($_GET['getinfo']) && preg_match("/^[0-9]{1,10}$/", $_GET['getinfo'])) {
	chhcn\Utils::checkCsrf();
	$rs = Database::querySingleLine("groups", Array("id" => $_GET['getinfo']));
	if($rs) {
		ob_clean();
		exit(json_encode($rs));
	} else {
		ob_clean();
		Header("HTTP/1.1 403");
		exit("未找到用户组");
	}
}
?>
<style type="text/css">
:root {
    --primary-color: #3498db;
    --secondary-color: #2c3e50;
    --success-color: #2ecc71;
    --danger-color: #e74c3c;
    --warning-color: #f39c12;
    --info-color: #1abc9c;
    --light-color: #ecf0f1;
    --dark-color: #34495e;
    --gray-color: #95a5a6;
    --white-color: #ffffff;
    --border-color: #dee2e6;
    --shadow-color: rgba(0, 0, 0, 0.1);
}

.group-table {
    box-shadow: 0 2px 5px var(--shadow-color);
    border-radius: 8px;
    overflow: hidden;
}

.group-table th {
    background-color: #f8f9fa;
    border-bottom: 2px solid var(--primary-color);
    font-weight: 500;
    text-transform: uppercase;
    font-size: 0.85rem;
    padding: 12px 15px;
}

.group-table td {
    padding: 12px 15px;
    vertical-align: middle;
}

.group-table tr:hover {
    background-color: #f8f9fa;
}

.group-table .text-center {
    text-align: center;
}

.group-actions a {
    display: inline-block;
    text-decoration: none;
    margin: 0 5px;
    padding: 4px 8px;
    border-radius: 4px;
    transition: all 0.2s;
}

.group-actions a:hover {
    background-color: rgba(52, 152, 219, 0.1);
}

.edit-link {
    color: var(--info-color);
}

.delete-link {
    color: var(--danger-color);
}

.breadcrumb {
    display: flex;
    padding: 0;
    margin: 0 0 15px 0;
    list-style: none;
    background-color: transparent;
}

.breadcrumb-item {
    font-size: 0.9rem;
    color: #6c757d;
}

.breadcrumb-item a {
    color: var(--primary-color);
    text-decoration: none;
}

.breadcrumb-item a:hover {
    text-decoration: underline;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: "/";
    display: inline-block;
    padding: 0 8px;
    color: #6c757d;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border-color);
}

.page-title {
    font-size: 1.5rem;
    font-weight: 500;
    margin: 0;
    display: flex;
    align-items: center;
}

.page-title i {
    margin-right: 10px;
    color: var(--primary-color);
}

.form-control {
    display: block;
    width: 100%;
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid var(--border-color);
    border-radius: 0.25rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus {
    color: #495057;
    background-color: #fff;
    border-color: var(--primary-color);
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
}

.form-group {
    margin-bottom: 1rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--dark-color);
}

.form-help {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: var(--gray-color);
}

.card {
    box-shadow: 0 2px 8px var(--shadow-color);
    border: none;
    border-radius: 8px;
    background-color: #fff;
}

.card-header {
    background-color: #f8f9fa;
    padding: 15px 20px;
    border-bottom: 1px solid var(--border-color);
    font-weight: 500;
}

.card-title {
    margin: 0;
    font-size: 1.25rem;
    color: var(--dark-color);
}

.card-body {
    padding: 20px;
}

.card-footer {
    background-color: #f8f9fa;
    padding: 15px 20px;
    border-top: 1px solid var(--border-color);
}

.btn {
    display: inline-block;
    font-weight: 400;
    text-align: center;
    white-space: nowrap;
    vertical-align: middle;
    user-select: none;
    border: 1px solid transparent;
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
    border-radius: 0.25rem;
    transition: all 0.15s ease-in-out;
    cursor: pointer;
}

.btn-primary {
    color: #fff;
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.btn-primary:hover {
    background-color: #2980b9;
    border-color: #2980b9;
}

.btn-success {
    color: #fff;
    background-color: var(--success-color);
    border-color: var(--success-color);
}

.btn-success:hover {
    background-color: #27ae60;
    border-color: #27ae60;
}

.float-right {
    float: right;
}

/* 分隔线 */
.divider {
    width: 100%;
    height: 1px;
    background-color: var(--border-color);
    margin: 20px 0;
}

/* 表单部分标题 */
.form-section-title {
    position: relative;
    font-size: 1.1rem;
    font-weight: 500;
    color: var(--dark-color);
    margin: 1.5rem 0 1rem;
    padding-left: 15px;
}

.form-section-title:before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background-color: var(--primary-color);
    border-radius: 2px;
}

/* 加载动画 */
.loading {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(52, 152, 219, 0.3);
    border-radius: 50%;
    border-top-color: var(--primary-color);
    animation: spin 1s ease-in-out infinite;
    margin-right: 10px;
    vertical-align: middle;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* 提示框 */
.alert {
    position: relative;
    padding: 0.75rem 1.25rem;
    margin-bottom: 1rem;
    border: 1px solid transparent;
    border-radius: 0.25rem;
}

.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}

.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

.alert-warning {
    color: #856404;
    background-color: #fff3cd;
    border-color: #ffeeba;
}

/* 状态徽章 */
.badge {
    display: inline-block;
    padding: 0.25em 0.4em;
    font-size: 75%;
    font-weight: 700;
    line-height: 1;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: 0.25rem;
}

.badge-primary {
    color: #fff;
    background-color: var(--primary-color);
}

.badge-success {
    color: #fff;
    background-color: var(--success-color);
}

.badge-danger {
    color: #fff;
    background-color: var(--danger-color);
}

.badge-warning {
    color: #212529;
    background-color: var(--warning-color);
}

.badge-info {
    color: #fff;
    background-color: var(--info-color);
}
</style>

<div class="content">
    <div class="container-fluid">
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="?page=panel">控制面板</a></li>
            <li class="breadcrumb-item">系统管理</li>
            <li class="breadcrumb-item">用户组管理</li>
        </ul>

        <div class="page-header">
            <h2 class="page-title"><i class="fas fa-users-cog"></i> 用户组管理</h2>
        </div>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title"><i class="fas fa-list"></i> 用户组列表</h3>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-striped table-valign-middle group-table">
                            <thead>
                                <tr>
                                    <th class='text-center' nowrap>ID</th>
                                    <th class='text-center' nowrap>组名称</th>
                                    <th class='text-center' nowrap>显示名称</th>
                                    <th class='text-center' nowrap>流量(MB)</th>
                                    <th class='text-center' nowrap>隧道数量</th>
                                    <th class='text-center' nowrap>入站带宽(KB)</th>
                                    <th class='text-center' nowrap>出站带宽(KB)</th>
                                    <th class='text-center' nowrap>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $rs = Database::toArray(Database::query("groups", "SELECT * FROM `groups`", true));
                                $i = 0;
                                foreach($rs as $group) {
                                    $i++;
                                    echo "<tr>
                                    <td class='text-center' nowrap>{$group[0]}</td>
                                    <td class='text-center' nowrap><span class='badge badge-primary'>{$group[1]}</span></td>
                                    <td class='text-center' nowrap>{$group[2]}</td>
                                    <td class='text-center' nowrap><span class='badge badge-info'>{$group[3]}</span></td>
                                    <td class='text-center' nowrap><span class='badge badge-success'>{$group[4]}</span></td>
                                    <td class='text-center' nowrap>{$group[5]}</td>
                                    <td class='text-center' nowrap>{$group[6]}</td>
                                    <td class='text-center group-actions' nowrap>
                                        <a href='javascript:edit({$group[0]})' class='edit-link'><i class='fas fa-edit'></i> 编辑</a>
                                        <a href='javascript:deletegroup({$group[0]})' class='delete-link'><i class='fas fa-trash'></i> 删除</a>
                                    </td>
                                    ";
                                }
                                ?>
                            </tbody>
                        </table>
                        <?php
                        if($i == 0) {
                            echo "<div class='alert alert-warning'><i class='fas fa-exclamation-triangle'></i> 没有找到符合条件的结果</div>";
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title"><i class="fas fa-edit"></i> <span id="form-title">添加或修改用户组信息</span></h3>
                    </div>
                    <div class="card-body">
                        <p id="statusmsg" class="text-muted"><i class="fas fa-info-circle"></i> 点击左侧用户组列表的编辑按钮来编辑用户组信息</p>
                        
                        <div class="form-section-title">用户组基本设置</div>
                        
                        <div class="form-group">
                            <label class="form-label" for="group_name">组名称</label>
                            <input type="text" class="form-control" id="group_name">
                            <small class="form-help">用于系统标识的唯一名称（如：vip1, default）</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="group_friendly_name">显示名称</label>
                            <input type="text" class="form-control" id="group_friendly_name">
                            <small class="form-help">用户可见的友好名称（如：钻石VIP）</small>
                        </div>
                        
                        <div class="form-section-title">资源限制配置</div>
                        
                        <div class="form-group">
                            <label class="form-label" for="group_traffic">流量限额(MB)</label>
                            <input type="number" class="form-control" id="group_traffic">
                            <small class="form-help">该组用户的流量限制</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="group_proxies">隧道数量</label>
                            <input type="number" class="form-control" id="group_proxies">
                            <small class="form-help">该组用户最多可创建的隧道数量</small>
                        </div>
                        
                        <div class="form-section-title">带宽限制配置</div>
                        
                        <div class="form-group">
                            <label class="form-label" for="group_inbound">入站带宽(KB)</label>
                            <input type="number" class="form-control" id="group_inbound">
                            <small class="form-help">该组用户的入站带宽限制</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="group_outbound">出站带宽(KB)</label>
                            <input type="number" class="form-control" id="group_outbound">
                            <small class="form-help">该组用户的出站带宽限制</small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary float-right" onclick="save()"><i class="fas fa-save"></i> 保存设置</button>
                        <button class="btn btn-success" onclick="resetForm()"><i class="fas fa-plus"></i> 新建用户组</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 添加Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<script type="text/javascript">
var csrf_token = "<?php echo $_SESSION['token']; ?>";
var groupid = undefined;

function resetForm() {
    groupid = undefined;
    $("#form-title").text("添加新用户组");
    $("#group_name").val("");
    $("#group_friendly_name").val("");
    $("#group_traffic").val("");
    $("#group_proxies").val("");
    $("#group_inbound").val("");
    $("#group_outbound").val("");
    $("#statusmsg").html("<i class='fas fa-plus-circle'></i> 正在创建新的用户组");
}

function edit(id) {
    $("#statusmsg").html("<span class='loading'></span> 正在加载用户组信息...");
    
    var htmlobj = $.ajax({
        type: 'GET',
        url: "?page=panel&module=groupmanage&getinfo=" + id + "&csrf=" + csrf_token,
        async: true,
        error: function() {
            $("#statusmsg").html("<div class='alert alert-danger'><i class='fas fa-exclamation-circle'></i> 错误：" + htmlobj.responseText + "</div>");
            return;
        },
        success: function() {
            try {
                var json = JSON.parse(htmlobj.responseText);
                groupid = id;
                $("#group_name").val(json.name);
                $("#group_friendly_name").val(json.friendly_name);
                $("#group_traffic").val(json.traffic);
                $("#group_proxies").val(json.proxies);
                $("#group_inbound").val(json.inbound);
                $("#group_outbound").val(json.outbound);
                $("#form-title").text("编辑用户组信息");
                $("#statusmsg").html("<div class='alert alert-success'><i class='fas fa-check-circle'></i> 正在编辑用户组 <strong>" + json.friendly_name + "</strong> 的设置</div>");
            } catch(e) {
                $("#statusmsg").html("<div class='alert alert-danger'><i class='fas fa-exclamation-circle'></i> 错误：无法解析服务器返回的数据</div>");
            }
            return;
        }
    });
}

function deletegroup(id) {
    if(!confirm("您确定要删除这个用户组吗？此操作不可恢复！")) {
        return;
    }
    
    var htmlobj = $.ajax({
        type: 'POST',
        url: "?action=deletegroup&page=panel&module=groupmanage&csrf=" + csrf_token,
        async:true,
        data: {
            id: id
        },
        beforeSend: function() {
            $("#statusmsg").html("<span class='loading'></span> 正在删除用户组...");
        },
        error: function() {
            $("#statusmsg").html("<div class='alert alert-danger'><i class='fas fa-exclamation-circle'></i> 错误：" + htmlobj.responseText + "</div>");
            return;
        },
        success: function() {
            alert(htmlobj.responseText);
            window.location.reload();
            return;
        }
    });
}

function save() {
    var url = "?action=updategroup&page=panel&module=groupmanage";
    var actionText = "更新";
    
    if(groupid == undefined) {
        groupid = null;
        url = "?action=addgroup&page=panel&module=groupmanage";
        actionText = "添加";
    }
    
    // 验证表单
    if($("#group_name").val() == "") {
        alert("请填写用户组名称");
        $("#group_name").focus();
        return;
    }
    
    if($("#group_friendly_name").val() == "") {
        alert("请填写用户组显示名称");
        $("#group_friendly_name").focus();
        return;
    }
    
    $("#statusmsg").html("<span class='loading'></span> 正在" + actionText + "用户组...");
    
    var htmlobj = $.ajax({
        type: 'POST',
        url: url + "&csrf=" + csrf_token,
        async:true,
        data: {
            id: groupid,
            name: $("#group_name").val(),
            friendly_name: $("#group_friendly_name").val(),
            traffic: $("#group_traffic").val(),
            proxies: $("#group_proxies").val(),
            inbound: $("#group_inbound").val(),
            outbound: $("#group_outbound").val()
        },
        error: function() {
            $("#statusmsg").html("<div class='alert alert-danger'><i class='fas fa-exclamation-circle'></i> 错误：" + htmlobj.responseText + "</div>");
            return;
        },
        success: function() {
            alert(htmlobj.responseText);
            window.location.reload();
            return;
        }
    });
}
</script> 