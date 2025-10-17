@servers(['dev' => 'root@124.222.142.23'])

@task('develop', ['on' => ['dev']])
su -s /bin/bash www
cd /www/wwwroot/api.comfyui-ai.wifixc.com
git reset --hard
git clean -df
git pull
echo "最近一次提交"
git log --pretty=format:"HEAD is at %h(%s) by %cn %ci %cr" -1  | xargs -0 echo

php composer install --no-progress --no-interaction
php artisan migrate --force
@endtask
