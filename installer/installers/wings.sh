#!/bin/bash
set -e

######################################################################################
#                                                                                    #
#                     Schnuffelll Panel Installer - Wings                             #
#                                                                                    #
#    Based on pterodactyl-installer by Vilhelm Prytz <vilhelm@prytznet.se>           #
#    Modified for Schnuffelll by @schnuffelll                                         #
#                                                                                    #
######################################################################################

# -------------------- Source Library -------------------- #
# Check if lib.sh already loaded (from parent install.sh)
if ! type lib_loaded &>/dev/null; then
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    INSTALLER_DIR="$(dirname "$SCRIPT_DIR")"

    if [ -f "$SCRIPT_DIR/../lib.sh" ]; then
        source "$SCRIPT_DIR/../lib.sh"
    elif [ -f "$INSTALLER_DIR/lib.sh" ]; then
        source "$INSTALLER_DIR/lib.sh"
    else
        echo "ERROR: Cannot find lib.sh"
        exit 1
    fi
fi

# -------------------- Variables -------------------- #
export WINGS_DIR="/etc/pterodactyl"
export WINGS_BIN="/usr/local/bin/wings"
export WINGS_VERSION="latest"

# User-configurable
export NODE_FQDN=""
export PANEL_URL=""
export CONFIGURE_FIREWALL=false
export CONFIGURE_LETSENCRYPT=false
export USER_EMAIL=""

# -------------------- Installation Functions -------------------- #
install_docker() {
    output "Installing Docker..."

    if command -v docker &>/dev/null; then
        output "Docker is already installed"
        return 0
    fi

    case "$OS" in
    ubuntu | debian)
        # Install prerequisites
        install_packages "apt-transport-https ca-certificates curl gnupg lsb-release" true

        # Add Docker GPG key
        curl -fsSL https://download.docker.com/linux/$OS/gpg | gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

        # Add Docker repository
        echo \
            "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/$OS \
            $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list >/dev/null

        update_repos true
        install_packages "docker-ce docker-ce-cli containerd.io" true
        ;;

    rocky | almalinux)
        dnf config-manager --add-repo https://download.docker.com/linux/centos/docker-ce.repo
        install_packages "docker-ce docker-ce-cli containerd.io"
        ;;
    esac

    # Enable and start Docker
    systemctl enable --now docker

    success "Docker installed successfully"
}

install_wings() {
    output "Installing Wings daemon..."

    # Create Wings directory
    mkdir -p "$WINGS_DIR"

    # Download Wings binary
    local wings_url="https://github.com/pterodactyl/wings/releases/latest/download/wings_linux_$ARCH"

    output "Downloading Wings from: $wings_url"

    if curl -L -o "$WINGS_BIN" "$wings_url"; then
        chmod +x "$WINGS_BIN"
        success "Wings binary downloaded"
    else
        error "Failed to download Wings"
        exit 1
    fi
}

configure_wings_systemd() {
    output "Configuring Wings systemd service..."

    cat >/etc/systemd/system/wings.service <<EOF
[Unit]
Description=Pterodactyl Wings Daemon
After=docker.service
Requires=docker.service
PartOf=docker.service

[Service]
User=root
WorkingDirectory=$WINGS_DIR
LimitNOFILE=4096
PIDFile=/var/run/wings/daemon.pid
ExecStart=$WINGS_BIN
Restart=on-failure
StartLimitInterval=180
StartLimitBurst=30
RestartSec=5s

[Install]
WantedBy=multi-user.target
EOF

    systemctl daemon-reload
    systemctl enable wings

    success "Wings systemd service configured"
}

configure_wings_ssl() {
    output "Configuring SSL for Wings..."

    # Install certbot
    case "$OS" in
    ubuntu | debian)
        install_packages "certbot" true
        ;;
    rocky | almalinux)
        install_packages "certbot" true
        ;;
    esac

    # Stop wings if running
    systemctl stop wings 2>/dev/null || true

    # Get certificate
    if certbot certonly --standalone --agree-tos --no-eff-email --email "$USER_EMAIL" -d "$NODE_FQDN"; then
        success "SSL certificate obtained for $NODE_FQDN"
    else
        warning "Failed to obtain SSL certificate. You may need to configure it manually."
        warning "Wings will run in HTTP mode or behind a reverse proxy."
    fi
}

create_wings_config() {
    output "Creating Wings configuration..."

    cat >"$WINGS_DIR/config.yml" <<EOF
# Wings Configuration
# This is a TEMPLATE - You must complete the configuration from the Panel!

debug: false
uuid: REPLACE_WITH_NODE_UUID
token_id: REPLACE_WITH_TOKEN_ID
token: REPLACE_WITH_TOKEN

api:
  host: 0.0.0.0
  port: 8080
  ssl:
    enabled: false
    cert: /etc/letsencrypt/live/$NODE_FQDN/fullchain.pem
    key: /etc/letsencrypt/live/$NODE_FQDN/privkey.pem
  upload_limit: 100

system:
  data: /var/lib/pterodactyl/volumes
  sftp:
    bind_port: 2022

allowed_mounts: []

remote: $PANEL_URL
EOF

    chmod 600 "$WINGS_DIR/config.yml"

    success "Wings config template created"
}

