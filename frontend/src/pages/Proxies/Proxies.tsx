import React, { useState, useEffect } from 'react';
import { Proxy } from '../../types';
import {
  PlusIcon,
  FunnelIcon,
  MagnifyingGlassIcon,
  ArrowPathIcon,
  PlayIcon,
  PauseIcon,
  PencilIcon,
  TrashIcon,
  EyeIcon,
  EyeSlashIcon,
  ServerIcon,
  GlobeAltIcon,
  LinkIcon,
  ExclamationTriangleIcon,
  CheckCircleIcon,
  ClockIcon
} from '@heroicons/react/24/outline';

const Proxies: React.FC = () => {
  const [proxies, setProxies] = useState<Proxy[]>([]);
  const [filteredProxies, setFilteredProxies] = useState<Proxy[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState<string>('all');
  const [typeFilter, setTypeFilter] = useState<string>('all');

  useEffect(() => {
    loadProxies();
  }, []);

  useEffect(() => {
    filterProxies();
  }, [proxies, searchTerm, statusFilter, typeFilter]);

  const loadProxies = async () => {
    setLoading(true);
    try {
      // 模拟数据加载
      const mockProxies: Proxy[] = [
        {
          id: 1,
          username: 'demo',
          proxy_name: '我的网站',
          proxy_type: 'http',
          local_ip: '127.0.0.1',
          local_port: 8080,
          remote_port: 80,
          custom_domain: 'demo.axiofrp.com',
          node_id: 1,
          node_name: '香港节点1',
          node_address: 'hk1.axiofrp.com',
          status: 'active',
          traffic_in: 1024000,
          traffic_out: 2048000,
          created_at: '2024-12-20T10:30:00Z',
          updated_at: '2024-12-29T10:30:00Z'
        },
        {
          id: 2,
          username: 'demo',
          proxy_name: 'SSH服务器',
          proxy_type: 'tcp',
          local_ip: '192.168.1.100',
          local_port: 22,
          remote_port: 2222,
          node_id: 2,
          node_name: '美国西部节点',
          node_address: 'usw1.axiofrp.com',
          status: 'active',
          traffic_in: 512000,
          traffic_out: 256000,
          created_at: '2024-12-22T14:15:00Z',
          updated_at: '2024-12-29T10:30:00Z'
        },
        {
          id: 3,
          username: 'demo',
          proxy_name: '游戏服务器',
          proxy_type: 'udp',
          local_ip: '192.168.1.50',
          local_port: 25565,
          remote_port: 25565,
          node_id: 3,
          node_name: '新加坡节点',
          node_address: 'sg1.axiofrp.com',
          status: 'error',
          traffic_in: 0,
          traffic_out: 0,
          created_at: '2024-12-25T09:20:00Z',
          updated_at: '2024-12-28T16:45:00Z'
        },
        {
          id: 4,
          username: 'demo',
          proxy_name: 'Web服务',
          proxy_type: 'https',
          local_ip: '127.0.0.1',
          local_port: 4433,
          custom_domain: 'web.axiofrp.com',
          node_id: 4,
          node_name: '日本东京节点',
          node_address: 'jp1.axiofrp.com',
          status: 'inactive',
          traffic_in: 256000,
          traffic_out: 128000,
          created_at: '2024-12-26T11:10:00Z',
          updated_at: '2024-12-26T11:10:00Z'
        },
        {
          id: 5,
          username: 'demo',
          proxy_name: '远程桌面',
          proxy_type: 'tcp',
          local_ip: '192.168.1.88',
          local_port: 3389,
          remote_port: 3389,
          node_id: 1,
          node_name: '香港节点1',
          node_address: 'hk1.axiofrp.com',
          status: 'active',
          traffic_in: 786432,
          traffic_out: 655360,
          created_at: '2024-12-27T16:30:00Z',
          updated_at: '2024-12-29T10:30:00Z'
        }
      ];
      
      setProxies(mockProxies);
    } catch (error) {
      console.error('加载隧道失败:', error);
    } finally {
      setLoading(false);
    }
  };

  const filterProxies = () => {
    let filtered = [...proxies];
    
    if (searchTerm) {
      filtered = filtered.filter(proxy => 
        proxy.proxy_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        proxy.local_ip.toLowerCase().includes(searchTerm.toLowerCase()) ||
        proxy.custom_domain?.toLowerCase().includes(searchTerm.toLowerCase()) ||
        proxy.node_name?.toLowerCase().includes(searchTerm.toLowerCase())
      );
    }
    
    if (statusFilter !== 'all') {
      filtered = filtered.filter(proxy => proxy.status === statusFilter);
    }
    
    if (typeFilter !== 'all') {
      filtered = filtered.filter(proxy => proxy.proxy_type === typeFilter);
    }
    
    setFilteredProxies(filtered);
  };

  const formatBytes = (bytes: number): string => {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'active':
        return <CheckCircleIcon className="w-5 h-5 text-success-400" />;
      case 'inactive':
        return <ClockIcon className="w-5 h-5 text-warning-400" />;
      case 'error':
        return <ExclamationTriangleIcon className="w-5 h-5 text-danger-400" />;
      default:
        return <ClockIcon className="w-5 h-5 text-gray-400" />;
    }
  };

  const getStatusText = (status: string) => {
    switch (status) {
      case 'active':
        return '运行中';
      case 'inactive':
        return '已暂停';
      case 'error':
        return '错误';
      default:
        return '未知';
    }
  };

  const getTypeIcon = (type: string) => {
    switch (type) {
      case 'http':
      case 'https':
        return <GlobeAltIcon className="w-4 h-4" />;
      case 'tcp':
      case 'udp':
        return <LinkIcon className="w-4 h-4" />;
      default:
        return <ServerIcon className="w-4 h-4" />;
    }
  };

  const getTypeColor = (type: string) => {
    switch (type) {
      case 'http':
        return 'text-blue-400';
      case 'https':
        return 'text-green-400';
      case 'tcp':
        return 'text-accent-400';
      case 'udp':
        return 'text-warning-400';
      default:
        return 'text-gray-400';
    }
  };

  const refreshProxies = () => {
    setLoading(true);
    setTimeout(() => {
      loadProxies();
    }, 1000);
  };

  const toggleProxyStatus = (id: number, currentStatus: string) => {
    setProxies(prev => prev.map(proxy => 
      proxy.id === id 
        ? { ...proxy, status: currentStatus === 'active' ? 'inactive' : 'active' }
        : proxy
    ));
  };

  const deleteProxy = (id: number) => {
    if (window.confirm('确定要删除这个隧道吗？此操作不可恢复。')) {
      setProxies(prev => prev.filter(proxy => proxy.id !== id));
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="spinner w-12 h-12"></div>
      </div>
    );
  }

  const uniqueTypes = [...new Set(proxies.map(proxy => proxy.proxy_type))];

  return (
    <div className="space-y-6">
      {/* 页面标题 */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 className="text-3xl font-bold text-white">隧道管理</h1>
          <p className="text-gray-400 mt-1">创建和管理您的内网穿透隧道</p>
        </div>
        <div className="flex items-center space-x-3 mt-4 sm:mt-0">
          <button 
            onClick={refreshProxies}
            className="btn btn-accent flex items-center space-x-2"
          >
            <ArrowPathIcon className="w-4 h-4" />
            <span>刷新</span>
          </button>
          <button className="btn btn-primary flex items-center space-x-2">
            <PlusIcon className="w-4 h-4" />
            <span>创建隧道</span>
          </button>
        </div>
      </div>

      {/* 统计卡片 */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div className="card-glass p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-400 mb-1">总隧道数</p>
              <p className="text-3xl font-bold text-white">{proxies.length}</p>
            </div>
            <div className="p-3 bg-gradient-to-br from-primary-600/20 to-accent-600/20 rounded-xl">
              <ServerIcon className="w-6 h-6 text-primary-400" />
            </div>
          </div>
        </div>
        
        <div className="card-glass p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-400 mb-1">运行中</p>
              <p className="text-3xl font-bold text-success-400">
                {proxies.filter(p => p.status === 'active').length}
              </p>
            </div>
            <div className="p-3 bg-success-600/20 rounded-xl">
              <PlayIcon className="w-6 h-6 text-success-400" />
            </div>
          </div>
        </div>
        
        <div className="card-glass p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-400 mb-1">已暂停</p>
              <p className="text-3xl font-bold text-warning-400">
                {proxies.filter(p => p.status === 'inactive').length}
              </p>
            </div>
            <div className="p-3 bg-warning-600/20 rounded-xl">
              <PauseIcon className="w-6 h-6 text-warning-400" />
            </div>
          </div>
        </div>
        
        <div className="card-glass p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-400 mb-1">总流量</p>
              <p className="text-2xl font-bold text-accent-400">
                {formatBytes(proxies.reduce((sum, p) => sum + p.traffic_in + p.traffic_out, 0))}
              </p>
            </div>
            <div className="p-3 bg-accent-600/20 rounded-xl">
              <GlobeAltIcon className="w-6 h-6 text-accent-400" />
            </div>
          </div>
        </div>
      </div>

      {/* 搜索和过滤器 */}
      <div className="card-glass p-6">
        <div className="flex flex-col lg:flex-row gap-4">
          {/* 搜索框 */}
          <div className="flex-1 relative">
            <MagnifyingGlassIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input
              type="text"
              placeholder="搜索隧道名称、IP或域名..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="input pl-10 w-full"
            />
          </div>
          
          {/* 状态过滤器 */}
          <div className="relative">
            <FunnelIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
            <select
              value={statusFilter}
              onChange={(e) => setStatusFilter(e.target.value)}
              className="input pl-10 appearance-none cursor-pointer"
            >
              <option value="all">所有状态</option>
              <option value="active">运行中</option>
              <option value="inactive">已暂停</option>
              <option value="error">错误</option>
            </select>
          </div>
          
          {/* 类型过滤器 */}
          <div className="relative">
            <ServerIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
            <select
              value={typeFilter}
              onChange={(e) => setTypeFilter(e.target.value)}
              className="input pl-10 appearance-none cursor-pointer"
            >
              <option value="all">所有类型</option>
              {uniqueTypes.map(type => (
                <option key={type} value={type}>{type.toUpperCase()}</option>
              ))}
            </select>
          </div>
        </div>
      </div>

      {/* 隧道列表 */}
      <div className="card-glass overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead>
              <tr className="border-b border-dark-700">
                <th className="text-left px-6 py-4 text-sm font-medium text-gray-400">隧道信息</th>
                <th className="text-left px-6 py-4 text-sm font-medium text-gray-400">类型</th>
                <th className="text-left px-6 py-4 text-sm font-medium text-gray-400">节点</th>
                <th className="text-left px-6 py-4 text-sm font-medium text-gray-400">状态</th>
                <th className="text-left px-6 py-4 text-sm font-medium text-gray-400">流量</th>
                <th className="text-left px-6 py-4 text-sm font-medium text-gray-400">操作</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-dark-700">
              {filteredProxies.map((proxy) => (
                <tr key={proxy.id} className="hover:bg-dark-800/50 transition-colors">
                  <td className="px-6 py-4">
                    <div>
                      <div className="font-medium text-white mb-1">{proxy.proxy_name}</div>
                      <div className="text-sm text-gray-400">
                        {proxy.local_ip}:{proxy.local_port} → 
                        {proxy.custom_domain || `:${proxy.remote_port}`}
                      </div>
                    </div>
                  </td>
                  
                  <td className="px-6 py-4">
                    <div className="flex items-center space-x-2">
                      <div className={`p-1 rounded ${getTypeColor(proxy.proxy_type)} bg-dark-800`}>
                        {getTypeIcon(proxy.proxy_type)}
                      </div>
                      <span className={`text-sm font-medium ${getTypeColor(proxy.proxy_type)}`}>
                        {proxy.proxy_type.toUpperCase()}
                      </span>
                    </div>
                  </td>
                  
                  <td className="px-6 py-4">
                    <div className="text-sm">
                      <div className="text-white">{proxy.node_name}</div>
                      <div className="text-gray-400 text-xs">{proxy.node_address}</div>
                    </div>
                  </td>
                  
                  <td className="px-6 py-4">
                    <div className="flex items-center space-x-2">
                      {getStatusIcon(proxy.status)}
                      <span className={`text-sm font-medium ${
                        proxy.status === 'active' ? 'text-success-400' :
                        proxy.status === 'inactive' ? 'text-warning-400' :
                        'text-danger-400'
                      }`}>
                        {getStatusText(proxy.status)}
                      </span>
                    </div>
                  </td>
                  
                  <td className="px-6 py-4">
                    <div className="text-sm">
                      <div className="text-white">↑ {formatBytes(proxy.traffic_out)}</div>
                      <div className="text-gray-400 text-xs">↓ {formatBytes(proxy.traffic_in)}</div>
                    </div>
                  </td>
                  
                  <td className="px-6 py-4">
                    <div className="flex items-center space-x-2">
                      <button
                        onClick={() => toggleProxyStatus(proxy.id, proxy.status)}
                        className={`p-2 rounded-lg transition-colors ${
                          proxy.status === 'active' 
                            ? 'text-warning-400 hover:bg-warning-600/20' 
                            : 'text-success-400 hover:bg-success-600/20'
                        }`}
                        title={proxy.status === 'active' ? '暂停' : '启动'}
                      >
                        {proxy.status === 'active' ? (
                          <PauseIcon className="w-4 h-4" />
                        ) : (
                          <PlayIcon className="w-4 h-4" />
                        )}
                      </button>
                      
                      <button
                        className="p-2 text-accent-400 hover:bg-accent-600/20 rounded-lg transition-colors"
                        title="编辑"
                      >
                        <PencilIcon className="w-4 h-4" />
                      </button>
                      
                      <button
                        className="p-2 text-danger-400 hover:bg-danger-600/20 rounded-lg transition-colors"
                        title="删除"
                        onClick={() => deleteProxy(proxy.id)}
                      >
                        <TrashIcon className="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
        
        {/* 无结果状态 */}
        {filteredProxies.length === 0 && !loading && (
          <div className="p-12 text-center">
            <div className="w-16 h-16 bg-gradient-to-br from-gray-600/20 to-gray-700/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
              <ServerIcon className="w-8 h-8 text-gray-400" />
            </div>
            <h3 className="text-lg font-medium text-white mb-2">未找到匹配的隧道</h3>
            <p className="text-gray-400 mb-6">尝试调整搜索条件或创建新的隧道</p>
            <div className="flex items-center justify-center space-x-3">
              <button 
                onClick={() => {
                  setSearchTerm('');
                  setStatusFilter('all');
                  setTypeFilter('all');
                }}
                className="btn btn-secondary"
              >
                清除筛选条件
              </button>
              <button className="btn btn-primary">
                创建隧道
              </button>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default Proxies;