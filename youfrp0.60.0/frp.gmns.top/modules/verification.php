<?php
namespace chhcn;
use chhcn;

$group_title = "账号管理";
$page_title = "实名认证";

// verification.php
if (!defined('ROOT')) {
    die('Insufficient Permissions');
}
$um = new UserManager();

// 检查用户是否已经实名认证
$userData = Database::querySingleLine("users", ['id' => $_SESSION['uid']]);
$isVerified = !empty($userData['realname']);
?>
<style type="text/css">
.verify-container {
    max-width: 800px;
    margin: 0 auto;
}
.card-outline {
    border-top: 3px solid #007bff;
}
.verified-icon {
    font-size: 48px;
    color: #28a745;
}
.verify-form label {
    font-weight: 600;
}
.help-card {
    border-left: 4px solid #17a2b8;
}
.help-card .card-header {
    background-color: rgba(23, 162, 184, 0.1);
}
.advert-card {
    border-left: 4px solid #ffc107;
}
.advert-card .card-header {
    background-color: rgba(255, 193, 7, 0.1);
}
</style>

<div class="content-header" oncopy="return false" oncut="return false;" onselectstart="return false" oncontextmenu="return false">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark"><?php echo $page_title; ?>&nbsp;&nbsp;<small class="text-muted text-xs">完成实名认证以使用更多功能</small></h1>
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

<section class="content" oncopy="return false" oncut="return false;" onselectstart="return false" oncontextmenu="return false">
    <div class="container-fluid">
        <div class="row">
            <!-- Main content -->
            <div class="col-md-8">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-id-card"></i> 实名认证</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($isVerified): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-check-circle verified-icon mb-3"></i>
                                <div class="alert alert-success">
                                    <h4><i class="icon fas fa-check"></i> 您已完成实名认证！</h4>
                                    <p class="mb-0">您好，<?php echo htmlspecialchars($userData['realname']); ?>，您已经完成了实名认证，无需重复操作。</p>
                                </div>
                                <a href="?page=panel&module=home" class="btn btn-primary mt-3">
                                    <i class="fas fa-home"></i> 返回首页
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> 请填写真实有效的身份信息，我们将对您的信息进行严格保密。
                            </div>
                            <form id="verify-form" method="post" action="?action=verify_id" class="verify-form mt-4">
                                <div class="form-group row">
                                    <label for="realname" class="col-sm-3 col-form-label">真实姓名</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            </div>
                                            <input type="text" class="form-control" id="realname" name="realname" placeholder="请输入您的真实姓名" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="idcard" class="col-sm-3 col-form-label">证件号码</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                            </div>
                                            <input type="text" class="form-control" id="idcard" name="idcard" placeholder="请输入您的证件号码" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="id_type" class="col-sm-3 col-form-label">证件类型</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-passport"></i></span>
                                            </div>
                                            <input type="text" class="form-control" id="id_type" name="id_type" value="中国大陆居民身份证" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="offset-sm-3 col-sm-9">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="agree" name="agree" required>
                                            <label class="form-check-label" for="agree">我已阅读并同意 <a href="#" data-toggle="modal" data-target="#privacyModal">《隐私政策》</a></label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="offset-sm-3 col-sm-9">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 提交验证</button>
                                    </div>
                                </div>
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['token']; ?>">
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-exclamation-circle"></i> 实名须知</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <p><i class="fas fa-exclamation-triangle"></i> 未完成实名认证的用户将无法体验完整服务，本服务未满18周岁的未成年用户请经监护人允许后进行认证。</p>
                        </div>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> 我们严格遵守相关法律法规，保护您的个人信息安全</li>
                            <li><i class="fas fa-check text-success"></i> 实名认证信息仅用于满足监管要求，不会用于其他用途</li>
                            <li><i class="fas fa-check text-success"></i> 认证完成后可享受所有功能及权益</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <div class="card advert-card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-ad"></i> 广告</h3>
                    </div>
                    <div class="card-body">
                        <div class="text-center py-4">
                            <i class="fas fa-bullhorn fa-3x mb-3 text-warning"></i>
                            <p>广告位招租，有意请联系负责人或管理员</p>
                        </div>
                    </div>
                </div>

                <div class="card help-card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-question-circle"></i> 帮助文档</h3>
                    </div>
                    <div class="card-body">
                        <div class="callout callout-info">
                            <h5><i class="fas fa-info-circle"></i> 常见问题</h5>
                            <p>如平台已成功向您发送一份验证码，但并未成功接收，您可以尝试关闭手机和运营商的骚扰拦截系统并等待几分钟后重试</p>
                        </div>
                        <ul class="list-group list-group-flush mt-3">
                            <li class="list-group-item"><i class="fas fa-globe-asia text-primary"></i> 当前仅支持中国大陆（+86）区号获取验证代码，完成实名认证</li>
                            <li class="list-group-item"><i class="fas fa-headset text-primary"></i> 满足条件的，无法通过平台完成实名认证则可联系工作人员处理</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Privacy Modal -->
<div class="modal fade" id="privacyModal" tabindex="-1" role="dialog" aria-labelledby="privacyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="privacyModalLabel">隐私政策</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h5>信息收集</h5>
                <p>我们收集您的实名信息仅用于满足相关法律法规要求，确保服务合规性。</p>
                
                <h5>信息使用</h5>
                <p>您提供的实名信息将严格保密，仅在必要时用于身份验证，不会用于商业用途或与第三方共享。</p>
                
                <h5>信息保护</h5>
                <p>我们采用行业标准的安全措施保护您的个人信息，防止未经授权的访问、使用或泄露。</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const form = document.getElementById('verify-form');
if (form) {
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const realnameInput = document.getElementById('realname');
        const idcardInput = document.getElementById('idcard');
        const agreeCheckbox = document.getElementById('agree');
        const submitButton = form.querySelector('button[type="submit"]');

        if (!realnameInput.value || !idcardInput.value) {
            Swal.fire({
                title: '错误',
                text: '请填写所有字段',
                icon: 'error',
                confirmButtonText: '确定'
            });
            return;
        }

        if (!agreeCheckbox.checked) {
            Swal.fire({
                title: '错误',
                text: '请先阅读并同意《隐私政策》',
                icon: 'error',
                confirmButtonText: '确定'
            });
            return;
        }

        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> 正在验证...';

        const formData = new FormData(form);

        fetch(form.action, {
            method: form.method,
            body: new URLSearchParams(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.code === 200) {
                Swal.fire({
                    title: '成功!',
                    text: data.msg,
                    icon: 'success',
                    confirmButtonText: '好的'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.reload();
                    }
                });
            } else {
                Swal.fire({
                    title: '验证失败',
                    text: data.msg,
                    icon: 'error',
                    confirmButtonText: '确定'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                title: '错误',
                text: '请求失败，请检查网络或联系管理员',
                icon: 'error',
                confirmButtonText: '确定'
            });
            console.error('Fetch Error:', error);
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-check"></i> 提交验证';
        });
    });
}
</script> 