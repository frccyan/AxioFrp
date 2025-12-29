<?php
namespace chhcn;

class VerificationManager {
    public function verify($realname, $idcard) {
        global $_config;

        // 检查是否已经实名
        $userData = Database::querySingleLine('users', ['username' => $_SESSION['user']]);
        if (!empty($userData['realname'])) {
            return ['code' => 400, 'msg' => '您已经实名认证，请勿重复提交'];
        }

        if (empty($realname) || empty($idcard)) {
            return ['code' => 400, 'msg' => '真实姓名和身份证号不能为空'];
        }

        // API信息
        $apiUrl = "https://api.byxy.vip/v2/idcard/";
        $appkey = $_config['id_appkey'] ?? "6c6810d9871b4f37afcc1cd8542dc336e6b5aaba548add55";

        $url = "{$apiUrl}?realname=" . urlencode($realname) . "&idcard={$idcard}&appkey={$appkey}";

        // 使用 cURL 发起请求
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        // 如果您的服务器无法验证SSL证书，可以取消下面的注释
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($httpCode != 200) {
            return ['code' => 500, 'msg' => '实名认证接口请求失败: ' . $error];
        }

        $result = json_decode($response, true);

        if (isset($result['code']) && $result['code'] == '100200' && isset($result['result']['res']) && $result['result']['res'] == '1') {
            // 认证成功
            $authData = $result['result'];
            $updateData = [
                'realname' => $authData['realname'],
                'idcard'   => $authData['idcard'],
                'sex'      => $authData['sex'],
                'age'      => (int)$authData['age'],
                'birthday' => $authData['birthday_text'],
                'address'  => $authData['address']
            ];

            // 使用 username 作为条件更新
            $userExists = Database::querySingleLine("users", ['username' => $_SESSION['user']]);
            if ($userExists) {
                $isSuccess = Database::update('users', $updateData, ['username' => $_SESSION['user']]);
                if ($isSuccess) {
                    return ['code' => 200, 'msg' => '实名认证成功！'];
                } else {
                    return ['code' => 500, 'msg' => '数据库更新失败: ' . Database::fetchError()];
                }
            } else {
                return ['code' => 404, 'msg' => '用户不存在，无法更新数据'];
            }
            
        } else {
            // 认证失败
            $msg = isset($result['msg']) ? $result['msg'] : '认证失败，请检查您填写的信息。';
            return ['code' => 400, 'msg' => $msg];
        }
    }
} 