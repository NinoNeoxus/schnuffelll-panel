#!/bin/bash

# Schnuffelll Panel Installer
# Adapted from Pterodactyl Installer

set -e
trap 'last_command=$current_command; current_command=$BASH_COMMAND' DEBUG
trap 'echo "CRITICAL ERROR: \"${last_command}\" command failed with exit code $?."' ERR

# Load Lib
GITHUB_BASE_URL="https://raw.githubusercontent.com/NinoNeoxus/schnuffelll-panel/master"
if [ -f /tmp/schnuffelll_lib.sh ]; then
  source /tmp/schnuffelll_lib.sh
else
  curl -sSL -o /tmp/schnuffelll_lib.sh "$GITHUB_BASE_URL/installer/lib.sh"
  source /tmp/schnuffelll_lib.sh
fi

# Variables
FQDN="${FQDN:-localhost}"
MYSQL_DB="${MYSQL_DB:-panel}"
MYSQL_USER="${MYSQL_USER:-schnuffelll}"
MYSQL_PASSWORD="${MYSQL_PASSWORD:-$(gen_passwd 64)}"
email="${email:-}"

# Dependencies
ubuntu_dep() {
  install_packages "software-properties-common apt-transport-https ca-certificates gnupg"
  add-apt-repository universe -y
  LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php
}

debian_dep() {
  install_packages "dirmngr ca-certificates apt-transport-https lsb-release"
  curl -o /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
  echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/php.list
}

alma_rocky_dep() {
  install_packages "epel-release http://rpms.remirepo.net/enterprise/remi-release-$OS_VER_MAJOR.rpm"
  dnf module enable -y php:remi-8.2
}

dep_install() {
  output "Installing dependencies for $OS..."
  update_repos

  install_firewall
  firewall_allow_ports "22 80 443"

  case "$OS" in
  ubuntu | debian)
    [ "$OS" == "ubuntu" ] && ubuntu_dep
    [ "$OS" == "debian" ] && debian_dep
    update_repos
    install_packages "php8.2 php8.2-{cli,common,gd,mysql,mbstring,bcmath,xml,fpm,curl,zip} mariadb-common mariadb-server mariadb-client nginx redis-server zip unzip tar git cron"
    install_packages "certbot python3-certbot-nginx"
    ;;
  rocky | almalinux)
    alma_rocky_dep
    install_packages "php php-{common,fpm,cli,json,mysqlnd,mcrypt,gd,mbstring,pdo,zip,bcmath,dom,opcache,posix} mariadb mariadb-server nginx redis zip unzip tar git cronie"
    install_packages "certbot python3-certbot-nginx"
    ;;
  esac

  systemctl enable nginx mariadb redis-server 2>/dev/null || systemctl enable redis 2>/dev/null
  systemctl start nginx mariadb redis-server 2>/dev/null || systemctl start redis 2>/dev/null
  success "Dependencies installed!"
}

install_composer() {
  output "Installing composer..."
  curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
  success "Composer installed!"
}

setup_database() {
  output "Setting up database..."
  
  # Drop user if exists, then create fresh
  mysql -u root -e "DROP USER IF EXISTS '$MYSQL_USER'@'127.0.0.1';" 2>/dev/null || true
  mysql -u root -e "DROP DATABASE IF EXISTS $MYSQL_DB;" 2>/dev/null || true
  
  mysql -u root -e "CREATE USER '$MYSQL_USER'@'127.0.0.1' IDENTIFIED BY '$MYSQL_PASSWORD';"
  mysql -u root -e "CREATE DATABASE $MYSQL_DB;"
  mysql -u root -e "GRANT ALL PRIVILEGES ON $MYSQL_DB.* TO '$MYSQL_USER'@'127.0.0.1' WITH GRANT OPTION;"
  mysql -u root -e "FLUSH PRIVILEGES;"
  
  success "Database configured!"
}

