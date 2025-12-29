import { Response } from 'express';
import { AuthRequest } from '../middleware/auth.js';
import { ConfigService } from '../services/ConfigService.js';
import { EmailService, EmailConfig } from '../services/EmailService.js';

export class ConfigController {
    private configService: ConfigService;
    private emailService: EmailService;

    constructor() {
        this.configService = new ConfigService();
        this.emailService = new EmailService();
    }

    /**
     * 获取所有配置项（分类显示）
     */
    public getConfigs = async (_req: AuthRequest, res: Response): Promise<void> => {
        try {
            const configs = await this.configService.getConfigs();
            res.json({
                success: true,
                data: configs,
                message: '获取配置成功'
            });
        } catch (error) {
            console.error('获取配置失败:', error);
            res.status(500).json({
                success: false,
                message: '获取配置失败'
            });
        }
    };

    /**
     * 获取指定分类的配置
     */
    public getConfigsByCategory = async (_req: AuthRequest, res: Response): Promise<void> => {
        try {
            const { category } = _req.params;
            const configs = await this.configService.getConfigsByCategory(category);
            res.json({
                success: true,
                data: configs,
                message: '获取配置成功'
            });
        } catch (error) {
            console.error('获取配置失败:', error);
            res.status(500).json({
                success: false,
                message: '获取配置失败'
            });
        }
    };

    /**
     * 更新配置项
     */
    public updateConfigs = async (req: AuthRequest, res: Response): Promise<void> => {
        try {
            const updates = req.body;
            const username = req.user?.username || 'system';

            // 验证配置项
            const validationResult = await this.configService.validateConfigs(updates);
            if (!validationResult.valid) {
                res.status(400).json({
                    success: false,
                    message: '配置验证失败',
                    errors: validationResult.errors
                });
                return;
            }

            // 更新配置
            const results = await this.configService.updateConfigs(updates, username);

            // 记录变更历史
            await this.configService.recordConfigChanges(updates, username, '管理员更新配置');

            res.json({
                success: true,
                data: results,
                message: '配置更新成功'
            });
        } catch (error) {
            console.error('更新配置失败:', error);
            res.status(500).json({
                success: false,
                message: '更新配置失败'
            });
        }
    };

    /**
     * 更新单个配置项
     */
    public updateConfig = async (req: AuthRequest, res: Response): Promise<void> => {
        try {
            const { key } = req.params;
            const { value, reason } = req.body;
            const username = req.user?.username || 'system';

            // 验证单个配置项
            const validationResult = await this.configService.validateConfig(key, value);
            if (!validationResult.valid) {
                res.status(400).json({
                    success: false,
                    message: validationResult.error
                });
                return;
            }

            // 更新配置
            const result = await this.configService.updateConfig(key, value, username);

            // 记录变更历史
            await this.configService.recordConfigChange(key, value, username, reason || '更新配置项');

            res.json({
                success: true,
                data: result,
                message: '配置更新成功'
            });
        } catch (error) {
            console.error('更新配置失败:', error);
            res.status(500).json({
                success: false,
                message: '更新配置失败'
            });
        }
    };

    /**
     * 重置配置到默认值
     */
    public resetConfig = async (req: AuthRequest, res: Response): Promise<void> => {
        try {
            const { key } = req.params;
            const username = req.user?.username || 'system';

            const result = await this.configService.resetConfig(key, username);

            // 记录变更历史
            await this.configService.recordConfigChange(key, result.old_value, username, '重置为默认值');

            res.json({
                success: true,
                data: result,
                message: '配置重置成功'
            });
        } catch (error) {
            console.error('重置配置失败:', error);
            res.status(500).json({
                success: false,
                message: '重置配置失败'
            });
        }
    };

    /**
     * 获取配置变更历史
     */
    public getConfigHistory = async (req: AuthRequest, res: Response): Promise<void> => {
        try {
            const { key } = req.params;
            const { page = 1, limit = 20 } = req.query;

            const history = await this.configService.getConfigHistory(
                key,
                parseInt(page as string),
                parseInt(limit as string)
            );

            res.json({
                success: true,
                data: history,
                message: '获取配置历史成功'
            });
        } catch (error) {
            console.error('获取配置历史失败:', error);
            res.status(500).json({
                success: false,
                message: '获取配置历史失败'
            });
        }
    };

