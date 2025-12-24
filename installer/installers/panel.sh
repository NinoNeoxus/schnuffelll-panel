#!/bin/bash

# Schnuffelll Panel Installer
# Adapted from Pterodactyl Installer

set -e

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
  
  # Download panel source code from GitHub
  output "Downloading panel source code..."
  cd /var/www
  rm -rf schnuffelll
  git clone https://github.com/NinoNeoxus/schnuffelll-panel.git schnuffelll
  
  cd /var/www/schnuffelll/panel
  
  # Check if .env.example exists
  if [ ! -f .env.example ]; then
    error "Panel source code not found or incomplete!"
    exit 1
  fi
  
  cp .env.example .env
  
  # Set permissions EARLY to ensure artisan doesn't fail on cache
  chmod -R 755 storage bootstrap/cache
  chown -R www-data:www-data storage bootstrap/cache
  
  output "Installing PHP dependencies..."
  # Remove lock file to force resolution for PHP 8.2 & remove memory limits
  rm -f composer.lock
  COMPOSER_MEMORY_LIMIT=-1 COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-interaction
  
  output "Configuring application..."
  php artisan key:generate --force
  
  # Write database config directly to .env
  sed -i "s|DB_CONNECTION=.*|DB_CONNECTION=mysql|g" .env
  sed -i "s|DB_HOST=.*|DB_HOST=127.0.0.1|g" .env
  sed -i "s|DB_PORT=.*|DB_PORT=3306|g" .env
  sed -i "s|DB_DATABASE=.*|DB_DATABASE=$MYSQL_DB|g" .env
  sed -i "s|DB_USERNAME=.*|DB_USERNAME=$MYSQL_USER|g" .env
  sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=$MYSQL_PASSWORD|g" .env
  sed -i "s|APP_URL=.*|APP_URL=https://$FQDN|g" .env
  
  output "Running database migrations..."
  php artisan migrate --seed --force
  
  # Set final permissions
  chown -R www-data:www-data /var/www/schnuffelll
  
  success "Panel setup complete!"
}

setup_nginx() {
  output "Configuring Nginx..."
  
  # Create Nginx config for panel
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

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

  # Enable site
  ln -sf /etc/nginx/sites-available/schnuffelll.conf /etc/nginx/sites-enabled/
  rm -f /etc/nginx/sites-enabled/default 2>/dev/null || true
  
  # Test and reload
  nginx -t
  systemctl reload nginx
  
  success "Nginx configured!"
}

setup_ssl() {
  output "Setting up SSL with Let's Encrypt..."
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
