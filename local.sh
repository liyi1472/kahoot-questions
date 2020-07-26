docker network create \
	--driver=bridge \
	--subnet=203.0.0.0/24 \
	--gateway=203.0.0.1 \
	local

docker stop local-php
docker run -d --rm \
	--net=local \
	--name local-php \
	-v ~/Code:/var/www/html \
	-v ~/Code/kahoot-questions/local/zoneinfo/Asia/Tokyo:/etc/localtime:ro \
	-v ~/Code/kahoot-questions/local/php:/usr/local/etc/php/conf.d \
	php:fpm

docker stop local-nginx
docker run -d --rm \
	--net=local \
	--name local-nginx \
	-p 8000:80 \
	-v ~/Code:/var/www/html \
	-v ~/Code/kahoot-questions/local/zoneinfo/Asia/Tokyo:/etc/localtime:ro \
	-v ~/Code/kahoot-questions/local/nginx:/etc/nginx/conf.d \
	nginx:latest

browser-sync start --no-notify --proxy "localhost:8000" --files="**/*.*"
