import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuthStore } from '../../stores/auth';
import { 
  UserPlusIcon,
  ArrowRightIcon,
  EyeIcon,
  EyeSlashIcon,
  CheckCircleIcon,
  XCircleIcon,
  SparklesIcon
} from '@heroicons/react/24/outline';

const Register: React.FC = () => {
  const [formData, setFormData] = useState({
    username: '',
    email: '',
    password: '',
    confirmPassword: ''
  });
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [passwordStrength, setPasswordStrength] = useState(0);
  
  const { register, isAuthenticated } = useAuthStore();
  const navigate = useNavigate();

  useEffect(() => {
    if (isAuthenticated) {
      navigate('/dashboard');
    }
  }, [isAuthenticated, navigate]);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
    setError('');

    // 计算密码强度
    if (name === 'password') {
      let strength = 0;
      if (value.length >= 8) strength += 25;
      if (value.length >= 12) strength += 25;
      if (/[a-z]/.test(value)) strength += 12.5;
      if (/[A-Z]/.test(value)) strength += 12.5;
      if (/[0-9]/.test(value)) strength += 12.5;
      if (/[^a-zA-Z0-9]/.test(value)) strength += 12.5;
      setPasswordStrength(Math.min(strength, 100));
    }
  };

  const getPasswordStrengthText = (strength: number) => {
    if (strength < 30) return { text: '弱', color: 'text-danger-400', bg: 'bg-danger-500' };
    if (strength < 60) return { text: '中', color: 'text-warning-400', bg: 'bg-warning-500' };
    if (strength < 80) return { text: '强', color: 'text-accent-400', bg: 'bg-accent-500' };
    return { text: '很强', color: 'text-success-400', bg: 'bg-success-500' };
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    // 表单验证
    if (formData.username.length < 3) {
      setError('用户名长度至少3位');
      setLoading(false);
      return;
    }

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
      setError('请输入有效的邮箱地址');
      setLoading(false);
      return;
    }

    if (formData.password.length < 6) {
      setError('密码长度至少6位');
      setLoading(false);
      return;
    }

    if (formData.password !== formData.confirmPassword) {
      setError('两次输入的密码不一致');
      setLoading(false);
      return;
    }

    try {
      await register(formData.username, formData.email, formData.password, formData.confirmPassword);
      navigate('/dashboard');
    } catch (err: any) {
      setError(err.message || '注册失败');
    } finally {
      setLoading(false);
    }
  };

  const passwordMatch = formData.password && formData.confirmPassword && formData.password === formData.confirmPassword;

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
              <UserPlusIcon className="w-8 h-8 text-white" />
            </div>
            <div className="absolute -bottom-1 -right-1 bg-success-500 rounded-full p-1">
              <SparklesIcon className="w-4 h-4 text-white" />
            </div>
          </div>
          <h1 className="mt-6 text-4xl font-bold text-white">创建账户</h1>
          <p className="mt-2 text-gray-400">
            加入 AxioFrp，开启内网穿透之旅
          </p>
        </div>

        {/* 注册卡片 */}
        <div className="card-glass p-8">
          {/* 错误消息 */}
          {error && (
            <div className="mb-6 p-4 bg-danger-900/30 border border-danger-800 rounded-lg">
              <div className="flex items-center">
                <div className="flex-shrink-0">
                  <XCircleIcon className="h-5 w-5 text-danger-400" />
                </div>
                <div className="ml-3">
                  <p className="text-sm font-medium text-danger-300">{error}</p>
                </div>
              </div>
            </div>
          )}

          {/* 注册表单 */}
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
                    placeholder="请输入用户名（至少3位）"
                  />
                  <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg className="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fillRule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clipRule="evenodd" />
                    </svg>
                  </div>
                </div>
              </div>

              <div>
                <label htmlFor="email" className="block text-sm font-medium text-gray-200 mb-2">
                  邮箱地址
                </label>
                <div className="relative">
                  <input
                    id="email"
                    name="email"
                    type="email"
                    required
                    value={formData.email}
                    onChange={handleChange}
                    className="input pl-10"
                    placeholder="请输入邮箱地址"
                  />
                  <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg className="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                      <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                      <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
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
                    placeholder="请输入密码（至少6位）"
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
                
                {/* 密码强度指示器 */}
                {formData.password && (
                  <div className="mt-2">
                    <div className="flex items-center justify-between mb-1">
                      <span className="text-xs text-gray-400">密码强度</span>
                      <span className={`text-xs font-medium ${getPasswordStrengthText(passwordStrength).color}`}>
                        {getPasswordStrengthText(passwordStrength).text}
                      </span>
                    </div>
                    <div className="w-full bg-dark-700 rounded-full h-1.5">
                      <div
                        className={`h-1.5 rounded-full transition-all duration-300 ${getPasswordStrengthText(passwordStrength).bg}`}
                        style={{ width: `${passwordStrength}%` }}
                      />
                    </div>
                  </div>
                )}
              </div>

              <div>
                <label htmlFor="confirmPassword" className="block text-sm font-medium text-gray-200 mb-2">
                  确认密码
                </label>
                <div className="relative">
                  <input
                    id="confirmPassword"
                    name="confirmPassword"
                    type={showConfirmPassword ? 'text' : 'password'}
                    required
                    value={formData.confirmPassword}
                    onChange={handleChange}
                    className="input pl-10 pr-10"
                    placeholder="请再次输入密码"
                  />
                  <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg className="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fillRule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clipRule="evenodd" />
                    </svg>
                  </div>
                  <button
                    type="button"
                    onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                    className="absolute inset-y-0 right-0 pr-3 flex items-center"
                  >
                    {showConfirmPassword ? (
                      <EyeSlashIcon className="h-5 w-5 text-gray-400 hover:text-gray-300" />
                    ) : (
                      <EyeIcon className="h-5 w-5 text-gray-400 hover:text-gray-300" />
                    )}
                  </button>
                </div>
                
                {/* 密码匹配指示器 */}
                {formData.confirmPassword && (
                  <div className="mt-2 flex items-center">
                    {passwordMatch ? (
                      <CheckCircleIcon className="h-4 w-4 text-success-400 mr-1" />
                    ) : (
                      <XCircleIcon className="h-4 w-4 text-danger-400 mr-1" />
                    )}
                    <span className={`text-xs ${passwordMatch ? 'text-success-400' : 'text-danger-400'}`}>
                      {passwordMatch ? '密码匹配' : '密码不匹配'}
                    </span>
                  </div>
                )}
              </div>
            </div>

            {/* 服务条款 */}
            <div className="flex items-center">
              <input
                id="terms"
                name="terms"
                type="checkbox"
                required
                className="h-4 w-4 bg-dark-800 border-dark-600 rounded text-primary-600 focus:ring-primary-500 focus:ring-offset-0"
              />
              <label htmlFor="terms" className="ml-2 block text-sm text-gray-400">
                我同意{' '}
                <Link to="/terms" className="text-primary-400 hover:text-primary-300 transition-colors">
                  服务条款
                </Link>
                {' '}和{' '}
                <Link to="/privacy" className="text-primary-400 hover:text-primary-300 transition-colors">
                  隐私政策
                </Link>
              </label>
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
                    注册中...
                  </>
                ) : (
                  <>
                    创建账户
                    <ArrowRightIcon className="ml-2 h-4 w-4 group-hover:translate-x-1 transition-transform duration-200" />
                  </>
                )}
              </span>
            </button>
          </form>

          {/* 登录链接 */}
          <div className="mt-6 text-center">
            <p className="text-gray-400">
              已有账户？{' '}
              <Link 
                to="/login" 
                className="font-medium text-primary-400 hover:text-primary-300 transition-colors"
              >
                立即登录
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

export default Register;