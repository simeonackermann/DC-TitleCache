#!/bin/sh

# Init TitleCache Docker Container

# better wait a second (may dockerizing-wait comes to fast)
sleep 1

# may update TitleCache Repo
# git pull

# increase php timeout
sed -i "s/\(max_execution_time\s*\)=.*/\1= 600/" /etc/php5/fpm/php.ini
# increase file upload
sed -i "s/\(upload_max_filesize\s*\)=.*/\1= 100M/" /etc/php5/fpm/php.ini
sed -i "s/\(post_max_size\s*\)=.*/\1= 100M/" /etc/php5/fpm/php.ini
# increase memory
sed -i "s/\(memory_limit\s*\)=.*/\1= 512M/" /etc/php5/fpm/php.ini

# echo "display_errors = On" >> /etc/php5/fpm/php.ini

# start the php5-fpm service
echo "[INFO] starting php …"
service php5-fpm start

# start memcached (creates an echo)
# service memcached start

# start the nginx service
echo "[INFO] starting nginx …"
service nginx start

echo "[INFO] Done. Hanging around idle and listen on port 80 for requests."
nc -kl 80 &

# may output log infos
touch /var/www/log.txt
chmod 777 /var/www/log.txt
tail -f /var/www/log.txt
