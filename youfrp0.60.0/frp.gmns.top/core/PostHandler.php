<?php
namespace chhcn;

use chhcn;

class PostHandler {
	
	public function switcher($params)
	{
		global $_config;
		
		if(isset($params['action']) && preg_match("/^[A-Za-z0-9\_\-]{1,20}$/", $params['action'])) {
			switch($params['action']) {
				case "login":
					$um = new chhcn\UserManager();
					$pages = new chhcn\Pages();
					if($_config['recaptcha']['enable']) {
						if(!isset($_POST["g-recaptcha-response"]) || !Utils::reCAPTCHA($_POST["g-recaptcha-response"])) {
							$data = Array("status" => false, "message" => "reCAPTCHA 验证失败，请刷新重试");
							$pages->loadPage("login", $data);
							exit;
						}
					}
					$data = $um->doLogin($_POST);
					if(isset($data['status']) && $data['status'] === true) {
						$_SESSION['user'] = $data['username'];
						$_SESSION['mail'] = $data['email'];
						$_SESSION['token'] = md5(mt_rand(0, 999999) . time() . $data['username']);
						exit("<script>location='?page=panel';</script>");
					}
					$pages->loadPage("login", $data);
					break;
				case "register":
					$um = new chhcn\UserManager();
					$pages = new chhcn\Pages();
					if($_config['recaptcha']['enable']) {
						if(!isset($_POST["g-recaptcha-response"]) || !Utils::reCAPTCHA($_POST["g-recaptcha-response"])) {
							$data = Array("status" => false, "message" => "reCAPTCHA 验证失败，请刷新重试");
							$pages->loadPage("register", $data);
							exit;
						}
					}
					$data = $um->doRegister($_POST);
					$pages->loadPage("register", $data);
					break;
				case "verify_id":
					$um = new UserManager();
					if($um->isLogged()) {
						/*
						if(!isset($_POST['csrf_token']) || !isset($_SESSION['token']) || $_POST['csrf_token'] !== $_SESSION['token']) {
							die(json_encode(['code' => 403, 'msg' => 'Invalid CSRF Token']));
						}
						*/
						// unset($_SESSION['token']); // 令牌用一次就失效 (暂时注释掉以进行调试)
						$vm = new VerificationManager();
						$result = $vm->verify($_POST['realname'], $_POST['idcard']);
						header('Content-Type: application/json');
						die(json_encode($result));
					} else {
						header('Content-Type: application/json');
						die(json_encode(['code' => 403, 'msg' => '登录会话已超时，请重新登录']));
					}
					break;
				case "sendmail":
					$um = new chhcn\UserManager();
					if(!$_config['smtp']['enable']) {
						exit("本站未开启 SMTP 服务！");
					}
					if(isset($_SESSION['reg_wait'])) {
						if(time() - $_SESSION['reg_wait'] < 60) {
							exit("您的操作过于频繁，请稍后再试。");
						}
					}
					if(!isset($_POST['mail']) || $_POST['mail'] == "") {
						exit("请填写邮箱！");
					}
					if(!$um->checkEmail($_POST['mail'])) {
						exit("不正确的邮箱格式！");
					}
					$rand = mt_rand(100000, 999999);
					$_SESSION['reg_verifycode'] = $rand;
					$_SESSION['reg_wait'] = time();
					$_SESSION['reg_email'] = $_POST['mail'];
					
					$um->sendRegisterEmail($_POST['mail'], $rand);
					exit("系统已发送一封邮件至您的邮箱，请查收。");
					break;
				case "findpass":
					$um = new chhcn\UserManager();
					$pages = new chhcn\Pages();
					if($_config['recaptcha']['enable']) {
						if(!isset($_POST["g-recaptcha-response"]) || !Utils::reCAPTCHA($_POST["g-recaptcha-response"])) {
							$data = Array("status" => false, "message" => "reCAPTCHA 验证失败，请刷新重试");
							$pages->loadPage("findpass", $data);
							exit;
						}
					}
					$data = $um->doFindpass($_POST);
					$pages->loadPage("findpass", $data);
					break;
				case "addproxy":
					$um = new chhcn\UserManager();
					$pm = new chhcn\ProxyManager();
					if($um->isLogged()) {
						Utils::checkCsrf();
						$result = $pm->checkRules($_POST);
						if(is_array($result) && isset($result[0])) {
							if($result[0]) {
								if($pm->addProxy($_POST)) {
									exit("隧道创建成功");
								} else {
									exit("隧道创建失败，请联系管理员：" . Database::fetchError());
								}
							} else {
								$msg = $result[1] ?? "未知错误";
								exit(htmlspecialchars($msg));
							}
						}
					} else {
						exit("登录会话已超时，请重新登录");
					}
					break;
				case "updatepass":
					$um = new chhcn\UserManager();
					if($um->isLogged()) {
						chhcn\Utils::checkCsrf();
						if(!isset($_POST['oldpass']) || !isset($_POST['newpass']) || !isset($_POST['newpass1'])
							|| $_POST['oldpass'] == "" || $_POST['newpass'] == "" || $_POST['newpass1'] == "") {
							exit("<script>alert('不完整的信息，请重新填写');location='?page=panel&module=profile';</script>");
						}
						$us = $um->getInfoByUser($_SESSION['user']);
						if($um->checkPassword($_POST['oldpass'], $us['password'])) {
							if(strlen($_POST['newpass']) < 5) exit("<script>alert('新密码不能少于 5 个字符，请重新输入');location='?page=panel&module=profile';</script>");
							if($_POST['newpass'] !== $_POST['newpass1']) exit("<script>alert('两次输入的密码不一致');location='?page=panel&module=profile';</script>");
							$password = $um->generatePassword($_POST['newpass']);
							$token    = substr(md5(sha1(md5($_SESSION['user']) . md5($password) . time() . mt_rand(0, 9999999))), 0, 16);
							// 更新数据库
							Database::update("users", Array("password" => $password), Array("username" => $_SESSION['user']));
							Database::update("tokens", Array("token" => $token), Array("username" => $_SESSION['user']));
							unset($_SESSION['user']);
							unset($_SESSION['mail']);
							unset($_SESSION['token']);
							exit("<script>alert('密码修改成功，请重新登录。');location='?';</script>");
						} else {
							exit("<script>alert('旧密码错误，请检查');location='?page=panel&module=profile';</script>");
						}
					} else {
						exit("<script>alert('登录会话已超时，请重新登录');location='?';</script>");
					}
					break;
				case "updateuser":
					$um = new chhcn\UserManager();
					if($um->isLogged()) {
						chhcn\Utils::checkCsrf();
						$us = $um->getInfoByUser($_SESSION['user']);
						if($us['group'] == "admin") {
							$valid = chhcn\Regex::isValid($_POST, [
								'id'       => chhcn\Regex::TYPE_NUMBER,
								'traffic'  => chhcn\Regex::TYPE_NUMBER,
								'proxies'  => chhcn\Regex::TYPE_NUMBER,
								'group'    => chhcn\Regex::TYPE_LETTER,
								'status'   => chhcn\Regex::TYPE_NUMBER,
							]);
							if($valid === true) {
								$update = $um->updateUser($_POST['id'], [
									'traffic'  => $_POST['traffic'],
									'proxies'  => $_POST['proxies'],
									'inbound'  => $_POST['inbound'] ?? "",
									'outbound' => $_POST['outbound'] ?? "",
									'group'    => $_POST['group'],
									'status'   => $_POST['status'],
								]);
								if($update === true) {
									exit("用户资料更新成功！");
								} else {
									Header("HTTP/1.1 404 Not Found");
									exit("该用户不存在！{$update}");
								}
							} else {
								Header("HTTP/1.1 404 Not Found");
								exit("提交的数据不合法！{$valid}");
							}
						} else {
							exit("你没有足够的权限这么做");
						}
					} else {
						exit("登录会话已超时，请重新登录");
					}
					break;
				case "updatenode":
					$um = new chhcn\UserManager();
					$nm = new chhcn\NodeManager();
					if($um->isLogged()) {
						chhcn\Utils::checkCsrf();
						$us = $um->getInfoByUser($_SESSION['user']);
						if($us['group'] == "admin") {
							$valid = chhcn\Regex::isValid($_POST, [
								'id'          => chhcn\Regex::TYPE_NUMBER,
								'name'        => chhcn\Regex::TYPE_NOTEMPTY,
								'description' => chhcn\Regex::TYPE_NOTEMPTY,
								'hostname'    => chhcn\Regex::TYPE_HOSTNAME,
								'ip'          => chhcn\Regex::TYPE_HOSTNAME,
								'port'        => chhcn\Regex::TYPE_NUMBER,
								'admin_port'  => chhcn\Regex::TYPE_NUMBER,
								'admin_pass'  => chhcn\Regex::TYPE_NOTEMPTY,
								'token'       => chhcn\Regex::TYPE_NOTEMPTY,
								'group'       => chhcn\Regex::TYPE_NOTEMPTY,
								'status'      => chhcn\Regex::TYPE_NUMBER,
							]);
							if($valid === true) {
								$update = $nm->updateNode($_POST['id'], [
									'id'          => $_POST['id'],
									'name'        => $_POST['name'],
									'description' => $_POST['description'],
									'hostname'    => $_POST['hostname'],
									'ip'          => $_POST['ip'],
									'port'        => $_POST['port'],
									'admin_port'  => $_POST['admin_port'],
									'admin_pass'  => $_POST['admin_pass'],
									'token'       => $_POST['token'],
									'group'       => $_POST['group'],
									'status'      => $_POST['status'],
								]);
								if($update === true) {
									exit("节点信息更新成功！");
								} else {
									Header("HTTP/1.1 404 Not Found");
									exit("该节点不存在！{$update}");
								}
							} else {
								Header("HTTP/1.1 404 Not Found");
								exit("提交的数据不合法！{$valid}");
							}
						} else {
							exit("你没有足够的权限这么做");
						}
					} else {
						exit("登录会话已超时，请重新登录");
					}
					break;
				case "addnode":
					$um = new chhcn\UserManager();
					$nm = new chhcn\NodeManager();
					if($um->isLogged()) {
						chhcn\Utils::checkCsrf();
						$us = $um->getInfoByUser($_SESSION['user']);
						if($us['group'] == "admin") {
							$valid = chhcn\Regex::isValid($_POST, [
								'name'        => chhcn\Regex::TYPE_NOTEMPTY,
								'description' => chhcn\Regex::TYPE_NOTEMPTY,
								'hostname'    => chhcn\Regex::TYPE_HOSTNAME,
								'ip'          => chhcn\Regex::TYPE_HOSTNAME,
								'port'        => chhcn\Regex::TYPE_NUMBER,
								'admin_port'  => chhcn\Regex::TYPE_NUMBER,
								'admin_pass'  => chhcn\Regex::TYPE_NOTEMPTY,
								'token'       => chhcn\Regex::TYPE_NOTEMPTY,
								'group'       => chhcn\Regex::TYPE_NOTEMPTY,
								'status'      => chhcn\Regex::TYPE_NUMBER,
							]);
							if($valid === true) {
								$update = $nm->addNode([
									'name'        => $_POST['name'],
									'description' => $_POST['description'],
									'hostname'    => $_POST['hostname'],
									'ip'          => $_POST['ip'],
									'port'        => $_POST['port'],
									'admin_port'  => $_POST['admin_port'],
									'admin_pass'  => $_POST['admin_pass'],
									'token'       => $_POST['token'],
									'group'       => $_POST['group'],
									'status'      => $_POST['status'],
								]);
								if($update === true) {
									exit("节点添加成功！");
								} else {
									Header("HTTP/1.1 404 Not Found");
									exit("节点添加失败！{$update}");
								}
							} else {
								Header("HTTP/1.1 404 Not Found");
								exit("提交的数据不合法！{$valid}");
							}
						} else {
							exit("你没有足够的权限这么做");
						}
					} else {
						exit("登录会话已超时，请重新登录");
					}
					break;
				case "deletenode":
					$um = new chhcn\UserManager();
					$nm = new chhcn\NodeManager();
					if($um->isLogged()) {
						chhcn\Utils::checkCsrf();
						$us = $um->getInfoByUser($_SESSION['user']);
						if($us['group'] == "admin") {
							$valid = chhcn\Regex::isValid($_POST, [
								'id'          => chhcn\Regex::TYPE_NUMBER
							]);
							if($valid === true) {
								$update = $nm->deleteNode($_POST['id']);
								if($update === true) {
									exit("节点删除成功！");
								} else {
									Header("HTTP/1.1 404 Not Found");
									exit("该节点不存在！{$update}");
								}
							} else {
								Header("HTTP/1.1 404 Not Found");
								exit("提交的数据不合法！{$valid}");
							}
						} else {
							exit("你没有足够的权限这么做");
						}
					} else {
						exit("登录会话已超时，请重新登录");
					}
					break;
				case "updatepackage":
					$um = new chhcn\UserManager();
					$pm = new chhcn\PackageManager();
					if($um->isLogged()) {
						chhcn\Utils::checkCsrf();
						$us = $um->getInfoByUser($_SESSION['user']);
						if($us['group'] == "admin") {
							$valid = chhcn\Regex::isValid($_POST, [
								'id'          => chhcn\Regex::TYPE_NUMBER,
								'name'        => chhcn\Regex::TYPE_NOTEMPTY,
								'price'       => chhcn\Regex::TYPE_DECIMAL,
								'duration'    => chhcn\Regex::TYPE_NUMBER,
								'group_name'  => chhcn\Regex::TYPE_NOTEMPTY,
								'status'      => chhcn\Regex::TYPE_NUMBER
							]);
							if($valid === true) {
								$update = $pm->updatePackage($_POST['id'], [
									'name'        => $_POST['name'],
									'description' => $_POST['description'],
									'price'       => $_POST['price'],
									'duration'    => $_POST['duration'],
									'group_name'  => $_POST['group_name'],
									'status'      => $_POST['status']
								]);
								if($update === true) {
									exit("套餐信息更新成功！");
								} else {
									Header("HTTP/1.1 500 Internal Server Error");
									$errorMessage = is_string($update) ? $update : "套餐不存在或更新失败";
									exit("操作失败: " . $errorMessage);
								}
							} else {
								Header("HTTP/1.1 400 Bad Request");
								exit("提交的数据不合法！{$valid}");
							}
						} else {
							exit("你没有足够的权限这么做");
						}
					} else {
						exit("登录会话已超时，请重新登录");
					}
					break;
				case "addpackage":
					$um = new chhcn\UserManager();
					$pm = new chhcn\PackageManager();
					if($um->isLogged()) {
						chhcn\Utils::checkCsrf();
						$us = $um->getInfoByUser($_SESSION['user']);
						if($us['group'] == "admin") {
							$valid = chhcn\Regex::isValid($_POST, [
								'name'        => chhcn\Regex::TYPE_NOTEMPTY,
								'price'       => chhcn\Regex::TYPE_DECIMAL,
								'duration'    => chhcn\Regex::TYPE_NUMBER,
								'group_name'  => chhcn\Regex::TYPE_NOTEMPTY,
								'status'      => chhcn\Regex::TYPE_NUMBER
							]);
							if($valid === true) {
								$add = $pm->addPackage([
									'name'        => $_POST['name'],
									'description' => $_POST['description'],
									'price'       => $_POST['price'],
									'duration'    => $_POST['duration'],
									'group_name'  => $_POST['group_name'],
									'status'      => $_POST['status']
								]);
								if($add === true) {
									exit("套餐添加成功！");
								} else {
									Header("HTTP/1.1 500 Internal Server Error");
									$errorMessage = is_string($add) ? $add : "添加套餐失败";
									exit("操作失败: " . $errorMessage);
								}
							} else {
								Header("HTTP/1.1 400 Bad Request");
								exit("提交的数据不合法！{$valid}");
							}
						} else {
							exit("你没有足够的权限这么做");
						}
					} else {
						exit("登录会话已超时，请重新登录");
					}
					break;
				case "deletepackage":
					$um = new chhcn\UserManager();
					$pm = new chhcn\PackageManager();
					if($um->isLogged()) {
						chhcn\Utils::checkCsrf();
						$us = $um->getInfoByUser($_SESSION['user']);
						if($us['group'] == "admin") {
							$valid = chhcn\Regex::isValid($_POST, [
								'id' => chhcn\Regex::TYPE_NUMBER
							]);
							if($valid === true) {
								$delete = $pm->deletePackage($_POST['id']);
								if($delete === true) {
									exit("套餐删除成功！");
								} else {
									Header("HTTP/1.1 500 Internal Server Error");
									$errorMessage = is_string($delete) ? $delete : "删除套餐失败";
									exit("操作失败: " . $errorMessage);
								}
							} else {
								Header("HTTP/1.1 400 Bad Request");
								exit("提交的数据不合法！{$valid}");
							}
						} else {
							exit("你没有足够的权限这么做");
						}
					} else {
						exit("登录会话已超时，请重新登录");
					}
					break;
				case "updatebroadcast":
					$um = new chhcn\UserManager();
					if($um->isLogged()) {
						chhcn\Utils::checkCsrf();
						$us = $um->getInfoByUser($_SESSION['user']);
						if($us['group'] == "admin") {
							if(isset($_POST['data'])) {
								$result = chhcn\Settings::set("broadcast", $_POST['data']);
								if($result === true) {
									exit("公告更新成功！");
								} else {
									exit("数据更新失败！{$result}");
								}
							} else {
								Header("HTTP/1.1 404 Not Found");
								exit("提交的数据不合法！");
							}
						} else {
							exit("你没有足够的权限这么做");
						}
					} else {
						exit("登录会话已超时，请重新登录");
					}
					break;
				case "updatehelpinfo":
					$um = new chhcn\UserManager();
					if($um->isLogged()) {
						chhcn\Utils::checkCsrf();
						$us = $um->getInfoByUser($_SESSION['user']);
						if($us['group'] == "admin") {
							if(isset($_POST['data'])) {
								$result = chhcn\Settings::set("helpinfo", $_POST['data']);
								if($result === true) {
									exit("帮助更新成功！");
								} else {
									exit("数据更新失败！{$result}");
								}
							} else {
								Header("HTTP/1.1 404 Not Found");
								exit("提交的数据不合法！");
							}
						} else {
							exit("你没有足够的权限这么做");
						}
					} else {
						exit("登录会话已超时，请重新登录");
					}
					break;
				case "preview":
					$um = new chhcn\UserManager();
					if($um->isLogged()) {
						chhcn\Utils::checkCsrf();
						include(ROOT . "/core/Parsedown.php");
						$markdown = new Parsedown();
						$markdown->setSafeMode(true);
						$markdown->setBreaksEnabled(true);
						$markdown->setUrlsLinked(true);
						if(isset($_POST['data'])) {
							exit($markdown->text($_POST['data']));
						} else {
							Header("HTTP/1.1 404 Not Found");
							exit("提交的数据不合法！");
						}
					} else {
						exit("登录会话已超时，请重新登录");
					}
					break;
				case "addgroup":
					$um = new chhcn\UserManager();
					$gm = new chhcn\GroupManager();
					if($um->isLogged()) {
						chhcn\Utils::checkCsrf();
						$us = $um->getInfoByUser($_SESSION['user']);
						if($us['group'] == "admin") {
							$valid = chhcn\Regex::isValid($_POST, [
								'name'          => chhcn\Regex::TYPE_NOTEMPTY,
								'friendly_name' => chhcn\Regex::TYPE_NOTEMPTY,
								'traffic'       => chhcn\Regex::TYPE_NUMBER,
								'proxies'       => chhcn\Regex::TYPE_NUMBER,
								'inbound'       => chhcn\Regex::TYPE_NUMBER,
								'outbound'      => chhcn\Regex::TYPE_NUMBER,
							]);
							if($valid === true) {
								// 检查组名是否已存在
								if($gm->isGroupExist($_POST['name'])) {
									Header("HTTP/1.1 400 Bad Request");
									exit("组名已存在，请使用其他组名");
								}
								
								$update = $gm->addGroup([
									'name'          => $_POST['name'],
									'friendly_name' => $_POST['friendly_name'],
									'traffic'       => $_POST['traffic'],
									'proxies'       => $_POST['proxies'],
									'inbound'       => $_POST['inbound'],
									'outbound'      => $_POST['outbound'],
								]);
								if($update === true) {
									exit("用户组添加成功！");
								} else {
									Header("HTTP/1.1 404 Not Found");
									exit("用户组添加失败！{$update}");
								}
							} else {
								Header("HTTP/1.1 404 Not Found");
								exit("提交的数据不合法！{$valid}");
							}
						} else {
							exit("你没有足够的权限这么做");
						}
					} else {
						exit("登录会话已超时，请重新登录");
					}
					break;
				case "updategroup":
					$um = new chhcn\UserManager();
					$gm = new chhcn\GroupManager();
					if($um->isLogged()) {
						chhcn\Utils::checkCsrf();
						$us = $um->getInfoByUser($_SESSION['user']);
						if($us['group'] == "admin") {
							$valid = chhcn\Regex::isValid($_POST, [
								'id'            => chhcn\Regex::TYPE_NUMBER,
								'name'          => chhcn\Regex::TYPE_NOTEMPTY,
								'friendly_name' => chhcn\Regex::TYPE_NOTEMPTY,
								'traffic'       => chhcn\Regex::TYPE_NUMBER,
								'proxies'       => chhcn\Regex::TYPE_NUMBER,
								'inbound'       => chhcn\Regex::TYPE_NUMBER,
								'outbound'      => chhcn\Regex::TYPE_NUMBER,
							]);
							if($valid === true) {
								$update = $gm->updateGroup($_POST['id'], [
									'name'          => $_POST['name'],
									'friendly_name' => $_POST['friendly_name'],
									'traffic'       => $_POST['traffic'],
									'proxies'       => $_POST['proxies'],
									'inbound'       => $_POST['inbound'],
									'outbound'      => $_POST['outbound'],
								]);
								if($update === true) {
									exit("用户组信息更新成功！");
								} else {
									Header("HTTP/1.1 404 Not Found");
									exit("该用户组不存在！{$update}");
								}
							} else {
								Header("HTTP/1.1 404 Not Found");
								exit("提交的数据不合法！{$valid}");
							}
						} else {
							exit("你没有足够的权限这么做");
						}
					} else {
						exit("登录会话已超时，请重新登录");
					}
					break;
				case "deletegroup":
					$um = new chhcn\UserManager();
					$gm = new chhcn\GroupManager();
					if($um->isLogged()) {
						chhcn\Utils::checkCsrf();
						$us = $um->getInfoByUser($_SESSION['user']);
						if($us['group'] == "admin") {
							if(chhcn\Regex::isValid($_POST, [
								"id" => chhcn\Regex::TYPE_NUMBER
							]) === true) {
								$result = $gm->deleteGroup($_POST['id']);
								if($result === true) {
									exit("用户组删除成功！");
								} else {
									Header("HTTP/1.1 404 Not Found");
									exit("用户组删除失败：{$result}");
								}
							} else {
								Header("HTTP/1.1 404 Not Found");
								exit("提交的数据不合法！");
							}
						} else {
							exit("你没有足够的权限这么做");
						}
					} else {
						exit("登录会话已超时，请重新登录");
					}
					break;
				case "generatecode":
					$um = new UserManager();
					$rcm = new RedeemCodeManager();
					if($um->isLogged()) {
						Utils::checkCsrf();
						$us = $um->getInfoByUser($_SESSION['user']);
						if($us['group'] == "admin") {
							$valid = Regex::isValid($_POST, [
								'amount' => Regex::TYPE_NUMBER,
								'count'  => Regex::TYPE_NUMBER,
							]);
							if($valid === true) {
								$amount = (float)$_POST['amount'];
								$count = (int)$_POST['count'];
								
								if($amount <= 0) {
									Header("HTTP/1.1 400 Bad Request");
									exit("金额必须大于0");
								}
								
								if($count <= 0 || $count > 100) {
									Header("HTTP/1.1 400 Bad Request");
									exit("生成数量必须在1-100之间");
								}
								
								$codes = $rcm->generateCodes($amount, $count);
								if(count($codes) > 0) {
									$codeList = implode(", ", $codes);
									$_SESSION['redeem_code_success'] = "兑换码生成成功！" . $codeList;
									exit("<script>location='?page=panel&module=redeemcodes';</script>");
								} else {
									Header("HTTP/1.1 500 Internal Server Error");
									exit("兑换码生成失败，请稍后重试");
								}
							} else {
								Header("HTTP/1.1 400 Bad Request");
								exit("提交的数据不合法！{$valid}");
							}
						} else {
							exit("你没有足够的权限这么做");
						}
					} else {
						exit("登录会话已超时，请重新登录");
					}
					break;
					
				case "deletecode":
					$um = new UserManager();
					$rcm = new RedeemCodeManager();
					if($um->isLogged()) {
						Utils::checkCsrf();
						$us = $um->getInfoByUser($_SESSION['user']);
						if($us['group'] == "admin") {
							if(isset($_POST['code']) && !empty($_POST['code'])) {
								$result = $rcm->deleteCode($_POST['code']);
								if($result) {
									$_SESSION['redeem_code_success'] = "兑换码删除成功！";
									exit("<script>location='?page=panel&module=redeemcodes';</script>");
								} else {
									Header("HTTP/1.1 404 Not Found");
									exit("兑换码删除失败，可能已被使用或不存在");
								}
							} else {
								Header("HTTP/1.1 400 Bad Request");
								exit("提交的数据不合法！");
							}
						} else {
							exit("你没有足够的权限这么做");
						}
					} else {
						exit("登录会话已超时，请重新登录");
					}
					break;
					
				case "usecode":
					$um = new UserManager();
					$rcm = new RedeemCodeManager();
					if($um->isLogged()) {
						Utils::checkCsrf();
						if(isset($_POST['code']) && !empty($_POST['code'])) {
							$result = $rcm->useCode($_POST['code'], $_SESSION['user']);
							if($result['status']) {
								exit($result['message']);
							} else {
								Header("HTTP/1.1 400 Bad Request");
								exit($result['message']);
							}
						} else {
							Header("HTTP/1.1 400 Bad Request");
							exit("请输入兑换码");
						}
					} else {
						exit("登录会话已超时，请重新登录");
					}
					break;
					
				case "buypackage":
					$um = new chhcn\UserManager();
					if($um->isLogged()) {
						if(!isset($_POST['csrf_token']) || !isset($_SESSION['token']) || $_POST['csrf_token'] !== $_SESSION['token']) {
							$_SESSION['error_msg'] = 'Invalid CSRF Token';
							header("Location: ?page=panel&module=buypackage");
							exit;
						}
						$pm = new \chhcn\PackageManager();
						$result = $pm->buyPackage($_SESSION['user'], intval($_POST['package_id']));
						if ($result === true) {
							$_SESSION['success_msg'] = '套餐购买成功！';
						} else {
							$_SESSION['error_msg'] = is_string($result) ? $result : '购买失败，未知错误。';
						}
						header("Location: ?page=panel&module=buypackage");
						exit;
					} else {
						exit("登录会话已超时，请重新登录");
					}
					break;
				case "redeemcode":
					$um = new chhcn\UserManager();
					if($um->isLogged()) {
						if(!isset($_POST['csrf_token']) || !isset($_SESSION['token']) || $_POST['csrf_token'] !== $_SESSION['token']) {
							$_SESSION['error_msg'] = 'Invalid CSRF Token';
							header("Location: ?page=panel&module=buypackage");
							exit;
						}
						$rcm = new \chhcn\RedeemCodeManager();
						$result = $rcm->useCode($_POST['redeem_code'], $_SESSION['user']);
						if ($result['status']) {
							$_SESSION['success_msg'] = $result['message'];
						} else {
							$_SESSION['error_msg'] = $result['message'];
						}
						header("Location: ?page=panel&module=buypackage");
						exit;
					} else {
						exit("登录会话已超时，请重新登录");
					}
					break;
				case "resettoken":
					$um = new chhcn\UserManager();
					if($um->isLogged()) {
						if (!isset($_GET['csrf']) || $_GET['csrf'] !== $_SESSION['token']) {
							Header("HTTP/1.1 403 Forbidden");
							exit("无效的会话令牌，请刷新页面后重试");
						}
						$user = $um->getInfoByUser($_SESSION['user']);
						$token = substr(md5(sha1(mt_rand() . time() . $_SESSION['user'])), 0, 16);
						$result = Database::update("tokens", ['token' => $token], ['username' => $_SESSION['user']]);
						if($result === true) {
							exit("密钥重置成功！");
						} else {
							exit("密钥重置失败，数据库错误：" . $result);
						}
					} else {
						exit("登录会话已超时，请重新登录");
					}
					break;
				default:
					Header("HTTP/1.1 404 Not Found");
					exit("Undefined action {$params['action']}");
			}
		}
	}
}
