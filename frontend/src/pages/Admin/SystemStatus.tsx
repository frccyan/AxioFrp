import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { 
  Server, 
  Database, 
  Wifi, 
  Cpu, 
  HardDrive, 
  Activity,
  Clock,
  Users,
  Globe,
  Zap,
  AlertTriangle,
  CheckCircle,
  RefreshCw,
  Loader2
} from 'lucide-react';

interface SystemStatus {
  database: {
    status: 'connected' | 'disconnected';
    connections: number;
    version: string;
  };
  redis: {
    status: 'connected' | 'disconnected';
    memory_usage: string;
    version: string;
  };
  services: {
    backend: 'running' | 'stopped';
    frontend: 'running' | 'stopped';
    database: 'running' | 'stopped';
  };
  performance: {
    cpu_usage: number;
    memory_usage: number;
    disk_usage: number;
    uptime: number;
  };
}

interface StatusCard {
  title: string;
  icon: React.ComponentType<any>;
  value: string | number;
  status: 'good' | 'warning' | 'error';
  description?: string;
}

const SystemStatus: React.FC = () => {
  const [status, setStatus] = useState<SystemStatus | null>(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  // 加载系统状态
  const loadSystemStatus = async () => {
    try {
      const response = await fetch('/api/config/system-status');
      const data = await response.json();
      
      if (data.success) {
        setStatus(data.data);
      } else {
        console.error('加载系统状态失败:', data.message);
      }
    } catch (error) {
      console.error('加载系统状态失败:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    loadSystemStatus();
    
    // 每30秒自动刷新
    const interval = setInterval(loadSystemStatus, 30000);
    return () => clearInterval(interval);
  }, []);

  const handleRefresh = () => {
    setRefreshing(true);
    loadSystemStatus();
  };

  const formatUptime = (seconds: number) => {
    const days = Math.floor(seconds / 86400);
    const hours = Math.floor((seconds % 86400) / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    
    if (days > 0) {
      return `${days}天 ${hours}小时 ${minutes}分钟`;
    } else if (hours > 0) {
      return `${hours}小时 ${minutes}分钟`;
    } else {
      return `${minutes}分钟`;
    }
  };

  const getStatusColor = (status: 'good' | 'warning' | 'error') => {
    switch (status) {
      case 'good': return 'text-green-400';
      case 'warning': return 'text-yellow-400';
      case 'error': return 'text-red-400';
    }
  };

  const getStatusBg = (status: 'good' | 'warning' | 'error') => {
    switch (status) {
      case 'good': return 'bg-green-500/10 border-green-500/20';
      case 'warning': return 'bg-yellow-500/10 border-yellow-500/20';
      case 'error': return 'bg-red-500/10 border-red-500/20';
    }
  };

  const getStatusIcon = (status: 'good' | 'warning' | 'error') => {
    switch (status) {
      case 'good': return CheckCircle;
      case 'warning': return AlertTriangle;
      case 'error': return AlertTriangle;
    }
  };

  const statusCards: StatusCard[] = status ? [
    {
      title: '数据库',
      icon: Database,
      value: status.database.version,
      status: status.database.status === 'connected' ? 'good' : 'error',
      description: `${status.database.connections} 个连接`
    },
    {
      title: 'Redis缓存',
      icon: Wifi,
      value: status.redis.version,
      status: status.redis.status === 'connected' ? 'good' : 'error',
      description: `内存使用: ${status.redis.memory_usage}`
    },
    {
      title: 'CPU使用率',
      icon: Cpu,
      value: `${status.performance.cpu_usage}%`,
      status: status.performance.cpu_usage < 70 ? 'good' : 
              status.performance.cpu_usage < 90 ? 'warning' : 'error'
    },
    {
      title: '内存使用率',
      icon: HardDrive,
      value: `${status.performance.memory_usage}%`,
      status: status.performance.memory_usage < 70 ? 'good' : 
              status.performance.memory_usage < 90 ? 'warning' : 'error'
    },
    {
      title: '磁盘使用率',
      icon: HardDrive,
      value: `${status.performance.disk_usage}%`,
      status: status.performance.disk_usage < 70 ? 'good' : 
              status.performance.disk_usage < 90 ? 'warning' : 'error'
    },
    {
      title: '系统运行时间',
      icon: Clock,
      value: formatUptime(status.performance.uptime),
      status: 'good'
    }
  ] : [];

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <Loader2 className="w-8 h-8 animate-spin text-purple-400" />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* 页面头部 */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-white">系统状态</h1>
          <p className="text-gray-400">监控系统运行状态和性能指标</p>
        </div>
        
        <button
          onClick={handleRefresh}
          disabled={refreshing}
          className="flex items-center gap-2 px-4 py-2 bg-white/10 text-white rounded-lg hover:bg-white/20 transition-colors disabled:opacity-50"
        >
          <RefreshCw className={`w-4 h-4 ${refreshing ? 'animate-spin' : ''}`} />
          刷新状态
        </button>
      </div>

      {/* 服务状态 */}
      {status && (
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.3 }}
          className="grid grid-cols-1 md:grid-cols-3 gap-4"
        >
          <div className={`p-6 rounded-lg border ${status.services.backend === 'running' ? 'bg-green-500/10 border-green-500/20' : 'bg-red-500/10 border-red-500/20'}`}>
            <div className="flex items-center gap-3">
              <Server className="w-6 h-6 text-green-400" />
              <div>
                <h3 className="font-medium text-white">后端服务</h3>
                <p className="text-sm text-gray-400">
                  {status.services.backend === 'running' ? '运行中' : '已停止'}
                </p>
              </div>
            </div>
          </div>

          <div className={`p-6 rounded-lg border ${status.services.frontend === 'running' ? 'bg-green-500/10 border-green-500/20' : 'bg-red-500/10 border-red-500/20'}`}>
            <div className="flex items-center gap-3">
              <Globe className="w-6 h-6 text-green-400" />
              <div>
                <h3 className="font-medium text-white">前端服务</h3>
                <p className="text-sm text-gray-400">
                  {status.services.frontend === 'running' ? '运行中' : '已停止'}
                </p>
              </div>
            </div>
          </div>

          <div className={`p-6 rounded-lg border ${status.services.database === 'running' ? 'bg-green-500/10 border-green-500/20' : 'bg-red-500/10 border-red-500/20'}`}>
            <div className="flex items-center gap-3">
              <Database className="w-6 h-6 text-green-400" />
              <div>
                <h3 className="font-medium text-white">数据库服务</h3>
                <p className="text-sm text-gray-400">
                  {status.services.database === 'running' ? '运行中' : '已停止'}
                </p>
              </div>
            </div>
          </div>
        </motion.div>
      )}

      {/* 状态卡片网格 */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {statusCards.map((card, index) => {
          const Icon = card.icon;
          const StatusIcon = getStatusIcon(card.status);
          
          return (
            <motion.div
              key={card.title}
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.3, delay: index * 0.1 }}
              className={`p-6 rounded-lg border ${getStatusBg(card.status)} transition-all hover:scale-[1.02]`}
            >
              <div className="flex items-start justify-between mb-4">
                <div className="flex items-center gap-3">
                  <Icon className="w-6 h-6 text-purple-400" />
                  <h3 className="font-medium text-white">{card.title}</h3>
                </div>
                <StatusIcon className={`w-5 h-5 ${getStatusColor(card.status)}`} />
              </div>
              
              <div className="space-y-2">
                <div className="text-2xl font-bold text-white">{card.value}</div>
                {card.description && (
                  <div className="text-sm text-gray-400">{card.description}</div>
                )}
              </div>
            </motion.div>
          );
        })}
      </div>

      {/* 性能图表区域 */}
      {status && (
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.3, delay: 0.4 }}
          className="grid grid-cols-1 lg:grid-cols-2 gap-6"
        >
          {/* CPU和内存使用率 */}
          <div className="p-6 bg-white/5 rounded-lg border border-white/10">
            <h3 className="font-medium text-white mb-4">资源使用率</h3>
            
            <div className="space-y-4">
              <div>
                <div className="flex items-center justify-between mb-2">
                  <div className="flex items-center gap-2">
                    <Cpu className="w-4 h-4 text-blue-400" />
                    <span className="text-sm text-gray-300">CPU使用率</span>
                  </div>
                  <span className="text-sm font-medium text-white">{status.performance.cpu_usage}%</span>
                </div>
                <div className="w-full bg-white/10 rounded-full h-2">
                  <div 
                    className={`h-2 rounded-full transition-all ${
                      status.performance.cpu_usage < 70 ? 'bg-green-400' :
                      status.performance.cpu_usage < 90 ? 'bg-yellow-400' :
                      'bg-red-400'
                    }`}
                    style={{ width: `${status.performance.cpu_usage}%` }}
                  />
                </div>
              </div>

              <div>
                <div className="flex items-center justify-between mb-2">
                  <div className="flex items-center gap-2">
                    <HardDrive className="w-4 h-4 text-purple-400" />
                    <span className="text-sm text-gray-300">内存使用率</span>
                  </div>
                  <span className="text-sm font-medium text-white">{status.performance.memory_usage}%</span>
                </div>
                <div className="w-full bg-white/10 rounded-full h-2">
                  <div 
                    className={`h-2 rounded-full transition-all ${
                      status.performance.memory_usage < 70 ? 'bg-green-400' :
                      status.performance.memory_usage < 90 ? 'bg-yellow-400' :
                      'bg-red-400'
                    }`}
                    style={{ width: `${status.performance.memory_usage}%` }}
                  />
                </div>
              </div>

              <div>
                <div className="flex items-center justify-between mb-2">
                  <div className="flex items-center gap-2">
                    <HardDrive className="w-4 h-4 text-green-400" />
                    <span className="text-sm text-gray-300">磁盘使用率</span>
                  </div>
                  <span className="text-sm font-medium text-white">{status.performance.disk_usage}%</span>
                </div>
                <div className="w-full bg-white/10 rounded-full h-2">
                  <div 
                    className={`h-2 rounded-full transition-all ${
                      status.performance.disk_usage < 70 ? 'bg-green-400' :
                      status.performance.disk_usage < 90 ? 'bg-yellow-400' :
                      'bg-red-400'
                    }`}
                    style={{ width: `${status.performance.disk_usage}%` }}
                  />
                </div>
              </div>
            </div>
          </div>

          {/* 系统信息 */}
          <div className="p-6 bg-white/5 rounded-lg border border-white/10">
            <h3 className="font-medium text-white mb-4">系统信息</h3>
            
            <div className="space-y-3">
              <div className="flex items-center justify-between py-2 border-b border-white/10">
                <span className="text-sm text-gray-400">数据库版本</span>
                <span className="text-sm text-white">{status.database.version}</span>
              </div>
              
              <div className="flex items-center justify-between py-2 border-b border-white/10">
                <span className="text-sm text-gray-400">Redis版本</span>
                <span className="text-sm text-white">{status.redis.version}</span>
              </div>
              
              <div className="flex items-center justify-between py-2 border-b border-white/10">
                <span className="text-sm text-gray-400">数据库连接</span>
                <span className="text-sm text-white">{status.database.connections}</span>
              </div>
              
              <div className="flex items-center justify-between py-2 border-b border-white/10">
                <span className="text-sm text-gray-400">Redis内存</span>
                <span className="text-sm text-white">{status.redis.memory_usage}</span>
              </div>
              
              <div className="flex items-center justify-between py-2">
                <span className="text-sm text-gray-400">运行时间</span>
                <span className="text-sm text-white">{formatUptime(status.performance.uptime)}</span>
              </div>
            </div>
          </div>
        </motion.div>
      )}
    </div>
  );
};

export default SystemStatus;