setup_app() {
  output "Setting up Schnuffelll Panel..."
  
  # Directory setup
  mkdir -p /var/www/schnuffelll
  cd /var/www/schnuffelll
  
  # Clone Repo with robust handling
  if [ -d ".git" ]; then
      output "Updating existing repository..."
      git fetch --all
      git reset --hard origin/master
  else
      output "Cloning repository..."
      # Clone to temp dir to avoid conflicts
      git clone https://github.com/NinoNeoxus/schnuffelll-panel.git /tmp/schnuffelll_temp
      # Copy hidden files and normal files
      cp -r /tmp/schnuffelll_temp/. /var/www/schnuffelll/
      rm -rf /tmp/schnuffelll_temp
  fi
  
  # Go into panel directory if it exists (repo structure compatibility)
  if [ -d "panel" ]; then
      cd panel
  fi

  # Environment setup
  if [ ! -f ".env.example" ]; then
      error "Installation corrupted: .env.example not found in $(pwd)"
      # List directory for debugging
      ls -la
      exit 1
  fi
  cp .env.example .env
  
  # Configure Database in .env
  sed -i "s/DB_HOST=127.0.0.1/DB_HOST=127.0.0.1/g" .env
  sed -i "s/DB_PORT=3306/DB_PORT=3306/g" .env
  sed -i "s/DB_DATABASE=laravel/DB_DATABASE=$MYSQL_DB/g" .env
  sed -i "s/DB_USERNAME=root/DB_USERNAME=$MYSQL_USER/g" .env
  sed -i "s/DB_PASSWORD=/DB_PASSWORD=$MYSQL_PASSWORD/g" .env
  
  # Configure URL
  sed -i "s|APP_URL=http://localhost|APP_URL=https://$FQDN|g" .env

  # Dependencies
  output "Installing PHP dependencies..."
  # Remove lock file to force resolution for PHP 8.2 & remove memory limits
  rm -f composer.lock
  COMPOSER_MEMORY_LIMIT=-1 COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-interaction
  
  # Key Generation
  php artisan key:generate --force
  
  # Get the APP_KEY that was just generated
  APP_KEY=$(grep "^APP_KEY=" .env | cut -d '=' -f 2)
  
  # Write .env file directly (Safer than sed)
  cat > .env <<EOF
APP_ENV=production
APP_DEBUG=false
APP_KEY=$APP_KEY
APP_TIMEZONE=UTC
APP_URL=https://$FQDN

LOG_CHANNEL=daily
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=$MYSQL_DB
DB_USERNAME=$MYSQL_USER
DB_PASSWORD=$MYSQL_PASSWORD

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DRIVER=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=null
MAIL_FROM_NAME="Schnuffelll"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=mt1

MIX_PUSHER_APP_KEY="\${PUSHER_APP_KEY}"
MIX_PUSHER_APP_CLUSTER="\${PUSHER_APP_CLUSTER}"

HASHIDS_SALT=$(gen_passwd 20)
HASHIDS_LENGTH=8
EOF
  
  output "Running database migrations..."
  php artisan migrate --seed --force
  
  # Set final permissions
  chown -R www-data:www-data /var/www/schnuffelll
  
  success "Panel setup complete!"
}

setup_nginx() {
  output "Configuring Nginx..."
  
  # Remove default config
  rm -f /etc/nginx/sites-enabled/default

  # Create Nginx config inline
  cat > /etc/nginx/sites-available/schnuffelll.conf <<EOF
server {
    listen 80;
    server_name $FQDN;
    root /var/www/schnuffelll/panel/public;

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    access_log off;
    error_log  /var/log/nginx/schnuffelll.app-error.log error;

    # Allow larger file uploads
    client_max_body_size 100m;
    client_body_timeout 120s;

    sendfile off;

    location ~ \.php$ {
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param PHP_VALUE "upload_max_filesize = 100M \n post_max_size=100M";
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_param HTTP_PROXY "";
        fastcgi_intercept_errors off;
        fastcgi_buffer_size 16k;
        fastcgi_buffers 4 16k;
        fastcgi_connect_timeout 300;
        fastcgi_send_timeout 300;
        fastcgi_read_timeout 300;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOF

  # Enable site
  ln -sf /etc/nginx/sites-available/schnuffelll.conf /etc/nginx/sites-enabled/schnuffelll.conf
  
  # Restart Nginx
  systemctl restart nginx
  
  success "Nginx configured!"
}

setup_ssl() {
  output "Configuring SSL..."
  certbot --nginx -d $FQDN --non-interactive --agree-tos -m $email --redirect
  success "SSL configured!"
}

setup_cron() {
  output "Setting up cron job..."
  (crontab -l 2>/dev/null; echo "* * * * * php /var/www/schnuffelll/panel/artisan schedule:run >> /dev/null 2>&1") | crontab -
  success "Cron job configured!"
}

setup_queue() {
  output "Setting up queue worker..."
  
  cat > /etc/systemd/system/schnuffelll.service <<EOF
[Unit]
Description=Schnuffelll Queue Worker
After=redis-server.service

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/schnuffelll/panel/artisan queue:work --sleep=3 --tries=3
StartLimitInterval=180
StartLimitBurst=30
RestartSec=5s

[Install]
WantedBy=multi-user.target
EOF

  systemctl enable schnuffelll
  systemctl start schnuffelll
  
  success "Queue worker configured!"
}

# Execution Flow
output "Schnuffelll Panel Installation"
read -p "Input Domain (FQDN): " FQDN
read -p "Input Email: " email

dep_install
install_composer
setup_database
setup_app
setup_nginx
setup_ssl
setup_cron
setup_queue

echo ""
success "============================================"
success "Installation Complete!"
success "============================================"
success "Panel URL: https://$FQDN"
success "Default Login: admin@schnuffelll.com / password"
success "============================================"
