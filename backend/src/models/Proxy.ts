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
  status: 'active' | 'inactive' | 'error';
  traffic_in: number;
  traffic_out: number;
  created_at: Date;
  updated_at: Date;
}

export interface ProxyCreateData {
  username: string;
  proxy_name: string;
  proxy_type: 'tcp' | 'udp' | 'http' | 'https' | 'stcp' | 'xtcp';
  local_ip: string;
  local_port: number;
  remote_port?: number;
  custom_domain?: string;
  node_id: number;
}

export interface ProxyUpdateData {
  proxy_name?: string;
  local_ip?: string;
  local_port?: number;
  remote_port?: number;
  custom_domain?: string;
  status?: 'active' | 'inactive' | 'error';
  traffic_in?: number;
  traffic_out?: number;
}