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
  dnf module enable -y php:remi-8.2 # Use 8.2 for compatibility
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

setup_app() {
  output "Setting up Schnuffelll Panel..."
  mkdir -p /var/www/schnuffelll
  
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
  
  output "Installing PHP dependencies..."
  composer install --no-dev --optimize-autoloader --no-interaction
  
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
  
  # Set permissions
  chown -R www-data:www-data /var/www/schnuffelll
  chmod -R 755 /var/www/schnuffelll/panel/storage
  chmod -R 755 /var/www/schnuffelll/panel/bootstrap/cache
  
  success "Panel setup complete!"
}

# Execution Flow
output "Schnuffelll Panel Installation"
read -p "Input Domain (FQDN): " FQDN
read -p "Input Email: " email

dep_install
install_composer
create_db_user "$MYSQL_USER" "$MYSQL_PASSWORD"
create_db "$MYSQL_DB" "$MYSQL_USER"
setup_app

# Nginx & SSL
output "Configuring Nginx & SSL..."
certbot --nginx -d $FQDN --non-interactive --agree-tos -m $email
success "Installation Complete! Access at https://$FQDN"
