import jwt from 'jsonwebtoken';
import bcrypt from 'bcryptjs';
import userService from './UserService.js';

export interface JwtPayload {
  userId: number;
  username: string;
  email: string;
}

export class AuthService {
  private readonly JWT_SECRET: string;
  private readonly JWT_EXPIRES_IN: string;

  constructor() {
    this.JWT_SECRET = process.env.JWT_SECRET || 'axiofrp_jwt_secret_key';
    this.JWT_EXPIRES_IN = process.env.JWT_EXPIRES_IN || '7d';
  }

  /**
   * 用户登录
   */
  async login(username: string, password: string): Promise<{ user: any; token: string } | null> {
    const user = await userService.getUserByUsername(username);
    
    if (!user) {
      throw new Error('用户不存在');
    }

    if (user.status !== 'active') {
      throw new Error('用户账户已被禁用');
    }

    const isPasswordValid = await userService.verifyPassword(password, user.password);
    if (!isPasswordValid) {
      throw new Error('密码错误');
    }

    // 更新最后登录时间
    await userService.updateUser(user.id, { last_login: new Date() });

    // 生成JWT token
    const token = this.generateToken({
      userId: user.id,
      username: user.username,
      email: user.email
    });

    // 返回用户信息（排除密码）
    const { password: _, ...userWithoutPassword } = user;
    
    return {
      user: userWithoutPassword,
      token
    };
  }

  /**
   * 用户注册
   */
  async register(data: {
    username: string;
    email: string;
    password: string;
    group_name?: string;
  }): Promise<{ user: any; token: string }> {
    // 检查用户名是否可用
    if (!(await userService.isUsernameAvailable(data.username))) {
      throw new Error('用户名已被占用');
    }

    // 检查邮箱是否可用
    if (!(await userService.isEmailAvailable(data.email))) {
      throw new Error('邮箱已被注册');
    }

    // 创建用户
    const user = await userService.createUser({
      username: data.username,
      email: data.email,
      password: data.password,
      group_name: data.group_name || 'default'
    });

    // 生成JWT token
    const token = this.generateToken({
      userId: user.id,
      username: user.username,
      email: user.email
    });

    // 返回用户信息（排除密码）
    const { password: _, ...userWithoutPassword } = user;
    
    return {
      user: userWithoutPassword,
      token
    };
  }

  /**
   * 生成JWT token
   */
  generateToken(payload: JwtPayload): string {
    return jwt.sign(payload, this.JWT_SECRET, {
      expiresIn: this.JWT_EXPIRES_IN
    });
  }

  /**
   * 验证JWT token
   */
  verifyToken(token: string): JwtPayload | null {
    try {
      return jwt.verify(token, this.JWT_SECRET) as JwtPayload;
    } catch (error) {
      return null;
    }
  }

  /**
   * 刷新token
   */
  refreshToken(oldToken: string): string | null {
    const payload = this.verifyToken(oldToken);
    if (!payload) {
      return null;
    }

    return this.generateToken(payload);
  }

  /**
   * 修改密码
   */
  async changePassword(userId: number, oldPassword: string, newPassword: string): Promise<boolean> {
    const user = await userService.getUserById(userId);
    if (!user) {
      throw new Error('用户不存在');
    }

    const isOldPasswordValid = await userService.verifyPassword(oldPassword, user.password);
    if (!isOldPasswordValid) {
      throw new Error('原密码错误');
    }

    const hashedNewPassword = await bcrypt.hash(newPassword, 12);
    await userService.updateUser(userId, { 
      password: hashedNewPassword 
    } as any);

    return true;
  }

  /**
   * 重置密码（管理员功能）
   */
  async resetPassword(userId: number, newPassword: string): Promise<boolean> {
    const user = await userService.getUserById(userId);
    if (!user) {
      throw new Error('用户不存在');
    }

    const hashedNewPassword = await bcrypt.hash(newPassword, 12);
    await userService.updateUser(userId, { 
      password: hashedNewPassword 
    } as any);

    return true;
  }

  /**
   * 检查用户权限
   */
  async checkPermission(userId: number, _requiredPermission: string): Promise<boolean> {
    const user = await userService.getUserById(userId);
    if (!user) {
      return false;
    }

    // 这里可以根据用户组检查权限
    // 暂时简单实现：管理员有所有权限
    if (user.group_name === 'admin') {
      return true;
    }

    // 其他用户组权限检查逻辑
    // 可以根据需要扩展

    return false;
  }

  /**
   * 生成密码重置token（用于忘记密码功能）
   */
  generatePasswordResetToken(email: string): string {
    return jwt.sign({ email, type: 'password_reset' }, this.JWT_SECRET, {
      expiresIn: '1h'
    });
  }

  /**
   * 验证密码重置token
   */
  verifyPasswordResetToken(token: string): { email: string; type: string } | null {
    try {
      const payload = jwt.verify(token, this.JWT_SECRET) as any;
      if (payload.type === 'password_reset') {
        return { email: payload.email, type: payload.type };
      }
      return null;
    } catch (error) {
      return null;
    }
  }
}

export default new AuthService();