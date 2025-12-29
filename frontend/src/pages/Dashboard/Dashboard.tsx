import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { useAuthStore } from '../../stores/auth';
import api from '../../services/api';
import { Proxy, ProxyStats, Package } from '../../types';
import {
  ServerIcon,
  ChartBarIcon,
  GlobeAltIcon,
  CurrencyDollarIcon,
  ArrowTrendingUpIcon,
  PlusIcon,
  RocketLaunchIcon,
  SparklesIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon
} from '@heroicons/react/24/outline';

const Dashboard: React.FC = () => {
  const { user } = useAuthStore();
  const [proxies, setProxies] = useState<Proxy[]>([]);
  const [stats, setStats] = useState<ProxyStats>({ total: 0, active: 0, totalTraffic: 0 });
  const [packages, setPackages] = useState<Package[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadDashboardData();
  }, []);

  const loadDashboardData = async () => {
    try {
      const [proxiesRes, statsRes, packagesRes] = await Promise.all([
        api.getProxies(),
        api.getProxyStats(),
        api.getPackages()
      ]);

      if (proxiesRes.success) setProxies(proxiesRes.data || []);
      if (statsRes.success) setStats(statsRes.data || { total: 0, active: 0, totalTraffic: 0 });
      if (packagesRes.success) setPackages(packagesRes.data || []);
    } catch (error) {
      console.error('加载仪表板数据失败:', error);
    } finally {
      setLoading(false);
    }
  };

  const formatBytes = (bytes: number): string => {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  };

  const getTrafficPercentage = (): number => {
    if (!user || user.traffic_limit === 0) return 0;
    return Math.min((user.traffic_used / user.traffic_limit) * 100, 100);
  };

  const getTrafficColor = (percentage: number) => {
    if (percentage < 70) return 'success';
    if (percentage < 90) return 'warning';
    return 'danger';
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="spinner w-12 h-12"></div>
      </div>
    );
  }

  const trafficPercentage = getTrafficPercentage();
  const trafficColor = getTrafficColor(trafficPercentage);

  return (
    <div className="space-y-8">
      {/* 欢迎区域 */}
      <div className="relative overflow-hidden card-glass p-8">
        <div className="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-gradient-to-br from-primary-600/20 to-accent-600/20 rounded-full blur-2xl"></div>
        <div className="relative">
          <div className="flex items-center space-x-4">
            <div className="relative">
              <div className="w-16 h-16 bg-gradient-to-br from-primary-600 to-accent-600 rounded-2xl flex items-center justify-center shadow-glow">
                <SparklesIcon className="w-8 h-8 text-white" />
              </div>
              <div className="absolute -bottom-1 -right-1 bg-success-500 rounded-full p-1">
                <CheckCircleIcon className="w-4 h-4 text-white" />
              </div>
            </div>
            <div>
              <h1 className="text-3xl font-bold text-white mb-2">
                欢迎回来，{user?.username}！
              </h1>
              <p className="text-gray-300 max-w-2xl">
                这是您的现代化内网穿透管理面板，可以在这里高效管理您的隧道、监控使用情况，并享受极致的内网穿透体验。
              </p>
            </div>
          </div>
        </div>
      </div>

      {/* 统计卡片 */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div className="card-glass p-6 hover:scale-105 transition-transform duration-300">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-400 mb-1">隧道总数</p>
              <p className="text-3xl font-bold text-white">{stats.total}</p>
              <p className="text-xs text-gray-500 mt-1">活跃 {stats.active} 个</p>
            </div>
            <div className="p-3 bg-gradient-to-br from-primary-600 to-primary-700 rounded-xl shadow-glow">
              <ServerIcon className="w-6 h-6 text-white" />
            </div>
          </div>
        </div>

        <div className="card-glass p-6 hover:scale-105 transition-transform duration-300">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-400 mb-1">活跃隧道</p>
              <p className="text-3xl font-bold text-white">{stats.active}</p>
              <p className="text-xs text-gray-500 mt-1">运行状态良好</p>
            </div>
            <div className="p-3 bg-gradient-to-br from-success-600 to-success-700 rounded-xl shadow-glow">
              <GlobeAltIcon className="w-6 h-6 text-white" />
            </div>
          </div>
        </div>

        <div className="card-glass p-6 hover:scale-105 transition-transform duration-300">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-400 mb-1">总流量</p>
              <p className="text-3xl font-bold text-white">{formatBytes(stats.totalTraffic)}</p>
              <p className="text-xs text-gray-500 mt-1">累计使用量</p>
            </div>
            <div className="p-3 bg-gradient-to-br from-accent-600 to-accent-700 rounded-xl shadow-glow">
              <ChartBarIcon className="w-6 h-6 text-white" />
            </div>
          </div>
        </div>

        <div className="card-glass p-6 hover:scale-105 transition-transform duration-300">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-400 mb-1">账户余额</p>
              <p className="text-3xl font-bold text-white">¥{user?.balance?.toFixed(2) || '0.00'}</p>
              <p className="text-xs text-gray-500 mt-1">可用余额</p>
            </div>
            <div className="p-3 bg-gradient-to-br from-warning-600 to-warning-700 rounded-xl shadow-glow">
              <CurrencyDollarIcon className="w-6 h-6 text-white" />
            </div>
          </div>
        </div>
      </div>

      {/* 流量使用情况 */}
      <div className="card-glass p-6">
        <div className="flex items-center justify-between mb-6">
          <div className="flex items-center space-x-3">
            <div className="p-2 bg-gradient-to-br from-primary-600/20 to-accent-600/20 rounded-lg">
              <ArrowTrendingUpIcon className="w-5 h-5 text-primary-400" />
            </div>
            <div>
              <h3 className="text-lg font-semibold text-white">流量使用情况</h3>
              <p className="text-sm text-gray-400">
                {formatBytes(user?.traffic_used || 0)} / {formatBytes(user?.traffic_limit || 0)}
              </p>
            </div>
          </div>
          <div className={`px-3 py-1 rounded-full text-sm font-medium ${
            trafficColor === 'success' ? 'bg-success-900/30 text-success-300' :
            trafficColor === 'warning' ? 'bg-warning-900/30 text-warning-300' :
            'bg-danger-900/30 text-danger-300'
          }`}>
            {trafficPercentage.toFixed(1)}% 已使用
          </div>
        </div>
        
        <div className="relative">
          <div className="w-full bg-dark-700 rounded-full h-3 overflow-hidden">
            <div 
              className={`h-3 rounded-full transition-all duration-500 ${
                trafficColor === 'success' ? 'bg-gradient-to-r from-success-600 to-success-700' :
                trafficColor === 'warning' ? 'bg-gradient-to-r from-warning-600 to-warning-700' :
                'bg-gradient-to-r from-danger-600 to-danger-700'
              }`}
              style={{ width: `${trafficPercentage}%` }}
            />
          </div>
          {trafficPercentage > 80 && (
            <div className="absolute -top-8 right-0 bg-danger-600 text-white text-xs px-2 py-1 rounded flex items-center space-x-1">
              <ExclamationTriangleIcon className="w-3 h-3" />
              <span>流量即将用尽</span>
            </div>
          )}
        </div>
        
        {trafficPercentage > 80 && (
          <div className="mt-4 p-3 bg-danger-900/20 border border-danger-800/50 rounded-lg">
            <p className="text-sm text-danger-300">
              您的流量使用量已超过80%，建议及时升级套餐以避免服务中断。
            </p>
          </div>
        )}
      </div>

      {/* 最近隧道 */}
      <div className="card-glass">
        <div className="card-header">
          <div className="flex items-center space-x-3">
            <div className="p-2 bg-gradient-to-br from-primary-600/20 to-accent-600/20 rounded-lg">
              <ServerIcon className="w-5 h-5 text-primary-400" />
            </div>
            <div>
              <h3 className="text-lg font-semibold text-white">最近隧道</h3>
              <p className="text-sm text-gray-400">管理您的内网穿透服务</p>
            </div>
          </div>
          <Link to="/proxies" className="btn btn-accent text-sm flex items-center space-x-2">
            <span>查看全部</span>
            <ArrowTrendingUpIcon className="w-4 h-4" />
          </Link>
        </div>
        
        <div className="card-body">
          {proxies.length > 0 ? (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {proxies.slice(0, 6).map((proxy) => (
                <div key={proxy.id} className="bg-dark-800/50 border border-dark-700 rounded-lg p-4 hover:border-primary-700/50 transition-all duration-300">
                  <div className="flex items-start justify-between mb-3">
                    <div>
                      <h4 className="font-medium text-white mb-1">{proxy.proxy_name}</h4>
                      <p className="text-xs text-gray-400">
                        {proxy.proxy_type.toUpperCase()} • {proxy.local_ip}:{proxy.local_port}
                      </p>
                    </div>
                    <span className={`status-${proxy.status}`}>
                      {proxy.status === 'active' ? '运行中' : proxy.status === 'error' ? '错误' : '未激活'}
                    </span>
                  </div>
                  
                  {proxy.node_name && (
                    <div className="text-xs text-gray-500">
                      节点: {proxy.node_name}
                    </div>
                  )}
                </div>
              ))}
            </div>
          ) : (
            <div className="text-center py-12">
              <div className="w-16 h-16 bg-gradient-to-br from-primary-600/20 to-accent-600/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <GlobeAltIcon className="w-8 h-8 text-primary-400" />
              </div>
              <h3 className="text-lg font-medium text-white mb-2">暂无隧道</h3>
              <p className="text-gray-400 mb-6">开始创建您的第一个内网穿透服务</p>
              <Link to="/proxies" className="btn btn-primary inline-flex items-center space-x-2">
                <PlusIcon className="w-4 h-4" />
                <span>创建隧道</span>
              </Link>
            </div>
          )}
        </div>
      </div>

      {/* 推荐套餐 */}
      {packages.length > 0 && (
        <div className="card-glass">
          <div className="card-header">
            <div className="flex items-center space-x-3">
              <div className="p-2 bg-gradient-to-br from-warning-600/20 to-warning-700/20 rounded-lg">
                <RocketLaunchIcon className="w-5 h-5 text-warning-400" />
              </div>
              <div>
                <h3 className="text-lg font-semibold text-white">推荐套餐</h3>
                <p className="text-sm text-gray-400">升级您的服务体验</p>
              </div>
            </div>
          </div>
          
          <div className="card-body">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              {packages.slice(0, 3).map((pkg, index) => (
                <div key={pkg.id} className={`
                  relative rounded-xl p-6 border transition-all duration-300 hover:scale-105
                  ${index === 1 
                    ? 'border-primary-600/50 bg-gradient-to-br from-primary-900/20 to-accent-900/20' 
                    : 'border-dark-700 bg-dark-800/50 hover:border-primary-700/50'
                  }
                `}>
                  {index === 1 && (
                    <div className="absolute -top-3 left-1/2 transform -translate-x-1/2">
                      <span className="bg-gradient-to-r from-primary-600 to-accent-600 text-white text-xs px-3 py-1 rounded-full font-medium">
                        推荐
                      </span>
                    </div>
                  )}
                  
                  <h4 className="text-lg font-bold text-white mb-2">{pkg.name}</h4>
                  <div className="mb-4">
                    <span className="text-3xl font-bold text-gradient">¥{pkg.price.toFixed(2)}</span>
                    <span className="text-sm text-gray-400">/{pkg.duration_days}天</span>
                  </div>
                  <p className="text-gray-300 text-sm mb-6">{pkg.description}</p>
                  
                  <div className="space-y-3 mb-6">
                    <div className="flex justify-between text-sm">
                      <span className="text-gray-400">流量限制</span>
                      <span className="text-white font-medium">{formatBytes(pkg.traffic_limit)}</span>
                    </div>
                    <div className="flex justify-between text-sm">
                      <span className="text-gray-400">最大隧道数</span>
                      <span className="text-white font-medium">{pkg.max_proxies}</span>
                    </div>
                  </div>
                  
                  <button className={`w-full py-2 rounded-lg font-medium transition-all duration-300 ${
                    index === 1
                      ? 'btn-primary'
                      : 'btn-secondary'
                  }`}>
                    {index === 1 ? '立即购买' : '选择套餐'}
                  </button>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default Dashboard;