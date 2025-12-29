<?php
global $_config;
?>
<!DOCTYPE HTML>
<html lang="zh_CN">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=11">
		<meta name="msapplication-TileColor" content="#F1F1F1">
		<link href="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/5.2.3/css/bootstrap.min.css" rel="stylesheet">
		<link href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
		<script src="https://cdn.bootcdn.net/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
		<script src="https://cdn.bootcdn.net/ajax/libs/popper.js/2.11.7/cjs/popper.min.js"></script>
		<script src="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/5.2.3/js/bootstrap.min.js"></script>
		<?php if($_config['recaptcha']['enable']) echo '<script src="https://www.recaptcha.net/recaptcha/api.js?render=' . $_config['recaptcha']['sitekey'] . '" defer></script>'; ?>
		<title>登录 :: <?php echo $_config['sitename']; ?> - <?php echo $_config['description']; ?></title>
		<style type="text/css">
			:root {
				--primary-color: #4e73df;
				--primary-hover: #2e59d9;
				--secondary-color: #36b9cc;
				--success-color: #1cc88a;
				--text-color: #5a5c69;
				--light-bg: #f8f9fc;
				--dark-bg: #4e73df;
				--border-radius: 10px;
				--box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
				--transition: all 0.3s ease;
			}
			
			body {
				background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
				min-height: 100vh;
				font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
				color: var(--text-color);
				display: flex;
				align-items: center;
				justify-content: center;
				margin: 0;
				padding: 0;
			}
			
			.auth-container {
				width: 100%;
				max-width: 1200px;
				display: flex;
				justify-content: center;
				align-items: center;
				min-height: 100vh;
				padding: 2rem;
				position: relative;
				z-index: 1;
			}
			
			.auth-card {
				width: 100%;
				max-width: 480px;
				background: rgba(255, 255, 255, 0.95);
				backdrop-filter: blur(10px);
				border-radius: var(--border-radius);
				box-shadow: var(--box-shadow);
				overflow: hidden;
				transition: var(--transition);
				animation: fadeIn 0.6s ease-out;
			}
			
			@keyframes fadeIn {
				from { opacity: 0; transform: translateY(20px); }
				to { opacity: 1; transform: translateY(0); }
			}
			
			.auth-card:hover {
				box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
				transform: translateY(-5px);
			}
			
			.auth-header {
				padding: 2.5rem 2rem 2rem;
				background: var(--dark-bg);
				background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
				color: white;
				text-align: center;
				position: relative;
				overflow: hidden;
			}
			
			.auth-header:before {
				content: '';
				position: absolute;
				top: -50%;
				left: -50%;
				width: 200%;
				height: 200%;
				background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
				opacity: 0.6;
			}
			
			.auth-header h2 {
				font-weight: 700;
				margin-bottom: 0.5rem;
				font-size: 1.75rem;
				position: relative;
				text-shadow: 0 2px 4px rgba(0,0,0,0.1);
			}
			
			.auth-header p {
				opacity: 0.9;
				margin-bottom: 0;
				font-size: 1rem;
				position: relative;
			}
			
			.auth-body {
				padding: 2.5rem 2rem;
			}
			
			.form-label {
				font-weight: 500;
				color: #495057;
				margin-bottom: 0.5rem;
			}
			
			.form-control {
				padding: 0.8rem 1rem;
				border-radius: var(--border-radius);
				border: 1px solid #d1d3e2;
				transition: var(--transition);
				font-size: 0.95rem;
				box-shadow: 0 2px 5px rgba(0,0,0,0.02);
			}
			
			.form-control:focus {
				border-color: var(--primary-color);
				box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
			}
			
			.input-group {
				box-shadow: 0 2px 5px rgba(0,0,0,0.02);
				border-radius: var(--border-radius);
				overflow: hidden;
			}
			
			.input-group-text {
				background-color: var(--light-bg);
				border: 1px solid #d1d3e2;
				color: #6e707e;
				padding-left: 1rem;
				padding-right: 1rem;
			}
			
			.btn-primary {
				background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
				border: none;
				padding: 0.8rem;
				border-radius: var(--border-radius);
				font-weight: 600;
				transition: var(--transition);
				box-shadow: 0 4px 10px rgba(46, 89, 217, 0.3);
				letter-spacing: 0.5px;
			}
			
			.btn-primary:hover {
				background: linear-gradient(135deg, var(--primary-hover) 0%, #1a46c5 100%);
				transform: translateY(-2px);
				box-shadow: 0 6px 15px rgba(46, 89, 217, 0.4);
			}
			
			.btn-primary:active {
				transform: translateY(0);
				box-shadow: 0 2px 5px rgba(46, 89, 217, 0.4);
			}
			
			.auth-footer {
				padding: 1.5rem 2rem;
				background-color: rgba(248, 249, 252, 0.7);
				text-align: center;
				border-top: 1px solid rgba(227, 230, 240, 0.7);
			}
			
			.auth-footer a {
				color: var(--primary-color);
				text-decoration: none;
				font-weight: 500;
				transition: var(--transition);
			}
			
			.auth-footer a:hover {
				color: var(--primary-hover);
				text-decoration: underline;
			}
			
			.copyright {
				position: fixed;
				bottom: 16px;
				left: 0;
				right: 0;
				color: rgba(255, 255, 255, 0.9);
				font-size: 14px;
				text-align: center;
				text-shadow: 0 1px 2px rgba(0,0,0,0.1);
			}
			
			.alert {
				border-radius: var(--border-radius);
				margin-bottom: 1.5rem;
				border: none;
				box-shadow: 0 3px 8px rgba(0,0,0,0.05);
			}
			
			.alert-success {
				background-color: rgba(28, 200, 138, 0.1);
				border-left: 4px solid var(--success-color);
				color: #168c62;
			}
			
			.alert-danger {
				background-color: rgba(231, 74, 59, 0.1);
				border-left: 4px solid #e74a3b;
				color: #be3326;
			}
			
			.divider {
				display: flex;
				align-items: center;
				margin: 1.5rem 0;
				color: #b7b9cc;
			}
			
			.divider::before, .divider::after {
				content: "";
				flex: 1;
				border-bottom: 1px solid #e3e6f0;
			}
			
			.divider::before {
				margin-right: 1rem;
			}
			
			.divider::after {
				margin-left: 1rem;
			}
			
			/* 添加背景装饰 */
			.bg-bubbles {
				position: absolute;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
				z-index: -1;
				overflow: hidden;
			}
			
			.bg-bubbles li {
				position: absolute;
				list-style: none;
				display: block;
				width: 40px;
				height: 40px;
				background-color: rgba(255, 255, 255, 0.1);
				bottom: -160px;
				animation: square 25s infinite;
				transition-timing-function: linear;
				border-radius: 50%;
			}
			
			.bg-bubbles li:nth-child(1) {
				left: 10%;
				width: 80px;
				height: 80px;
				animation-delay: 0s;
			}
			
			.bg-bubbles li:nth-child(2) {
				left: 20%;
				width: 60px;
				height: 60px;
				animation-delay: 2s;
				animation-duration: 17s;
			}
			
			.bg-bubbles li:nth-child(3) {
				left: 25%;
				animation-delay: 4s;
			}
			
			.bg-bubbles li:nth-child(4) {
				left: 40%;
				width: 120px;
				height: 120px;
				animation-duration: 22s;
			}
			
			.bg-bubbles li:nth-child(5) {
				left: 70%;
				width: 90px;
				height: 90px;
			}
			
			.bg-bubbles li:nth-child(6) {
				left: 80%;
				width: 50px;
				height: 50px;
				animation-delay: 3s;
			}
			
			.bg-bubbles li:nth-child(7) {
				left: 32%;
				width: 60px;
				height: 60px;
				animation-delay: 7s;
			}
			
			.bg-bubbles li:nth-child(8) {
				left: 55%;
				width: 20px;
				height: 20px;
				animation-delay: 15s;
				animation-duration: 40s;
			}
			
			.bg-bubbles li:nth-child(9) {
				left: 25%;
				width: 30px;
				height: 30px;
				animation-delay: 2s;
				animation-duration: 40s;
			}
			
			.bg-bubbles li:nth-child(10) {
				left: 85%;
				width: 70px;
				height: 70px;
				animation-delay: 11s;
			}
			
			@keyframes square {
				0% {
					transform: translateY(0) rotate(0deg);
					opacity: 0.8;
					border-radius: 50%;
				}
				100% {
					transform: translateY(-1000px) rotate(600deg);
					opacity: 0;
					border-radius: 50%;
				}
			}
			
			@media (max-width: 576px) {
				.auth-container {
					padding: 1rem;
				}
				
				.auth-card {
					border-radius: calc(var(--border-radius) - 2px);
				}
				
				.auth-header {
					padding: 2rem 1.5rem;
				}
				
				.auth-body {
					padding: 2rem 1.5rem;
				}
			}
		</style>
	</head>
	<body>
		<div class="auth-container">
			<div class="auth-card">
				<div class="auth-header">
					<h2><?php echo $_config['sitename']; ?></h2>
					<p><?php echo $_config['description']; ?></p>
				</div>
				
				<div class="auth-body">
					<?php
					if(isset($data['status']) && isset($data['message'])) {
						$alertType = $data['status'] ? "success" : "danger";
						echo '<div class="alert alert-' . $alertType . ' alert-dismissable fade show"><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' . $data['message'] . '</div>';
					}
					?>
					
					<form method="POST" action="?action=login&page=login">
						<input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response" />
						
						<div class="mb-3">
							<label for="username" class="form-label">账号</label>
							<div class="input-group">
								<span class="input-group-text"><i class="fas fa-user"></i></span>
								<input type="text" class="form-control" name="username" id="username" placeholder="请输入您的账号" required>
							</div>
						</div>
						
						<div class="mb-4">
							<label for="password" class="form-label">密码</label>
							<div class="input-group">
								<span class="input-group-text"><i class="fas fa-lock"></i></span>
								<input type="password" class="form-control" name="password" id="password" placeholder="请输入您的密码" required>
							</div>
						</div>
						
						<button type="submit" class="btn btn-primary w-100 mb-3">登 录</button>
					</form>
				</div>
				
				<div class="auth-footer">
					<?php
					if($_config['register']['enable']) {
						echo "<a href='?page=register'>注册新账号</a> &middot; ";
					}
					?>
					<a href='?page=findpass'>忘记密码？</a>
				</div>
			</div>
		</div>
		
		<ul class="bg-bubbles">
			<li></li>
			<li></li>
			<li></li>
			<li></li>
			<li></li>
			<li></li>
			<li></li>
			<li></li>
			<li></li>
			<li></li>
		</ul>
		
		<p class="copyright">&copy; <?php echo date("Y") . " {$_config['sitename']}"; ?></p>
		
		<?php
		if($_config['recaptcha']['enable']) {
			echo <<<EOF
		<script type="text/javascript">
			window.onload = function() {
				grecaptcha.ready(function() {
					grecaptcha.execute('{$_config['recaptcha']['sitekey']}', {action:'validate_captcha'}).then(function(token) {
						document.getElementById('g-recaptcha-response').value = token;
					});
				});
			}
		</script>
EOF;
		}
		?>
	</body>
</html>