<?php
$db = [
	/* 数据库地址 */ "host" => "localhost",
	/* 数据库账号 */ "user" => "frp",
	/* 数据库密码 */ "pass" => "tgx123456",
	/* 数据库名字 */ "name" => "frp",
	/* 数据库端口 */ "port" => 3306
];


if(php_sapi_name() !== "cli") {
	exit("This program only can running on cli mode");
}

function Println($data) {
	echo date("[Y-m-d H:i:s] ") . $data . "\n";
}

function ResultToArray($result) {
	$data = Array();
	while($rw = mysqli_fetch_row($result)) {
		$data[] = $rw;
	}
	return $data;
}

function http($url, $post = '', $cookie = '', $headers = '', $returnHeader = 0) {
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
	curl_setopt($curl, CURLOPT_REFERER, $url);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	if($post) {
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
	}
	if($cookie) {
		curl_setopt($curl, CURLOPT_COOKIE, $cookie);
	}
	if($headers) {
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	}
	curl_setopt($curl, CURLOPT_HEADER, 0);
	curl_setopt($curl, CURLOPT_TIMEOUT, 60);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$data = curl_exec($curl);
	$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	if(curl_errno($curl)) {
		$httpCode = curl_error($curl);
	}
	curl_close($curl);
	return [
		'status' => $httpCode,
		'body'   => $data
	];
}

function GetTokensToUsers($conn) {
	$rs = mysqli_query($conn, "SELECT * FROM `tokens`");
	$rs = ResultToArray($rs);
	$tokens = [];
	foreach($rs as $line) {
		$tokens[$line[2]] = $line[1];
	}
	return $tokens;
}

function kickFromAllNode($conn, $user) {
	$ts = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM `tokens` WHERE `username`='{$user}'"));
	if($ts) {
		$rs = mysqli_query($conn, "SELECT * FROM `nodes` WHERE `status`='200' OR `status`='401'");
		$rs = ResultToArray($rs);
		foreach($rs as $node) {
			http("http://admin:{$node[7]}@{$node[4]}:{$node[6]}/api/client/close/{$ts['token']}");
		}
	} else {
		Println("警告！未找到用户 {$user} 的 Token！");
	}
}

