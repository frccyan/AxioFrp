import db from '../config/database.js';
import { Validator } from '../utils/Validator.js';

export interface ConfigItem {
    setting_key: string;
    setting_value: string;
    description: string;
    config_type: string;
    category: string;
    is_editable: boolean;
    validation_rule: string;
    display_order: number;
}

export interface ConfigCategory {
    name: string;
    label: string;
    description: string;
    icon: string;
    configs: ConfigItem[];
}

export interface ConfigHistory {
    id: number;
    setting_key: string;
    old_value: string;
    new_value: string;
    changed_by: string;
    change_reason: string;
    created_at: Date;
}

export interface SystemStatus {
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

export class ConfigService {
    private validator: Validator;

    constructor() {
        this.validator = new Validator();
    }

    /**
     * 获取所有配置项（按分类组织）
     */
    public async getConfigs(): Promise<ConfigCategory[]> {
        const query = `
            SELECT * FROM settings 
            WHERE is_editable = TRUE 
            ORDER BY config_type, category, display_order
        `;
        const results = await db.query(query);

        // 按分类组织配置
        const categories = this.organizeConfigs(results);
        return categories;
    }

    /**
     * 获取指定分类的配置
     */
    public async getConfigsByCategory(category: string): Promise<ConfigItem[]> {
        const query = `
            SELECT * FROM settings 
            WHERE category = ? AND is_editable = TRUE 
            ORDER BY display_order
        `;
        const results = await db.query(query, [category]);
        return results;
    }

    /**
     * 获取所有配置项（包括不可编辑的）
     */
    public async getAllConfigs(): Promise<ConfigItem[]> {
        const query = 'SELECT * FROM settings ORDER BY config_type, category, display_order';
        const results = await db.query(query);
        return results;
    }

    /**
     * 更新配置项
     */
    public async updateConfig(key: string, value: string, username: string): Promise<any> {
        // 获取旧值
        const oldConfig = await this.getConfig(key);
        
        // 更新配置
        const query = 'UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?';
        await db.query(query, [value, key]);

        return {
            key,
            old_value: oldConfig?.setting_value,
            new_value: value,
            updated_by: username
        };
    }

    /**
     * 批量更新配置
     */
    public async updateConfigs(updates: Record<string, string>, username: string): Promise<any[]> {
        const results = [];
        
        for (const [key, value] of Object.entries(updates)) {
            try {
                const result = await this.updateConfig(key, value, username);
                results.push(result);
            } catch (error) {
                console.error(`更新配置 ${key} 失败:`, error);
                results.push({ key, error: error.message });
            }
        }

        return results;
    }

    /**
     * 重置配置到默认值
     */
    public async resetConfig(key: string, username: string): Promise<any> {
        // 获取当前值
        const oldConfig = await this.getConfig(key);
        
        // 获取默认值（这里需要根据配置项的默认值来设置）
        const defaultValue = this.getDefaultValue(key);
        
        // 更新配置
        await this.updateConfig(key, defaultValue, username);

        return {
            key,
            old_value: oldConfig?.setting_value,
            new_value: defaultValue,
            updated_by: username
        };
    }

    /**
     * 验证单个配置项
     */
    public async validateConfig(key: string, value: string): Promise<{ valid: boolean; error?: string }> {
        try {
            const config = await this.getConfig(key);
            if (!config) {
                return { valid: false, error: '配置项不存在' };
            }

            if (!config.validation_rule) {
                return { valid: true };
            }

            const validation = this.validator.validate(value, config.validation_rule);
            return validation;
        } catch (error) {
            return { valid: false, error: '配置验证失败' };
        }
    }

    /**
     * 验证多个配置项
     */
    public async validateConfigs(updates: Record<string, string>): Promise<{ valid: boolean; errors?: Record<string, string> }> {
        const errors: Record<string, string> = {};
        let valid = true;

        for (const [key, value] of Object.entries(updates)) {
            const validation = await this.validateConfig(key, value);
            if (!validation.valid) {
                errors[key] = validation.error;
                valid = false;
            }
        }

        return { valid, errors: valid ? undefined : errors };
    }

