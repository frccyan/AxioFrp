import { Router, Response } from 'express';
import { AuthRequest } from '../middleware/auth.js';
import { authenticateToken, requireAdmin } from '../middleware/auth.js';

const router = Router();

// 所有路由都需要认证
router.use(authenticateToken);

/**
 * 获取节点列表
 */
router.get('/', async (_req: AuthRequest, res: Response) => {
  try {
    // TODO: 实现节点服务
    const nodes = [
      {
        id: 1,
        name: '默认节点',
        address: 'frp.example.com',
        port: 7000,
        status: 'online',
        region: '中国大陆',
        max_connections: 1000,
        current_connections: 150
      }
    ];

    res.json({
      success: true,
      data: nodes
    });
  } catch (error: any) {
    res.status(500).json({
      success: false,
      message: '获取节点列表失败'
    });
  }
});

/**
 * 获取节点详情
 */
router.get('/:id', async (_req: AuthRequest, res: Response) => {
  try {
    const { id } = _req.params;
    
    // TODO: 实现节点服务
    const node = {
      id: parseInt(id),
      name: '默认节点',
      address: 'frp.example.com',
      port: 7000,
      status: 'online',
      region: '中国大陆',
      max_connections: 1000,
      current_connections: 150,
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString()
    };

    res.json({
      success: true,
      data: node
    });
  } catch (error: any) {
    res.status(500).json({
      success: false,
      message: '获取节点信息失败'
    });
  }
});

/**
 * 创建节点（管理员功能）
 */
router.post('/', requireAdmin, async (req: AuthRequest, res: Response) => {
  try {
    const { name, address, port, token, max_connections, region } = req.body;

    if (!name || !address || !port || !token) {
      return res.status(400).json({
        success: false,
        message: '必填字段不能为空'
      });
    }

    // TODO: 实现节点服务
    const newNode = {
      id: Math.floor(Math.random() * 1000),
      name,
      address,
      port: parseInt(port),
      token,
      max_connections: max_connections || 1000,
      region: region || '中国大陆',
      status: 'online',
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString()
    };

    res.status(201).json({
      success: true,
      message: '节点创建成功',
      data: newNode
    });
  } catch (error: any) {
    res.status(400).json({
      success: false,
      message: error.message
    });
  }
});

/**
 * 更新节点（管理员功能）
 */
router.put('/:id', requireAdmin, async (req, res) => {
  try {
    const { id } = req.params;
    const updateData = req.body;

    // TODO: 实现节点服务
    const node = {
      id: parseInt(id),
      name: '默认节点',
      address: 'frp.example.com',
      port: 7000,
      status: 'online',
      region: '中国大陆',
      max_connections: 1000,
      current_connections: 150,
      ...updateData
    };

    res.json({
      success: true,
      message: '节点更新成功',
      data: node
    });
  } catch (error: any) {
    res.status(400).json({
      success: false,
      message: error.message
    });
  }
});

/**
 * 删除节点（管理员功能）
 */
router.delete('/:id', requireAdmin, async (req, res) => {
  try {
    const { id } = req.params;

    // TODO: 实现节点服务
    res.json({
      success: true,
      message: '节点删除成功'
    });
  } catch (error: any) {
    res.status(500).json({
      success: false,
      message: error.message
    });
  }
});

export default router;