function updateTraffic($conn, $user, $traffic) {
	$user        = mysqli_real_escape_string($conn, $user);
	$enc_traffic = mysqli_real_escape_string($conn, $traffic);
	$rs = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM `todaytraffic` WHERE `user`='{$user}'"));
	$us = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM `users` WHERE `username`='{$user}'"));
	if($rs && $us) {
		// 判断流量是否已用完
		$userTraffic = $us['traffic'] * 1024 * 1024;
		Println("用户 {$user} 当前总流量: {$us['traffic']} MB, 转换为字节: {$userTraffic}");
		
		// 计算本次新增流量
		$trafficDiff = 0;
		if ($rs) {
			// 如果有历史记录，计算差值
			if ($traffic > $rs['traffic']) {
				$trafficDiff = $traffic - $rs['traffic'];
				Println("用户 {$user} 新增流量: 当前流量 {$traffic} - 历史流量 {$rs['traffic']} = {$trafficDiff} 字节");
			} else {
				// 如果当前流量小于历史记录，可能是frps重启，直接使用当前流量作为差值
				$trafficDiff = $traffic;
				Println("用户 {$user} 流量重置: 使用当前流量 {$traffic} 字节作为差值");
			}
		} else {
			// 如果没有历史记录，直接使用当前流量
			$trafficDiff = $traffic;
			Println("用户 {$user} 首次记录: 使用当前流量 {$traffic} 字节作为差值");
		}
		
		// 确保差值不为零，至少扣减一些流量（测试用）
		if ($trafficDiff == 0) {
			// 不再强制扣减，让系统自然运行
			Println("用户 {$user} 流量差值为零，无需扣减流量");
		}
		
		// 计算扣除后的剩余流量
		$trafficDiffMB = round($trafficDiff / 1024 / 1024, 2);
		$newTraffic = round($us['traffic'] - $trafficDiffMB, 2);
		Println("用户 {$user} 扣减流量计算: 差值 {$trafficDiff} 字节 = {$trafficDiffMB} MB");
		Println("用户 {$user} 扣减流量计算: {$us['traffic']} MB - {$trafficDiffMB} MB = {$newTraffic} MB");
		
		if($newTraffic <= 0) {
			if(count(ResultToArray(mysqli_query($conn, "SELECT * FROM `proxies` WHERE `status`='0' AND `username`='{$user}'"))) > 0) {
				Println("用户 {$user} 的流量已经用完，正在停止该用户的所有隧道");
				mysqli_query($conn, "UPDATE `proxies` SET `status`='2' WHERE `username`='{$user}'");
				kickFromAllNode($conn, $user);
				
				// 更新今日流量记录
				if ($rs) {
					mysqli_query($conn, "UPDATE `todaytraffic` SET `traffic`='{$enc_traffic}' WHERE `user`='{$user}'");
					Println("更新用户 {$user} 的今日流量记录: {$enc_traffic} 字节");
				} else {
					mysqli_query($conn, "INSERT INTO `todaytraffic` (`user`, `traffic`) VALUES ('{$user}', '{$enc_traffic}')");
					Println("创建用户 {$user} 的今日流量记录: {$enc_traffic} 字节");
				}
				
				// 设置剩余流量为0
				mysqli_query($conn, "UPDATE `users` SET `traffic`='0' WHERE `username`='{$user}'");
				Println("用户 {$user} 流量已用尽，设置剩余流量为 0 MB");
			}
		} else {
			// 更新用户总流量
			$updateResult = mysqli_query($conn, "UPDATE `users` SET `traffic`='{$newTraffic}' WHERE `username`='{$user}'");
			if ($updateResult) {
				Println("成功更新用户 {$user} 的剩余流量: {$newTraffic} MB");
				
				// 立即查询数据库，确认更新是否成功
				$checkResult = mysqli_fetch_array(mysqli_query($conn, "SELECT `traffic` FROM `users` WHERE `username`='{$user}'"));
				if ($checkResult) {
					Println("数据库验证: 用户 {$user} 当前流量为 {$checkResult['traffic']} MB");
					if ($checkResult['traffic'] != $newTraffic) {
						Println("警告: 数据库中的流量值 ({$checkResult['traffic']}) 与期望值 ({$newTraffic}) 不一致!");
					}
				}
			} else {
				Println("更新用户 {$user} 的剩余流量失败: " . mysqli_error($conn));
			}
			
			// 更新今日流量记录
			if ($rs) {
				mysqli_query($conn, "UPDATE `todaytraffic` SET `traffic`='{$enc_traffic}' WHERE `user`='{$user}'");
				Println("更新用户 {$user} 的今日流量记录: {$enc_traffic} 字节");
			} else {
				mysqli_query($conn, "INSERT INTO `todaytraffic` (`user`, `traffic`) VALUES ('{$user}', '{$enc_traffic}')");
				Println("创建用户 {$user} 的今日流量记录: {$enc_traffic} 字节");
			}
			
			mysqli_query($conn, "UPDATE `proxies` SET `status`='0' WHERE `username`='{$user}' AND `status`='2'");
		}
	} elseif($us) {
		// 更新用户的剩余流量
		$userTraffic = $us['traffic'] * 1024 * 1024;
		Println("用户 {$user} 当前总流量: {$us['traffic']} MB, 转换为字节: {$userTraffic}");
		
		// 如果没有历史记录，直接使用当前流量作为差值
		$trafficDiff = $traffic;
		Println("用户 {$user} 无历史记录: 使用当前流量 {$traffic} 字节作为差值");
		
		// 确保差值不为零，至少扣减一些流量（测试用）
		if ($trafficDiff == 0) {
			// 不再强制扣减，让系统自然运行
			Println("用户 {$user} 流量差值为零，无需扣减流量");
		}
		
		// 计算扣除后的剩余流量
		$trafficDiffMB = round($trafficDiff / 1024 / 1024, 2);
		$newTraffic = round($us['traffic'] - $trafficDiffMB, 2);
		Println("用户 {$user} 扣减流量计算: 差值 {$trafficDiff} 字节 = {$trafficDiffMB} MB");
		Println("用户 {$user} 扣减流量计算: {$us['traffic']} MB - {$trafficDiffMB} MB = {$newTraffic} MB");
		
		if($newTraffic <= 0) {
			if(count(ResultToArray(mysqli_query($conn, "SELECT * FROM `proxies` WHERE `status`='0' AND `username`='{$user}'"))) > 0) {
				Println("用户 {$user} 的流量已经用完，正在停止该用户的所有隧道");
				mysqli_query($conn, "UPDATE `proxies` SET `status`='2' WHERE `username`='{$user}'");
				kickFromAllNode($conn, $user);
				mysqli_query($conn, "INSERT INTO `todaytraffic` (`user`, `traffic`) VALUES ('{$user}', '{$enc_traffic}')");
				mysqli_query($conn, "UPDATE `users` SET `traffic`='0' WHERE `username`='{$user}'");
				Println("用户 {$user} 流量已用尽，设置剩余流量为 0 MB");
			}
		} else {
			mysqli_query($conn, "INSERT INTO `todaytraffic` (`user`, `traffic`) VALUES ('{$user}', '{$enc_traffic}')");
			Println("创建用户 {$user} 的今日流量记录: {$enc_traffic} 字节");
			
			$updateResult = mysqli_query($conn, "UPDATE `users` SET `traffic`='{$newTraffic}' WHERE `username`='{$user}'");
			if ($updateResult) {
				Println("成功更新用户 {$user} 的剩余流量: {$newTraffic} MB");
				
				// 立即查询数据库，确认更新是否成功
				$checkResult = mysqli_fetch_array(mysqli_query($conn, "SELECT `traffic` FROM `users` WHERE `username`='{$user}'"));
				if ($checkResult) {
					Println("数据库验证: 用户 {$user} 当前流量为 {$checkResult['traffic']} MB");
					if ($checkResult['traffic'] != $newTraffic) {
						Println("警告: 数据库中的流量值 ({$checkResult['traffic']}) 与期望值 ({$newTraffic}) 不一致!");
					}
				}
			} else {
				Println("更新用户 {$user} 的剩余流量失败: " . mysqli_error($conn));
			}
			
			mysqli_query($conn, "UPDATE `proxies` SET `status`='0' WHERE `username`='{$user}' AND `status`='2'");
		}
	} else {
		Println("警告！发现有不存在数据库中的用户名 {$user}，可能是数据库条目被误删导致！");
	}
}

