apt-get update
apt-get install curl -y
apt-get install -y php php-dev
cd /
mkdir php-mod
cd php-mod
curl 'http://pecl.php.net/get/redis-3.1.2.tgz' > redis.tgz
tar -zxvf redis.tgz
cd redis-3.1.2/
phpize
./configure
make
make install
echo "extension=redis.so" > /etc/php/7.0/cli/conf.d/20-redis.ini
cd /php-mod/
curl 'http://pecl.php.net/get/swoole-1.9.13.tgz' >swoole.tgz
tar zxvf swoole.tgz
cd swoole-1.9.13/
phpize
./configure
make 
make install
echo "extension=swoole.so" > /etc/php/7.0/cli/conf.d/20-swoole.ini
cd /
mkdir barrage
cd barrage/
apt-get install lrzsz -y