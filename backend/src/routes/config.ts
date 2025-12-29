import { Router } from 'express';
import { ConfigController } from '../controllers/ConfigController.js';
import { authenticateToken, requireAdmin } from '../middleware/auth.js';

const router = Router();
const configController = new ConfigController();

// 所有配置管理路由都需要管理员权限
router.use(authenticateToken);
router.use(requireAdmin);

/**
 * @route   GET /api/config
 * @desc    获取所有配置项（按分类显示）
 * @access  Admin
 */
router.get('/', configController.getConfigs);

/**
 * @route   GET /api/config/category/:category
 * @desc    获取指定分类的配置
 * @access  Admin
 */
router.get('/category/:category', configController.getConfigsByCategory);

/**
 * @route   PUT /api/config
 * @desc    批量更新配置项
 * @access  Admin
 */
router.put('/', configController.updateConfigs);

/**
 * @route   PUT /api/config/:key
 * @desc    更新单个配置项
 * @access  Admin
 */
router.put('/:key', configController.updateConfig);

/**
 * @route   POST /api/config/:key/reset
 * @desc    重置配置到默认值
 * @access  Admin
 */
router.post('/:key/reset', configController.resetConfig);

/**
 * @route   GET /api/config/:key/history
 * @desc    获取配置变更历史
 * @access  Admin
 */
router.get('/:key/history', configController.getConfigHistory);

/**
 * @route   POST /api/config/test-email
 * @desc    测试邮件配置
 * @access  Admin
 */
router.post('/test-email', configController.testEmailConfig);

/**
 * @route   GET /api/config/export
 * @desc    导出配置
 * @access  Admin
 */
router.get('/export', configController.exportConfigs);

/**
 * @route   POST /api/config/import
 * @desc    导入配置
 * @access  Admin
 */
router.post('/import', configController.importConfigs);

/**
 * @route   GET /api/config/system-status
 * @desc    获取系统状态
 * @access  Admin
 */
router.get('/system-status', configController.getSystemStatus);

/**
 * @route   POST /api/config/backup
 * @desc    备份配置
 * @access  Admin
 */
router.post('/backup', configController.backupConfigs);

/**
 * @route   POST /api/config/restore
 * @desc    恢复配置
 * @access  Admin
 */
router.post('/restore', configController.restoreConfigs);

export default router;