function fetchData($node, $type, $trafficList) {
	Println("正在爬取 {$node[1]} 服务器节点的 {$type} 隧道数据");
	$data = http("http://admin:{$node[7]}@{$node[4]}:{$node[6]}/api/proxy/{$type}");
	if($data['status'] == 200) {
		$json = json_decode($data['body'], true);
		if(is_array($json) && isset($json['proxies'])) {
			foreach($json['proxies'] as $proxy) {
				$expName = explode(".", $proxy['name']);
				if(count($expName) == 2) {
					if(!isset($trafficList[$expName[0]])) $trafficList[$expName[0]] = 0;
					$trafficList[$expName[0]] = $trafficList[$expName[0]] + $proxy['todayTrafficIn'] + $proxy['todayTrafficOut'];
				} else {
					Println("发现不规范的名称：{$proxy['name']}");
				}
			}
		} else {
			Println("无法解析返回的数据，但是状态码是正常状态：{$data['body']}");
		}
	} else {
		Println("服务器返回错误状态：{$data['status']}");
	}
	return $trafficList;
}

// 添加一个运行次数计数器
$runCount = 0;

while(true) {
	$runCount++;
	Println("第 {$runCount} 次运行开始");
	
	$conn = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['name'], $db['port']);
	$rs   = mysqli_query($conn, "SELECT * FROM `nodes` WHERE `status`='200' OR `status`='401'");
	if($rs) {
		$rs = ResultToArray($rs);
		$trafficList = [];
		// 挨个挨个节点爬取数据
		foreach($rs as $node) {
			// 爬取 TCP 隧道数据
			$trafficList = fetchData($node, "tcp", $trafficList);
			
			// 爬取 UDP 隧道数据
			$trafficList = fetchData($node, "udp", $trafficList);
			
			// 爬取 HTTP 隧道数据
			$trafficList = fetchData($node, "http", $trafficList);
			
			// 爬取 HTTPS 隧道数据
			$trafficList = fetchData($node, "https", $trafficList);
			
			// 爬取 STCP 隧道数据
			$trafficList = fetchData($node, "stcp", $trafficList);
		}
		Println("所有隧道爬取完成，正在写入数据库");
		$tokens = GetTokensToUsers($conn);
		foreach($trafficList as $user => $traffic) {
			if(isset($tokens[$user])) {
				updateTraffic($conn, $tokens[$user], $traffic);
				Println("{$tokens[$user]} 今日流量 {$traffic}");
			}
		}
	}
	mysqli_close($conn);
	
	// 不再限制运行次数，让系统持续运行
	// if ($runCount >= 1) {
	// 	Println("测试模式：已运行 {$runCount} 次，程序退出");
	// 	break;
	// }
	
	sleep(180);
}
