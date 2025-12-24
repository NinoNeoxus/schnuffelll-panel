#!/bin/bash

# Schnuffelll Panel Installer v3.0
# @author schnuffelll

set -e
trap 'last_command=$current_command; current_command=$BASH_COMMAND' DEBUG
trap 'echo "CRITICAL ERROR: \"${last_command}\" command failed with exit code $?."' ERR

# Load Lib
GITHUB_BASE_URL="https://raw.githubusercontent.com/NinoNeoxus/schnuffelll-panel/master"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

if [ -f "$SCRIPT_DIR/../lib.sh" ]; then
  source "$SCRIPT_DIR/../lib.sh"
elif [ -f /tmp/schnuffelll_lib.sh ]; then
  source /tmp/schnuffelll_lib.sh
else
  curl -sSL -o /tmp/schnuffelll_lib.sh "$GITHUB_BASE_URL/installer/lib.sh"
  source /tmp/schnuffelll_lib.sh
fi

# Variables (can be set via environment)
FQDN="${FQDN:-}"
MYSQL_DB="${MYSQL_DB:-panel}"
MYSQL_USER="${MYSQL_USER:-schnuffelll}"
MYSQL_PASSWORD="${MYSQL_PASSWORD:-$(gen_passwd 64)}"
USER_EMAIL="${USER_EMAIL:-}"
SKIP_SSL="${SKIP_SSL:-false}"

# Input validation
get_user_input() {
  # Get FQDN
  while [ -z "$FQDN" ]; do
    read -p "Enter Domain/FQDN (e.g., panel.example.com): " FQDN
    if [ -z "$FQDN" ]; then
      error "FQDN cannot be empty!"
    fi
  done

  # Get Email (required for SSL)
  if [ "$SKIP_SSL" != "true" ]; then
    while [ -z "$USER_EMAIL" ]; do
      read -p "Enter Email (for SSL certificate): " USER_EMAIL
      if ! valid_email "$USER_EMAIL"; then
        error "Invalid email format!"
        USER_EMAIL=""
      fi
    done
  else
    output "Skipping SSL setup (SKIP_SSL=true)"
    if [ -z "$USER_EMAIL" ]; then
      USER_EMAIL="admin@localhost"
    fi
  fi
}

# Dependencies
ubuntu_dep() {
  output "Adding PHP repository for Ubuntu..."
  install_packages "software-properties-common apt-transport-https ca-certificates gnupg"
  add-apt-repository universe -y 2>/dev/null || true
  LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php
}

debian_dep() {
  output "Adding PHP repository for Debian..."
  install_packages "dirmngr ca-certificates apt-transport-https lsb-release"
  curl -sSLo /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
  echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/php.list
}

alma_rocky_dep() {
  output "Adding PHP repository for RHEL-based..."
  install_packages "epel-release http://rpms.remirepo.net/enterprise/remi-release-$OS_VER_MAJOR.rpm"
  dnf module enable -y php:remi-8.2
}

dep_install() {
  output "Installing dependencies for $OS $OS_VER..."
  update_repos

  install_firewall
  firewall_allow_ports "22 80 443"

  case "$OS" in
  ubuntu | debian)
    [ "$OS" == "ubuntu" ] && ubuntu_dep
    [ "$OS" == "debian" ] && debian_dep
    update_repos
    install_packages "php8.2 php8.2-{cli,common,gd,mysql,mbstring,bcmath,xml,fpm,curl,zip} mariadb-common mariadb-server mariadb-client nginx zip unzip tar git cron"
    # Install Redis
    install_packages "redis-server" || install_packages "redis"
    # Install certbot if SSL enabled
    [ "$SKIP_SSL" != "true" ] && install_packages "certbot python3-certbot-nginx"
    ;;
  rocky | almalinux)
    alma_rocky_dep
    install_packages "php php-{common,fpm,cli,json,mysqlnd,mcrypt,gd,mbstring,pdo,zip,bcmath,dom,opcache,posix} mariadb mariadb-server nginx zip unzip tar git cronie"
    # Install Redis
    install_packages "redis"
    # Install certbot if SSL enabled
    [ "$SKIP_SSL" != "true" ] && install_packages "certbot python3-certbot-nginx"
    ;;
  esac

  # Enable services - handle different Redis package names
  systemctl enable nginx mariadb
  systemctl enable redis-server 2>/dev/null || systemctl enable redis 2>/dev/null || true
  
  systemctl start nginx mariadb
  systemctl start redis-server 2>/dev/null || systemctl start redis 2>/dev/null || true
  
  success "Dependencies installed!"
}

install_composer() {
  output "Installing Composer..."
  if [ -x "$(command -v composer)" ]; then
    output "Composer already installed, updating..."
    composer self-update 2>/dev/null || true
  else
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
  fi
  success "Composer ready!"
}