    /**
     * 记录配置变更历史
     */
    public async recordConfigChange(key: string, value: string, username: string, reason: string): Promise<void> {
        const query = `
            INSERT INTO config_history (setting_key, new_value, changed_by, change_reason)
            VALUES (?, ?, ?, ?)
        `;
        await db.query(query, [key, value, username, reason]);
    }

    /**
     * 批量记录配置变更历史
     */
    public async recordConfigChanges(updates: Record<string, string>, username: string, reason: string): Promise<void> {
        for (const [key, value] of Object.entries(updates)) {
            await this.recordConfigChange(key, value, username, reason);
        }
    }

    /**
     * 获取配置变更历史
     */
    public async getConfigHistory(key: string, page: number = 1, limit: number = 20): Promise<{
        history: ConfigHistory[];
        total: number;
        page: number;
        limit: number;
    }> {
        const offset = (page - 1) * limit;
        
        // 获取历史记录
        const historyQuery = `
            SELECT * FROM config_history 
            WHERE setting_key = ? 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        `;
        const history = await this.db.query(historyQuery, [key, limit, offset]);

        // 获取总数
        const countQuery = 'SELECT COUNT(*) as total FROM config_history WHERE setting_key = ?';
        const countResult = await this.db.query(countQuery, [key]);
        const total = countResult[0].total;

        return {
            history,
            total,
            page,
            limit
        };
    }

    /**
     * 获取邮件配置
     */
    public async getEmailConfig(): Promise<Record<string, string>> {
        const emailConfigs = await this.getConfigsByCategory('smtp');
        const config: Record<string, string> = {};
        
        emailConfigs.forEach(item => {
            config[item.setting_key] = item.setting_value;
        });

        return config;
    }

    /**
     * 获取系统状态
     */
    public async getSystemStatus(): Promise<SystemStatus> {
        // 数据库状态
        const dbStatus = await this.getDatabaseStatus();
        
        // Redis状态
        const redisStatus = await this.getRedisStatus();
        
        // 服务状态
        const servicesStatus = await this.getServicesStatus();
        
        // 性能状态
        const performanceStatus = await this.getPerformanceStatus();

        return {
            database: dbStatus,
            redis: redisStatus,
            services: servicesStatus,
            performance: performanceStatus
        };
    }

    /**
     * 备份配置
     */
    public async backupConfigs(username: string): Promise<any> {
        const configs = await this.getAllConfigs();
        const backupData = {
            id: Date.now(),
            created_by: username,
            created_at: new Date(),
            configs: configs
        };

        // 这里可以将备份存储到文件系统或数据库
        // 为简化示例，返回备份ID和数据
        return backupData;
    }

    /**
     * 恢复配置
     */
    public async restoreConfigs(backupId: string, username: string): Promise<any> {
        // 这里需要从备份存储中恢复配置
        // 为简化示例，假设可以从某个地方获取备份数据
        const backupData = await this.getBackupData(backupId);
        
        if (!backupData) {
            throw new Error('备份不存在');
        }

        // 恢复配置
        const results = await this.updateConfigs(
            backupData.configs.reduce((acc, config) => {
                acc[config.setting_key] = config.setting_value;
                return acc;
            }, {}),
            username
        );

        return {
            backup_id: backupId,
            restored_by: username,
            restored_at: new Date(),
            results
        };
    }

    /**
     * 导入配置
     */
    public async importConfigs(configs: ConfigItem[], username: string): Promise<any[]> {
        const results = [];
        
        for (const config of configs) {
            try {
                await this.updateConfig(config.setting_key, config.setting_value, username);
                results.push({ key: config.setting_key, success: true });
            } catch (error) {
                results.push({ key: config.setting_key, success: false, error: error.message });
            }
        }

        return results;
    }

    // 私有方法

