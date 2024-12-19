#!/bin/sh

# 執行 migration
php artisan migrate --force

# 檢查資料庫是否已經有資料
record_count=$(php artisan tinker --execute="echo DB::table('seats')->count();" | tr -d '\n\r')

if [ "$record_count" -eq 0 ]; then
    echo "Database is empty. Running seeders..."
    php artisan db:seed --force
else
    echo "Database already has data. Skipping seeders."
fi

# 啟動 Laravel 伺服器
php artisan serve --host=0.0.0.0 --port=8000
