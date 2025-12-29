import { Response } from 'express';
import { AuthRequest } from '../middleware/auth.js';
import proxyService from '../services/ProxyService.js';

export class ProxyController {
  /**
   * 获取用户隧道列表
   */
  async getUserProxies(req: AuthRequest, res: Response) {
    try {
      const { username } = req.user;
      const proxies = await proxyService.getUserProxies(username);

      res.json({
        success: true,
        data: proxies
      });
    } catch (error: any) {
      res.status(500).json({
        success: false,
        message: '获取隧道列表失败'
      });
    }
  }

  /**
   * 创建隧道
   */
  async createProxy(req: AuthRequest, res: Response) {
    try {
      const { username } = req.user;
      const {
        proxy_name,
        proxy_type,
        local_ip,
        local_port,
        remote_port,
        custom_domain,
        node_id
      } = req.body;

      if (!proxy_name || !proxy_type || !local_ip || !local_port || !node_id) {
        return res.status(400).json({
          success: false,
          message: '必填字段不能为空'
        });
      }

      const proxy = await proxyService.createProxy({
        username,
        proxy_name,
        proxy_type,
        local_ip,
        local_port,
        remote_port,
        custom_domain,
        node_id
      });

      res.status(201).json({
        success: true,
        message: '隧道创建成功',
        data: proxy
      });
    } catch (error: any) {
      res.status(400).json({
        success: false,
        message: error.message
      });
    }
  }

  /**
   * 更新隧道
   */
  async updateProxy(req: AuthRequest, res: Response) {
    try {
      const { id } = req.params;
      const updateData = req.body;

      // 检查隧道是否属于当前用户
      const proxy = await proxyService.getProxyById(parseInt(id));
      if (!proxy) {
        return res.status(404).json({
          success: false,
          message: '隧道不存在'
        });
      }

      if (proxy.username !== req.user.username) {
        return res.status(403).json({
          success: false,
          message: '无权操作此隧道'
        });
      }

      const updatedProxy = await proxyService.updateProxy(parseInt(id), updateData);

      res.json({
        success: true,
        message: '隧道更新成功',
        data: updatedProxy
      });
    } catch (error: any) {
      res.status(400).json({
        success: false,
        message: error.message
      });
    }
  }

  /**
   * 删除隧道
   */
  async deleteProxy(req: AuthRequest, res: Response) {
    try {
      const { id } = req.params;

      // 检查隧道是否属于当前用户
      const proxy = await proxyService.getProxyById(parseInt(id));
      if (!proxy) {
        return res.status(404).json({
          success: false,
          message: '隧道不存在'
        });
      }

      if (proxy.username !== req.user.username) {
        return res.status(403).json({
          success: false,
          message: '无权操作此隧道'
        });
      }

      const success = await proxyService.deleteProxy(parseInt(id));

      if (success) {
        res.json({
          success: true,
          message: '隧道删除成功'
        });
      } else {
        res.status(500).json({
          success: false,
          message: '隧道删除失败'
        });
      }
    } catch (error: any) {
      res.status(500).json({
        success: false,
        message: error.message
      });
    }
  }

  /**
   * 获取隧道配置
   */
  async getProxyConfig(req: AuthRequest, res: Response) {
    try {
      const { id } = req.params;

      // 检查隧道是否属于当前用户
      const proxy = await proxyService.getProxyById(parseInt(id));
      if (!proxy) {
        return res.status(404).json({
          success: false,
          message: '隧道不存在'
        });
      }

      if (proxy.username !== req.user.username) {
        return res.status(403).json({
          success: false,
          message: '无权查看此隧道配置'
        });
      }

      const config = await proxyService.generateProxyConfig(parseInt(id));

      res.json({
        success: true,
        data: {
          config,
          filename: `${proxy.proxy_name}.ini`
        }
      });
    } catch (error: any) {
      res.status(500).json({
        success: false,
        message: error.message
      });
    }
  }

  /**
   * 获取隧道统计信息
   */
  async getProxyStats(req: AuthRequest, res: Response) {
    try {
      const { username } = req.user;
      const stats = await proxyService.getProxyStats(username);

      res.json({
        success: true,
        data: stats
      });
    } catch (error: any) {
      res.status(500).json({
        success: false,
        message: '获取统计信息失败'
      });
    }
  }

  /**
   * 启动/停止隧道
   */
  async toggleProxy(req: AuthRequest, res: Response) {
    try {
      const { id } = req.params;
      const { action } = req.body; // 'start' or 'stop'

      if (!['start', 'stop'].includes(action)) {
        return res.status(400).json({
          success: false,
          message: '操作类型无效'
        });
      }

      // 检查隧道是否属于当前用户
      const proxy = await proxyService.getProxyById(parseInt(id));
      if (!proxy) {
        return res.status(404).json({
          success: false,
          message: '隧道不存在'
        });
      }

      if (proxy.username !== req.user.username) {
        return res.status(403).json({
          success: false,
          message: '无权操作此隧道'
        });
      }

      const status = action === 'start' ? 'active' : 'inactive';
      const updatedProxy = await proxyService.updateProxy(parseInt(id), { status });

      res.json({
        success: true,
        message: `隧道${action === 'start' ? '启动' : '停止'}成功`,
        data: updatedProxy
      });
    } catch (error: any) {
      res.status(400).json({
        success: false,
        message: error.message
      });
    }
  }

  /**
   * 获取所有隧道（管理员功能）
   */
  async getAllProxies(req: AuthRequest, res: Response) {
    try {
      const { page = 1, limit = 20, username } = req.query;
      
      let whereClause = '';
      const params = [];
      
      if (username) {
        whereClause = 'WHERE p.username = ?';
        params.push(username);
      }

      const offset = (parseInt(page as string) - 1) * parseInt(limit as string);
      params.push(parseInt(limit as string), offset);

      const sql = `
        SELECT p.*, u.email, n.name as node_name
        FROM proxies p
        LEFT JOIN users u ON p.username = u.username
        LEFT JOIN nodes n ON p.node_id = n.id
        ${whereClause}
        ORDER BY p.created_at DESC
        LIMIT ? OFFSET ?
      `;

      const proxies = await proxyService.query(sql, whereClause ? params.slice(0, -2) : params);
      const totalResult = await proxyService.queryOne<{ total: number }>(`
        SELECT COUNT(*) as total FROM proxies p ${whereClause}
      `, username ? [username] : []);

      res.json({
        success: true,
        data: {
          proxies,
          pagination: {
            page: parseInt(page as string),
            limit: parseInt(limit as string),
            total: totalResult?.total || 0
          }
        }
      });
    } catch (error: any) {
      res.status(500).json({
        success: false,
        message: '获取隧道列表失败'
      });
    }
  }

  // 临时方法，等待数据库服务完善
  private async query(sql: string, params: any[]): Promise<any[]> {
    // 这里需要调用数据库服务
    return [];
  }

  private async queryOne<T>(sql: string, params: any[]): Promise<T | null> {
    // 这里需要调用数据库服务
    return null;
  }
}

export default new ProxyController();