setup_database() {
  output "Setting up database..."
  
  # Detect mysql command
  MYSQL_CMD="mysql"
  if command -v mariadb &> /dev/null; then
    MYSQL_CMD="mariadb"
  fi
  
  # Drop existing if any
  $MYSQL_CMD -u root -e "DROP USER IF EXISTS '$MYSQL_USER'@'127.0.0.1';" 2>/dev/null || true
  $MYSQL_CMD -u root -e "DROP USER IF EXISTS '$MYSQL_USER'@'localhost';" 2>/dev/null || true
  $MYSQL_CMD -u root -e "DROP DATABASE IF EXISTS $MYSQL_DB;" 2>/dev/null || true
  
  # Create Database & Users
  $MYSQL_CMD -u root -e "CREATE DATABASE $MYSQL_DB;"
  $MYSQL_CMD -u root -e "CREATE USER '$MYSQL_USER'@'127.0.0.1' IDENTIFIED BY '$MYSQL_PASSWORD';"
  $MYSQL_CMD -u root -e "CREATE USER '$MYSQL_USER'@'localhost' IDENTIFIED BY '$MYSQL_PASSWORD';"
  
  # Grant Privileges
  $MYSQL_CMD -u root -e "GRANT ALL PRIVILEGES ON $MYSQL_DB.* TO '$MYSQL_USER'@'127.0.0.1' WITH GRANT OPTION;"
  $MYSQL_CMD -u root -e "GRANT ALL PRIVILEGES ON $MYSQL_DB.* TO '$MYSQL_USER'@'localhost' WITH GRANT OPTION;"
  $MYSQL_CMD -u root -e "FLUSH PRIVILEGES;"
  
  success "Database configured!"
}

setup_app() {
  output "Setting up Schnuffelll Panel..."
  
  mkdir -p /var/www/schnuffelll
  cd /var/www/schnuffelll
  
  # Clone or update repo
  if [ -d ".git" ]; then
      output "Updating existing repository..."
      git fetch --all
      git reset --hard origin/master
  else
      output "Cloning repository..."
      rm -rf /tmp/schnuffelll_temp
      git clone https://github.com/NinoNeoxus/schnuffelll-panel.git /tmp/schnuffelll_temp
      cp -r /tmp/schnuffelll_temp/. /var/www/schnuffelll/
      rm -rf /tmp/schnuffelll_temp
  fi
  
  # Go into panel directory
  if [ -d "panel" ]; then
      cd panel
  fi

  # Verify installation
  if [ ! -f ".env.example" ]; then
      error "Installation corrupted: .env.example not found!"
      ls -la
      exit 1
  fi
  
  cp .env.example .env
  
  # Install dependencies
  output "Installing PHP dependencies..."
  rm -f composer.lock
  COMPOSER_MEMORY_LIMIT=-1 COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-interaction
  
  # Generate key
  php artisan key:generate --force
  APP_KEY=$(grep "^APP_KEY=" .env | cut -d '=' -f 2)
  
  # Determine URL scheme
  if [ "$SKIP_SSL" == "true" ]; then
    APP_URL="http://$FQDN"
  else
    APP_URL="https://$FQDN"
  fi
  
  # Write .env
  cat > .env <<EOF
APP_ENV=production
APP_DEBUG=false
APP_KEY=$APP_KEY
APP_TIMEZONE=UTC
APP_URL=$APP_URL

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
MAIL_FROM_ADDRESS=noreply@$FQDN
MAIL_FROM_NAME="Schnuffelll"

HASHIDS_SALT=$(gen_passwd 20)
HASHIDS_LENGTH=8
EOF
  
  output "Running database migrations..."
  php artisan migrate --seed --force
  
  # Set permissions
  chown -R www-data:www-data /var/www/schnuffelll
  chmod -R 755 /var/www/schnuffelll/panel/storage
  chmod -R 755 /var/www/schnuffelll/panel/bootstrap/cache
  
  success "Panel setup complete!"
}

setup_nginx() {
  output "Configuring Nginx..."
  
  rm -f /etc/nginx/sites-enabled/default

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

    client_max_body_size 100m;
    client_body_timeout 120s;
    sendfile off;

    location ~ \.php\$ {
        fastcgi_split_path_info ^(.+\.php)(/.+)\$;
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

  ln -sf /etc/nginx/sites-available/schnuffelll.conf /etc/nginx/sites-enabled/schnuffelll.conf
  
  # Test config before restart
  nginx -t
  systemctl restart nginx
  
  success "Nginx configured!"
}

setup_ssl() {
  if [ "$SKIP_SSL" == "true" ]; then
    output "Skipping SSL setup..."
    return
  fi
  
  output "Configuring SSL with Let's Encrypt..."
  certbot --nginx -d $FQDN --non-interactive --agree-tos -m $USER_EMAIL --redirect
  success "SSL configured!"
}

setup_cron() {
  output "Setting up cron job..."
  (crontab -l 2>/dev/null | grep -v "schnuffelll"; echo "* * * * * php /var/www/schnuffelll/panel/artisan schedule:run >> /dev/null 2>&1") | crontab -
  success "Cron job configured!"
}

setup_queue() {
  output "Setting up queue worker..."
  
  cat > /etc/systemd/system/schnuffelll.service <<EOF
[Unit]
Description=Schnuffelll Queue Worker
After=redis-server.service redis.service

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

  systemctl daemon-reload
  systemctl enable schnuffelll
  systemctl start schnuffelll
  
  success "Queue worker configured!"
}

# ==================== MAIN ====================

output "╔════════════════════════════════════════╗"
output "║     SCHNUFFELLL PANEL INSTALLATION     ║"
output "╚════════════════════════════════════════╝"

get_user_input

dep_install
install_composer
setup_database
setup_app
setup_nginx
setup_ssl
setup_cron
setup_queue

echo ""
print_brake 50
success "Installation Complete!"
print_brake 50
echo ""
if [ "$SKIP_SSL" == "true" ]; then
  output "Panel URL: http://$FQDN"
else
  output "Panel URL: https://$FQDN"
fi
output "Default Login: admin@schnuffelll.com / password"
output ""
warning "CHANGE YOUR PASSWORD IMMEDIATELY!"
print_brake 50
