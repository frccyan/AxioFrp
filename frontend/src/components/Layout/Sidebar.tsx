import React from 'react';
import { Link, useLocation } from 'react-router-dom';
import { useAuthStore } from '../../stores/auth';
import {
  HomeIcon,
  ServerIcon,
  GlobeAltIcon,
  ChartBarIcon,
  UserGroupIcon,
  XMarkIcon,
  ArrowRightOnRectangleIcon,
  Cog6ToothIcon,
  ActivityIcon,
} from '@heroicons/react/24/outline';

interface SidebarProps {
  isOpen: boolean;
  onClose: () => void;
}

const Sidebar: React.FC<SidebarProps> = ({ isOpen, onClose }) => {
  const location = useLocation();
  const { user, logout } = useAuthStore();

  const navigation = [
    {
      name: '仪表板',
      href: '/dashboard',
      icon: HomeIcon,
      current: location.pathname === '/dashboard',
    },
    {
      name: '隧道管理',
      href: '/proxies',
      icon: GlobeAltIcon,
      current: location.pathname === '/proxies',
    },
    {
      name: '节点列表',
      href: '/nodes',
      icon: ServerIcon,
      current: location.pathname === '/nodes',
    },
    {
      name: '套餐购买',
      href: '/packages',
      icon: ChartBarIcon,
      current: location.pathname === '/packages',
    },
  ];

  // 管理员专用菜单
  const adminNavigation = [
    {
      name: '系统设置',
      href: '/admin/settings',
      icon: Cog6ToothIcon,
      current: location.pathname === '/admin/settings',
    },
    {
      name: '系统状态',
      href: '/admin/system-status',
      icon: ActivityIcon,
      current: location.pathname === '/admin/system-status',
    },
  ];

  const handleLogout = () => {
    logout();
    onClose();
  };

  const NavItem: React.FC<{ item: any }> = ({ item }) => (
    <Link
      to={item.href}
      onClick={() => onClose()}
      className={`
        group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200
        ${item.current
          ? 'bg-primary-500/10 text-primary-400 border-l-2 border-primary-500'
          : 'text-gray-400 hover:text-white hover:bg-white/5'
        }
      `}
    >
      <item.icon className="mr-3 h-5 w-5 flex-shrink-0" />
      {item.name}
      {item.current && (
        <div className="ml-auto w-2 h-2 bg-primary-500 rounded-full"></div>
      )}
    </Link>
  );

  return (
    <>
      {/* 移动端遮罩 */}
      {isOpen && (
        <div
          className="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 lg:hidden"
          onClick={onClose}
        />
      )}

      {/* 侧边栏 */}
      <div
        className={`
          fixed inset-y-0 left-0 z-50 w-64 bg-dark-900 border-r border-dark-800
          transform transition-transform duration-300 ease-in-out
          ${isOpen ? 'translate-x-0' : '-translate-x-full'}
          lg:translate-x-0 lg:static lg:inset-0
        `}
      >
        <div className="flex flex-col h-full">
          {/* Logo区域 */}
          <div className="flex items-center justify-between p-6 border-b border-dark-800">
            <div className="flex items-center space-x-3">
              <div className="relative">
                <div className="w-10 h-10 bg-gradient-to-br from-primary-600 to-accent-600 rounded-xl flex items-center justify-center shadow-glow">
                  <span className="text-white font-bold text-lg">A</span>
                </div>
                <div className="absolute -bottom-1 -right-1 w-3 h-3 bg-success-500 rounded-full border-2 border-dark-900"></div>
              </div>
              <div>
                <h1 className="text-xl font-bold text-white">AxioFrp</h1>
                <p className="text-xs text-gray-400">管理面板</p>
              </div>
            </div>
            <button
              onClick={onClose}
              className="lg:hidden p-2 text-gray-400 hover:text-white rounded-lg hover:bg-white/5"
            >
              <XMarkIcon className="h-5 w-5" />
            </button>
          </div>

          {/* 用户信息区域 */}
          <div className="p-4 border-b border-dark-800">
            <div className="flex items-center space-x-3">
              <div className="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center">
                <span className="text-white font-semibold">
                  {user?.username?.charAt(0).toUpperCase()}
                </span>
              </div>
              <div className="flex-1 min-w-0">
                <p className="text-sm font-medium text-white truncate">
                  {user?.username}
                </p>
                <p className="text-xs text-gray-400 truncate">
                  {user?.email}
                  {user?.is_admin && (
                    <span className="ml-2 px-2 py-0.5 bg-primary-500/20 text-primary-400 text-xs rounded-full">
                      管理员
                    </span>
                  )}
                </p>
              </div>
            </div>
          </div>

          {/* 导航菜单 */}
          <nav className="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            {/* 主要功能 */}
            <div className="mb-6">
              <h3 className="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
                主要功能
              </h3>
              {navigation.map((item) => (
                <NavItem key={item.name} item={item} />
              ))}
            </div>

            {/* 管理员功能 */}
            {user?.is_admin && (
              <div>
                <h3 className="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
                  系统管理
                </h3>
                {adminNavigation.map((item) => (
                  <NavItem key={item.name} item={item} />
                ))}
              </div>
            )}
          </nav>

          {/* 底部操作区域 */}
          <div className="p-3 border-t border-dark-800">
            <button
              onClick={handleLogout}
              className="w-full group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-gray-400 hover:text-white hover:bg-red-500/10 transition-all duration-200"
            >
              <ArrowRightOnRectangleIcon className="mr-3 h-5 w-5 flex-shrink-0" />
              退出登录
            </button>
          </div>
        </div>
      </div>
    </>
  );
};

export default Sidebar;