import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuthStore } from '../../stores/auth';
import { 
  ShieldCheckIcon, 
  ArrowRightIcon,
  EyeIcon,
  EyeSlashIcon,
  SparklesIcon
} from '@heroicons/react/24/outline';

const Login: React.FC = () => {
  const [formData, setFormData] = useState({
    username: '',
    password: ''
  });
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  
  const { login, isAuthenticated } = useAuthStore();
  const navigate = useNavigate();

  useEffect(() => {
    if (isAuthenticated) {
      navigate('/dashboard');
    }
  }, [isAuthenticated, navigate]);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });
    setError('');
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    try {
      await login(formData.username, formData.password);
      navigate('/dashboard');
    } catch (err: any) {
      setError(err.message || '登录失败');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-dark-950 bg-gradient-mesh py-12 px-4 sm:px-6 lg:px-8">
      {/* 背景装饰 */}
      <div className="fixed inset-0 bg-gradient-to-br from-primary-900/10 via-transparent to-accent-900/10 pointer-events-none" />
      <div className="fixed inset-0 bg-[url('/grid.svg')] opacity-5 pointer-events-none" />
      
      <div className="relative w-full max-w-md">
        {/* Logo区域 */}
        <div className="text-center mb-8">
          <div className="relative inline-block">
            <div className="mx-auto w-16 h-16 bg-gradient-to-br from-primary-600 to-accent-600 rounded-2xl flex items-center justify-center shadow-glow-lg animate-pulse-glow">
              <ShieldCheckIcon className="w-8 h-8 text-white" />
            </div>
            <div className="absolute -bottom-1 -right-1 bg-success-500 rounded-full p-1">
              <SparklesIcon className="w-4 h-4 text-white" />
            </div>
          </div>
          <h1 className="mt-6 text-4xl font-bold text-white">欢迎回来</h1>
          <p className="mt-2 text-gray-400">
            登录到 AxioFrp 管理平台
          </p>
        </div>

        {/* 登录卡片 */}
        <div className="card-glass p-8">
          {/* 错误消息 */}
          {error && (
            <div className="mb-6 p-4 bg-danger-900/30 border border-danger-800 rounded-lg">
              <div className="flex items-center">
                <div className="flex-shrink-0">
                  <svg className="h-5 w-5 text-danger-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                  </svg>
                </div>
                <div className="ml-3">
                  <p className="text-sm font-medium text-danger-300">{error}</p>
                </div>
              </div>
            </div>
          )}

          {/* 登录表单 */}
          <form onSubmit={handleSubmit} className="space-y-6">
            <div className="space-y-5">
              <div>
                <label htmlFor="username" className="block text-sm font-medium text-gray-200 mb-2">
                  用户名
                </label>
                <div className="relative">
                  <input
                    id="username"
                    name="username"
                    type="text"
                    required
                    value={formData.username}
                    onChange={handleChange}
                    className="input pl-10"
                    placeholder="请输入用户名"
                  />
                  <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg className="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fillRule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clipRule="evenodd" />
                    </svg>
                  </div>
                </div>
              </div>

              <div>
                <label htmlFor="password" className="block text-sm font-medium text-gray-200 mb-2">
                  密码
                </label>
                <div className="relative">
                  <input
                    id="password"
                    name="password"
                    type={showPassword ? 'text' : 'password'}
                    required
                    value={formData.password}
                    onChange={handleChange}
                    className="input pl-10 pr-10"
                    placeholder="请输入密码"
                  />
                  <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg className="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fillRule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clipRule="evenodd" />
                    </svg>
                  </div>
                  <button
                    type="button"
                    onClick={() => setShowPassword(!showPassword)}
                    className="absolute inset-y-0 right-0 pr-3 flex items-center"
                  >
                    {showPassword ? (
                      <EyeSlashIcon className="h-5 w-5 text-gray-400 hover:text-gray-300" />
                    ) : (
                      <EyeIcon className="h-5 w-5 text-gray-400 hover:text-gray-300" />
                    )}
                  </button>
                </div>
              </div>
            </div>

            <div className="flex items-center justify-between">
              <div className="flex items-center">
                <input
                  id="remember-me"
                  name="remember-me"
                  type="checkbox"
                  className="h-4 w-4 bg-dark-800 border-dark-600 rounded text-primary-600 focus:ring-primary-500 focus:ring-offset-0"
                />
                <label htmlFor="remember-me" className="ml-2 block text-sm text-gray-400">
                  记住我
                </label>
              </div>

              <Link 
                to="/forgot-password" 
                className="text-sm text-primary-400 hover:text-primary-300 transition-colors"
              >
                忘记密码？
              </Link>
            </div>

            <button
              type="submit"
              disabled={loading}
              className="btn-primary w-full relative group"
            >
              <span className="absolute inset-0 bg-gradient-to-r from-primary-600 to-accent-600 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
              <span className="relative flex items-center justify-center">
                {loading ? (
                  <>
                    <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                      <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    登录中...
                  </>
                ) : (
                  <>
                    登录
                    <ArrowRightIcon className="ml-2 h-4 w-4 group-hover:translate-x-1 transition-transform duration-200" />
                  </>
                )}
              </span>
            </button>
          </form>

          {/* 注册链接 */}
          <div className="mt-6 text-center">
            <p className="text-gray-400">
              还没有账户？{' '}
              <Link 
                to="/register" 
                className="font-medium text-primary-400 hover:text-primary-300 transition-colors"
              >
                立即注册
              </Link>
            </p>
          </div>
        </div>

        {/* 底部信息 */}
        <div className="mt-8 text-center">
          <p className="text-xs text-gray-500">
            © 2024 AxioFrp. All rights reserved.
          </p>
        </div>
      </div>
    </div>
  );
};

export default Login;