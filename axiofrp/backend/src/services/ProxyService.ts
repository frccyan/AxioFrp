import db from '../config/database.js';
import { Proxy, ProxyCreateData, ProxyUpdateData } from '../models/Proxy.js';

export class ProxyService {
  /**
   * 创建隧道
   */
  async createProxy(data: ProxyCreateData): Promise<Proxy> {
    // 验证端口和域名可用性
    await this.validateProxyCreation(data);
    
    const sql = `
      INSERT INTO proxies (username, proxy_name, proxy_type, local_ip, local_port, remote_port, custom_domain, node_id)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    `;
    
    const params = [
      data.username,
      data.proxy_name,
      data.proxy_type,
      data.local_ip,
      data.local_port,
      data.remote_port,
      data.custom_domain,
      data.node_id
    ];
    
    const result = await db.execute(sql, params);
    return this.getProxyById(result[0].insertId);
  }

  /**
   * 根据ID获取隧道
   */
  async getProxyById(id: number): Promise<Proxy | null> {
    const sql = 'SELECT * FROM proxies WHERE id = ?';
    return await db.queryOne<Proxy>(sql, [id]);
  }

  /**
   * 获取用户的所有隧道
   */
  async getUserProxies(username: string): Promise<Proxy[]> {
    const sql = `
      SELECT p.*, n.name as node_name, n.address as node_address
      FROM proxies p
      LEFT JOIN nodes n ON p.node_id = n.id
      WHERE p.username = ? AND p.status = 'active'
      ORDER BY p.created_at DESC
    `;
    
    return await db.query<Proxy>(sql, [username]);
  }

  /**
   * 获取节点上的所有隧道
   */
  async getNodeProxies(nodeId: number): Promise<Proxy[]> {
    const sql = 'SELECT * FROM proxies WHERE node_id = ? AND status = "active"';
    return await db.query<Proxy>(sql, [nodeId]);
  }

  /**
   * 更新隧道信息
   */
  async updateProxy(id: number, data: ProxyUpdateData): Promise<Proxy | null> {
    const fields = [];
    const params = [];

    if (data.proxy_name) {
      fields.push('proxy_name = ?');
      params.push(data.proxy_name);
    }
    if (data.local_ip) {
      fields.push('local_ip = ?');
      params.push(data.local_ip);
    }
    if (data.local_port) {
      fields.push('local_port = ?');
      params.push(data.local_port);
    }
    if (data.remote_port !== undefined) {
      fields.push('remote_port = ?');
      params.push(data.remote_port);
    }
    if (data.custom_domain !== undefined) {
      fields.push('custom_domain = ?');
      params.push(data.custom_domain);
    }
    if (data.status) {
      fields.push('status = ?');
      params.push(data.status);
    }
    if (data.traffic_in !== undefined) {
      fields.push('traffic_in = ?');
      params.push(data.traffic_in);
    }
    if (data.traffic_out !== undefined) {
      fields.push('traffic_out = ?');
      params.push(data.traffic_out);
    }

    if (fields.length === 0) {
      return this.getProxyById(id);
    }

    params.push(id);
    const sql = `UPDATE proxies SET ${fields.join(', ')} WHERE id = ?`;
    
    await db.execute(sql, params);
    return this.getProxyById(id);
  }

  /**
   * 更新隧道流量统计
   */
  async updateProxyTraffic(id: number, trafficIn: number, trafficOut: number): Promise<void> {
    const sql = `
      UPDATE proxies 
      SET traffic_in = traffic_in + ?, traffic_out = traffic_out + ?
      WHERE id = ?
    `;
    
    await db.execute(sql, [trafficIn, trafficOut, id]);
  }

  /**
   * 删除隧道
   */
  async deleteProxy(id: number): Promise<boolean> {
    const sql = 'DELETE FROM proxies WHERE id = ?';
    const result = await db.execute(sql, [id]);
    return result[0].affectedRows > 0;
  }

