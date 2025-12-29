<?php
namespace chhcn;

use chhcn;

//$page_title = "站点设置";
$um = new chhcn\UserManager();
$nm = new chhcn\NodeManager();
$pm = new chhcn\ProxyManager();
$rs = Database::querySingleLine("users", Array("username" => $_SESSION['user']));

if(!$rs || $rs['group'] !== "admin") {
	exit("<script>location='?page=panel';</script>");
}

$broadcast = chhcn\Settings::get("broadcast");
$helpinfo  = chhcn\Settings::get("helpinfo");
?>
<style type="text/css">
.fix-text p {
	margin-bottom: 4px;
}
.infotable th {
	width: 30%;
	color: #555;
	font-weight: 600;
}
.editor-container {
    position: relative;
    border-radius: 4px;
    border: 1px solid #ddd;
    overflow: hidden;
    margin-bottom: 20px;
}
.editor-header {
    display: flex;
    align-items: center;
    background: #f5f5f5;
    padding: 8px 15px;
    border-bottom: 1px solid #ddd;
}
.editor-title {
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 0;
}
.editor-actions {
    margin-left: auto;
}
.editor-actions button {
    border: none;
    background: none;
    font-size: 14px;
    padding: 4px 8px;
    color: #666;
    cursor: pointer;
    transition: all 0.2s;
}
.editor-actions button:hover {
    color: #007bff;
}
.editor-content {
    padding: 15px;
}
#broadcast, #helpinfo {
    width: 100%;
    min-height: 200px;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', 'Consolas', 'source-code-pro', monospace;
    line-height: 1.5;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    resize: vertical;
}
.stats-card {
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.3s ease;
    margin-bottom: 20px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
}
.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0,0,0,0.1);
}
.stats-icon {
    position: absolute;
    right: 20px;
    top: 15px;
    font-size: 28px;
    opacity: 0.15;
}
.stats-header {
    padding: 15px 20px 5px;
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #6c757d;
}
.stats-value {
    padding: 0 20px 15px;
    font-size: 28px;
    font-weight: 700;
}
.preview-md {
    background-color: #f8f9fa;
    border-radius: 4px;
    padding: 20px;
    border-left: 4px solid #007bff;
}
.icon-box {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    font-size: 24px;
    color: white;
}
.bg-gradient-blue {
    background: linear-gradient(135deg, #0061f2 0%, #6900ff 100%);
}
.bg-gradient-green {
    background: linear-gradient(135deg, #00b09b 0%, #96c93d 100%);
}
.bg-gradient-orange {
    background: linear-gradient(135deg, #ff9966 0%, #ff5e62 100%);
}
.info-card {
    transition: all 0.3s ease;
}
.info-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0,0,0,0.1);
}
</style>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-cogs mr-2"></i>站点设置</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="?page=panel"><i class="fas fa-home"></i> 首页</a></li>
                    <li class="breadcrumb-item active">站点设置</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <!-- 系统统计 -->
        <div class="row">
            <div class="col-md-4">
                <div class="stats-card bg-white">
                    <div class="stats-icon text-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stats-header">用户数量</div>
                    <div class="stats-value text-primary"><?php echo $um->getTotalUsers(); ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card bg-white">
                    <div class="stats-icon text-success">
                        <i class="fas fa-server"></i>
                    </div>
                    <div class="stats-header">节点数量</div>
                    <div class="stats-value text-success"><?php echo $nm->getTotalNodes(); ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card bg-white">
                    <div class="stats-icon text-info">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <div class="stats-header">隧道数量</div>
                    <div class="stats-value text-info"><?php echo $pm->getTotalProxies(); ?></div>
                </div>
            </div>
        </div>
    
        <div class="row">
            <div class="col-lg-8">
                <!-- 公告编辑 -->
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-bullhorn mr-2"></i>编辑公告</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i> 在此处填写公告内容，支持 Markdown 语法，将显示在用户控制面板首页
                        </div>
                        
                        <div class="editor-container">
                            <div class="editor-header">
                                <div class="editor-title"><i class="fas fa-edit mr-1"></i> 编辑器</div>
                                <div class="editor-actions">
                                    <button onclick="insertMarkdown('broadcast', '**粗体**')"><i class="fas fa-bold"></i></button>
                                    <button onclick="insertMarkdown('broadcast', '*斜体*')"><i class="fas fa-italic"></i></button>
                                    <button onclick="insertMarkdown('broadcast', '# 标题')"><i class="fas fa-heading"></i></button>
                                    <button onclick="insertMarkdown('broadcast', '[链接](http://example.com)')"><i class="fas fa-link"></i></button>
                                    <button onclick="insertMarkdown('broadcast', '- 列表项\n- 列表项')"><i class="fas fa-list-ul"></i></button>
                                </div>
                            </div>
                            <div class="editor-content">
                                <textarea class="form-control" id="broadcast"><?php echo $broadcast; ?></textarea>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-outline-info mr-2" onclick="preview(broadcast.value)">
                                <i class="fas fa-eye mr-1"></i> 预览效果
                            </button>
                            <button type="button" class="btn btn-primary" onclick="saveBroadcast()">
                                <i class="fas fa-save mr-1"></i> 保存修改
                            </button>
                        </div>
					</div>
                </div>
                
                <!-- 帮助编辑 -->
				<div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-question-circle mr-2"></i>编辑帮助</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i> 在此处填写帮助内容，让用户更好的了解如何使用，支持 Markdown 语法
                        </div>
                        
                        <div class="editor-container">
                            <div class="editor-header">
                                <div class="editor-title"><i class="fas fa-edit mr-1"></i> 编辑器</div>
                                <div class="editor-actions">
                                    <button onclick="insertMarkdown('helpinfo', '**粗体**')"><i class="fas fa-bold"></i></button>
                                    <button onclick="insertMarkdown('helpinfo', '*斜体*')"><i class="fas fa-italic"></i></button>
                                    <button onclick="insertMarkdown('helpinfo', '# 标题')"><i class="fas fa-heading"></i></button>
                                    <button onclick="insertMarkdown('helpinfo', '[链接](http://example.com)')"><i class="fas fa-link"></i></button>
                                    <button onclick="insertMarkdown('helpinfo', '- 列表项\n- 列表项')"><i class="fas fa-list-ul"></i></button>
                                </div>
                            </div>
                            <div class="editor-content">
                                <textarea class="form-control" id="helpinfo"><?php echo $helpinfo; ?></textarea>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end mt-3">
                            <button type="button" class="btn btn-outline-info mr-2" onclick="preview(helpinfo.value)">
                                <i class="fas fa-eye mr-1"></i> 预览效果
                            </button>
                            <button type="button" class="btn btn-success" onclick="saveHelpInfo()">
                                <i class="fas fa-save mr-1"></i> 保存修改
                            </button>
                        </div>
                    </div>
                </div>
			</div>
			
			<div class="col-lg-4">
			    <!-- 站点信息 -->
				<div class="card card-info card-outline info-card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>站点信息</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
						<table class="table table-striped table-valign-middle infotable mb-0">
							<tr>
								<th><i class="fas fa-code-branch text-primary mr-2"></i>版本</th>
								<td>
								    <span class="badge badge-success"><?php echo chhcn\Utils::PANEL_VERSION; ?></span>
								    <span class="text-muted">最新版本</span>
								</td>
							</tr>
							<tr>
								<th><i class="fas fa-server text-primary mr-2"></i>服务程序</th>
								<td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
							</tr>
							<tr>
								<th><i class="fas fa-cog text-primary mr-2"></i>运行模式</th>
								<td><?php echo php_sapi_name(); ?></td>
							</tr>
							<tr>
								<th><i class="fas fa-comment text-primary mr-2"></i>原作者说</th>
								<td>不违法可以商业 不限制二开</td>
							</tr>
							<tr>
								<th><i class="fas fa-code text-primary mr-2"></i>重构整理</th>
								<td>由凡客用ai继续二次开发出来的。</td>
							</tr>
						</table>
                    </div>
                </div>
                
                <!-- 系统日志 -->
                <div class="card card-warning card-outline info-card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-tasks mr-2"></i>快速操作</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <a href="?page=panel&module=userlist" class="btn btn-light btn-block mb-3">
                                    <i class="fas fa-users text-primary mr-2"></i> 用户管理
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="?page=panel&module=tunnelmanage" class="btn btn-light btn-block mb-3">
                                    <i class="fas fa-project-diagram text-info mr-2"></i> 隧道管理
                                </a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <a href="?page=panel&module=nodes" class="btn btn-light btn-block mb-3">
                                    <i class="fas fa-server text-success mr-2"></i> 节点管理
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="?page=panel&module=traffic" class="btn btn-light btn-block mb-3">
                                    <i class="fas fa-chart-line text-warning mr-2"></i> 流量统计
                                </a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <a href="?page=panel&module=packages" class="btn btn-light btn-block">
                                    <i class="fas fa-box text-danger mr-2"></i> 套餐管理
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="?page=panel&module=redeemcodes" class="btn btn-light btn-block">
                                    <i class="fas fa-ticket-alt text-purple mr-2"></i> 兑换码
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
		</div>
	</div>
</div>

<!-- 预览模态框 -->
<div class="modal fade" id="modal-default">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="msg-title"></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="msg-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                <button type="button" class="btn btn-primary" data-dismiss="modal">确定</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
var csrf_token = "<?php echo $_SESSION['token']; ?>";

$(document).ready(function() {
    // 页面加载动画
    $('.stats-card').css('opacity', 0);
    $('.info-card').css('opacity', 0);
    $('.card').css('opacity', 0);
    
    setTimeout(function() {
        $('.stats-card').each(function(i) {
            $(this).delay(i * 100).animate({
                opacity: 1
            }, 500);
        });
        
        setTimeout(function() {
            $('.info-card').each(function(i) {
                $(this).delay(i * 100).animate({
                    opacity: 1
                }, 500);
            });
            
            setTimeout(function() {
                $('.card:not(.stats-card):not(.info-card)').each(function(i) {
                    $(this).delay(i * 150).animate({
                        opacity: 1
                    }, 500);
                });
            }, 200);
        }, 200);
    }, 100);
});

function alertMessage(title, body) {
    $("#msg-title").html(title);
    $("#msg-body").html('<div class="preview-md">' + body + '</div>');
    $("#modal-default").modal('show');
}

function insertMarkdown(editorId, text) {
    var textarea = document.getElementById(editorId);
    var start = textarea.selectionStart;
    var end = textarea.selectionEnd;
    var selectedText = textarea.value.substring(start, end);
    
    // 根据是否有选中文本进行不同处理
    if (selectedText) {
        // 如果有选中文本，则根据不同的格式进行处理
        if (text === '**粗体**') {
            var replacement = '**' + selectedText + '**';
        } else if (text === '*斜体*') {
            var replacement = '*' + selectedText + '*';
        } else if (text === '# 标题') {
            var replacement = '# ' + selectedText;
        } else if (text === '[链接](http://example.com)') {
            var replacement = '[' + selectedText + '](http://example.com)';
        } else {
            var replacement = text;
        }
        
        textarea.value = textarea.value.substring(0, start) + replacement + textarea.value.substring(end);
        textarea.selectionStart = start;
        textarea.selectionEnd = start + replacement.length;
    } else {
        // 如果没有选中文本，则直接插入
        textarea.value = textarea.value.substring(0, start) + text + textarea.value.substring(end);
        textarea.selectionStart = start + text.length;
        textarea.selectionEnd = start + text.length;
    }
    
    textarea.focus();
}

function saveBroadcast() {
    Swal.fire({
        title: '保存中...',
        text: '正在保存公告内容',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading()
        }
    });
    
    $.ajax({
        type: 'POST',
        url: "?action=updatebroadcast&page=panel&module=settings&csrf=" + csrf_token,
        data: {
            data: $("#broadcast").val()
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: '保存失败',
                text: xhr.responseText || '发生未知错误'
            });
        },
        success: function(response) {
            Swal.fire({
                icon: 'success',
                title: '保存成功',
                text: response || '公告内容已成功保存'
            });
        }
    });
}

