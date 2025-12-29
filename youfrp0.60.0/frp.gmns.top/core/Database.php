<?php
namespace chhcn;

class Database {
	
	private static $instance = null;
	
	public function __construct()
	{
		global $_config, $conn;
		
		$conn = mysqli_connect(
			$_config['db_host'],
			$_config['db_user'],
			$_config['db_pass'],
			$_config['db_name'],
			$_config['db_port']
		);
		if($conn) {
			mysqli_set_charset($conn, $_config['db_code']);
			mysqli_select_db($conn, $_config['db_name']);
		}
	}
	
	/**
	 * 获取数据库实例（单例模式）
	 * @return Database 数据库实例
	 */
	public static function getInstance()
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * 开始事务
	 */
	public function beginTransaction()
	{
		global $conn;
		mysqli_autocommit($conn, false);
		return mysqli_begin_transaction($conn);
	}
	
	/**
	 * 提交事务
	 */
	public function commit()
	{
		global $conn;
		$result = mysqli_commit($conn);
		mysqli_autocommit($conn, true);
		return $result;
	}
	
	/**
	 * 回滚事务
	 */
	public function rollBack()
	{
		global $conn;
		$result = mysqli_rollback($conn);
		mysqli_autocommit($conn, true);
		return $result;
	}
	
	/**
	 * 预处理语句
	 * @param string $sql SQL语句
	 * @return \mysqli_stmt
	 */
	public function prepare($sql)
	{
		global $conn;
		return mysqli_prepare($conn, $sql);
	}
	
	public static function query($table, $query, $mode = "AND", $raw = false)
	{
		global $conn;
		
		$mode = $mode == "" ? "AND" : $mode;
		
		if(isset($table) && $table !== "") {
			if(!$raw && is_array($query)) {
				$querySQL = "";
				$i = 0;
				$total = count($query);
				foreach($query as $key => $value) {
					$i++;
					$key = mysqli_real_escape_string($conn, $key);
					$value = mysqli_real_escape_string($conn, $value);
					$querySQL .= "`{$key}`='{$value}'";
					if($i < $total) {
						$querySQL .= " {$mode} ";
					}
				}
				if($total > 0) {
					$querySQL = " WHERE {$querySQL}";
				}
				$table = mysqli_real_escape_string($conn, $table);
				$querySQL = "SELECT * FROM `{$table}`{$querySQL}";
				$result = mysqli_query($conn, $querySQL);
				$error  = mysqli_error($conn);
				if(!empty($error)) {
					return $error;
				} else {
					return $result;
				}
			} else {
				$result = mysqli_query($conn, $query);
				$error  = mysqli_error($conn);
				if(!empty($error)) {
					return $error;
				} else {
					return $result;
				}
			}
		} else {
			return false;
		}
	}
	
	public static function update($table, $data, $query, $mode = "AND", $raw = false)
	{
		global $conn;
		
		$mode = $mode == "" ? "AND" : $mode;
		
		if(isset($table) && $table !== "") {
			if(!$raw && is_array($query) && is_array($data)) {
				// 处理要更新的数据
				$updateSQL = "";
				$i = 0;
				$total = count($data);
				foreach($data as $key => $value) {
					$i++;
					$key = mysqli_real_escape_string($conn, $key);
					if (is_numeric($value)) {
						$updateSQL .= "`{$key}`={$value}";
					} else {
						$value = mysqli_real_escape_string($conn, $value);
						$updateSQL .= "`{$key}`='{$value}'";
					}
					if($i < $total) {
						$updateSQL .= ", ";
					}
				}
				
				// 处理查询部分
				$querySQL = "";
				$i = 0;
				$total = count($query);
				foreach($query as $key => $value) {
					$i++;
					$key = mysqli_real_escape_string($conn, $key);
					$value = mysqli_real_escape_string($conn, $value);
					$querySQL .= "`{$key}`='{$value}'";
					if($i < $total) {
						$querySQL .= " {$mode} ";
					}
				}
				
				if($total > 0) {
					$querySQL = " WHERE {$querySQL}";
				}
				
				$table = mysqli_real_escape_string($conn, $table);
				$querySQL = "UPDATE `{$table}` SET {$updateSQL}{$querySQL}";
				mysqli_query($conn, $querySQL);
				$error  = mysqli_error($conn);
				if(!empty($error)) {
					return $error;
				} else {
					return true;
				}
			} else {
				mysqli_query($conn, $query);
				$error  = mysqli_error($conn);
				if(!empty($error)) {
					return $error;
				} else {
					return true;
				}
			}
		} else {
			return false;
		}
	}
	
