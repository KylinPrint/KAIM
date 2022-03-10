#!/bin/bash
# 变量
WORKSPACE=`dirname $(dirname $(readlink -f $0))`
SQL=$WORKSPACE/deploy/init.sql

# 开整
# TODO: 依赖(Nginx PHP MariaDB Composer *Redis)

cd $WORKSPACE
# Composer 依赖
composer install

# 检查.env
if [ ! -f .env ]; then
    cp .env.example .env
    echo "请填写.env中的数据库字段后重新执行脚本"
    exit 0
fi
# 加载.env
source .env

# 校验数据库连接信息
SQLTEST=`mysql -u$DB_USERNAME -p$DB_PASSWORD -e "quit" 2>&1`
if [ -n "$SQLTEST" ]; then
    echo ".env文件中的数据库信息不正确"
    exit 1
fi

# 运行迁移
# TODO 判断执行结果
php artisan admin:install

# 执行init.sql
SQLINIT=`mysql -u$DB_USERNAME -p$DB_PASSWORD -e "
use $DB_DATABASE;
source $SQL;
"`

# 生成APP_KEY
if [ -z "$APP_KEY" ]; then
    php artisan key:generate
fi

# 生成storage link
# TODO 先判断有没有
php artisan storage:link

echo "部署完成,默认用户名/密码: admin/admin"
