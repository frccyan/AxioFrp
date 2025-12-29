import { Router } from 'express';
import proxyController from '../controllers/ProxyController.js';
import { authenticateToken, requireAdmin, requireSelfOrAdmin } from '../middleware/auth.js';

const router = Router();

// 所有路由都需要认证
router.use(authenticateToken);

// 用户隧道管理
router.get('/', proxyController.getUserProxies);
router.post('/', proxyController.createProxy);
router.get('/stats', proxyController.getProxyStats);

// 单个隧道操作
router.get('/:id', proxyController.getUserProxies); // 获取特定隧道详情
router.put('/:id', proxyController.updateProxy);
router.delete('/:id', proxyController.deleteProxy);
router.get('/:id/config', proxyController.getProxyConfig);
router.post('/:id/toggle', proxyController.toggleProxy);

// 管理员功能
router.get('/admin/all', requireAdmin, proxyController.getAllProxies);

export default router;