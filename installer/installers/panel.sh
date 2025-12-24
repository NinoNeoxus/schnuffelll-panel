#!/bin/bash
set -e

######################################################################################
#                                                                                    #
#                     Schnuffelll Panel Installer - Panel                             #
#                                                                                    #
#    Based on pterodactyl-installer by Vilhelm Prytz <vilhelm@prytznet.se>           #
#    Modified for Schnuffelll by @schnuffelll                                         #
#                                                                                    #
######################################################################################

# -------------------- Source Library -------------------- #
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
INSTALLER_DIR="$(dirname "$SCRIPT_DIR")"

# Source from installers folder or from root
if [ -f "$SCRIPT_DIR/../lib.sh" ]; then
    source "$SCRIPT_DIR/../lib.sh"
elif [ -f "$INSTALLER_DIR/lib.sh" ]; then
    source "$INSTALLER_DIR/lib.sh"
else
    echo "ERROR: Cannot find lib.sh"
    exit 1
fi

# -------------------- Variables -------------------- #
export PANEL_DIR="/var/www/schnuffelll"
export WEBSERVER_USER="www-data"
export PHP_VERSION="8.2"

# User-configurable variables (set later)
export FQDN=""
export USER_EMAIL=""
export CONFIGURE_LETSENCRYPT=false
export CONFIGURE_FIREWALL=false
export ASSUME_SSL=false

# Database configuration
export DB_NAME="schnuffelll"
export DB_USER="schnuffelll"
export DB_PASS=""
export DB_HOST="127.0.0.1"

# Admin user
export ADMIN_EMAIL=""
export ADMIN_USER=""
export ADMIN_FIRST=""
export ADMIN_LAST=""
export ADMIN_PASS=""

# Timezone
export TIMEZONE="UTC"

# -------------------- Installation Functions -------------------- #
install_dependencies() {
    output "Installing dependencies..."

    case "$OS" in
    ubuntu | debian)
        # Add PHP repository
        if ! dpkg -l | grep -q software-properties-common; then
            install_packages "software-properties-common curl apt-transport-https ca-certificates gnupg"
        fi

        # Add Sury PHP repository
        if [ ! -f /etc/apt/sources.list.d/php.list ]; then
            LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php 2>/dev/null || {
                curl -sSLo /usr/share/keyrings/deb.sury.org-php.gpg https://packages.sury.org/php/apt.gpg
                echo "deb [signed-by=/usr/share/keyrings/deb.sury.org-php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" \
                    >/etc/apt/sources.list.d/php.list
            }
        fi

        # Add Redis repository
        if [ ! -f /etc/apt/sources.list.d/redis.list ]; then
            curl -fsSL https://packages.redis.io/gpg | gpg --dearmor -o /usr/share/keyrings/redis-archive-keyring.gpg
            echo "deb [signed-by=/usr/share/keyrings/redis-archive-keyring.gpg] https://packages.redis.io/deb $(lsb_release -cs) main" \
                >/etc/apt/sources.list.d/redis.list
        fi

        # Add MariaDB repo
        curl -sS https://downloads.mariadb.com/MariaDB/mariadb_repo_setup | bash -s -- --mariadb-server-version="mariadb-10.11" --skip-maxscale 2>/dev/null || true

        update_repos

        # Install packages
        install_packages "php${PHP_VERSION} php${PHP_VERSION}-{common,cli,gd,mysql,mbstring,bcmath,xml,curl,zip,fpm}" true
        install_packages "mariadb-server nginx tar unzip git redis-server curl" true

        ;;
    rocky | almalinux)
        dnf install -y epel-release
        rpm -Uvh http://rpms.remirepo.net/enterprise/remi-release-${OS_VER_MAJOR}.rpm 2>/dev/null || true
        dnf module enable -y php:remi-${PHP_VERSION}
        
        install_packages "php php-{common,fpm,cli,json,mysqlnd,gd,mbstring,pdo,zip,bcmath,dom,curl}"
        install_packages "mariadb-server nginx git unzip redis"
        ;;
    esac

    # Start and enable services
    systemctl enable --now mariadb 2>/dev/null || systemctl enable --now mysql 2>/dev/null
    systemctl enable --now redis-server 2>/dev/null || systemctl enable --now redis 2>/dev/null
    systemctl enable --now nginx
    systemctl enable --now php${PHP_VERSION}-fpm 2>/dev/null || systemctl enable --now php-fpm 2>/dev/null

    success "Dependencies installed successfully"
}

