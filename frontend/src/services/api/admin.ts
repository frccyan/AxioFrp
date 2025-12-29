import { apiRequest } from '../api';

// 系统配置相关接口
export const configApi = {
  // 获取配置列表
  getConfigs: (category?: string) => 
    apiRequest.get('/api/config', { params: { category } }),
  
  // 获取配置分类
  getCategories: () => 
    apiRequest.get('/api/config/categories'),
  
  // 更新配置
  updateConfig: (configKey: string, value: any) => 
    apiRequest.put(`/api/config/${configKey}`, { value }),
  
  // 批量更新配置
  updateBatchConfigs: (configs: { key: string; value: any }[]) => 
    apiRequest.put('/api/config/batch', { configs }),
  
  // 重置配置
  resetConfig: (configKey: string) => 
    apiRequest.post(`/api/config/${configKey}/reset`),
  
  // 测试邮件配置
  testEmail: (email: string) => 
    apiRequest.post('/api/config/test-email', { email }),
  
  // 导出配置
  exportConfigs: () => 
    apiRequest.get('/api/config/export'),
  
  // 导入配置
  importConfigs: (configData: any) => 
    apiRequest.post('/api/config/import', configData),
  
  // 获取配置历史
  getConfigHistory: (configKey?: string) => 
    apiRequest.get('/api/config/history', { params: { configKey } }),
  
  // 回滚配置
  rollbackConfig: (configKey: string, historyId: number) => 
    apiRequest.post(`/api/config/${configKey}/rollback`, { historyId }),
};

// 系统状态相关接口
export const systemApi = {
  // 获取系统状态
  getSystemStatus: () => 
    apiRequest.get('/api/config/system-status'),
  
  // 获取服务状态
  getServiceStatus: () => 
    apiRequest.get('/api/config/service-status'),
  
  // 获取数据库状态
  getDatabaseStatus: () => 
    apiRequest.get('/api/config/database-status'),
  
  // 重启服务
  restartService: (serviceName: string) => 
    apiRequest.post(`/api/config/restart-service/${serviceName}`),
  
  // 清理缓存
  clearCache: () => 
    apiRequest.post('/api/config/clear-cache'),
  
  // 获取系统日志
  getSystemLogs: (level?: string, limit?: number) => 
    apiRequest.get('/api/config/logs', { params: { level, limit } }),
};

export default { configApi, systemApi };