    /**
     * 测试邮件配置
     */
    public testEmailConfig = async (req: AuthRequest, res: Response): Promise<void> => {
        try {
            const { to } = req.body;
            const username = req.user?.username || 'system';

            if (!to) {
                res.status(400).json({
                    success: false,
                    message: '请提供测试邮箱地址'
                });
                return;
            }

            // 获取当前邮件配置
            const emailConfig = await this.configService.getEmailConfig();

            // 临时更新邮件配置进行测试
            this.emailService.updateConfig(emailConfig as unknown as EmailConfig);

            // 发送测试邮件
            const testResult = await this.emailService.sendTestEmail(to);

            // 记录测试历史
            await this.configService.recordConfigChange(
                'smtp_test',
                JSON.stringify({ to, result: testResult.success }),
                username,
                '测试邮件配置'
            );

            res.json({
                success: testResult.success,
                message: testResult.success ? '邮件发送成功' : '邮件发送失败',
                data: testResult
            });
        } catch (error) {
            console.error('测试邮件配置失败:', error);
            res.status(500).json({
                success: false,
                message: '测试邮件配置失败'
            });
        }
    };

    /**
     * 导出配置
     */
    public exportConfigs = async (_req: AuthRequest, res: Response): Promise<void> => {
        try {
            const configs = await this.configService.getAllConfigs();

            // 过滤敏感信息
            const exportData = configs.filter(config => !config.setting_key.includes('password') && !config.setting_key.includes('secret'));

            res.json({
                success: true,
                data: exportData,
                message: '配置导出成功',
                exported_at: new Date().toISOString()
            });
        } catch (error) {
            console.error('导出配置失败:', error);
            res.status(500).json({
                success: false,
                message: '导出配置失败'
            });
        }
    };

    /**
     * 导入配置
     */
    public importConfigs = async (req: AuthRequest, res: Response): Promise<void> => {
        try {
            const { configs } = req.body;
            const username = req.user?.username || 'system';

            if (!Array.isArray(configs)) {
                res.status(400).json({
                    success: false,
                    message: '配置数据格式错误'
                });
                return;
            }

            // 验证配置项
            const updates: Record<string, any> = {};
            configs.forEach((config: any) => {
                updates[config.setting_key] = config.setting_value;
            });

            const validationResult = await this.configService.validateConfigs(updates);
            if (!validationResult.valid) {
                res.status(400).json({
                    success: false,
                    message: '配置验证失败',
                    errors: validationResult.errors
                });
                return;
            }

            // 导入配置
            const results = await this.configService.importConfigs(configs, username);

            // 记录变更历史
            await this.configService.recordConfigChanges(updates, username, '批量导入配置');

            res.json({
                success: true,
                data: results,
                message: '配置导入成功'
            });
        } catch (error) {
            console.error('导入配置失败:', error);
            res.status(500).json({
                success: false,
                message: '导入配置失败'
            });
        }
    };

    /**
     * 获取系统状态
     */
    public getSystemStatus = async (_req: AuthRequest, res: Response): Promise<void> => {
        try {
            const status = await this.configService.getSystemStatus();

            res.json({
                success: true,
                data: status,
                message: '获取系统状态成功'
            });
        } catch (error) {
            console.error('获取系统状态失败:', error);
            res.status(500).json({
                success: false,
                message: '获取系统状态失败'
            });
        }
    };

    /**
     * 备份配置
     */
    public backupConfigs = async (req: AuthRequest, res: Response): Promise<void> => {
        try {
            const username = req.user?.username || 'system';
            const backup = await this.configService.backupConfigs(username);

            res.json({
                success: true,
                data: backup,
                message: '配置备份成功'
            });
        } catch (error) {
            console.error('备份配置失败:', error);
            res.status(500).json({
                success: false,
                message: '备份配置失败'
            });
        }
    };

    /**
     * 恢复配置
     */
    public restoreConfigs = async (req: AuthRequest, res: Response): Promise<void> => {
        try {
            const { backupId } = req.body;
            const username = req.user?.username || 'system';

            const result = await this.configService.restoreConfigs(backupId, username);

            // 记录变更历史
            await this.configService.recordConfigChange(
                'config_restore',
                backupId,
                username,
                '恢复配置备份'
            );

            res.json({
                success: true,
                data: result,
                message: '配置恢复成功'
            });
        } catch (error) {
            console.error('恢复配置失败:', error);
            res.status(500).json({
                success: false,
                message: '恢复配置失败'
            });
        }
    };
}