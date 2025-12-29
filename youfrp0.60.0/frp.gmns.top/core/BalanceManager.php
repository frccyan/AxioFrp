<?php

namespace chhcn;

class BalanceManager
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * 获取用户余额
     * @param string $username 用户名
     * @return float 余额
     */
    public function getBalance($username)
    {
        $stmt = $this->db->prepare("SELECT balance FROM user_balance WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        
        $result = Database::fetch($stmt);
        if ($result) {
            return (float)$result['balance'];
        } else {
            // 如果用户没有余额记录，则创建一条
            $this->createBalanceRecord($username);
            return 0.0;
        }
    }

    /**
     * 为用户创建余额记录
     * @param string $username 用户名
     */
    private function createBalanceRecord($username)
    {
        $stmt = $this->db->prepare("INSERT INTO user_balance (username, balance) VALUES (?, 0) ON DUPLICATE KEY UPDATE username = username");
        $stmt->bind_param("s", $username);
        $stmt->execute();
    }

    /**
     * 更新用户余额
     * @param string $username 用户名
     * @param float $amount 变动金额
     * @param string $type 类型：recharge充值 consume消费
     * @param string $description 描述
     * @return bool 操作结果
     */
    public function updateBalance($username, $amount, $type, $description = '')
    {
        try {
            $this->db->beginTransaction();
            
            // 获取当前余额
            $currentBalance = $this->getBalance($username);
            
            // 计算新余额
            $newBalance = $currentBalance;
            if ($type == 'recharge') {
                $newBalance += $amount;
            } else if ($type == 'consume') {
                $newBalance -= $amount;
                // 检查余额是否足够
                if ($newBalance < 0) {
                    $this->db->rollBack();
                    return false;
                }
            }
            
            // 更新余额
            $stmt = $this->db->prepare("INSERT INTO user_balance (username, balance) VALUES (?, ?) ON DUPLICATE KEY UPDATE balance = ?");
            $stmt->bind_param("sdd", $username, $newBalance, $newBalance);
            $stmt->execute();
            
            // 记录余额变动
            $stmt = $this->db->prepare("INSERT INTO balance_logs (username, amount, type, description, created_at) VALUES (?, ?, ?, ?, ?)");
            $currentTime = time();
            $stmt->bind_param("sdssi", $username, $amount, $type, $description, $currentTime);
            $stmt->execute();
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * 获取用户余额变动记录
     * @param string $username 用户名
     * @param int $limit 限制条数
     * @return array 记录列表
     */
    public function getBalanceLogs($username, $limit = 10)
    {
        $stmt = $this->db->prepare("SELECT * FROM balance_logs WHERE username = ? ORDER BY created_at DESC LIMIT ?");
        $stmt->bind_param("si", $username, $limit);
        $stmt->execute();
        return Database::fetchAll($stmt);
    }
} 