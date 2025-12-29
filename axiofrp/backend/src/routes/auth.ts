import { Router } from 'express';
import authController from '../controllers/AuthController.js';
import { authenticateToken } from '../middleware/auth.js';

const router = Router();

// 公开路由
router.post('/login', authController.login);
router.post('/register', authController.register);
router.post('/forgot-password', authController.forgotPassword);
router.post('/reset-password', authController.resetPassword);

// 需要认证的路由
router.get('/profile', authenticateToken, authController.getProfile);
router.post('/change-password', authenticateToken, authController.changePassword);
router.post('/refresh-token', authenticateToken, authController.refreshToken);
router.post('/logout', authenticateToken, authController.logout);

export default router;