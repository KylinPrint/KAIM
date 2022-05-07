#!/bin/bash

## 变量
WORKSPACE=`dirname $(dirname $(readlink -f $0))`
SQL_ADMIN=$WORKSPACE/deploy/admin.sql
SQL_DATA=$WORKSPACE/deploy/data.sql

## 检查LNMP
checkLNMP()
{
    # TODO: 依赖(Nginx PHP MariaDB Composer *Redis)
    echo "跳过LNMP检查"
}

## Composer 依赖
deployComposer()
{
    # TODO Composer检查
    composer install
}

## 检查.env
checkEnv()
{
    if [ ! -f .env ]; then
        cp .env.example .env
        echo "请填写.env中的数据库字段后重新执行脚本"
        exit 0
    fi
    # 加载.env
    source .env
}

## 校验数据库连接信息
checkSQL()
{
    SQLTEST=`mysql -u$DB_USERNAME -p$DB_PASSWORD -e "quit" 2>&1`
    if [ -n "$SQLTEST" ]; then
        echo ".env文件中的数据库信息不正确"
        exit 1
    fi
}

## 运行迁移
deployDcat()
{
    # TODO 判断执行结果
    php artisan admin:install
}

## 初始化数据
initAdmin()
{
    echo "平台初始化中..."
    SQLRES_ADMIN=`mysql -u$DB_USERNAME -p$DB_PASSWORD -e "
    use $DB_DATABASE;
    source $SQL_ADMIN;
    "`
}

initData()
{
    echo "数据初始化中..."
    SQLRES_DATA=`mysql -u$DB_USERNAME -p$DB_PASSWORD -e "
    use $DB_DATABASE;
    source $SQL_DATA;
    "`
}

## 生成APP_KEY
keyGen()
{
    if [ -z "$APP_KEY" ]; then
        php artisan key:generate
    fi
}

## 生成storage link
storageLink()
{
    rm -f public/storage
    php artisan storage:link
}

## 开整
cd $WORKSPACE

checkEnv
if [ "$1" != "-sqlonly" ]; then
    checkLNMP
    deployComposer
    checkSQL    
    keyGen
    storageLink
fi

deployDcat
initAdmin
if [ "$1" != "-nodata" ]; then
    initData
fi
