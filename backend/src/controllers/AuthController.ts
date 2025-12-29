import { Request, Response } from 'express';
import { AuthRequest } from '../middleware/auth.js';
import authService from '../services/AuthService.js';
import userService from '../services/UserService.js';

export class AuthController {
  /**
   * 用户登录
   */
  async login(req: Request, res: Response) {
    try {
      const { username, password } = req.body;

      if (!username || !password) {
        return res.status(400).json({
          success: false,
          message: '用户名和密码不能为空'
        });
      }

      const result = await authService.login(username, password);
      
      res.json({
        success: true,
        message: '登录成功',
        data: result
      });
    } catch (error: any) {
      res.status(400).json({
        success: false,
        message: error.message
      });
    }
  }

  /**
   * 用户注册
   */
  async register(req: Request, res: Response) {
    try {
      const { username, email, password, confirmPassword } = req.body;

      if (!username || !email || !password) {
        return res.status(400).json({
          success: false,
          message: '用户名、邮箱和密码不能为空'
        });
      }

      if (password !== confirmPassword) {
        return res.status(400).json({
          success: false,
          message: '两次输入的密码不一致'
        });
      }

      if (password.length < 6) {
        return res.status(400).json({
          success: false,
          message: '密码长度至少6位'
        });
      }

      const result = await authService.register({
        username,
        email,
        password
      });

      res.status(201).json({
        success: true,
        message: '注册成功',
        data: result
      });
    } catch (error: any) {
      res.status(400).json({
        success: false,
        message: error.message
      });
    }
  }

  /**
   * 获取当前用户信息
   */
  async getProfile(req: AuthRequest, res: Response) {
    try {
      const user = await userService.getUserById(req.user.userId);
      
      if (!user) {
        return res.status(404).json({
          success: false,
          message: '用户不存在'
        });
      }

      // 排除密码字段
      const { password, ...userWithoutPassword } = user;

      res.json({
        success: true,
        data: userWithoutPassword
      });
    } catch (error: any) {
      res.status(500).json({
        success: false,
        message: '获取用户信息失败'
      });
    }
  }

  /**
   * 修改密码
   */
  async changePassword(req: AuthRequest, res: Response) {
    try {
      const { oldPassword, newPassword, confirmPassword } = req.body;

      if (!oldPassword || !newPassword || !confirmPassword) {
        return res.status(400).json({
          success: false,
          message: '所有密码字段不能为空'
        });
      }

      if (newPassword !== confirmPassword) {
        return res.status(400).json({
          success: false,
          message: '新密码和确认密码不一致'
        });
      }

      if (newPassword.length < 6) {
        return res.status(400).json({
          success: false,
          message: '新密码长度至少6位'
        });
      }

      await authService.changePassword(req.user.userId, oldPassword, newPassword);

      res.json({
        success: true,
        message: '密码修改成功'
      });
    } catch (error: any) {
      res.status(400).json({
        success: false,
        message: error.message
      });
    }
  }

  /**
   * 刷新token
   */
  async refreshToken(req: AuthRequest, res: Response) {
    try {
      const authHeader = req.headers.authorization;
      const oldToken = authHeader && authHeader.split(' ')[1];

      if (!oldToken) {
        return res.status(400).json({
          success: false,
          message: '令牌不能为空'
        });
      }

      const newToken = authService.refreshToken(oldToken);

      if (!newToken) {
        return res.status(400).json({
          success: false,
          message: '令牌刷新失败'
        });
      }

      res.json({
        success: true,
        data: {
          token: newToken
        }
      });
    } catch (error: any) {
      res.status(400).json({
        success: false,
        message: error.message
      });
    }
  }

  /**
   * 忘记密码 - 发送重置邮件
   */
  async forgotPassword(req: Request, res: Response) {
    try {
      const { email } = req.body;

      if (!email) {
        return res.status(400).json({
          success: false,
          message: '邮箱不能为空'
        });
      }

      const user = await userService.getUserByEmail(email);
      if (!user) {
        // 出于安全考虑，不透露用户是否存在
        return res.json({
          success: true,
          message: '如果邮箱存在，重置邮件已发送'
        });
      }

      // 生成重置token
      const resetToken = authService.generatePasswordResetToken(email);

      // TODO: 发送重置邮件
      console.log('Password reset token:', resetToken);

      res.json({
        success: true,
        message: '密码重置邮件已发送'
      });
    } catch (error: any) {
      res.status(500).json({
        success: false,
        message: '发送重置邮件失败'
      });
    }
  }

  /**
   * 重置密码
   */
  async resetPassword(req: Request, res: Response) {
    try {
      const { token, newPassword, confirmPassword } = req.body;

      if (!token || !newPassword || !confirmPassword) {
        return res.status(400).json({
          success: false,
          message: '所有字段不能为空'
        });
      }

      if (newPassword !== confirmPassword) {
        return res.status(400).json({
          success: false,
          message: '两次输入的密码不一致'
        });
      }

      const payload = authService.verifyPasswordResetToken(token);
      if (!payload) {
        return res.status(400).json({
          success: false,
          message: '重置令牌无效或已过期'
        });
      }

      const user = await userService.getUserByEmail(payload.email);
      if (!user) {
        return res.status(404).json({
          success: false,
          message: '用户不存在'
        });
      }

      await authService.resetPassword(user.id, newPassword);

      res.json({
        success: true,
        message: '密码重置成功'
      });
    } catch (error: any) {
      res.status(400).json({
        success: false,
        message: error.message
      });
    }
  }

  /**
   * 退出登录
   */
  async logout(req: AuthRequest, res: Response) {
    // JWT是无状态的，客户端需要清除token
    res.json({
      success: true,
      message: '退出登录成功'
    });
  }
}

export default new AuthController();