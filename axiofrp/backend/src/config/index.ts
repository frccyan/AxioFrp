import dotenv from 'dotenv';

// 加载环境变量
dotenv.config();

export const config = {
  // 应用配置
  app: {
    port: parseInt(process.env.PORT || '8000'),
    env: process.env.NODE_ENV || 'development',
    name: 'AxioFrp Backend'
  },

  // 数据库配置
  database: {
    host: process.env.DB_HOST || 'localhost',
    port: parseInt(process.env.DB_PORT || '3306'),
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    name: process.env.DB_NAME || 'axiofrp'
  },

  // Redis配置
  redis: {
    url: process.env.REDIS_URL || 'redis://localhost:6379'
  },

  // JWT配置
  jwt: {
    secret: process.env.JWT_SECRET || 'axiofrp_jwt_secret_key',
    expiresIn: process.env.JWT_EXPIRES_IN || '7d'
  },

  // 邮件配置
  email: {
    host: process.env.SMTP_HOST || '',
    port: parseInt(process.env.SMTP_PORT || '587'),
    user: process.env.SMTP_USER || '',
    pass: process.env.SMTP_PASS || ''
  },

  // 系统配置
  system: {
    maxProxiesPerUser: parseInt(process.env.MAX_PROXIES_PER_USER || '10'),
    defaultTrafficLimit: parseInt(process.env.DEFAULT_TRAFFIC_LIMIT || '1073741824'), // 1GB
    frontendUrl: process.env.FRONTEND_URL || 'http://localhost:3000'
  }
};

export type Config = typeof config;