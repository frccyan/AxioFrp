import React from 'react';
import { Link } from 'react-router-dom';
import { useAuthStore } from '../../stores/auth';
import {
  Bars3Icon,
  BellIcon,
  UserCircleIcon,
  ArrowRightOnRectangleIcon,
} from '@heroicons/react/24/outline';

interface HeaderProps {
  onSidebarToggle: () => void;
}

const Header: React.FC<HeaderProps> = ({ onSidebarToggle }) => {
  const { user, logout } = useAuthStore();

  const handleLogout = () => {
    logout();
  };

  return (
    <header className="fixed top-0 left-0 right-0 z-30 bg-dark-900/80 backdrop-blur-xl border-b border-dark-800/50">
      <div className="px-4 lg:px-8">
        <div className="flex items-center justify-between h-16">
          {/* 左侧：移动端菜单按钮 + Logo */}
          <div className="flex items-center space-x-4">
            <button
              onClick={onSidebarToggle}
              className="lg:hidden p-2 rounded-lg text-gray-400 hover:text-white hover:bg-dark-800 transition-colors"
            >
              <Bars3Icon className="w-6 h-6" />
            </button>
            
            <Link to="/" className="flex items-center space-x-3">
              <div className="relative">
                <div className="w-8 h-8 bg-gradient-to-br from-primary-600 to-accent-600 rounded-lg flex items-center justify-center shadow-glow">
                  <span className="text-white font-bold text-sm">A</span>
                </div>
                <div className="absolute -bottom-1 -right-1 w-2 h-2 bg-success-500 rounded-full border-2 border-dark-900"></div>
              </div>
              <div className="hidden sm:block">
                <h1 className="text-lg font-bold text-white">AxioFrp</h1>
                <p className="text-xs text-gray-500">内网穿透管理平台</p>
              </div>
            </Link>
          </div>

          {/* 右侧：通知、用户菜单 */}
          <div className="flex items-center space-x-4">
            {/* 通知铃铛 */}
            <button className="relative p-2 rounded-lg text-gray-400 hover:text-white hover:bg-dark-800 transition-colors">
              <BellIcon className="w-5 h-5" />
              <span className="absolute top-1 right-1 w-2 h-2 bg-danger-500 rounded-full"></span>
            </button>

            {user ? (
              <div className="flex items-center space-x-3">
                {/* 用户信息（桌面端显示） */}
                <div className="hidden sm:flex items-center space-x-2">
                  <div className="relative">
                    <div className="w-8 h-8 bg-gradient-to-br from-primary-500 to-accent-500 rounded-full flex items-center justify-center">
                      <span className="text-white font-semibold text-sm">
                        {user.username.charAt(0).toUpperCase()}
                      </span>
                    </div>
                    {user.is_admin && (
                      <div className="absolute -bottom-1 -right-1 bg-warning-500 rounded-full p-0.5">
                        <span className="text-xs text-white">⚡</span>
                      </div>
                    )}
                  </div>
                  <div className="text-sm">
                    <div className="font-medium text-white">{user.username}</div>
                    <div className="text-gray-400">{user.group_name || '普通用户'}</div>
                  </div>
                </div>
                
                {/* 退出按钮 */}
                <button
                  onClick={handleLogout}
                  className="flex items-center space-x-2 px-3 py-2 text-sm text-gray-300 hover:text-danger-400 hover:bg-dark-800 rounded-lg transition-all duration-200 group"
                >
                  <ArrowRightOnRectangleIcon className="w-4 h-4 group-hover:text-danger-400" />
                  <span className="hidden sm:inline">退出</span>
                </button>
              </div>
            ) : (
              <div className="flex items-center space-x-2">
                <Link 
                  to="/login" 
                  className="btn btn-secondary text-sm"
                >
                  登录
                </Link>
                <Link 
                  to="/register" 
                  className="btn btn-primary text-sm"
                >
                  注册
                </Link>
              </div>
            )}
          </div>
        </div>
      </div>
    </header>
  );
};

export default Header;