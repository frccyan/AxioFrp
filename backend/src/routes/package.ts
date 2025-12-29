import { Router } from 'express';
import { authenticateToken, requireAdmin } from '../middleware/auth.js';

const router = Router();

// 公开路由 - 获取套餐列表
router.get('/', async (req, res) => {
  try {
    // TODO: 实现套餐服务
    const packages = [
      {
        id: 1,
        name: '免费套餐',
        description: '基础免费套餐',
        price: 0.00,
        duration_days: 30,
        traffic_limit: 1073741824, // 1GB
        max_proxies: 5,
        status: 'active'
      },
      {
        id: 2,
        name: '基础套餐',
        description: '适合个人用户',
        price: 9.99,
        duration_days: 30,
        traffic_limit: 5368709120, // 5GB
        max_proxies: 20,
        status: 'active'
      },
      {
        id: 3,
        name: '高级套餐',
        description: '适合团队使用',
        price: 19.99,
        duration_days: 30,
        traffic_limit: 21474836480, // 20GB
        max_proxies: 50,
        status: 'active'
      },
      {
        id: 4,
        name: '企业套餐',
        description: '适合企业用户',
        price: 49.99,
        duration_days: 30,
        traffic_limit: 107374182400, // 100GB
        max_proxies: 100,
        status: 'active'
      }
    ];

    res.json({
      success: true,
      data: packages
    });
  } catch (error: any) {
    res.status(500).json({
      success: false,
      message: '获取套餐列表失败'
    });
  }
});

// 需要认证的路由
router.use(authenticateToken);

/**
 * 购买套餐
 */
router.post('/purchase', async (req: any, res) => {
  try {
    const { package_id } = req.body;
    const { username } = req.user;

    if (!package_id) {
      return res.status(400).json({
        success: false,
        message: '套餐ID不能为空'
      });
    }

    // TODO: 实现套餐购买逻辑
    // 1. 验证套餐是否存在且可用
    // 2. 检查用户余额是否足够
    // 3. 扣除余额并创建订单
    // 4. 更新用户组和流量配额

    const order = {
      id: Math.floor(Math.random() * 10000),
      username,
      package_id: parseInt(package_id),
      amount: 9.99, // 临时值
      status: 'paid',
      expires_at: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString(),
      created_at: new Date().toISOString()
    };

    res.status(201).json({
      success: true,
      message: '套餐购买成功',
      data: order
    });
  } catch (error: any) {
    res.status(400).json({
      success: false,
      message: error.message
    });
  }
});

/**
 * 获取用户套餐订单
 */
router.get('/orders', async (req: any, res) => {
  try {
    const { username } = req.user;
    const { page = 1, limit = 10 } = req.query;

    // TODO: 实现订单查询
    const orders = [
      {
        id: 1,
        username,
        package_id: 1,
        package_name: '免费套餐',
        amount: 0.00,
        status: 'paid',
        expires_at: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString(),
        created_at: new Date().toISOString()
      }
    ];

    res.json({
      success: true,
      data: {
        orders,
        pagination: {
          page: parseInt(page as string),
          limit: parseInt(limit as string),
          total: 1
        }
      }
    });
  } catch (error: any) {
    res.status(500).json({
      success: false,
      message: '获取订单列表失败'
    });
  }
});

/**
 * 创建套餐（管理员功能）
 */
router.post('/', requireAdmin, async (req, res) => {
  try {
    const { name, description, price, duration_days, traffic_limit, max_proxies } = req.body;

    if (!name || !price || !duration_days) {
      return res.status(400).json({
        success: false,
        message: '必填字段不能为空'
      });
    }

    // TODO: 实现套餐服务
    const newPackage = {
      id: Math.floor(Math.random() * 1000),
      name,
      description: description || '',
      price: parseFloat(price),
      duration_days: parseInt(duration_days),
      traffic_limit: traffic_limit || 0,
      max_proxies: max_proxies || 10,
      status: 'active',
      created_at: new Date().toISOString()
    };

    res.status(201).json({
      success: true,
      message: '套餐创建成功',
      data: newPackage
    });
  } catch (error: any) {
    res.status(400).json({
      success: false,
      message: error.message
    });
  }
});

/**
 * 更新套餐（管理员功能）
 */
router.put('/:id', requireAdmin, async (req, res) => {
  try {
    const { id } = req.params;
    const updateData = req.body;

    // TODO: 实现套餐服务
    const packageData = {
      id: parseInt(id),
      name: '基础套餐',
      description: '适合个人用户',
      price: 9.99,
      duration_days: 30,
      traffic_limit: 5368709120,
      max_proxies: 20,
      status: 'active',
      ...updateData
    };

    res.json({
      success: true,
      message: '套餐更新成功',
      data: packageData
    });
  } catch (error: any) {
    res.status(400).json({
      success: false,
      message: error.message
    });
  }
});

/**
 * 删除套餐（管理员功能）
 */
router.delete('/:id', requireAdmin, async (req, res) => {
  try {
    const { id } = req.params;

    // TODO: 实现套餐服务
    res.json({
      success: true,
      message: '套餐删除成功'
    });
  } catch (error: any) {
    res.status(500).json({
      success: false,
      message: error.message
    });
  }
});

export default router;