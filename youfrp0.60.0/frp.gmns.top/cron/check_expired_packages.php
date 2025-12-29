<?php
/**
 * 套餐过期检查脚本
 * 用于检查和处理过期的套餐，将用户重置回默认组并删除过期的购买记录
 * 
 * 可以通过Web访问（需要管理员权限）或通过计划任务定时执行
 * 
 * 计划任务示例（每天凌晨3点执行）：
 * 0 3 * * * php /path/to/youfrpc2/cron/check_expired_packages.php
 */

// 定义根目录
define('ROOT', dirname(__DIR__));

// 包含配置文件
// require_once(ROOT . '/chh.php'); // 移除冲突的连接文件
require_once(ROOT . '/configuration.php');

// 包含必要的类文件
require_once(ROOT . '/core/Database.php');
require_once(ROOT . '/core/UserManager.php');
require_once(ROOT . '/core/PackageManager.php');
require_once(ROOT . '/core/GroupManager.php');
require_once(ROOT . '/core/Utils.php');
require_once(ROOT . '/core/Regex.php');
require_once(ROOT . '/core/NodeManager.php');

// 初始化类命名空间
use chhcn\PackageManager;
use chhcn\UserManager;
use chhcn\NodeManager;
use chhcn\GroupManager;
use chhcn\Database;
use chhcn\Utils;
use chhcn\Regex;

// 确保数据库连接已初始化
$db = new Database();

// 当通过Web方式访问时的权限验证
if (php_sapi_name() !== 'cli') {
    session_start();
    
    // 检查是否登录
    if (!isset($_SESSION['user'])) {
        echo json_encode(['status' => false, 'message' => '未登录，无法执行此操作']);
        exit;
    }
    
    // 检查是否是管理员
    $um = new UserManager();
    if (!$um->isLoggedIn() || !$um->isAdmin()) {
        echo json_encode(['status' => false, 'message' => '权限不足，仅管理员可执行此操作']);
        exit;
    }
}

// 执行过期套餐检查
$pm = new PackageManager();
$count = $pm->checkExpiredPackages(true); // 传入true表示删除过期的购买记录

// 输出结果
if (php_sapi_name() === 'cli') {
    echo "已处理 {$count} 个过期套餐用户，重置为默认组并删除过期记录。\n";
} else {
    echo json_encode([
        'status' => true, 
        'message' => "已处理 {$count} 个过期套餐用户，重置为默认组并删除过期记录。"
    ]);
} 