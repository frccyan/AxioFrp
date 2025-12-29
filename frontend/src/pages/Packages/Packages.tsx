import React, { useState, useEffect } from 'react';
import { Package, PackageOrder } from '../../types';
import {
  RocketLaunchIcon,
  CheckCircleIcon,
  XMarkIcon,
  CurrencyDollarIcon,
  ClockIcon,
  ServerIcon,
  GlobeAltIcon,
  ArrowRightIcon,
  StarIcon,
  SparklesIcon,
  CreditCardIcon,
  GiftIcon
} from '@heroicons/react/24/outline';

const Packages: React.FC = () => {
  const [packages, setPackages] = useState<Package[]>([]);
  const [orders, setOrders] = useState<PackageOrder[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedPackage, setSelectedPackage] = useState<Package | null>(null);
  const [billingCycle, setBillingCycle] = useState<'monthly' | 'yearly'>('monthly');

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    setLoading(true);
    try {
      // 模拟数据加载
      const mockPackages: Package[] = [
        {
          id: 1,
          name: '入门版',
          description: '适合个人用户和小型项目，满足基础内网穿透需求',
          price: 9.9,
          duration_days: 30,
          traffic_limit: 10737418240, // 10GB
          max_proxies: 3,
          status: 'active',
          created_at: '2024-01-01T00:00:00Z'
        },
        {
          id: 2,
          name: '专业版',
          description: '适合中小企业和专业用户，提供更多功能和更高性能',
          price: 29.9,
          duration_days: 30,
          traffic_limit: 107374182400, // 100GB
          max_proxies: 10,
          status: 'active',
          created_at: '2024-01-01T00:00:00Z'
        },
        {
          id: 3,
          name: '企业版',
          description: '适合大型企业和高流量应用，提供企业级服务保障',
          price: 99.9,
          duration_days: 30,
          traffic_limit: 1073741824000, // 1TB
          max_proxies: 50,
          status: 'active',
          created_at: '2024-01-01T00:00:00Z'
        },
        {
          id: 4,
          name: '旗舰版',
          description: '无限制使用，为大型项目提供最高性能和稳定性',
          price: 299.9,
          duration_days: 30,
          traffic_limit: 0, // 无限制
          max_proxies: 0, // 无限制
          status: 'active',
          created_at: '2024-01-01T00:00:00Z'
        }
      ];

      const mockOrders: PackageOrder[] = [
        {
          id: 1,
          username: 'demo',
          package_id: 2,
          package_name: '专业版',
          amount: 29.9,
          status: 'paid',
          expires_at: '2025-01-29T00:00:00Z',
          created_at: '2024-12-29T00:00:00Z'
        },
        {
          id: 2,
          username: 'demo',
          package_id: 1,
          package_name: '入门版',
          amount: 9.9,
          status: 'expired',
          expires_at: '2024-12-01T00:00:00Z',
          created_at: '2024-11-01T00:00:00Z'
        }
      ];

      setPackages(mockPackages);
      setOrders(mockOrders);
    } catch (error) {
      console.error('加载数据失败:', error);
    } finally {
      setLoading(false);
    }
  };

  const formatBytes = (bytes: number): string => {
    if (bytes === 0) return '无限制';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  };

  const formatPrice = (price: number) => {
    if (billingCycle === 'yearly') {
      return (price * 12 * 0.8).toFixed(2); // 年付8折
    }
    return price.toFixed(2);
  };

  const getStatusText = (status: string) => {
    switch (status) {
      case 'paid':
        return '已付费';
      case 'pending':
        return '待支付';
      case 'expired':
        return '已过期';
      case 'cancelled':
        return '已取消';
      default:
        return '未知';
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'paid':
        return 'success';
      case 'pending':
        return 'warning';
      case 'expired':
        return 'danger';
      case 'cancelled':
        return 'gray';
      default:
        return 'gray';
    }
  };

  const getDaysRemaining = (expiresAt: string) => {
    const now = new Date();
    const expires = new Date(expiresAt);
    const diffTime = expires.getTime() - now.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays > 0 ? diffDays : 0;
  };

  const handlePurchase = (pkg: Package) => {
    setSelectedPackage(pkg);
    // 这里可以打开支付模态框或跳转到支付页面
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="spinner w-12 h-12"></div>
      </div>
    );
  }

  const activeOrder = orders.find(order => order.status === 'paid' && getDaysRemaining(order.expires_at) > 0);

  return (
    <div className="space-y-8">
      {/* 页面标题 */}
      <div className="text-center">
        <h1 className="text-4xl font-bold text-white mb-4">选择适合您的套餐</h1>
        <p className="text-gray-400 text-lg max-w-2xl mx-auto">
          我们提供灵活的定价方案，满足从个人开发者到大型企业的各种需求
        </p>
      </div>

      {/* 当前套餐状态 */}
      {activeOrder && (
        <div className="card-glass p-6 border-l-4 border-success-500">
          <div className="flex items-center justify-between">
            <div className="flex items-center space-x-4">
              <div className="p-3 bg-success-600/20 rounded-xl">
                <CheckCircleIcon className="w-6 h-6 text-success-400" />
              </div>
              <div>
                <h3 className="text-lg font-semibold text-white">当前套餐: {activeOrder.package_name}</h3>
                <p className="text-sm text-gray-400">
                  剩余时间: {getDaysRemaining(activeOrder.expires_at)} 天
                </p>
              </div>
            </div>
            <div className="text-right">
              <p className="text-sm text-gray-400">到期时间</p>
              <p className="text-white font-medium">
                {new Date(activeOrder.expires_at).toLocaleDateString()}
              </p>
            </div>
          </div>
        </div>
      )}

      {/* 计费周期切换 */}
      <div className="flex justify-center">
        <div className="inline-flex rounded-lg bg-dark-800 p-1">
          <button
            onClick={() => setBillingCycle('monthly')}
            className={`px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 ${
              billingCycle === 'monthly'
                ? 'bg-primary-600 text-white shadow-glow'
                : 'text-gray-400 hover:text-white'
            }`}
          >
            月付
          </button>
          <button
            onClick={() => setBillingCycle('yearly')}
            className={`px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 ${
              billingCycle === 'yearly'
                ? 'bg-primary-600 text-white shadow-glow'
                : 'text-gray-400 hover:text-white'
            }`}
          >
            年付
            <span className="ml-2 bg-warning-600 text-white text-xs px-2 py-0.5 rounded-full">
              省20%
            </span>
          </button>
        </div>
      </div>

      {/* 套餐列表 */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {packages.map((pkg, index) => {
          const isPopular = index === 2; // 专业版为推荐套餐
          
          return (
            <div key={pkg.id} className={`
              relative card-glass p-6 hover:scale-105 transition-all duration-300
              ${isPopular ? 'border-2 border-primary-600/50 shadow-glow-lg' : ''}
            `}>
              {/* 推荐标签 */}
              {isPopular && (
                <div className="absolute -top-4 left-1/2 transform -translate-x-1/2">
                  <div className="bg-gradient-to-r from-primary-600 to-accent-600 text-white text-sm px-4 py-1 rounded-full font-medium flex items-center space-x-1">
                    <StarIcon className="w-4 h-4" />
                    <span>最受欢迎</span>
                  </div>
                </div>
              )}

              {/* 套餐头部 */}
              <div className="text-center mb-6">
                <div className={`w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4 ${
                  index === 0 ? 'bg-gray-600/20' :
                  index === 1 ? 'bg-accent-600/20' :
                  index === 2 ? 'bg-primary-600/20' :
                  'bg-warning-600/20'
                }`}>
                  {index === 0 && <ServerIcon className="w-8 h-8 text-gray-400" />}
                  {index === 1 && <GlobeAltIcon className="w-8 h-8 text-accent-400" />}
                  {index === 2 && <RocketLaunchIcon className="w-8 h-8 text-primary-400" />}
                  {index === 3 && <SparklesIcon className="w-8 h-8 text-warning-400" />}
                </div>
                
                <h3 className="text-xl font-bold text-white mb-2">{pkg.name}</h3>
                <p className="text-sm text-gray-400 mb-4">{pkg.description}</p>
                
                <div className="mb-4">
                  <span className="text-3xl font-bold text-gradient">¥{formatPrice(pkg.price)}</span>
                  <span className="text-gray-400 text-sm">/{billingCycle === 'yearly' ? '年' : '月'}</span>
                </div>
                
                {billingCycle === 'yearly' && (
                  <div className="bg-success-900/30 text-success-300 text-xs px-2 py-1 rounded-full inline-flex items-center space-x-1">
                    <GiftIcon className="w-3 h-3" />
                    <span>节省 ¥{(pkg.price * 12 * 0.2).toFixed(2)}</span>
                  </div>
                )}
              </div>

              {/* 套餐特性 */}
              <div className="space-y-3 mb-6">
                <div className="flex items-center justify-between">
                  <span className="text-sm text-gray-400">流量限制</span>
                  <span className="text-sm font-medium text-white">
                    {formatBytes(pkg.traffic_limit)}
                  </span>
                </div>
                
                <div className="flex items-center justify-between">
                  <span className="text-sm text-gray-400">最大隧道数</span>
                  <span className="text-sm font-medium text-white">
                    {pkg.max_proxies === 0 ? '无限制' : pkg.max_proxies}
                  </span>
                </div>
                
                <div className="flex items-center justify-between">
                  <span className="text-sm text-gray-400">支持协议</span>
                  <span className="text-sm font-medium text-white">
                    TCP/UDP/HTTP/HTTPS
                  </span>
                </div>
                
                <div className="flex items-center justify-between">
                  <span className="text-sm text-gray-400">技术支持</span>
                  <span className="text-sm font-medium text-white">
                    {index === 0 ? '邮件支持' :
                     index === 1 ? '优先支持' :
                     index === 2 ? '24/7支持' : '专属客服'}
                  </span>
                </div>
              </div>

              {/* 购买按钮 */}
              <button 
                onClick={() => handlePurchase(pkg)}
                className={`w-full py-3 rounded-lg font-medium transition-all duration-300 flex items-center justify-center space-x-2 ${
                  isPopular
                    ? 'btn-primary'
                    : activeOrder?.package_id === pkg.id
                    ? 'btn-secondary'
                    : 'btn-accent'
                }`}
                disabled={activeOrder?.package_id === pkg.id}
              >
                {activeOrder?.package_id === pkg.id ? (
                  <>
                    <CheckCircleIcon className="w-4 h-4" />
                    <span>当前套餐</span>
                  </>
                ) : (
                  <>
                    <CreditCardIcon className="w-4 h-4" />
                    <span>立即购买</span>
                    <ArrowRightIcon className="w-4 h-4" />
                  </>
                )}
              </button>
            </div>
          );
        })}
      </div>

      {/* 历史订单 */}
      <div className="card-glass">
        <div className="card-header">
          <div className="flex items-center space-x-3">
            <div className="p-2 bg-gradient-to-br from-primary-600/20 to-accent-600/20 rounded-lg">
              <ClockIcon className="w-5 h-5 text-primary-400" />
            </div>
            <div>
              <h3 className="text-lg font-semibold text-white">订单历史</h3>
              <p className="text-sm text-gray-400">查看您的套餐购买记录</p>
            </div>
          </div>
        </div>
        
        <div className="card-body">
          {orders.length > 0 ? (
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead>
                  <tr className="border-b border-dark-700">
                    <th className="text-left px-6 py-4 text-sm font-medium text-gray-400">套餐名称</th>
                    <th className="text-left px-6 py-4 text-sm font-medium text-gray-400">金额</th>
                    <th className="text-left px-6 py-4 text-sm font-medium text-gray-400">状态</th>
                    <th className="text-left px-6 py-4 text-sm font-medium text-gray-400">购买时间</th>
                    <th className="text-left px-6 py-4 text-sm font-medium text-gray-400">到期时间</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-dark-700">
                  {orders.map((order) => (
                    <tr key={order.id} className="hover:bg-dark-800/50 transition-colors">
                      <td className="px-6 py-4">
                        <div className="font-medium text-white">{order.package_name}</div>
                      </td>
                      <td className="px-6 py-4">
                        <span className="text-white font-medium">¥{order.amount.toFixed(2)}</span>
                      </td>
                      <td className="px-6 py-4">
                        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-${getStatusColor(order.status)}-900/30 text-${getStatusColor(order.status)}-300`}>
                          {getStatusText(order.status)}
                        </span>
                      </td>
                      <td className="px-6 py-4">
                        <span className="text-sm text-gray-300">
                          {new Date(order.created_at).toLocaleDateString()}
                        </span>
                      </td>
                      <td className="px-6 py-4">
                        <div>
                          <span className="text-sm text-gray-300">
                            {new Date(order.expires_at).toLocaleDateString()}
                          </span>
                          {order.status === 'paid' && (
                            <div className="text-xs text-gray-500">
                              剩余 {getDaysRemaining(order.expires_at)} 天
                            </div>
                          )}
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          ) : (
            <div className="text-center py-8">
              <CurrencyDollarIcon className="w-12 h-12 text-gray-400 mx-auto mb-4" />
              <h3 className="text-lg font-medium text-white mb-2">暂无订单记录</h3>
              <p className="text-gray-400">选择一个套餐开始使用吧</p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default Packages;