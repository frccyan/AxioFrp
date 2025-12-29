FROM golang:1.16-alpine AS builder

WORKDIR /build

# 安装必要的构建工具
RUN apk add --no-cache gcc musl-dev

# 复制go.mod和go.sum（如果存在）
COPY go.mod* ./
COPY go.sum* ./

# 下载依赖
RUN go mod download || true

# 复制源代码
COPY yougo_toml.go .

# 编译
RUN go build -o frp-client-toml yougo_toml.go

# 使用较小的基础镜像
FROM alpine:latest

# 安装必要的运行时依赖
RUN apk add --no-cache ca-certificates tzdata curl

WORKDIR /app

# 从构建阶段复制编译好的可执行文件
COPY --from=builder /build/frp-client-toml /app/

# 创建配置目录
RUN mkdir -p /app/config

# 声明/app/config为挂载点
VOLUME ["/app/config"]

# 从官方下载frpc可执行文件 (使用较新版本支持TOML格式)
ARG FRPC_VERSION=0.60.0
ARG TARGETARCH
RUN if [ "$TARGETARCH" = "arm64" ]; then \
        ARCH="arm64"; \
    elif [ "$TARGETARCH" = "arm" ]; then \
        ARCH="arm"; \
    else \
        ARCH="amd64"; \
    fi && \
    curl -L https://github.com/fatedier/frp/releases/download/v${FRPC_VERSION}/frp_${FRPC_VERSION}_linux_${ARCH}.tar.gz | tar xz && \
    mv frp_${FRPC_VERSION}_linux_${ARCH}/frpc /app/config/ && \
    chmod +x /app/config/frpc && \
    rm -rf frp_${FRPC_VERSION}_linux_${ARCH}

# 设置工作目录为配置目录
WORKDIR /app/config

# 设置环境变量
ENV CONFIG_DIR=/app/config
ENV FRP_TOKEN="你的用户密钥"
ENV FRP_NODE="你的节点IP"

# 创建示例配置文件
RUN echo "# 这是一个示例配置文件" > /app/config/config.ini

# 容器启动命令
ENTRYPOINT ["/app/frp-client-toml"]
CMD []

# 添加元数据标签
LABEL maintainer="FRP Client Docker"
LABEL description="FRP Client Docker Image with Node Selection Support (TOML format)"
LABEL version="1.2"