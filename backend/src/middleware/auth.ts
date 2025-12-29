import { Request, Response, NextFunction } from 'express';
import authService from '../services/AuthService.js';

export interface AuthRequest extends Request {
  user?: any;
}

/**
 * JWT认证中间件
 */
export const authenticateToken = (req: AuthRequest, res: Response, next: NextFunction) => {
  const authHeader = req.headers.authorization;
  const token = authHeader && authHeader.split(' ')[1]; // Bearer TOKEN

  if (!token) {
    return res.status(401).json({
      success: false,
      message: '访问令牌缺失'
    });
  }

  const payload = authService.verifyToken(token);
  if (!payload) {
    return res.status(403).json({
      success: false,
      message: '令牌无效或已过期'
    });
  }

  req.user = payload;
  next();
};

/**
 * 管理员权限检查中间件
 */
export const requireAdmin = async (req: AuthRequest, res: Response, next: NextFunction) => {
  if (!req.user) {
    return res.status(401).json({
      success: false,
      message: '未认证'
    });
  }

  const hasPermission = await authService.checkPermission(req.user.userId, 'admin');
  if (!hasPermission) {
    return res.status(403).json({
      success: false,
      message: '权限不足，需要管理员权限'
    });
  }

  next();
};

/**
 * 检查用户是否是自己或管理员
 */
export const requireSelfOrAdmin = async (req: AuthRequest, res: Response, next: NextFunction) => {
  if (!req.user) {
    return res.status(401).json({
      success: false,
      message: '未认证'
    });
  }

  const targetUsername = req.params.username || req.body.username;
  
  // 如果是管理员，允许操作任何用户
  const isAdmin = await authService.checkPermission(req.user.userId, 'admin');
  if (isAdmin) {
    return next();
  }

  // 普通用户只能操作自己的数据
  if (req.user.username !== targetUsername) {
    return res.status(403).json({
      success: false,
      message: '只能操作自己的数据'
    });
  }

  next();
};