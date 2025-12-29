export interface Node {
  id: number;
  name: string;
  address: string;
  port: number;
  token: string;
  status: 'online' | 'offline' | 'maintenance';
  max_connections: number;
  region?: string;
  created_at: Date;
  updated_at: Date;
}

export interface NodeCreateData {
  name: string;
  address: string;
  port: number;
  token: string;
  max_connections?: number;
  region?: string;
}

export interface NodeUpdateData {
  name?: string;
  address?: string;
  port?: number;
  token?: string;
  status?: 'online' | 'offline' | 'maintenance';
  max_connections?: number;
  region?: string;
}