	public static function delete($table, $query, $mode = "AND", $raw = false)
	{
		global $conn;
		
		$mode = $mode == "" ? "AND" : $mode;
		
		if(isset($table) && $table !== "") {
			if(!$raw && is_array($query)) {
				$querySQL = "";
				$i = 0;
				$total = count($query);
				foreach($query as $key => $value) {
					$i++;
					$key = mysqli_real_escape_string($conn, $key);
					$value = mysqli_real_escape_string($conn, $value);
					$querySQL .= "`{$key}`='{$value}'";
					if($i < $total) {
						$querySQL .= " {$mode} ";
					}
				}
				if($total > 0) {
					$querySQL = " WHERE {$querySQL}";
				}
				$table = mysqli_real_escape_string($conn, $table);
				$querySQL = "DELETE FROM `{$table}`{$querySQL}";
				mysqli_query($conn, $querySQL);
				$error = mysqli_error($conn);
				if(!empty($error)) {
					return $error;
				} else {
					return true;
				}
			} else {
				mysqli_query($conn, $query);
				$error = mysqli_error($conn);
				if(!empty($error)) {
					return $error;
				} else {
					return true;
				}
			}
		} else {
			return false;
		}
	}
	
	public static function search($table, $query, $mode = "AND", $raw = false)
	{
		global $conn;
		
		$mode = $mode == "" ? "AND" : $mode;
		
		if(isset($table) && $table !== "") {
			if(!$raw && is_array($query)) {
				$querySQL = "";
				$i = 0;
				$total = count($query);
				foreach($query as $key => $value) {
					$i++;
					$key = mysqli_real_escape_string($conn, $key);
					$value = mysqli_real_escape_string($conn, $value);
					$querySQL .= "POSITION('{$value}' IN `{$key}`)";
					if($i < $total) {
						$querySQL .= " {$mode} ";
					}
				}
				if($total > 0) {
					$querySQL = " WHERE {$querySQL}";
				}
				$table = mysqli_real_escape_string($conn, $table);
				$querySQL = "SELECT * FROM `{$table}`{$querySQL}";
				$result = mysqli_query($conn, $querySQL);
				$error  = mysqli_error($conn);
				if(!empty($error)) {
					return $error;
				} else {
					return $result;
				}
			} else {
				$result = mysqli_query($conn, $query);
				$error  = mysqli_error($conn);
				if(!empty($error)) {
					return $error;
				} else {
					return $result;
				}
			}
		} else {
			return false;
		}
	}
	
	public static function insert($table, $query, $raw = false)
	{
		global $conn;
		
		if(isset($table) && $table !== "") {
			if(!$raw && is_array($query)) {
				$queryKey = "";
				$queryValue = "";
				$i = 0;
				$total = count($query);
				foreach($query as $key => $value) {
					$i++;
					$svalue = $value;
					$key = mysqli_real_escape_string($conn, $key);
					$value = mysqli_real_escape_string($conn, $value);
					$queryKey .= "`{$key}`";
					$queryValue .= $svalue === null ? "NULL" : "'{$value}'";
					if($i < $total) {
						$queryKey .= ", ";
						$queryValue .= ", ";
					}
				}
				$table = mysqli_real_escape_string($conn, $table);
				$querySQL = "INSERT INTO `{$table}` ({$queryKey}) VALUES ({$queryValue})";
				mysqli_query($conn, $querySQL);
				$error  = mysqli_error($conn);
				if(!empty($error)) {
					return $error;
				} else {
					return true;
				}
			} else {
				mysqli_query($conn, $query);
				$error  = mysqli_error($conn);
				if(!empty($error)) {
					return $error;
				} else {
					return true;
				}
			}
		} else {
			return false;
		}
	}
	
	public static function querySingleLine($table, $query, $raw = false)
	{
		global $conn;
		
		return mysqli_fetch_array(Database::query($table, $query, $raw));
	}
	
	public static function toArray($result)
	{
		$data = Array();
		while($rw = mysqli_fetch_row($result)) {
			$data[] = $rw;
		}
		return $data;
	}
	
	public static function fetchError()
	{
		global $conn;
		return mysqli_error($conn);
	}
	
	/**
	 * 为mysqli_stmt添加fetchAll方法，模拟PDO的行为
	 * 
	 * @param \mysqli_stmt $stmt mysqli预处理语句对象
	 * @return array 查询结果数组
	 */
	public static function fetchAll($stmt) 
	{
		$result = $stmt->get_result();
		$rows = [];
		while ($row = $result->fetch_assoc()) {
			$rows[] = $row;
		}
		return $rows;
	}
	
	/**
	 * 为mysqli_stmt添加fetch方法，模拟PDO的行为
	 * 
	 * @param \mysqli_stmt $stmt mysqli预处理语句对象
	 * @return array|false 查询结果行或false
	 */
	public static function fetch($stmt) 
	{
		$result = $stmt->get_result();
		return $result->fetch_assoc();
	}
	
	public static function escape($str)
	{
		global $conn;
		return mysqli_real_escape_string($conn, $str);
	}
}