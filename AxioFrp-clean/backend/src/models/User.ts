export interface User {
  id: number;
  username: string;
  email: string;
  password: string;
  group_name: string;
  traffic_used: number;
  traffic_limit: number;
  balance: number;
  status: 'active' | 'inactive' | 'banned';
  last_login: Date | null;
  created_at: Date;
  updated_at: Date;
}

export interface UserCreateData {
  username: string;
  email: string;
  password: string;
  group_name?: string;
  traffic_limit?: number;
  balance?: number;
}

export interface UserUpdateData {
  email?: string;
  group_name?: string;
  traffic_limit?: number;
  balance?: number;
  status?: 'active' | 'inactive' | 'banned';
  last_login?: Date;
}