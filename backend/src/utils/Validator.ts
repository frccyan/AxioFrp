export interface ValidationResult {
    valid: boolean;
    error?: string;
}

export class Validator {
    /**
     * 验证配置值
     * @param value 要验证的值
     * @param rule 验证规则
     */
    public validate(value: string, rule: string): ValidationResult {
        try {
            if (!rule) {
                return { valid: true };
            }

            const rules = rule.split('|');
            
            for (const ruleItem of rules) {
                const result = this.validateRule(value, ruleItem);
                if (!result.valid) {
                    return result;
                }
            }

            return { valid: true };
        } catch (error) {
            return { valid: false, error: '验证规则解析失败' };
        }
    }

    /**
     * 验证单个规则
     */
    private validateRule(value: string, rule: string): ValidationResult {
        // 解析规则参数
        const [ruleName, ...params] = rule.split(':');

        switch (ruleName) {
            case 'string':
                return this.validateString(value, params);
            case 'number':
                return this.validateNumber(value, params);
            case 'email':
                return this.validateEmail(value);
            case 'url':
                return this.validateUrl(value);
            case 'boolean':
                return this.validateBoolean(value);
            case 'enum':
                return this.validateEnum(value, params);
            case 'color':
                return this.validateColor(value);
            case 'range':
                return this.validateRange(value, params);
            default:
                return { valid: true }; // 未知规则，跳过验证
        }
    }

    /**
     * 验证字符串
     */
    private validateString(value: string, params: string[]): ValidationResult {
        const minLength = params.length > 0 ? parseInt(params[0]) : 0;
        const maxLength = params.length > 1 ? parseInt(params[1]) : 255;

        if (value.length < minLength) {
            return { valid: false, error: `长度不能少于 ${minLength} 个字符` };
        }

        if (value.length > maxLength) {
            return { valid: false, error: `长度不能超过 ${maxLength} 个字符` };
        }

        return { valid: true };
    }

    /**
     * 验证数字
     */
    private validateNumber(value: string, params: string[]): ValidationResult {
        const num = parseFloat(value);
        
        if (isNaN(num)) {
            return { valid: false, error: '必须是数字' };
        }

        const min = params.length > 0 ? parseFloat(params[0]) : -Infinity;
        const max = params.length > 1 ? parseFloat(params[1]) : Infinity;

        if (num < min) {
            return { valid: false, error: `不能小于 ${min}` };
        }

        if (num > max) {
            return { valid: false, error: `不能大于 ${max}` };
        }

        return { valid: true };
    }

    /**
     * 验证邮箱
     */
    private validateEmail(value: string): ValidationResult {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (!emailRegex.test(value)) {
            return { valid: false, error: '邮箱格式不正确' };
        }

        return { valid: true };
    }

    /**
     * 验证URL
     */
    private validateUrl(value: string): ValidationResult {
        try {
            new URL(value);
            return { valid: true };
        } catch {
            return { valid: false, error: 'URL格式不正确' };
        }
    }

    /**
     * 验证布尔值
     */
    private validateBoolean(value: string): ValidationResult {
        const validValues = ['true', 'false', '1', '0', 'yes', 'no'];
        
        if (!validValues.includes(value.toLowerCase())) {
            return { valid: false, error: '必须是 true/false、1/0 或 yes/no' };
        }

        return { valid: true };
    }

    /**
     * 验证枚举值
     */
    private validateEnum(value: string, params: string[]): ValidationResult {
        const validValues = params[0].split(',');
        
        if (!validValues.includes(value)) {
            return { valid: false, error: `必须是以下值之一: ${validValues.join(', ')}` };
        }

        return { valid: true };
    }

    /**
     * 验证颜色值
     */
    private validateColor(value: string): ValidationResult {
        const colorRegex = /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/;
        
        if (!colorRegex.test(value)) {
            return { valid: false, error: '颜色格式不正确，应为 #RRGGBB 或 #RGB' };
        }

        return { valid: true };
    }

    /**
     * 验证范围
     */
    private validateRange(value: string, params: string[]): ValidationResult {
        const num = parseFloat(value);
        
        if (isNaN(num)) {
            return { valid: false, error: '必须是数字' };
        }

        const min = parseFloat(params[0]);
        const max = parseFloat(params[1]);

        if (num < min || num > max) {
            return { valid: false, error: `必须在 ${min} 到 ${max} 之间` };
        }

        return { valid: true };
    }

    /**
     * 验证密码强度
     */
    public validatePassword(password: string, minLength: number = 8, requireSpecial: boolean = true): ValidationResult {
        if (password.length < minLength) {
            return { valid: false, error: `密码长度不能少于 ${minLength} 个字符` };
        }

        if (requireSpecial) {
            const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);
            if (!hasSpecial) {
                return { valid: false, error: '密码必须包含至少一个特殊字符' };
            }
        }

        const hasNumber = /\d/.test(password);
        if (!hasNumber) {
            return { valid: false, error: '密码必须包含至少一个数字' };
        }

        const hasLetter = /[a-zA-Z]/.test(password);
        if (!hasLetter) {
            return { valid: false, error: '密码必须包含至少一个字母' };
        }

        return { valid: true };
    }

    /**
     * 验证用户名
     */
    public validateUsername(username: string): ValidationResult {
        const usernameRegex = /^[a-zA-Z0-9_-]{3,20}$/;
        
        if (!usernameRegex.test(username)) {
            return { valid: false, error: '用户名只能包含字母、数字、下划线和横线，长度3-20个字符' };
        }

        return { valid: true };
    }

    /**
     * 验证域名
     */
    public validateDomain(domain: string): ValidationResult {
        const domainRegex = /^[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
        
        if (!domainRegex.test(domain)) {
            return { valid: false, error: '域名格式不正确' };
        }

        return { valid: true };
    }

    /**
     * 验证IP地址
     */
    public validateIP(ip: string): ValidationResult {
        const ipv4Regex = /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
        const ipv6Regex = /^(?:[0-9a-fA-F]{1,4}:){7}[0-9a-fA-F]{1,4}$/;
        
        if (!ipv4Regex.test(ip) && !ipv6Regex.test(ip)) {
            return { valid: false, error: 'IP地址格式不正确' };
        }

        return { valid: true };
    }

    /**
     * 验证端口号
     */
    public validatePort(port: string): ValidationResult {
        const portNum = parseInt(port);
        
        if (isNaN(portNum) || portNum < 1 || portNum > 65535) {
            return { valid: false, error: '端口号必须在 1-65535 范围内' };
        }

        return { valid: true };
    }

    /**
     * 验证文件大小
     */
    public validateFileSize(size: string, maxSize: number = 10 * 1024 * 1024): ValidationResult {
        const sizeNum = parseFloat(size);
        
        if (isNaN(sizeNum) || sizeNum < 0) {
            return { valid: false, error: '文件大小必须是非负数' };
        }

        if (sizeNum > maxSize) {
            return { valid: false, error: `文件大小不能超过 ${this.formatBytes(maxSize)}` };
        }

        return { valid: true };
    }

    /**
     * 格式化字节数
     */
    private formatBytes(bytes: number): string {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
}