install_composer() {
    output "Installing Composer..."

    if ! command -v composer &>/dev/null; then
        curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    fi

    success "Composer installed"
}

download_panel() {
    output "Downloading Schnuffelll Panel..."

    mkdir -p "$PANEL_DIR"
    cd "$PANEL_DIR"

    # Clone from GitHub
    if [ -d "$PANEL_DIR/.git" ]; then
        git pull origin main 2>/dev/null || git pull origin master 2>/dev/null || true
    else
        rm -rf "$PANEL_DIR"/* "$PANEL_DIR"/.[!.]* 2>/dev/null || true
        git clone https://github.com/NinoNeoxus/schnuffelll-panel.git "$PANEL_DIR" 2>/dev/null || {
            error "Failed to clone repository"
            exit 1
        }
    fi

    # Copy panel source if exists
    if [ -d "$PANEL_DIR/panel" ]; then
        cp -r "$PANEL_DIR/panel"/* "$PANEL_DIR/" 2>/dev/null || true
    fi

    success "Panel downloaded"
}

install_composer_deps() {
    output "Installing Composer dependencies..."

    cd "$PANEL_DIR"

    # Create .env from template if doesn't exist
    if [ ! -f "$PANEL_DIR/.env" ]; then
        cp "$PANEL_DIR/.env.example" "$PANEL_DIR/.env"
    fi

    COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-interaction

    success "Dependencies installed"
}

configure_environment() {
    output "Configuring environment..."

    cd "$PANEL_DIR"

    # Generate app key if not set
    if ! grep -q "^APP_KEY=base64:" "$PANEL_DIR/.env" 2>/dev/null; then
        php artisan key:generate --force
    fi

    # Run p:environment:setup
    php artisan p:environment:setup \
        --author="$USER_EMAIL" \
        --url="https://$FQDN" \
        --timezone="$TIMEZONE" \
        --cache="redis" \
        --session="redis" \
        --queue="redis" \
        --redis-host="127.0.0.1" \
        --redis-pass="null" \
        --redis-port="6379" \
        --settings-ui=true \
        --no-interaction

    # Run p:environment:database
    php artisan p:environment:database \
        --host="$DB_HOST" \
        --port="3306" \
        --database="$DB_NAME" \
        --username="$DB_USER" \
        --password="$DB_PASS" \
        --no-interaction

    success "Environment configured"
}

setup_database() {
    output "Setting up database..."

    # Create database and user
    create_db_user "$DB_USER" "$DB_PASS" "$DB_HOST"
    create_db "$DB_NAME" "$DB_USER" "$DB_HOST"

    # Verify connection
    cd "$PANEL_DIR"
    
    # Run migrations
    php artisan migrate --seed --force

    success "Database setup complete"
}

create_admin_user() {
    output "Creating admin user..."

    cd "$PANEL_DIR"

    php artisan p:user:make \
        --email="$ADMIN_EMAIL" \
        --username="$ADMIN_USER" \
        --name-first="$ADMIN_FIRST" \
        --name-last="$ADMIN_LAST" \
        --password="$ADMIN_PASS" \
        --admin=1 \
        --no-interaction

    success "Admin user created"
}

set_permissions() {
    output "Setting permissions..."

    chown -R "$WEBSERVER_USER":"$WEBSERVER_USER" "$PANEL_DIR"
    chmod -R 755 "$PANEL_DIR/storage" "$PANEL_DIR/bootstrap/cache" 2>/dev/null || true

    success "Permissions set"
}

configure_crontab() {
    output "Configuring crontab..."

    crontab_line="* * * * * php $PANEL_DIR/artisan schedule:run >> /dev/null 2>&1"

    if (crontab -l 2>/dev/null | grep -Fq "artisan schedule:run"); then
        output "Crontab already configured"
    else
        (
            crontab -l 2>/dev/null || true
            echo "$crontab_line"
        ) | crontab -
        output "Crontab entry added"
    fi

    success "Crontab configured"
}

configure_pteroq() {
    output "Configuring queue worker..."

    cat >/etc/systemd/system/pteroq.service <<EOF
[Unit]
Description=Schnuffelll Queue Worker
After=redis-server.service

[Service]
User=$WEBSERVER_USER
Group=$WEBSERVER_USER
Restart=always
ExecStart=/usr/bin/php $PANEL_DIR/artisan queue:work --queue=high,standard,low --sleep=3 --tries=3
StartLimitInterval=180
StartLimitBurst=30
RestartSec=5s

[Install]
WantedBy=multi-user.target
EOF

    systemctl daemon-reload
    systemctl enable --now pteroq.service

    success "Queue worker configured"
}

configure_nginx() {
    output "Configuring Nginx..."

    # Auto-detect PHP-FPM socket
    PHP_SOCKET=""
    for sock in \
        "/run/php/php${PHP_VERSION}-fpm.sock" \
        "/var/run/php/php${PHP_VERSION}-fpm.sock" \
        "/run/php-fpm/www.sock" \
        "/var/run/php-fpm/www.sock"; do
        if [ -S "$sock" ] || [ -f "$(dirname "$sock")/www.conf" ] 2>/dev/null; then
            PHP_SOCKET="$sock"
            break
        fi
    done

    if [ -z "$PHP_SOCKET" ]; then
        PHP_SOCKET="/run/php/php${PHP_VERSION}-fpm.sock"
        warning "Could not auto-detect PHP socket, using: $PHP_SOCKET"
    fi

    # Different config based on SSL
    if [ "$CONFIGURE_LETSENCRYPT" = true ]; then
        # HTTPS config
        cat >/etc/nginx/sites-available/schnuffelll.conf <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name $FQDN;
    return 301 https://\$server_name\$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name $FQDN;

    root $PANEL_DIR/public;
    index index.php;

    access_log /var/log/nginx/schnuffelll.app-access.log;
    error_log  /var/log/nginx/schnuffelll.app-error.log error;

    client_max_body_size 100m;
    client_body_timeout 120s;

    sendfile off;

    ssl_certificate /etc/letsencrypt/live/$FQDN/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/$FQDN/privkey.pem;
    ssl_session_cache shared:SSL:10m;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers "ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384";
    ssl_prefer_server_ciphers on;

    add_header X-Content-Type-Options nosniff;
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Robots-Tag none;
    add_header Content-Security-Policy "frame-ancestors 'self'";
    add_header X-Frame-Options DENY;
    add_header Referrer-Policy same-origin;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        fastcgi_split_path_info ^(.+\.php)(/.+)\$;
        fastcgi_pass unix:$PHP_SOCKET;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param PHP_VALUE "upload_max_filesize = 100M \\n post_max_size=100M";
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
    elif [ "$ASSUME_SSL" = true ]; then
        # HTTP only but app assumes SSL (behind proxy)
        cat >/etc/nginx/sites-available/schnuffelll.conf <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name $FQDN;

    root $PANEL_DIR/public;
    index index.php;

    access_log /var/log/nginx/schnuffelll.app-access.log;
    error_log  /var/log/nginx/schnuffelll.app-error.log error;

    client_max_body_size 100m;
    client_body_timeout 120s;

    sendfile off;

    add_header X-Content-Type-Options nosniff;
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Robots-Tag none;
    add_header Content-Security-Policy "frame-ancestors 'self'";
    add_header X-Frame-Options DENY;
    add_header Referrer-Policy same-origin;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        fastcgi_split_path_info ^(.+\.php)(/.+)\$;
        fastcgi_pass unix:$PHP_SOCKET;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param PHP_VALUE "upload_max_filesize = 100M \\n post_max_size=100M";
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
    else
        # HTTP only, no SSL at all
        cat >/etc/nginx/sites-available/schnuffelll.conf <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name $FQDN;

    root $PANEL_DIR/public;
    index index.php;

    access_log /var/log/nginx/schnuffelll.app-access.log;
    error_log  /var/log/nginx/schnuffelll.app-error.log error;

    client_max_body_size 100m;
    client_body_timeout 120s;

    sendfile off;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        fastcgi_split_path_info ^(.+\.php)(/.+)\$;
        fastcgi_pass unix:$PHP_SOCKET;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param PHP_VALUE "upload_max_filesize = 100M \\n post_max_size=100M";
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
    fi

    # Enable site
    rm -f /etc/nginx/sites-enabled/default 2>/dev/null || true
    ln -sf /etc/nginx/sites-available/schnuffelll.conf /etc/nginx/sites-enabled/schnuffelll.conf

    # Test configuration
    if nginx -t; then
        systemctl reload nginx
        success "Nginx configured and reloaded"
    else
        error "Nginx configuration test failed"
        exit 1
    fi
}

configure_letsencrypt() {
    output "Configuring Let's Encrypt..."

    # Install certbot
    case "$OS" in
    ubuntu | debian)
        install_packages "certbot python3-certbot-nginx" true
        ;;
    rocky | almalinux)
        install_packages "certbot python3-certbot-nginx" true
        ;;
    esac

    # Get certificate
    systemctl stop nginx

    if certbot certonly --standalone --agree-tos --no-eff-email --email "$USER_EMAIL" -d "$FQDN"; then
        success "SSL certificate obtained"
    else
        warning "Failed to obtain SSL certificate. You may need to configure it manually."
    fi

    systemctl start nginx
}

# -------------------- Interactive Prompt -------------------- #
ask_database() {
    print_brake 50
    output "DATABASE CONFIGURATION"
    print_brake 50

    required_input DB_NAME "Database name (default: schnuffelll): " "" "schnuffelll"
    required_input DB_USER "Database username (default: schnuffelll): " "" "schnuffelll"
    
    echo ""
    output "Generating random database password..."
    DB_PASS=$(gen_passwd 32)
    output "Password generated successfully"
    echo ""
}

ask_admin() {
    print_brake 50
    output "ADMIN USER CONFIGURATION"
    print_brake 50

    email_input ADMIN_EMAIL "Admin email: " "Invalid email address"
    required_input ADMIN_USER "Admin username: " "Username is required"
    required_input ADMIN_FIRST "Admin first name: " "First name is required"
    required_input ADMIN_LAST "Admin last name: " "Last name is required"
    
    echo ""
    password_input ADMIN_PASS "Admin password: " "Password is required"
    echo ""
}

ask_fqdn() {
    print_brake 50
    output "FQDN CONFIGURATION"
    print_brake 50

    output "The FQDN is the domain/hostname that users will use to access the panel."
    output "Example: panel.example.com"
    echo ""

    while true; do
        required_input FQDN "Panel FQDN: " "FQDN is required"

        # Validate FQDN (basic check)
        if [[ "$FQDN" =~ ^[a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?)*$ ]]; then
            break
        else
            error "Invalid FQDN format. Please enter a valid domain (e.g., panel.example.com)"
        fi
    done
    
    email_input USER_EMAIL "Email for SSL/contact: " "Invalid email address"
    echo ""
}

ask_timezone() {
    print_brake 50
    output "TIMEZONE CONFIGURATION"
    print_brake 50

    output "Enter your timezone (e.g., UTC, America/New_York, Asia/Jakarta)"
    required_input TIMEZONE "Timezone (default: UTC): " "" "UTC"
    echo ""
}

ask_ssl() {
    print_brake 50
    output "SSL CONFIGURATION"
    print_brake 50

    output "Choose how to configure SSL:"
    echo "  1) Configure automatic SSL with Let's Encrypt"
    echo "  2) I will configure SSL myself (assume HTTPS)"
    echo "  3) No SSL (HTTP only) - NOT RECOMMENDED"
    echo ""

    local choice
    while true; do
        read -rp "* Enter choice (1-3): " choice
        case "$choice" in
        1)
            CONFIGURE_LETSENCRYPT=true
            ASSUME_SSL=false
            break
            ;;
        2)
            CONFIGURE_LETSENCRYPT=false
            ASSUME_SSL=true
            break
            ;;
        3)
            CONFIGURE_LETSENCRYPT=false
            ASSUME_SSL=false
            warning "Running without SSL is not recommended for production!"
            break
            ;;
        *)
            error "Invalid choice"
            ;;
        esac
    done
    echo ""
}

show_summary() {
    print_brake 50
    output "INSTALLATION SUMMARY"
    print_brake 50

    echo ""
    echo "  Panel FQDN:     $FQDN"
    echo "  Timezone:       $TIMEZONE"
    echo ""
    echo "  Database name:  $DB_NAME"
    echo "  Database user:  $DB_USER"
    echo ""
    echo "  Admin email:    $ADMIN_EMAIL"
    echo "  Admin username: $ADMIN_USER"
    echo ""
    echo "  SSL:            $([ "$CONFIGURE_LETSENCRYPT" = true ] && echo "Let's Encrypt" || ([ "$ASSUME_SSL" = true ] && echo "Manual/Behind Proxy" || echo "None (HTTP)"))"
    echo "  Firewall:       $([ "$CONFIGURE_FIREWALL" = true ] && echo "Yes" || echo "No")"
    echo ""
    print_brake 50
}

# -------------------- Main Installation -------------------- #
main() {
    check_os_supported

    print_banner

    output "Welcome to the Schnuffelll Panel installer!"
    output "This script will install and configure the Schnuffelll Panel."
    echo ""

    if ! confirm "Do you want to proceed with the installation?"; then
        output "Installation cancelled."
        exit 0
    fi

    echo ""

    # Collect configuration
    ask_fqdn
    ask_database
    ask_admin
    ask_timezone
    ask_ssl
    ask_firewall CONFIGURE_FIREWALL

    # Show summary
    show_summary

    if ! confirm "Proceed with installation?" "y"; then
        output "Installation cancelled."
        exit 0
    fi

    echo ""
    print_brake 50
    output "Starting installation..."
    print_brake 50
    echo ""

    # Install everything
    install_dependencies
    install_composer
    download_panel
    install_composer_deps
    setup_database
    configure_environment
    create_admin_user
    set_permissions
    configure_crontab
    configure_pteroq
    
    # Configure firewall
    if [ "$CONFIGURE_FIREWALL" = true ]; then
        install_firewall
        firewall_allow_ports "80 443"
    fi

    # Configure SSL if requested
    if [ "$CONFIGURE_LETSENCRYPT" = true ]; then
        configure_letsencrypt
    fi

    configure_nginx

    # Final success message
    echo ""
    print_brake 50
    echo -e "${COLOR_GREEN}"
    echo " ✓ INSTALLATION COMPLETE!"
    echo -e "${COLOR_NC}"
    print_brake 50
    echo ""
    echo "  Panel URL: $([ "$ASSUME_SSL" = true ] || [ "$CONFIGURE_LETSENCRYPT" = true ] && echo "https" || echo "http")://$FQDN"
    echo "  Admin:     $ADMIN_EMAIL"
    echo ""
    echo "  Your database password has been saved to: /root/.schnuffelll_db_password"
    echo ""
    print_brake 50

    # Save password to file
    echo "$DB_PASS" > /root/.schnuffelll_db_password
    chmod 600 /root/.schnuffelll_db_password
}

# Run if executed directly
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main
fi