# -------------------- Interactive Prompt -------------------- #
ask_node_fqdn() {
    print_brake 50
    output "NODE CONFIGURATION"
    print_brake 50

    output "The NODE FQDN is the domain/hostname for THIS node."
    output "This is DIFFERENT from your panel FQDN!"
    output ""
    output "Example: node1.example.com"
    echo ""

    while true; do
        required_input NODE_FQDN "Node FQDN: " "Node FQDN is required"

        # Validate FQDN
        if [[ "$NODE_FQDN" =~ ^[a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?)*$ ]]; then
            break
        else
            error "Invalid FQDN format"
        fi
    done
    echo ""
}

ask_panel_url() {
    print_brake 50
    output "PANEL URL CONFIGURATION"
    print_brake 50

    output "Enter the full URL to your Schnuffelll Panel."
    output "Example: https://panel.example.com"
    echo ""

    while true; do
        required_input PANEL_URL "Panel URL: " "Panel URL is required"

        if [[ "$PANEL_URL" =~ ^https?:// ]]; then
            break
        else
            error "URL must start with http:// or https://"
        fi
    done
    echo ""
}

ask_wings_ssl() {
    print_brake 50
    output "SSL CONFIGURATION"
    print_brake 50

    output "Do you want to configure SSL for Wings?"
    output "This is recommended if Wings is not behind a reverse proxy."
    echo ""

    if confirm "Configure SSL with Let's Encrypt for Wings?"; then
        CONFIGURE_LETSENCRYPT=true
        email_input USER_EMAIL "Email for Let's Encrypt: " "Invalid email"
    else
        CONFIGURE_LETSENCRYPT=false
        output "Wings will run in HTTP mode (port 8080)"
        output "Make sure to configure a reverse proxy for HTTPS!"
    fi
    echo ""
}

show_summary() {
    print_brake 50
    output "INSTALLATION SUMMARY"
    print_brake 50

    echo ""
    echo "  Node FQDN:   $NODE_FQDN"
    echo "  Panel URL:   $PANEL_URL"
    echo "  SSL:         $([ "$CONFIGURE_LETSENCRYPT" = true ] && echo "Let's Encrypt" || echo "None/Behind Proxy")"
    echo "  Firewall:    $([ "$CONFIGURE_FIREWALL" = true ] && echo "Yes" || echo "No")"
    echo ""
    print_brake 50
}

show_next_steps() {
    print_brake 70
    echo ""
    echo -e "${COLOR_GREEN}# NEXT STEPS TO COMPLETE INSTALLATION${COLOR_NC}"
    echo ""
    echo "  1. Go to your Panel: $PANEL_URL"
    echo ""
    echo "  2. Navigate to: Admin → Nodes → Create New"
    echo ""
    echo "  3. Fill in the node details:"
    echo "     - Name: Your node name"
    echo "     - FQDN: $NODE_FQDN"
    echo "     - SSL: $([ "$CONFIGURE_LETSENCRYPT" = true ] && echo "Use HTTPS" || echo "HTTP/Behind Proxy")"
    echo "     - Daemon Port: 8080"
    echo "     - SFTP Port: 2022"
    echo ""
    echo "  4. After creating the node, click on it and go to 'Configuration'"
    echo ""
    echo "  5. Copy the auto-deploy command OR paste the config.yml content"
    echo "     into: $WINGS_DIR/config.yml"
    echo ""
    echo "  6. Start Wings with:"
    echo "     sudo systemctl start wings"
    echo ""
    echo "  7. Check status with:"
    echo "     sudo systemctl status wings"
    echo ""
    echo "  8. View logs with:"
    echo "     sudo journalctl -u wings -f"
    echo ""
    print_brake 70
}

# -------------------- Main Installation -------------------- #
main() {
    check_os_supported

    print_banner

    output "Welcome to the Schnuffelll Wings Installer!"
    output "This script will install the Wings daemon for running game servers."
    echo ""

    if ! confirm "Do you want to proceed with the installation?"; then
        output "Installation cancelled."
        exit 0
    fi

    echo ""

    # Collect configuration
    ask_node_fqdn
    ask_panel_url
    ask_wings_ssl
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

    # Install components
    install_docker
    install_wings
    configure_wings_systemd
    create_wings_config

    # Configure firewall
    if [ "$CONFIGURE_FIREWALL" = true ]; then
        install_firewall
        firewall_allow_ports "8080 2022"
    fi

    # Configure SSL
    if [ "$CONFIGURE_LETSENCRYPT" = true ]; then
        configure_wings_ssl
    fi

    # Final success message
    echo ""
    print_brake 50
    echo -e "${COLOR_GREEN}"
    echo " ✓ WINGS INSTALLATION COMPLETE!"
    echo -e "${COLOR_NC}"
    print_brake 50
    echo ""

    show_next_steps
}

# Run if executed directly
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main
fi
