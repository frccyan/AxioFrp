<?php
namespace chhcn;

use chhcn;

class PackageManager {
	
	private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
	
	public function isPackageExist($id)
	{
		$stmt = $this->db->prepare("SELECT id FROM packages WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return Database::fetch($stmt) ? true : false;
	}
	
	public function getPackageInfo($id)
	{
		$stmt = $this->db->prepare("SELECT * FROM packages WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return Database::fetch($stmt);
	}
	
	public function getAllPackages()
	{
		$stmt = $this->db->prepare("SELECT * FROM packages ORDER BY price ASC");
        $stmt->execute();
        return Database::fetchAll($stmt);
	}
	
	public function getActivePackages()
	{
		$stmt = $this->db->prepare("SELECT * FROM packages WHERE status = 1 ORDER BY price ASC");
        $stmt->execute();
        return Database::fetchAll($stmt);
	}
	
	public function updatePackage($id, $data)
	{
		if($this->getPackageInfo($id)) {
			return Database::update("packages", $data, Array("id" => $id));
		} else {
			return false;
		}
	}
	
	public function addPackage($data)
	{
		return Database::insert("packages", $data);
	}
	
	public function deletePackage($id)
	{
		if($this->getPackageInfo($id)) {
			// 检查是否有订单关联
			$stmt = $this->db->prepare("SELECT COUNT(*) as count FROM package_orders WHERE package_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = Database::fetch($stmt);
            
            if($result['count'] > 0) {
                return "该套餐已有用户购买，无法删除";
            }
            
            $stmt = $this->db->prepare("DELETE FROM packages WHERE id = ?");
            $stmt->bind_param("i", $id);
            return $stmt->execute();
		} else {
			return false;
		}
	}
	
	/**
     * 购买套餐
     * @param string $username 用户名
     * @param int $packageId 套餐ID
     * @return mixed 成功返回true，失败返回错误信息
     */
	public function buyPackage($username, $packageId)
	{
		try {
            $this->db->beginTransaction();
            
            // 获取套餐信息
            $package = $this->getPackageInfo($packageId);
            if(!$package) {
                $this->db->rollBack();
                return "套餐不存在";
            }
            
            if($package['status'] != 1) {
                $this->db->rollBack();
                return "该套餐已下架";
            }
            
            // 获取用户信息
            $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? FOR UPDATE");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $user = Database::fetch($stmt);
            if(!$user) {
                $this->db->rollBack();
                return "用户不存在";
            }
            
            // 获取用户组信息
            $stmt = $this->db->prepare("SELECT * FROM groups WHERE name = ?");
            $stmt->bind_param("s", $package['group_name']);
            $stmt->execute();
            $group = Database::fetch($stmt);
            if(!$group) {
                $this->db->rollBack();
                return "套餐对应的用户组不存在";
            }
            
            // 检查用户余额
            $balanceManager = new BalanceManager();
            $userBalance = $balanceManager->getBalance($username);
            
            if($userBalance < $package['price']) {
                $this->db->rollBack();
                return "余额不足，请先充值";
            }
            
            // 扣除用户余额
            $result = $balanceManager->updateBalance($username, $package['price'], 'consume', '购买套餐：' . $package['name']);
            if(!$result) {
                $this->db->rollBack();
                return "扣款失败";
            }
            
            // 计算到期时间
            $expireTime = 0;
            if($package['duration'] > 0) {
                $expireTime = time() + $package['duration'] * 86400; // 天数转换为秒
            }
            
            // 创建订单
            $stmt = $this->db->prepare("INSERT INTO package_orders (username, package_id, order_time, expire_time, price) VALUES (?, ?, ?, ?, ?)");
            $currentTime = time();
            $price = $package['price'];
            $stmt->bind_param("siids", $username, $packageId, $currentTime, $expireTime, $price);
            if (!$stmt->execute()) {
                throw new \Exception("创建订单失败：" . $stmt->error);
            }
            
            // 更新用户组和流量
            $stmt = $this->db->prepare("UPDATE users SET `group` = ?, traffic = ?, proxies = ? WHERE username = ?");
            $traffic = $group['traffic']; // 直接使用用户组的流量值，不做单位转换
            $proxies = $group['proxies'];
            $stmt->bind_param("sdis", $package['group_name'], $traffic, $proxies, $username);
            if (!$stmt->execute()) {
                throw new \Exception("更新用户组和流量失败：" . $stmt->error);
            }
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("购买套餐失败：" . $e->getMessage());
            return "购买失败：" . $e->getMessage();
        }
	}
	
	/**
     * 获取用户购买的套餐订单
     * @param string $username 用户名
     * @return array 订单列表
     */
	public function getUserOrders($username)
	{
		$stmt = $this->db->prepare("
            SELECT o.*, p.name, p.price, p.group_name, p.duration
            FROM package_orders o 
            LEFT JOIN packages p ON o.package_id = p.id 
            WHERE o.username = ? 
            ORDER BY o.order_time DESC
        ");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        return Database::fetchAll($stmt);
	}
	
	/**
     * 获取所有套餐订单
     * @return array 订单列表
     */
	public function getAllOrders()
	{
		$stmt = $this->db->prepare("
            SELECT o.*, p.name, p.price, p.group_name, p.duration, u.email 
            FROM package_orders o 
            LEFT JOIN packages p ON o.package_id = p.id 
            LEFT JOIN users u ON o.username = u.username 
            ORDER BY o.order_time DESC
        ");
        $stmt->execute();
        return Database::fetchAll($stmt);
	}
    
    /**
     * 检查用户套餐是否过期，并更新用户状态
     * @return int 更新的用户数量
     */
    public function checkExpiredPackages()
    {
        $now = time();
        $count = 0;
        
        try {
            $this->db->beginTransaction();
            
            // 查找已过期但未处理的订单
            $stmt = $this->db->prepare("
                SELECT o.username, o.expire_time
                FROM package_orders o
                INNER JOIN (
                    SELECT username, MAX(order_time) as latest_order
                    FROM package_orders 
                    WHERE expire_time > 0
                    GROUP BY username
                ) latest ON o.username = latest.username AND o.order_time = latest.latest_order
                WHERE o.expire_time > 0 AND o.expire_time < ?
            ");
            $stmt->bind_param("i", $now);
            $stmt->execute();
            $expiredUsers = Database::fetchAll($stmt);
            
            // 获取默认用户组
            $stmt = $this->db->prepare("SELECT * FROM groups WHERE name = 'default' LIMIT 1");
            $stmt->execute();
            $defaultGroup = Database::fetch($stmt);
            
            if(!$defaultGroup) {
                $this->db->rollBack();
                return 0;
            }
            
            // 更新过期用户的用户组
            foreach($expiredUsers as $user) {
                $stmt = $this->db->prepare("UPDATE users SET `group` = ?, traffic = ?, proxies = ? WHERE username = ?");
                $groupName = $defaultGroup['name'];
                $traffic = $defaultGroup['traffic']; // 直接使用用户组的流量值，不做单位转换
                $proxies = $defaultGroup['proxies'];
                $username = $user['username'];
                $stmt->bind_param("sdis", $groupName, $traffic, $proxies, $username);
                $stmt->execute();
                $count++;
            }
            
            $this->db->commit();
            return $count;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return 0;
        }
    }
} 