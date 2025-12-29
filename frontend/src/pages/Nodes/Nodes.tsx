import React, { useState, useEffect } from 'react';
import { Node } from '../../types';
import {
  ServerIcon,
  MapPinIcon,
  SignalIcon,
  CheckCircleIcon,
  XCircleIcon,
  ClockIcon,
  FunnelIcon,
  MagnifyingGlassIcon,
  ArrowPathIcon,
  GlobeAltIcon
} from '@heroicons/react/24/outline';

const Nodes: React.FC = () => {
  const [nodes, setNodes] = useState<Node[]>([]);
  const [filteredNodes, setFilteredNodes] = useState<Node[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState<string>('all');
  const [regionFilter, setRegionFilter] = useState<string>('all');

  useEffect(() => {
    loadNodes();
  }, []);

  useEffect(() => {
    filterNodes();
  }, [nodes, searchTerm, statusFilter, regionFilter]);

  const loadNodes = async () => {
    setLoading(true);
    try {
      // 模拟数据加载
      const mockNodes: Node[] = [
        {
          id: 1,
          name: '香港节点1',
          address: 'hk1.axiofrp.com',
          port: 7000,
          status: 'online',
          max_connections: 1000,
          current_connections: 256,
          region: '香港',
          created_at: '2024-01-15T10:30:00Z',
          updated_at: '2024-12-29T10:30:00Z'
        },
        {
          id: 2,
          name: '美国西部节点',
          address: 'usw1.axiofrp.com',
          port: 7000,
          status: 'online',
          max_connections: 800,
          current_connections: 128,
          region: '美国西部',
          created_at: '2024-02-20T15:45:00Z',
          updated_at: '2024-12-29T10:30:00Z'
        },
        {
          id: 3,
          name: '新加坡节点',
          address: 'sg1.axiofrp.com',
          port: 7000,
          status: 'maintenance',
          max_connections: 600,
          current_connections: 0,
          region: '新加坡',
          created_at: '2024-03-10T09:20:00Z',
          updated_at: '2024-12-29T08:00:00Z'
        },
        {
          id: 4,
          name: '日本东京节点',
          address: 'jp1.axiofrp.com',
          port: 7000,
          status: 'online',
          max_connections: 500,
          current_connections: 89,
          region: '日本',
          created_at: '2024-04-05T14:15:00Z',
          updated_at: '2024-12-29T10:30:00Z'
        },
        {
          id: 5,
          name: '欧洲德国节点',
          address: 'de1.axiofrp.com',
          port: 7000,
          status: 'offline',
          max_connections: 750,
          current_connections: 0,
          region: '德国',
          created_at: '2024-05-12T11:30:00Z',
          updated_at: '2024-12-28T16:45:00Z'
        }
      ];
      
      setNodes(mockNodes);
    } catch (error) {
      console.error('加载节点失败:', error);
    } finally {
      setLoading(false);
    }
  };

  const filterNodes = () => {
    let filtered = [...nodes];
    
    if (searchTerm) {
      filtered = filtered.filter(node => 
        node.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        node.address.toLowerCase().includes(searchTerm.toLowerCase()) ||
        node.region.toLowerCase().includes(searchTerm.toLowerCase())
      );
    }
    
    if (statusFilter !== 'all') {
      filtered = filtered.filter(node => node.status === statusFilter);
    }
    
    if (regionFilter !== 'all') {
      filtered = filtered.filter(node => node.region === regionFilter);
    }
    
    setFilteredNodes(filtered);
  };

  const getUniqueRegions = () => {
    return [...new Set(nodes.map(node => node.region))];
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'online':
        return <CheckCircleIcon className="w-5 h-5 text-success-400" />;
      case 'offline':
        return <XCircleIcon className="w-5 h-5 text-danger-400" />;
      case 'maintenance':
        return <ClockIcon className="w-5 h-5 text-warning-400" />;
      default:
        return <SignalIcon className="w-5 h-5 text-gray-400" />;
    }
  };

  const getStatusText = (status: string) => {
    switch (status) {
      case 'online':
        return '在线';
      case 'offline':
        return '离线';
      case 'maintenance':
        return '维护中';
      default:
        return '未知';
    }
  };

  const getConnectionPercentage = (node: Node) => {
    if (node.max_connections === 0) return 0;
    return Math.round((node.current_connections || 0) / node.max_connections * 100);
  };

  const getConnectionColor = (percentage: number) => {
    if (percentage < 50) return 'success';
    if (percentage < 80) return 'warning';
    return 'danger';
  };

  const refreshNodes = () => {
    setLoading(true);
    setTimeout(() => {
      loadNodes();
    }, 1000);
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="spinner w-12 h-12"></div>
      </div>
    );
  }

  const uniqueRegions = getUniqueRegions();

  return (
    <div className="space-y-6">
      {/* 页面标题 */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 className="text-3xl font-bold text-white">节点管理</h1>
          <p className="text-gray-400 mt-1">监控和管理全球分布的内网穿透节点</p>
        </div>
        <button 
          onClick={refreshNodes}
          className="mt-4 sm:mt-0 btn btn-accent flex items-center space-x-2"
        >
          <ArrowPathIcon className="w-4 h-4" />
          <span>刷新状态</span>
        </button>
      </div>

      {/* 统计卡片 */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="card-glass p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-400 mb-1">总节点数</p>
              <p className="text-3xl font-bold text-white">{nodes.length}</p>
            </div>
            <div className="p-3 bg-gradient-to-br from-primary-600/20 to-accent-600/20 rounded-xl">
              <ServerIcon className="w-6 h-6 text-primary-400" />
            </div>
          </div>
        </div>
        
        <div className="card-glass p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-400 mb-1">在线节点</p>
              <p className="text-3xl font-bold text-success-400">
                {nodes.filter(n => n.status === 'online').length}
              </p>
            </div>
            <div className="p-3 bg-success-600/20 rounded-xl">
              <CheckCircleIcon className="w-6 h-6 text-success-400" />
            </div>
          </div>
        </div>
        
        <div className="card-glass p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-400 mb-1">异常节点</p>
              <p className="text-3xl font-bold text-danger-400">
                {nodes.filter(n => n.status === 'offline' || n.status === 'maintenance').length}
              </p>
            </div>
            <div className="p-3 bg-danger-600/20 rounded-xl">
              <XCircleIcon className="w-6 h-6 text-danger-400" />
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
              placeholder="搜索节点名称、地址或地区..."
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
              <option value="online">在线</option>
              <option value="offline">离线</option>
              <option value="maintenance">维护中</option>
            </select>
          </div>
          
          {/* 地区过滤器 */}
          <div className="relative">
            <MapPinIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
            <select
              value={regionFilter}
              onChange={(e) => setRegionFilter(e.target.value)}
              className="input pl-10 appearance-none cursor-pointer"
            >
              <option value="all">所有地区</option>
              {uniqueRegions.map(region => (
                <option key={region} value={region}>{region}</option>
              ))}
            </select>
          </div>
        </div>
      </div>

      {/* 节点列表 */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {filteredNodes.map((node) => {
          const connectionPercentage = getConnectionPercentage(node);
          const connectionColor = getConnectionColor(connectionPercentage);
          
          return (
            <div key={node.id} className="card-glass p-6 hover:scale-105 transition-all duration-300">
              {/* 节点头部 */}
              <div className="flex items-start justify-between mb-4">
                <div className="flex items-center space-x-3">
                  <div className="relative">
                    <div className={`w-12 h-12 rounded-xl flex items-center justify-center ${
                      node.status === 'online' ? 'bg-success-600/20' :
                      node.status === 'maintenance' ? 'bg-warning-600/20' :
                      'bg-danger-600/20'
                    }`}>
                      <GlobeAltIcon className={`w-6 h-6 ${
                        node.status === 'online' ? 'text-success-400' :
                        node.status === 'maintenance' ? 'text-warning-400' :
                        'text-danger-400'
                      }`} />
                    </div>
                    {getStatusIcon(node.status)}
                  </div>
                  <div>
                    <h3 className="text-lg font-semibold text-white">{node.name}</h3>
                    <p className="text-sm text-gray-400">{node.region}</p>
                  </div>
                </div>
              </div>
              
              {/* 节点信息 */}
              <div className="space-y-3 mb-4">
                <div>
                  <p className="text-xs text-gray-400 mb-1">服务器地址</p>
                  <p className="text-sm text-gray-300 font-mono">{node.address}:{node.port}</p>
                </div>
                
                <div>
                  <p className="text-xs text-gray-400 mb-1">连接状态</p>
                  <div className="flex items-center justify-between">
                    <span className={`text-sm font-medium ${
                      node.status === 'online' ? 'text-success-400' :
                      node.status === 'maintenance' ? 'text-warning-400' :
                      'text-danger-400'
                    }`}>
                      {getStatusText(node.status)}
                    </span>
                  </div>
                </div>
                
                <div>
                  <p className="text-xs text-gray-400 mb-1">连接使用率</p>
                  <div className="flex items-center space-x-2">
                    <div className="flex-1 bg-dark-700 rounded-full h-2 overflow-hidden">
                      <div 
                        className={`h-2 rounded-full transition-all duration-500 ${
                          connectionColor === 'success' ? 'bg-success-600' :
                          connectionColor === 'warning' ? 'bg-warning-600' :
                          'bg-danger-600'
                        }`}
                        style={{ width: `${connectionPercentage}%` }}
                      />
                    </div>
                    <span className="text-xs text-gray-400 min-w-[3rem] text-right">
                      {node.current_connections}/{node.max_connections}
                    </span>
                  </div>
                </div>
              </div>
              
              {/* 节点操作 */}
              <div className="flex items-center justify-between pt-4 border-t border-dark-700">
                <span className="text-xs text-gray-500">
                  更新时间: {new Date(node.updated_at).toLocaleString()}
                </span>
                <button className="btn btn-ghost text-xs">
                  查看详情
                </button>
              </div>
            </div>
          );
        })}
      </div>
      
      {/* 无结果状态 */}
      {filteredNodes.length === 0 && !loading && (
        <div className="card-glass p-12 text-center">
          <div className="w-16 h-16 bg-gradient-to-br from-gray-600/20 to-gray-700/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <ServerIcon className="w-8 h-8 text-gray-400" />
          </div>
          <h3 className="text-lg font-medium text-white mb-2">未找到匹配的节点</h3>
          <p className="text-gray-400 mb-6">尝试调整搜索条件或筛选器</p>
          <button 
            onClick={() => {
              setSearchTerm('');
              setStatusFilter('all');
              setRegionFilter('all');
            }}
            className="btn btn-secondary"
          >
            清除筛选条件
          </button>
        </div>
      )}
    </div>
  );
};

export default Nodes;