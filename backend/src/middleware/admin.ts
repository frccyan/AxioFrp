import { Response, NextFunction } from 'express';
import { AuthRequest } from './auth.js';
import Database from '../config/database.js';

export class AdminMiddleware {
    /**
     * 要求管理员权限
     */
    public static requireAdmin = async (req: AuthRequest, res: Response, next: NextFunction) => {
        try {
            if (!req.user) {
                return res.status(401).json({
                    success: false,
                    message: '未授权访问'
                });
            }

            const query = 'SELECT is_admin FROM users WHERE username = ?';
            const results = await Database.query(query, [req.user.username]);

            if (results.length === 0) {
                return res.status(401).json({
                    success: false,
                    message: '用户不存在'
                });
            }

            const user = results[0];
            if (!user.is_admin) {
                return res.status(403).json({
                    success: false,
                    message: '需要管理员权限'
                });
            }

            req.user.is_admin = true;
            next();
        } catch (error) {
            console.error('管理员权限检查失败:', error);
            return res.status(500).json({
                success: false,
                message: '权限检查失败'
            });
        }
    };

    /**
     * 检查是否为管理员（不强制要求）
     */
    public static checkAdmin = async (req: AuthRequest, res: Response, next: NextFunction) => {
        try {
            if (!req.user) {
                return next();
            }

            const query = 'SELECT is_admin FROM users WHERE username = ?';
            const results = await Database.query(query, [req.user.username]);

            if (results.length > 0) {
                req.user.is_admin = results[0].is_admin;
            }

            next();
        } catch (error) {
            console.error('管理员身份检查失败:', error);
            next();
        }
    };
}