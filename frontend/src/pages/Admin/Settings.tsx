import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { 
  Settings as SettingsIcon, 
  Mail, 
  Shield, 
  Users, 
  Activity,
  Palette,
  Zap,
  Database,
  Save,
  RotateCcw,
  TestTube,
  Download,
  Upload,
  History,
  Bell,
  CheckCircle,
  XCircle,
  AlertCircle,
  Loader2
} from 'lucide-react';

interface ConfigItem {
  setting_key: string;
  setting_value: string;
  description: string;
  config_type: string;
  category: string;
  is_editable: boolean;
  validation_rule: string;
  display_order: number;
}

interface ConfigCategory {
  name: string;
  label: string;
  description: string;
  icon: React.ComponentType<any>;
  configs: ConfigItem[];
}

interface ConfigHistory {
  id: number;
  setting_key: string;
  old_value: string;
  new_value: string;
  changed_by: string;
  change_reason: string;
  created_at: string;
}

const Settings: React.FC = () => {
  const [categories, setCategories] = useState<ConfigCategory[]>([]);
  const [activeCategory, setActiveCategory] = useState<string>('general');
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [changes, setChanges] = useState<Record<string, string>>({});
  const [testEmailLoading, setTestEmailLoading] = useState(false);
  const [notifications, setNotifications] = useState<Array<{
    id: string;
    type: 'success' | 'error' | 'warning';
    message: string;
  }>>([]);

  // 图标映射
  const iconMap: Record<string, React.ComponentType<any>> = {
    'general': SettingsIcon,
    'smtp': Mail,
    'registration': Users,
    'security': Shield,
    'access': Shield,
    'proxies': Activity,
    'traffic': Activity,
    'appearance': Palette,
    'features': Zap,
    'performance': Database,
  };

  // 加载配置
  const loadConfigs = async () => {
    try {
      setLoading(true);
      const response = await fetch('/api/config');
      const data = await response.json();
      
      if (data.success) {
        setCategories(data.data.map((cat: any) => ({
          ...cat,
          icon: iconMap[cat.name] || SettingsIcon
        })));
      } else {
        showNotification('error', '加载配置失败');
      }
    } catch (error) {
      console.error('加载配置失败:', error);
      showNotification('error', '加载配置失败');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadConfigs();
  }, []);

  // 显示通知
  const showNotification = (type: 'success' | 'error' | 'warning', message: string) => {
    const id = Date.now().toString();
    setNotifications(prev => [...prev, { id, type, message }]);
    
    setTimeout(() => {
      setNotifications(prev => prev.filter(n => n.id !== id));
    }, 3000);
  };

  // 处理配置变更
  const handleConfigChange = (key: string, value: string) => {
    setChanges(prev => ({
      ...prev,
      [key]: value
    }));
  };

  // 保存配置
  const saveConfigs = async () => {
    try {
      setSaving(true);
      const response = await fetch('/api/config', {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(changes)
      });

      const data = await response.json();
      
      if (data.success) {
        showNotification('success', '配置保存成功');
        setChanges({});
        await loadConfigs(); // 重新加载配置
      } else {
        showNotification('error', data.message || '保存配置失败');
        if (data.errors) {
          // 显示详细错误
          Object.entries(data.errors).forEach(([key, error]) => {
            showNotification('error', `${key}: ${error}`);
          });
        }
      }
    } catch (error) {
      console.error('保存配置失败:', error);
      showNotification('error', '保存配置失败');
    } finally {
      setSaving(false);
    }
  };

  // 重置配置
  const resetConfig = async (key: string) => {
    try {
      const response = await fetch(`/api/config/${key}/reset`, {
        method: 'POST'
      });

      const data = await response.json();
      
      if (data.success) {
        showNotification('success', '配置重置成功');
        await loadConfigs();
        setChanges(prev => {
          const newChanges = { ...prev };
          delete newChanges[key];
          return newChanges;
        });
      } else {
        showNotification('error', data.message || '重置配置失败');
      }
    } catch (error) {
      console.error('重置配置失败:', error);
      showNotification('error', '重置配置失败');
    }
  };

  // 测试邮件配置
  const testEmailConfig = async () => {
    const testEmail = prompt('请输入测试邮箱地址：');
    if (!testEmail) return;

    try {
      setTestEmailLoading(true);
      const response = await fetch('/api/config/test-email', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ to: testEmail })
      });

      const data = await response.json();
      
      if (data.success) {
        showNotification('success', '测试邮件发送成功，请检查收件箱');
      } else {
        showNotification('error', data.message || '邮件发送失败');
      }
    } catch (error) {
      console.error('测试邮件失败:', error);
      showNotification('error', '测试邮件失败');
    } finally {
      setTestEmailLoading(false);
    }
  };

  // 导出配置
  const exportConfigs = async () => {
    try {
      const response = await fetch('/api/config/export');
      const data = await response.json();
      
      if (data.success) {
        const blob = new Blob([JSON.stringify(data.data, null, 2)], {
          type: 'application/json'
        });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `axiofrp-config-${new Date().toISOString().split('T')[0]}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
        
        showNotification('success', '配置导出成功');
      } else {
        showNotification('error', '导出配置失败');
      }
    } catch (error) {
      console.error('导出配置失败:', error);
      showNotification('error', '导出配置失败');
    }
  };

  // 渲染配置项
  const renderConfigItem = (config: ConfigItem) => {
    const currentValue = changes[config.setting_key] !== undefined 
      ? changes[config.setting_key] 
      : config.setting_value;

    const hasChanges = changes[config.setting_key] !== undefined;

    const renderInput = () => {
      // 根据配置类型和验证规则渲染不同的输入组件
      if (config.setting_key.includes('password') || config.setting_key.includes('secret')) {
        return (
          <div className="relative">
            <input
              type="password"
              value={currentValue}
              onChange={(e) => handleConfigChange(config.setting_key, e.target.value)}
              className="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg focus:border-purple-400 focus:outline-none transition-colors"
              placeholder="输入密码"
            />
            <button
              type="button"
              onClick={() => {
                const input = document.querySelector(`input[name="${config.setting_key}"]`) as HTMLInputElement;
                input.type = input.type === 'password' ? 'text' : 'password';
              }}
              className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white"
            >
              👁️
            </button>
          </div>
        );
      }

      if (config.validation_rule?.includes('boolean')) {
        return (
          <select
            value={currentValue}
            onChange={(e) => handleConfigChange(config.setting_key, e.target.value)}
            className="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg focus:border-purple-400 focus:outline-none transition-colors"
          >
            <option value="true">启用</option>
            <option value="false">禁用</option>
          </select>
        );
      }

      if (config.validation_rule?.includes('enum:')) {
        const options = config.validation_rule.split('enum:')[1].split(',');
        return (
          <select
            value={currentValue}
            onChange={(e) => handleConfigChange(config.setting_key, e.target.value)}
            className="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg focus:border-purple-400 focus:outline-none transition-colors"
          >
            {options.map(option => (
              <option key={option} value={option}>
                {option === 'daily' ? '每日' :
                 option === 'weekly' ? '每周' :
                 option === 'monthly' ? '每月' :
                 option === 'yearly' ? '每年' :
                 option === 'light' ? '浅色' :
                 option === 'dark' ? '深色' :
                 option === 'auto' ? '自动' : option}
              </option>
            ))}
          </select>
        );
      }

      if (config.validation_rule?.includes('color')) {
        return (
          <div className="flex items-center gap-3">
            <input
              type="color"
              value={currentValue}
              onChange={(e) => handleConfigChange(config.setting_key, e.target.value)}
              className="w-16 h-10 bg-white/5 border border-white/10 rounded cursor-pointer"
            />
            <input
              type="text"
              value={currentValue}
              onChange={(e) => handleConfigChange(config.setting_key, e.target.value)}
              className="flex-1 px-4 py-2 bg-white/5 border border-white/10 rounded-lg focus:border-purple-400 focus:outline-none transition-colors"
              placeholder="#000000"
            />
          </div>
        );
      }

      if (config.validation_rule?.includes('number')) {
        return (
          <input
            type="number"
            value={currentValue}
            onChange={(e) => handleConfigChange(config.setting_key, e.target.value)}
            className="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg focus:border-purple-400 focus:outline-none transition-colors"
          />
        );
      }

      if (config.validation_rule?.includes('email')) {
        return (
          <input
            type="email"
            value={currentValue}
            onChange={(e) => handleConfigChange(config.setting_key, e.target.value)}
            className="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg focus:border-purple-400 focus:outline-none transition-colors"
            placeholder="email@example.com"
          />
        );
      }

      // 默认文本输入
      return (
        <input
          type="text"
          value={currentValue}
          onChange={(e) => handleConfigChange(config.setting_key, e.target.value)}
          className="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg focus:border-purple-400 focus:outline-none transition-colors"
        />
      );
    };

    return (
      <motion.div
        key={config.setting_key}
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.3 }}
        className={`p-4 bg-white/5 rounded-lg border ${hasChanges ? 'border-purple-400' : 'border-white/10'} transition-all`}
      >
        <div className="flex items-start justify-between mb-3">
          <div className="flex-1">
            <h4 className="font-medium text-white mb-1">
              {config.description}
              {hasChanges && <span className="ml-2 text-xs bg-purple-500 text-white px-2 py-1 rounded">已修改</span>}
            </h4>
            <p className="text-sm text-gray-400">
              {config.setting_key}
            </p>
          </div>
          <button
            onClick={() => resetConfig(config.setting_key)}
            className="p-2 text-gray-400 hover:text-white transition-colors"
            title="重置为默认值"
          >
            <RotateCcw className="w-4 h-4" />
          </button>
        </div>
        
        {renderInput()}
      </motion.div>
    );
  };

  const activeCategoryData = categories.find(cat => cat.name === activeCategory);

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
          <h1 className="text-2xl font-bold text-white">系统设置</h1>
          <p className="text-gray-400">管理和配置系统参数</p>
        </div>
        
        <div className="flex items-center gap-3">
          {activeCategory === 'smtp' && (
            <button
              onClick={testEmailConfig}
              disabled={testEmailLoading}
              className="flex items-center gap-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors disabled:opacity-50"
            >
              {testEmailLoading ? (
                <Loader2 className="w-4 h-4 animate-spin" />
              ) : (
                <TestTube className="w-4 h-4" />
              )}
              测试邮件
            </button>
          )}
          
          <button
            onClick={exportConfigs}
            className="flex items-center gap-2 px-4 py-2 bg-white/10 text-white rounded-lg hover:bg-white/20 transition-colors"
          >
            <Download className="w-4 h-4" />
            导出配置
          </button>
          
          <button
            onClick={saveConfigs}
            disabled={Object.keys(changes).length === 0 || saving}
            className="flex items-center gap-2 px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors disabled:opacity-50"
          >
            {saving ? (
              <Loader2 className="w-4 h-4 animate-spin" />
            ) : (
              <Save className="w-4 h-4" />
            )}
            保存更改 ({Object.keys(changes).length})
          </button>
        </div>
      </div>

      <div className="flex gap-6">
        {/* 分类导航 */}
        <div className="w-64 space-y-2">
          {categories.map((category) => {
            const Icon = category.icon;
            return (
              <button
                key={category.name}
                onClick={() => setActiveCategory(category.name)}
                className={`w-full flex items-center gap-3 px-4 py-3 rounded-lg text-left transition-all ${
                  activeCategory === category.name
                    ? 'bg-purple-500 text-white'
                    : 'bg-white/5 text-gray-300 hover:bg-white/10'
                }`}
              >
                <Icon className="w-5 h-5" />
                <div>
                  <div className="font-medium">{category.label}</div>
                  <div className="text-sm opacity-80">{category.description}</div>
                </div>
              </button>
            );
          })}
        </div>

        {/* 配置内容 */}
        <div className="flex-1">
          {activeCategoryData && (
            <motion.div
              key={activeCategory}
              initial={{ opacity: 0, x: 20 }}
              animate={{ opacity: 1, x: 0 }}
              transition={{ duration: 0.3 }}
              className="space-y-4"
            >
              <div className="flex items-center gap-3 mb-6">
                <activeCategoryData.icon className="w-6 h-6 text-purple-400" />
                <h2 className="text-xl font-bold text-white">{activeCategoryData.label}</h2>
                <p className="text-gray-400">{activeCategoryData.description}</p>
              </div>

              {activeCategoryData.configs.map(renderConfigItem)}
            </motion.div>
          )}
        </div>
      </div>

      {/* 通知提示 */}
      <div className="fixed top-4 right-4 z-50 space-y-2">
        <AnimatePresence>
          {notifications.map((notification) => (
            <motion.div
              key={notification.id}
              initial={{ opacity: 0, x: 100 }}
              animate={{ opacity: 1, x: 0 }}
              exit={{ opacity: 0, x: 100 }}
              className={`flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg ${
                notification.type === 'success' ? 'bg-green-500' :
                notification.type === 'error' ? 'bg-red-500' :
                'bg-yellow-500'
              } text-white min-w-[300px]`}
            >
              {notification.type === 'success' && <CheckCircle className="w-5 h-5" />}
              {notification.type === 'error' && <XCircle className="w-5 h-5" />}
              {notification.type === 'warning' && <AlertCircle className="w-5 h-5" />}
              <span>{notification.message}</span>
            </motion.div>
          ))}
        </AnimatePresence>
      </div>
    </div>
  );
};

export default Settings;