  /**
   * 验证隧道创建参数
   */
  private async validateProxyCreation(data: ProxyCreateData): Promise<void> {
    // 检查用户是否存在
    const userSql = 'SELECT id FROM users WHERE username = ? AND status = "active"';
    const user = await db.queryOne<{ id: number }>(userSql, [data.username]);
    
    if (!user) {
      throw new Error('用户不存在或已被禁用');
    }

    // 检查节点是否存在
    const nodeSql = 'SELECT id FROM nodes WHERE id = ? AND status = "online"';
    const node = await db.queryOne<{ id: number }>(nodeSql, [data.node_id]);
    
    if (!node) {
      throw new Error('节点不存在或不可用');
    }

    // 检查隧道名称是否重复
    const proxySql = 'SELECT id FROM proxies WHERE username = ? AND proxy_name = ?';
    const existingProxy = await db.queryOne<{ id: number }>(proxySql, [data.username, data.proxy_name]);
    
    if (existingProxy) {
      throw new Error('隧道名称已存在');
    }

    // 检查端口冲突（对于需要远程端口的隧道类型）
    if (data.remote_port) {
      const portSql = 'SELECT id FROM proxies WHERE node_id = ? AND remote_port = ?';
      const portConflict = await db.queryOne<{ id: number }>(portSql, [data.node_id, data.remote_port]);
      
      if (portConflict) {
        throw new Error('远程端口已被占用');
      }
    }

    // 检查域名冲突（对于HTTP/HTTPS隧道）
    if (data.custom_domain) {
      const domainSql = 'SELECT id FROM proxies WHERE custom_domain = ?';
      const domainConflict = await db.queryOne<{ id: number }>(domainSql, [data.custom_domain]);
      
      if (domainConflict) {
        throw new Error('自定义域名已被使用');
      }
    }

    // 检查用户隧道数量限制
    const userProxyCountSql = 'SELECT COUNT(*) as count FROM proxies WHERE username = ?';
    const userProxyCount = await db.queryOne<{ count: number }>(userProxyCountSql, [data.username]);
    
    const userGroupSql = 'SELECT g.max_proxies FROM users u JOIN groups g ON u.group_name = g.name WHERE u.username = ?';
    const userGroup = await db.queryOne<{ max_proxies: number }>(userGroupSql, [data.username]);
    
    if (userProxyCount && userGroup && userProxyCount.count >= userGroup.max_proxies) {
      throw new Error('已达到用户隧道数量限制');
    }
  }

  /**
   * 生成FRP客户端配置
   */
  async generateProxyConfig(proxyId: number): Promise<string> {
    const proxy = await this.getProxyById(proxyId);
    if (!proxy) {
      throw new Error('隧道不存在');
    }

    const node = await db.queryOne<any>('SELECT * FROM nodes WHERE id = ?', [proxy.node_id]);
    if (!node) {
      throw new Error('节点不存在');
    }

    let config = `[common]\nserver_addr = ${node.address}\nserver_port = ${node.port}\ntoken = ${node.token}\n\n`;
    
    config += `[${proxy.proxy_name}]\n`;
    config += `type = ${proxy.proxy_type}\n`;
    config += `local_ip = ${proxy.local_ip}\n`;
    config += `local_port = ${proxy.local_port}\n`;
    
    if (proxy.remote_port) {
      config += `remote_port = ${proxy.remote_port}\n`;
    }
    
    if (proxy.custom_domain) {
      config += `custom_domain = ${proxy.custom_domain}\n`;
    }

    return config;
  }

  /**
   * 获取隧道统计信息
   */
  async getProxyStats(username?: string): Promise<{
    total: number;
    active: number;
    totalTraffic: number;
  }> {
    let whereClause = '';
    const params = [];
    
    if (username) {
      whereClause = 'WHERE username = ?';
      params.push(username);
    }

    const sql = `
      SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
        SUM(traffic_in + traffic_out) as total_traffic
      FROM proxies ${whereClause}
    `;

    const result = await db.queryOne<{
      total: number;
      active: number;
      total_traffic: number;
    }>(sql, params);

    return {
      total: result?.total || 0,
      active: result?.active || 0,
      totalTraffic: result?.total_traffic || 0
    };
  }
}

export default new ProxyService();