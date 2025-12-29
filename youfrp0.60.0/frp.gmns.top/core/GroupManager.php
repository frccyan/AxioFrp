<?php
namespace chhcn;

use chhcn;

class GroupManager {
	
	public function isGroupExist($name)
	{
		return Database::querySingleLine("groups", Array("name" => $name)) ? true : false;
	}
	
	public function getGroupInfo($id)
	{
		return Database::querySingleLine("groups", Array("id" => $id));
	}
	
	public function getGroupInfoByName($name)
	{
		return Database::querySingleLine("groups", Array("name" => $name));
	}
	
	public function updateGroup($id, $data)
	{
		if($this->getGroupInfo($id)) {
			return Database::update("groups", $data, Array("id" => $id));
		} else {
			return false;
		}
	}
	
	public function getTotalGroups()
	{
		$rs = Database::toArray(Database::query("groups", Array()));
		return count($rs);
	}
	
	public function getAllGroups()
	{
		return Database::toArray(Database::query("groups", Array()));
	}
	
	public function addGroup($data)
	{
		return Database::insert("groups", $data);
	}
	
	public function deleteGroup($data)
	{
		// 检查是否有用户在使用这个组
		$group = $this->getGroupInfo($data);
		if (!$group) {
			return false;
		}
		
		$users = Database::toArray(Database::query("users", Array("group" => $group['name'])));
		if (count($users) > 0) {
			return "该用户组下有用户，不能删除";
		}
		
		return Database::delete("groups", Array("id" => $data));
	}
} 