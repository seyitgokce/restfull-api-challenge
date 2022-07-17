# docker-lemp-symfony5
Building a Symfony website (Linux, Nginx, MySQL, PHP) using docker compose

Support HTTP and HTTPS

## Installation

```console
# Create folder to store all your docker compose projects
mkdir docker-projects  

cd docker-projects

git clone https://github.com/seyitgokce/restfull-api-challenge  

cd restfull-api-challenge

cd docker  

# Optional, update environment variables for MySQL connection and Symfony
vi .env

# Optional, update port mapping
# Default
#   MySQL 3306
#   HTTP 80
#   HTTPS 443
#   php-fpm 9000 (can't change here)
vi docker-compose.yml

# Optional, update SSL certificate
# Path
#   nginx/ssl/nginx-selfsigned.crt
#   nginx/ssl/nginx-selfsigned.key

# Start the containers
docker-compose up 

## Version of the images
Linux  
Nginx 1.18  
Mariadb 10.4  
PHP 7.4    
Symfony 5.4
