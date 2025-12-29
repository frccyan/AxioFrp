import nodemailer from 'nodemailer';

export interface EmailConfig {
    smtp_enabled: string;
    smtp_host: string;
    smtp_port: string;
    smtp_secure: string;
    smtp_user: string;
    smtp_password: string;
    smtp_from_name: string;
}

export interface EmailOptions {
    to: string | string[];
    subject: string;
    html: string;
    text?: string;
}

export class EmailService {
    private transporter: nodemailer.Transporter | null = null;
    private config: EmailConfig | null = null;

    constructor() {
        // 初始化时加载配置
        this.loadConfig();
    }

    /**
     * 加载邮件配置
     */
    private async loadConfig(): Promise<void> {
        // 这里应该从数据库或配置文件加载
        // 为简化，暂时使用环境变量
        this.config = {
            smtp_enabled: process.env.SMTP_HOST ? 'true' : 'false',
            smtp_host: process.env.SMTP_HOST || 'smtp.gmail.com',
            smtp_port: process.env.SMTP_PORT || '587',
            smtp_secure: process.env.SMTP_SECURE || 'true',
            smtp_user: process.env.SMTP_USER || '',
            smtp_password: process.env.SMTP_PASS || '',
            smtp_from_name: process.env.SMTP_FROM_NAME || 'AxioFrp'
        };

        if (this.config.smtp_enabled === 'true' && this.config.smtp_user) {
            await this.createTransporter();
        }
    }

    /**
     * 创建邮件传输器
     */
    private async createTransporter(): Promise<void> {
        if (!this.config) return;

        try {
            this.transporter = nodemailer.createTransport({
                host: this.config.smtp_host,
                port: parseInt(this.config.smtp_port),
                secure: this.config.smtp_secure === 'true',
                auth: {
                    user: this.config.smtp_user,
                    pass: this.config.smtp_password
                }
            });

            // 验证连接
            await this.transporter!.verify();
            console.log('邮件服务初始化成功');
        } catch (error) {
            console.error('邮件服务初始化失败:', error);
            this.transporter = null;
        }
    }

    /**
     * 更新配置
     */
    public async updateConfig(config: EmailConfig): Promise<void> {
        this.config = config;
        
        if (config.smtp_enabled === 'true' && config.smtp_user) {
            await this.createTransporter();
        } else {
            this.transporter = null;
        }
    }

    /**
     * 发送邮件
     */
    public async sendEmail(options: EmailOptions): Promise<{ success: boolean; error?: string; messageId?: string }> {
        if (!this.transporter || !this.config) {
            return { success: false, error: '邮件服务未配置' };
        }

        try {
            const mailOptions = {
                from: `"${this.config.smtp_from_name}" <${this.config.smtp_user}>`,
                to: Array.isArray(options.to) ? options.to.join(', ') : options.to,
                subject: options.subject,
                html: options.html,
                text: options.text
            };

            const result = await this.transporter.sendMail(mailOptions);
            return {
                success: true,
                messageId: result.messageId
            };
        } catch (error) {
            console.error('发送邮件失败:', error);
            return {
                success: false,
                error: error.message
            };
        }
    }

    /**
     * 发送验证码邮件
     */
    public async sendVerificationEmail(to: string, code: string): Promise<{ success: boolean; error?: string }> {
        const html = `
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>邮箱验证</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #667eea, #764ba2); color: white; text-align: center; padding: 30px; border-radius: 10px 10px 0 0; }
                    .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                    .code { background: #667eea; color: white; font-size: 24px; font-weight: bold; padding: 15px 25px; border-radius: 5px; text-align: center; margin: 20px 0; }
                    .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>🎯 AxioFrp 邮箱验证</h1>
                    </div>
                    <div class="content">
                        <p>您好！</p>
                        <p>感谢您注册 AxioFrp 账户。请使用以下验证码完成邮箱验证：</p>
                        <div class="code">${code}</div>
                        <p><strong>注意事项：</strong></p>
                        <ul>
                            <li>验证码有效期为 10 分钟</li>
                            <li>请勿将验证码告知他人</li>
                            <li>如非本人操作，请忽略此邮件</li>
                        </ul>
                    </div>
                    <div class="footer">
                        <p>此邮件由系统自动发送，请勿回复。</p>
                        <p>© 2024 AxioFrp - 现代化内网穿透管理面板</p>
                    </div>
                </div>
            </body>
            </html>
        `;

        return this.sendEmail({
            to,
            subject: 'AxioFrp - 邮箱验证码',
            html,
            text: `您的验证码是：${code}，有效期为10分钟。`
        });
    }

