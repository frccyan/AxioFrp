import { Router } from 'express';
import userService from '../services/UserService.js';
import { authenticateToken, requireAdmin, requireSelfOrAdmin } from '../middleware/auth.js';

const router = Router();

// 所有路由都需要认证
router.use(authenticateToken);

/**
 * 获取用户列表（管理员功能）
 */
router.get('/', requireAdmin, async (req, res) => {
  try {
    const { page = 1, limit = 20 } = req.query;
    const result = await userService.getUsers(
      parseInt(page as string),
      parseInt(limit as string)
    );

    res.json({
      success: true,
      data: result
    });
  } catch (error: any) {
    res.status(500).json({
      success: false,
      message: '获取用户列表失败'
    });
  }
});

/**
 * 获取用户详情
 */
router.get('/:username', requireSelfOrAdmin, async (req, res) => {
  try {
    const { username } = req.params;
    const user = await userService.getUserByUsername(username);

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
});

/**
 * 更新用户信息（管理员或用户自己）
 */
router.put('/:username', requireSelfOrAdmin, async (req, res) => {
  try {
    const { username } = req.params;
    const updateData = req.body;

    const user = await userService.getUserByUsername(username);
    if (!user) {
      return res.status(404).json({
        success: false,
        message: '用户不存在'
      });
    }

    const updatedUser = await userService.updateUser(user.id, updateData);

    if (!updatedUser) {
      return res.status(500).json({
        success: false,
        message: '更新用户信息失败'
      });
    }

    // 排除密码字段
    const { password, ...userWithoutPassword } = updatedUser;

    res.json({
      success: true,
      message: '用户信息更新成功',
      data: userWithoutPassword
    });
  } catch (error: any) {
    res.status(400).json({
      success: false,
      message: error.message
    });
  }
});

/**
 * 删除用户（管理员功能）
 */
router.delete('/:username', requireAdmin, async (req, res) => {
  try {
    const { username } = req.params;
    
    const user = await userService.getUserByUsername(username);
    if (!user) {
      return res.status(404).json({
        success: false,
        message: '用户不存在'
      });
    }

    const success = await userService.deleteUser(user.id);

    if (success) {
      res.json({
        success: true,
        message: '用户删除成功'
      });
    } else {
      res.status(500).json({
        success: false,
        message: '用户删除失败'
      });
    }
  } catch (error: any) {
    res.status(500).json({
      success: false,
      message: error.message
    });
  }
});

export default router;