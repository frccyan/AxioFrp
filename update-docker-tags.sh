#!/bin/bash
# 更新 Docker Compose 中的镜像标签脚本

set -e

# 获取最新的Git标签
LATEST_TAG=$(git describe --tags --abbrev=0 2>/dev/null || echo "latest")
echo "Latest tag: $LATEST_TAG"

# 更新 docker-compose.yml 中的镜像标签
if [ -f "docker-compose.yml" ]; then
    echo "Updating docker-compose.yml..."
    sed -i.bak "s|frccyan/AxioFrp-backend:.*|frccyan/AxioFrp-backend:$LATEST_TAG|g" docker-compose.yml
    sed -i.bak "s|frccyan/AxioFrp-frontend:.*|frccyan/AxioFrp-frontend:$LATEST_TAG|g" docker-compose.yml
    echo "Docker Compose updated with tag: $LATEST_TAG"
else
    echo "docker-compose.yml not found"
    exit 1
fi

# 如果有环境变量，也可以更新
if [ ! -z "$1" ]; then
    CUSTOM_TAG="$1"
    echo "Using custom tag: $CUSTOM_TAG"
    sed -i.bak "s|$LATEST_TAG|$CUSTOM_TAG|g" docker-compose.yml
fi

echo "Done!"