    /**
     * 发送密码重置邮件
     */
    public async sendPasswordResetEmail(to: string, resetToken: string): Promise<{ success: boolean; error?: string }> {
        const resetLink = `${process.env.FRONTEND_URL}/reset-password?token=${resetToken}`;
        
        const html = `
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>密码重置</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #f093fb, #f5576c); color: white; text-align: center; padding: 30px; border-radius: 10px 10px 0 0; }
                    .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                    .reset-button { background: #f5576c; color: white; text-decoration: none; padding: 15px 30px; border-radius: 5px; display: inline-block; margin: 20px 0; }
                    .reset-button:hover { background: #e0455a; }
                    .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>🔐 AxioFrp 密码重置</h1>
                    </div>
                    <div class="content">
                        <p>您好！</p>
                        <p>我们收到了您的密码重置请求。请点击下方按钮重置您的密码：</p>
                        <div style="text-align: center;">
                            <a href="${resetLink}" class="reset-button">重置密码</a>
                        </div>
                        <p>如果按钮无法点击，请复制以下链接到浏览器地址栏：</p>
                        <p style="background: #eee; padding: 10px; border-radius: 3px; word-break: break-all;">${resetLink}</p>
                        <p><strong>注意事项：</strong></p>
                        <ul>
                            <li>重置链接有效期为 30 分钟</li>
                            <li>请勿将链接分享给他人</li>
                            <li>如非本人操作，请忽略此邮件</li>
                        </ul>
                    </div>
                    <div class="footer">
                        <p>此邮件由系统自动发送，请勿回复。</p>
                        <p>© 2024 AxioFrp - 现代化内网穿透管理面板</p>
                    </div>
                </div>
            </body>
            </html>
        `;

        return this.sendEmail({
            to,
            subject: 'AxioFrp - 密码重置',
            html,
            text: `请访问以下链接重置密码：${resetLink}，链接有效期为30分钟。`
        });
    }

    /**
     * 发送欢迎邮件
     */
    public async sendWelcomeEmail(to: string, username: string): Promise<{ success: boolean; error?: string }> {
        const html = `
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>欢迎加入</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #4facfe, #00f2fe); color: white; text-align: center; padding: 30px; border-radius: 10px 10px 0 0; }
                    .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                    .feature { background: white; padding: 15px; margin: 10px 0; border-left: 4px solid #4facfe; border-radius: 3px; }
                    .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>🎉 欢迎加入 AxioFrp</h1>
                        <p>现代化内网穿透管理面板</p>
                    </div>
                    <div class="content">
                        <p>亲爱的 ${username}：</p>
                        <p>欢迎您加入 AxioFrp！您已成功注册账户，现在可以开始使用我们强大的内网穿透服务了。</p>
                        
                        <h3>🚀 快速开始</h3>
                        <div class="feature">
                            <strong>创建隧道</strong><br>
                            登录控制面板，点击"隧道管理"创建您的第一个内网穿透隧道。
                        </div>
                        <div class="feature">
                            <strong>选择节点</strong><br>
                            我们提供多个高速节点，选择离您最近的节点获得最佳体验。
                        </div>
                        <div class="feature">
                            <strong>监控状态</strong><br>
                            实时查看隧道状态和流量使用情况。
                        </div>
                        
                        <h3>💡 使用技巧</h3>
                        <ul>
                            <li>为隧道设置有意义的名称便于管理</li>
                            <li>定期查看流量使用情况避免超限</li>
                            <li>遇到问题可以查看帮助文档</li>
                        </ul>
                        
                        <p>如有任何问题，欢迎随时联系我们的技术支持团队。</p>
                    </div>
                    <div class="footer">
                        <p>此邮件由系统自动发送，请勿回复。</p>
                        <p>© 2024 AxioFrp - 现代化内网穿透管理面板</p>
                    </div>
                </div>
            </body>
            </html>
        `;

        return this.sendEmail({
            to,
            subject: '欢迎加入 AxioFrp！',
            html,
            text: `欢迎 ${username} 加入 AxioFrp！您已成功注册，现在可以开始使用内网穿透服务了。`
        });
    }

    /**
     * 发送测试邮件
     */
    public async sendTestEmail(to: string): Promise<{ success: boolean; error?: string; messageId?: string }> {
        const html = `
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>邮件测试</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #667eea, #764ba2); color: white; text-align: center; padding: 30px; border-radius: 10px; }
                    .content { background: #f9f9f9; padding: 30px; border-radius: 10px; }
                    .success { background: #4caf50; color: white; padding: 15px; border-radius: 5px; text-align: center; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>✅ 邮件配置测试</h1>
                    </div>
                    <div class="content">
                        <p>恭喜！您的邮件配置测试成功。</p>
                        <div class="success">
                            <strong>测试时间：</strong>${new Date().toLocaleString()}<br>
                            <strong>收件地址：</strong>${to}
                        </div>
                        <p>这表示您的 SMTP 邮件服务已正确配置，AxioFrp 系统现在可以正常发送邮件了。</p>
                    </div>
                </div>
            </body>
            </html>
        `;

        return this.sendEmail({
            to,
            subject: 'AxioFrp - 邮件配置测试成功',
            html,
            text: `邮件配置测试成功！测试时间：${new Date().toLocaleString()}`
        });
    }

    /**
     * 检查邮件服务状态
     */
    public async checkStatus(): Promise<{ enabled: boolean; connected: boolean; error?: string }> {
        if (!this.config || this.config.smtp_enabled !== 'true') {
            return { enabled: false, connected: false };
        }

        if (!this.transporter) {
            return { enabled: true, connected: false, error: '传输器未初始化' };
        }

        try {
            await this.transporter.verify();
            return { enabled: true, connected: true };
        } catch (error) {
            return { enabled: true, connected: false, error: error.message };
        }
    }
}