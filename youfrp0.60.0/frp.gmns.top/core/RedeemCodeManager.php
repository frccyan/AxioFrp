<?php

namespace chhcn;

class RedeemCodeManager
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * 生成兑换码
     * @param float $amount 金额
     * @param int $count 生成数量
     * @return array 生成的兑换码列表
     */
    public function generateCodes($amount, $count = 1)
    {
        $codes = [];
        $time = time();
        
        try {
            $this->db->beginTransaction();
            
            for ($i = 0; $i < $count; $i++) {
                $code = $this->generateUniqueCode();
                $stmt = $this->db->prepare("INSERT INTO redeem_codes (code, amount, status, created_at) VALUES (?, ?, 0, ?)");
                $stmt->bind_param("sdi", $code, $amount, $time);
                if (!$stmt->execute()) {
                    throw new \Exception("无法插入兑换码：" . $stmt->error);
                }
                $codes[] = $code;
            }
            
            $this->db->commit();
            return $codes;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("生成兑换码错误: " . $e->getMessage());
            return [];
        }
    }

    /**
     * 生成唯一兑换码
     * @return string 兑换码
     */
    private function generateUniqueCode()
    {
        do {
            $code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 16));
            $stmt = $this->db->prepare("SELECT code FROM redeem_codes WHERE code = ?");
            $stmt->bind_param("s", $code);
            $stmt->execute();
        } while (Database::fetch($stmt));
        
        return $code;
    }

    /**
     * 使用兑换码
     * @param string $code 兑换码
     * @param string $username 用户名
     * @return array 结果，包含status和message
     */
    public function useCode($code, $username)
    {
        try {
            $this->db->beginTransaction();
            
            // 查询兑换码
            $stmt = $this->db->prepare("SELECT * FROM redeem_codes WHERE code = ? FOR UPDATE");
            $stmt->bind_param("s", $code);
            $stmt->execute();
            $codeInfo = Database::fetch($stmt);
            
            if (!$codeInfo) {
                $this->db->rollBack();
                return ['status' => false, 'message' => '兑换码不存在'];
            }
            
            if ($codeInfo['status'] == 1) {
                $this->db->rollBack();
                return ['status' => false, 'message' => '兑换码已使用'];
            }
            
            // 标记兑换码为已使用
            $stmt = $this->db->prepare("UPDATE redeem_codes SET status = 1, used_at = ?, used_by = ? WHERE code = ?");
            $currentTime = time();
            $stmt->bind_param("iss", $currentTime, $username, $code);
            $stmt->execute();
            
            // 为用户增加余额
            $balanceManager = new BalanceManager();
            $result = $balanceManager->updateBalance($username, $codeInfo['amount'], 'recharge', '兑换码充值：' . $code);
            
            if (!$result) {
                $this->db->rollBack();
                return ['status' => false, 'message' => '余额更新失败'];
            }
            
            $this->db->commit();
            return [
                'status' => true, 
                'message' => '兑换成功，已增加余额 ' . $codeInfo['amount'] . ' 元',
                'amount' => $codeInfo['amount']
            ];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['status' => false, 'message' => '兑换失败：' . $e->getMessage()];
        }
    }

    /**
     * 获取兑换码列表
     * @param string $status 状态：all所有 used已使用 unused未使用
     * @param int $page 页码
     * @param int $limit 每页条数
     * @return array 兑换码列表和总数
     */
    public function getCodes($status = 'all', $page = 1, $limit = 10)
    {
        $offset = ($page - 1) * $limit;
        $where = "";
        $params = [];
        
        if ($status == 'used') {
            $where = "WHERE status = 1";
        } elseif ($status == 'unused') {
            $where = "WHERE status = 0";
        }
        
        // 获取总数
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM redeem_codes " . $where);
        $stmt->execute();
        $total = Database::fetch($stmt)['total'];
        
        // 获取列表
        $stmt = $this->db->prepare("SELECT * FROM redeem_codes {$where} ORDER BY created_at DESC LIMIT ?, ?");
        $stmt->bind_param("ii", $offset, $limit);
        $stmt->execute();
        $codes = Database::fetchAll($stmt);
        
        return [
            'total' => $total,
            'codes' => $codes
        ];
    }
    
    /**
     * 获取指定状态的所有兑换码 (不分页)
     * @param string $status 状态：all所有 used已使用 unused未使用
     * @return array 兑换码列表
     */
    public function getAllCodesByStatus($status = 'all')
    {
        $where = "";
        if ($status == 'used') {
            $where = "WHERE status = 1";
        } elseif ($status == 'unused') {
            $where = "WHERE status = 0";
        }
        
        $stmt = $this->db->prepare("SELECT * FROM redeem_codes {$where} ORDER BY created_at DESC");
        $stmt->execute();
        return Database::fetchAll($stmt);
    }

    /**
     * 删除兑换码
     * @param string $code 兑换码
     * @return bool 操作结果
     */
    public function deleteCode($code)
    {
        $stmt = $this->db->prepare("DELETE FROM redeem_codes WHERE code = ? AND status = 0");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
} 