import { Request, Response, NextFunction } from 'express';
import authService from '../services/AuthService.js';

export interface AuthRequest extends Request {
  user?: any;
}

/**
 * JWT认证中间件
 */
export const authenticateToken = (req: AuthRequest, res: Response, next: NextFunction): void => {
  const authHeader = req.headers.authorization;
  const token = authHeader && authHeader.split(' ')[1]; // Bearer TOKEN

  if (!token) {
    res.status(401).json({
      success: false,
      message: '访问令牌缺失'
    });
    return;
  }

  const payload = authService.verifyToken(token);
  if (!payload) {
    res.status(403).json({
      success: false,
      message: '令牌无效或已过期'
    });
    return;
  }

  req.user = payload;
  next();
};

/**
 * 管理员权限检查中间件
 */
export const requireAdmin = async (req: AuthRequest, res: Response, next: NextFunction): Promise<void> => {
  if (!req.user) {
    res.status(401).json({
      success: false,
      message: '未认证'
    });
    return;
  }

  const hasPermission = await authService.checkPermission(req.user.userId, 'admin');
  if (!hasPermission) {
    res.status(403).json({
      success: false,
      message: '权限不足，需要管理员权限'
    });
    return;
  }

  next();
};

/**
 * 检查用户是否是自己或管理员
 */
export const requireSelfOrAdmin = async (req: AuthRequest, res: Response, next: NextFunction): Promise<void> => {
  if (!req.user) {
    res.status(401).json({
      success: false,
      message: '未认证'
    });
    return;
  }

  const targetUsername = req.params.username || req.body.username;

  // 如果是管理员，允许操作任何用户
  const isAdmin = await authService.checkPermission(req.user.userId, 'admin');
  if (isAdmin) {
    next();
    return;
  }

  // 普通用户只能操作自己的数据
  if (req.user.username !== targetUsername) {
    res.status(403).json({
      success: false,
      message: '只能操作自己的数据'
    });
    return;
  }

  next();
};