import axios, { AxiosInstance, AxiosResponse } from 'axios';
import { 
  ApiResponse, 
  LoginRequest, 
  RegisterRequest, 
  User, 
  Proxy, 
  ProxyCreateRequest, 
  ProxyStats,
  Node,
  Package,
  PackageOrder
} from '../types';

class ApiService {
  private client: AxiosInstance;

  constructor() {
    this.client = axios.create({
      baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
      timeout: 10000,
      headers: {
        'Content-Type': 'application/json',
      },
    });

    // 请求拦截器 - 添加认证 token
    this.client.interceptors.request.use(
      (config) => {
        const token = localStorage.getItem('axiofrp_token');
        if (token) {
          config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
      },
      (error) => {
        return Promise.reject(error);
      }
    );

    // 响应拦截器 - 统一错误处理
    this.client.interceptors.response.use(
      (response) => response,
      (error) => {
        if (error.response?.status === 401) {
          // Token 过期或无效，清除本地存储并跳转到登录页
          localStorage.removeItem('axiofrp_token');
          localStorage.removeItem('axiofrp_user');
          window.location.href = '/login';
        }
        return Promise.reject(error);
      }
    );
  }

  // 通用请求方法
  private async request<T>(config: any): Promise<ApiResponse<T>> {
    try {
      const response: AxiosResponse<ApiResponse<T>> = await this.client(config);
      return response.data;
    } catch (error: any) {
      return {
        success: false,
        message: error.response?.data?.message || error.message || '请求失败'
      };
    }
  }

  // 认证相关 API
  async login(data: LoginRequest): Promise<ApiResponse<{ user: User; token: string }>> {
    return this.request({
      method: 'POST',
      url: '/auth/login',
      data
    });
  }

  async register(data: RegisterRequest): Promise<ApiResponse<{ user: User; token: string }>> {
    return this.request({
      method: 'POST',
      url: '/auth/register',
      data
    });
  }

  async getProfile(): Promise<ApiResponse<User>> {
    return this.request({
      method: 'GET',
      url: '/auth/profile'
    });
  }

  async changePassword(data: { oldPassword: string; newPassword: string; confirmPassword: string }): Promise<ApiResponse> {
    return this.request({
      method: 'POST',
      url: '/auth/change-password',
      data
    });
  }

  async logout(): Promise<ApiResponse> {
    return this.request({
      method: 'POST',
      url: '/auth/logout'
    });
  }

  // 用户相关 API
  async getUsers(page: number = 1, limit: number = 20): Promise<ApiResponse<{ users: User[]; total: number }>> {
    return this.request({
      method: 'GET',
      url: '/users',
      params: { page, limit }
    });
  }

  // 隧道相关 API
  async getProxies(): Promise<ApiResponse<Proxy[]>> {
    return this.request({
      method: 'GET',
      url: '/proxies'
    });
  }

  async createProxy(data: ProxyCreateRequest): Promise<ApiResponse<Proxy>> {
    return this.request({
      method: 'POST',
      url: '/proxies',
      data
    });
  }

  async updateProxy(id: number, data: Partial<Proxy>): Promise<ApiResponse<Proxy>> {
    return this.request({
      method: 'PUT',
      url: `/proxies/${id}`,
      data
    });
  }

  async deleteProxy(id: number): Promise<ApiResponse> {
    return this.request({
      method: 'DELETE',
      url: `/proxies/${id}`
    });
  }

  async getProxyConfig(id: number): Promise<ApiResponse<{ config: string; filename: string }>> {
    return this.request({
      method: 'GET',
      url: `/proxies/${id}/config`
    });
  }

  async toggleProxy(id: number, action: 'start' | 'stop'): Promise<ApiResponse<Proxy>> {
    return this.request({
      method: 'POST',
      url: `/proxies/${id}/toggle`,
      data: { action }
    });
  }

  async getProxyStats(): Promise<ApiResponse<ProxyStats>> {
    return this.request({
      method: 'GET',
      url: '/proxies/stats'
    });
  }

  // 节点相关 API
  async getNodes(): Promise<ApiResponse<Node[]>> {
    return this.request({
      method: 'GET',
      url: '/nodes'
    });
  }

  async getNode(id: number): Promise<ApiResponse<Node>> {
    return this.request({
      method: 'GET',
      url: `/nodes/${id}`
    });
  }

  // 套餐相关 API
  async getPackages(): Promise<ApiResponse<Package[]>> {
    return this.request({
      method: 'GET',
      url: '/packages'
    });
  }

  async purchasePackage(packageId: number): Promise<ApiResponse<PackageOrder>> {
    return this.request({
      method: 'POST',
      url: '/packages/purchase',
      data: { package_id: packageId }
    });
  }

  async getPackageOrders(page: number = 1, limit: number = 10): Promise<ApiResponse<{ orders: PackageOrder[]; pagination: any }>> {
    return this.request({
      method: 'GET',
      url: '/packages/orders',
      params: { page, limit }
    });
  }

  // 系统健康检查
  async healthCheck(): Promise<ApiResponse<{ version: string; timestamp: string; message: string }>> {
    return this.request({
      method: 'GET',
      url: '/health'
    });
  }

  // 工具方法
  setToken(token: string): void {
    localStorage.setItem('axiofrp_token', token);
  }

  getToken(): string | null {
    return localStorage.getItem('axiofrp_token');
  }

  removeToken(): void {
    localStorage.removeItem('axiofrp_token');
    localStorage.removeItem('axiofrp_user');
  }

  isAuthenticated(): boolean {
    return !!this.getToken();
  }
}

export default new ApiService();