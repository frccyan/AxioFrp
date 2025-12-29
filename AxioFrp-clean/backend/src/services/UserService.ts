import bcrypt from 'bcryptjs';
import db from '../config/database.js';
import { User, UserCreateData, UserUpdateData } from '../models/User.js';

export class UserService {
  private static readonly SALT_ROUNDS = 12;

  /**
   * 创建新用户
   */
  async createUser(data: UserCreateData): Promise<User> {
    const hashedPassword = await bcrypt.hash(data.password, UserService.SALT_ROUNDS);
    
    const sql = `
      INSERT INTO users (username, email, password, group_name, traffic_limit, balance)
      VALUES (?, ?, ?, ?, ?, ?)
    `;
    
    const params = [
      data.username,
      data.email,
      hashedPassword,
      data.group_name || 'default',
      data.traffic_limit || 1073741824, // 1GB
      data.balance || 0.00
    ];
    
    const result = await db.execute(sql, params);
    
    return this.getUserById(result[0].insertId);
  }

  /**
   * 根据ID获取用户
   */
  async getUserById(id: number): Promise<User | null> {
    const sql = 'SELECT * FROM users WHERE id = ?';
    return await db.queryOne<User>(sql, [id]);
  }

  /**
   * 根据用户名获取用户
   */
  async getUserByUsername(username: string): Promise<User | null> {
    const sql = 'SELECT * FROM users WHERE username = ?';
    return await db.queryOne<User>(sql, [username]);
  }

  /**
   * 根据邮箱获取用户
   */
  async getUserByEmail(email: string): Promise<User | null> {
    const sql = 'SELECT * FROM users WHERE email = ?';
    return await db.queryOne<User>(sql, [email]);
  }

  /**
   * 验证用户密码
   */
  async verifyPassword(plainPassword: string, hashedPassword: string): Promise<boolean> {
    return await bcrypt.compare(plainPassword, hashedPassword);
  }

  /**
   * 更新用户信息
   */
  async updateUser(id: number, data: UserUpdateData): Promise<User | null> {
    const fields = [];
    const params = [];

    if (data.email) {
      fields.push('email = ?');
      params.push(data.email);
    }
    if (data.group_name) {
      fields.push('group_name = ?');
      params.push(data.group_name);
    }
    if (data.traffic_limit !== undefined) {
      fields.push('traffic_limit = ?');
      params.push(data.traffic_limit);
    }
    if (data.balance !== undefined) {
      fields.push('balance = ?');
      params.push(data.balance);
    }
    if (data.status) {
      fields.push('status = ?');
      params.push(data.status);
    }
    if (data.last_login) {
      fields.push('last_login = ?');
      params.push(data.last_login);
    }

    if (fields.length === 0) {
      return this.getUserById(id);
    }

    params.push(id);
    const sql = `UPDATE users SET ${fields.join(', ')} WHERE id = ?`;
    
    await db.execute(sql, params);
    return this.getUserById(id);
  }

  /**
   * 更新用户流量使用情况
   */
  async updateUserTraffic(username: string, trafficIn: number, trafficOut: number): Promise<void> {
    const sql = `
      UPDATE users 
      SET traffic_used = traffic_used + ? 
      WHERE username = ? AND traffic_used + ? <= traffic_limit
    `;
    
    await db.execute(sql, [trafficIn + trafficOut, username, trafficIn + trafficOut]);
  }

  /**
   * 检查用户名是否可用
   */
  async isUsernameAvailable(username: string): Promise<boolean> {
    const user = await this.getUserByUsername(username);
    return user === null;
  }

  /**
   * 检查邮箱是否可用
   */
  async isEmailAvailable(email: string): Promise<boolean> {
    const user = await this.getUserByEmail(email);
    return user === null;
  }

  /**
   * 获取用户列表（分页）
   */
  async getUsers(page: number = 1, limit: number = 20): Promise<{ users: User[], total: number }> {
    const offset = (page - 1) * limit;
    
    const [users, totalResult] = await Promise.all([
      db.query<User>('SELECT * FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?', [limit, offset]),
      db.queryOne<{ total: number }>('SELECT COUNT(*) as total FROM users')
    ]);

    return {
      users,
      total: totalResult?.total || 0
    };
  }

  /**
   * 删除用户
   */
  async deleteUser(id: number): Promise<boolean> {
    const sql = 'DELETE FROM users WHERE id = ?';
    const result = await db.execute(sql, [id]);
    return result[0].affectedRows > 0;
  }
}

export default new UserService();