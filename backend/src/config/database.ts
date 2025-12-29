import mysql from 'mysql2/promise';

const dbConfig = {
  host: process.env.DB_HOST || 'localhost',
  port: parseInt(process.env.DB_PORT || '3306'),
  user: process.env.DB_USER || 'root',
  password: process.env.DB_PASSWORD || '',
  database: process.env.DB_NAME || 'axiofrp',
  charset: 'utf8mb4',
  timezone: '+08:00',
  connectionLimit: 10,
  acquireTimeout: 60000,
  timeout: 60000,
  reconnect: true,
};

class Database {
  private pool: mysql.Pool;

  constructor() {
    this.pool = mysql.createPool(dbConfig);
  }

  async getConnection(): Promise<mysql.PoolConnection> {
    return await this.pool.getConnection();
  }

  async query<T = any>(sql: string, params?: any[]): Promise<T[]> {
    const [rows] = await this.pool.execute(sql, params);
    return rows as T[];
  }

  async queryOne<T = any>(sql: string, params?: any[]): Promise<T | null> {
    const rows = await this.query<T>(sql, params);
    return rows.length > 0 ? rows[0] : null;
  }

  async execute(sql: string, params?: any[]): Promise<any> {
    return await this.pool.execute(sql, params);
  }

  async beginTransaction(): Promise<mysql.PoolConnection> {
    const connection = await this.getConnection();
    await connection.beginTransaction();
    return connection;
  }

  async commit(connection: mysql.PoolConnection): Promise<void> {
    await connection.commit();
    connection.release();
  }

  async rollback(connection: mysql.PoolConnection): Promise<void> {
    await connection.rollback();
    connection.release();
  }

  async close(): Promise<void> {
    await this.pool.end();
  }
}

export default new Database();