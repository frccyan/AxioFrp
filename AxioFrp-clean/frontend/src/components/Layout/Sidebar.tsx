import React from 'react';
import { Link, useLocation } from 'react-router-dom';
import { useAuthStore } from '../../stores/auth';
import {
  HomeIcon,
  ServerIcon,
  GlobeAltIcon,
  CogIcon,
  ChartBarIcon,
  UserGroupIcon,
  XMarkIcon,
  ArrowRightOnRectangleIcon,
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
    {
      name: '用户管理',
      href: '/users',
      icon: UserGroupIcon,
      current: location.pathname === '/users',
      adminOnly: true,
    },
    {
      name: '系统设置',
      href: '/settings',
      icon: CogIcon,
      current: location.pathname === '/settings',
    },
  ];

  const filteredNavigation = navigation.filter(
    item => !item.adminOnly || (user?.is_admin)
  );

  const handleLogout = () => {
    logout();
    onClose();
  };

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
                <p class="text-xs text-gray-500">内网穿透管理平台</p>
              </div>
            </div>
            <button
              onClick={onClose}
              className="lg:hidden p-2 rounded-lg text-gray-400 hover:text-white hover:bg-dark-800 transition-colors"
            >
              <XMarkIcon className="w-5 h-5" />
            </button>
          </div>

          {/* 用户信息 */}
          {user && (
            <div className="p-4 border-b border-dark-800">
              <div className="flex items-center space-x-3">
                <div className="relative">
                  <div className="w-10 h-10 bg-gradient-to-br from-primary-500 to-accent-500 rounded-full flex items-center justify-center">
                    <span className="text-white font-semibold">
                      {user.username.charAt(0).toUpperCase()}
                    </span>
                  </div>
                  {user.is_admin && (
                    <div className="absolute -bottom-1 -right-1 bg-warning-500 rounded-full p-1">
                      <CogIcon className="w-3 h-3 text-white" />
                    </div>
                  )}
                </div>
                <div className="flex-1 min-w-0">
                  <p className="text-sm font-medium text-white truncate">
                    {user.username}
                  </p>
                  <p className="text-xs text-gray-400 truncate">
                    {user.group_name || '普通用户'}
                  </p>
                </div>
              </div>
            </div>
          )}

          {/* 导航菜单 */}
          <nav className="flex-1 p-4 space-y-1 overflow-y-auto">
            {filteredNavigation.map((item) => {
              const Icon = item.icon;
              return (
                <Link
                  key={item.name}
                  to={item.href}
                  onClick={onClose}
                  className={`
                    nav-link group ${item.current ? 'active' : ''}
                  `}
                >
                  <Icon
                    className={`
                      mr-3 h-5 w-5 flex-shrink-0
                      ${item.current 
                        ? 'text-primary-400' 
                        : 'text-gray-400 group-hover:text-gray-300'
                      }
                    `}
                  />
                  {item.name}
                </Link>
              );
            })}
          </nav>

          {/* 底部操作 */}
          <div className="p-4 border-t border-dark-800">
            {user && (
              <button
                onClick={handleLogout}
                className="w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg text-gray-300 hover:bg-dark-800 hover:text-danger-400 transition-all duration-200 group"
              >
                <ArrowRightOnRectangleIcon className="mr-3 h-5 w-5 text-gray-400 group-hover:text-danger-400" />
                退出登录
              </button>
            )}
          </div>
        </div>
      </div>
    </>
  );
};

export default Sidebar;