    /**
     * 获取单个配置项
     */
    private async getConfig(key: string): Promise<ConfigItem | null> {
        const query = 'SELECT * FROM settings WHERE setting_key = ?';
        const results = await this.db.query(query, [key]);
        return results.length > 0 ? results[0] : null;
    }

    /**
     * 获取配置项的默认值
     */
    private getDefaultValue(key: string): string {
        const defaults: Record<string, string> = {
            'site_name': 'AxioFrp',
            'smtp_enabled': 'false',
            'registration_enabled': 'true',
            'max_proxies_per_user': '10',
            'theme_primary_color': '#6366f1',
            'maintenance_mode': 'false'
            // 更多默认值...
        };

        return defaults[key] || '';
    }

    /**
     * 按分类组织配置
     */
    private organizeConfigs(configs: ConfigItem[]): ConfigCategory[] {
        const categoryMap: Record<string, ConfigCategory> = {
            'general': {
                name: 'general',
                label: '基础设置',
                description: '网站基本信息配置',
                icon: 'cog',
                configs: []
            },
            'smtp': {
                name: 'smtp',
                label: '邮件配置',
                description: 'SMTP邮件服务设置',
                icon: 'mail',
                configs: []
            },
            'registration': {
                name: 'registration',
                label: '注册设置',
                description: '用户注册相关配置',
                icon: 'user-plus',
                configs: []
            },
            'security': {
                name: 'security',
                label: '安全设置',
                description: '系统安全相关配置',
                icon: 'shield',
                configs: []
            },
            'access': {
                name: 'access',
                label: '访问控制',
                description: '用户访问权限设置',
                icon: 'key',
                configs: []
            },
            'proxies': {
                name: 'proxies',
                label: '隧道限制',
                description: '隧道创建和管理限制',
                icon: 'network',
                configs: []
            },
            'traffic': {
                name: 'traffic',
                label: '流量管理',
                description: '流量统计和限制设置',
                icon: 'bar-chart',
                configs: []
            },
            'appearance': {
                name: 'appearance',
                label: '外观设置',
                description: '界面外观和主题配置',
                icon: 'palette',
                configs: []
            },
            'features': {
                name: 'features',
                label: '功能开关',
                description: '系统功能启用/禁用',
                icon: 'toggle-on',
                configs: []
            },
            'performance': {
                name: 'performance',
                label: '性能设置',
                description: '系统性能优化配置',
                icon: 'speedometer',
                configs: []
            }
        };

        // 将配置项归类
        configs.forEach(config => {
            if (categoryMap[config.category]) {
                categoryMap[config.category].configs.push(config);
            }
        });

        // 转换为数组并排序
        return Object.values(categoryMap).filter(category => category.configs.length > 0);
    }

    /**
     * 获取数据库状态
     */
    private async getDatabaseStatus(): Promise<any> {
        try {
            const result = await this.db.query('SELECT VERSION() as version, CONNECTION_ID() as connection_id');
            return {
                status: 'connected',
                connections: 1, // 简化处理
                version: result[0].version
            };
        } catch (error) {
            return {
                status: 'disconnected',
                connections: 0,
                version: 'unknown'
            };
        }
    }

    /**
     * 获取Redis状态
     */
    private async getRedisStatus(): Promise<any> {
        // 这里需要实现Redis连接检查
        return {
            status: 'connected',
            memory_usage: '5MB',
            version: '7.0'
        };
    }

    /**
     * 获取服务状态
     */
    private async getServicesStatus(): Promise<any> {
        return {
            backend: 'running',
            frontend: 'running',
            database: 'running'
        };
    }

    /**
     * 获取性能状态
     */
    private async getPerformanceStatus(): Promise<any> {
        return {
            cpu_usage: 15,
            memory_usage: 60,
            disk_usage: 35,
            uptime: 86400 // 秒
        };
    }

    /**
     * 获取备份数据
     */
    private async getBackupData(backupId: string): Promise<any> {
        // 这里需要实现备份数据获取逻辑
        return null;
    }
}