function saveHelpInfo() {
    Swal.fire({
        title: '保存中...',
        text: '正在保存帮助内容',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading()
        }
    });
    
    $.ajax({
        type: 'POST',
        url: "?action=updatehelpinfo&page=panel&module=settings&csrf=" + csrf_token,
        data: {
            data: $("#helpinfo").val()
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: '保存失败',
                text: xhr.responseText || '发生未知错误'
            });
        },
        success: function(response) {
            Swal.fire({
                icon: 'success',
                title: '保存成功',
                text: response || '帮助内容已成功保存'
            });
        }
    });
}

function preview(data) {
    $("#modal-default").modal('show');
    $("#msg-title").html('<i class="fas fa-eye mr-2"></i>预览效果');
    $("#msg-body").html('<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">正在加载预览...</p></div>');
    
    $.ajax({
        type: 'POST',
        url: "?action=preview&page=panel&module=settings&csrf=" + csrf_token,
        data: {
            data: data
        },
        error: function(xhr) {
            $("#msg-body").html('<div class="alert alert-danger"><i class="fas fa-exclamation-circle mr-2"></i>' + (xhr.responseText || '发生未知错误') + '</div>');
        },
        success: function(response) {
            $("#msg-body").html('<div class="preview-md">' + response + '</div>');
        }
    });
}
</script>