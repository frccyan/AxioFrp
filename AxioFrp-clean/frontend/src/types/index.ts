// 用户相关类型
export interface User {
  id: number;
  username: string;
  email: string;
  group_name: string;
  traffic_used: number;
  traffic_limit: number;
  balance: number;
  status: 'active' | 'inactive' | 'banned';
  is_admin: boolean;
  last_login: string | null;
  created_at: string;
  updated_at: string;
}

export interface LoginRequest {
  username: string;
  password: string;
}

export interface RegisterRequest {
  username: string;
  email: string;
  password: string;
  confirmPassword: string;
}

export interface AuthResponse {
  success: boolean;
  message: string;
  data?: {
    user: User;
    token: string;
  };
}

// 隧道相关类型
export interface Proxy {
  id: number;
  username: string;
  proxy_name: string;
  proxy_type: 'tcp' | 'udp' | 'http' | 'https' | 'stcp' | 'xtcp';
  local_ip: string;
  local_port: number;
  remote_port?: number;
  custom_domain?: string;
  node_id: number;
  node_name?: string;
  node_address?: string;
  status: 'active' | 'inactive' | 'error';
  traffic_in: number;
  traffic_out: number;
  created_at: string;
  updated_at: string;
}

export interface ProxyCreateRequest {
  proxy_name: string;
  proxy_type: 'tcp' | 'udp' | 'http' | 'https' | 'stcp' | 'xtcp';
  local_ip: string;
  local_port: number;
  remote_port?: number;
  custom_domain?: string;
  node_id: number;
}

export interface ProxyStats {
  total: number;
  active: number;
  totalTraffic: number;
}

// 节点相关类型
export interface Node {
  id: number;
  name: string;
  address: string;
  port: number;
  status: 'online' | 'offline' | 'maintenance';
  max_connections: number;
  current_connections?: number;
  region?: string;
  created_at: string;
  updated_at: string;
}

// 套餐相关类型
export interface Package {
  id: number;
  name: string;
  description: string;
  price: number;
  duration_days: number;
  traffic_limit: number;
  max_proxies: number;
  status: 'active' | 'inactive';
  created_at: string;
}

export interface PackageOrder {
  id: number;
  username: string;
  package_id: number;
  package_name?: string;
  amount: number;
  status: 'pending' | 'paid' | 'expired' | 'cancelled';
  expires_at: string;
  created_at: string;
}

// API 响应通用类型
export interface ApiResponse<T = any> {
  success: boolean;
  message: string;
  data?: T;
}

export interface PaginatedResponse<T> {
  data: T[];
  pagination: {
    page: number;
    limit: number;
    total: number;
  };
}

// 表单验证相关
export interface ValidationError {
  field: string;
  message: string;
}

// 系统状态
export interface SystemStatus {
  version: string;
  timestamp: string;
  message: string;
}