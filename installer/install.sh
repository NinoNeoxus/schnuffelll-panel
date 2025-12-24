#!/bin/bash
set -e

######################################################################################
#                                                                                    #
#                     Schnuffelll Panel - Unified Installer                          #
#                                                                                    #
#     Based on pterodactyl-installer by Vilhelm Prytz                                #
#     Modified for Schnuffelll by @schnuffelll                                        #
#                                                                                    #
#          https://github.com/NinoNeoxus/schnuffelll-panel                           #
#                                                                                    #
######################################################################################

export SCRIPT_VERSION="4.0.0"
export GITHUB_BASE_URL="https://raw.githubusercontent.com/NinoNeoxus/schnuffelll-panel/master"

# -------------------- Download and Source Library -------------------- #
TEMP_DIR=$(mktemp -d)
trap "rm -rf $TEMP_DIR" EXIT

download_lib() {
    echo "* Downloading installer files..."
    
    curl -sSL "$GITHUB_BASE_URL/installer/lib.sh" -o "$TEMP_DIR/lib.sh" || {
        echo "ERROR: Failed to download lib.sh"
        exit 1
    }
    
    mkdir -p "$TEMP_DIR/installers"
    
    curl -sSL "$GITHUB_BASE_URL/installer/installers/panel.sh" -o "$TEMP_DIR/installers/panel.sh" || {
        echo "ERROR: Failed to download panel.sh"
        exit 1
    }
    
    curl -sSL "$GITHUB_BASE_URL/installer/installers/wings.sh" -o "$TEMP_DIR/installers/wings.sh" || {
        echo "ERROR: Failed to download wings.sh"
        exit 1
    }
    
    chmod +x "$TEMP_DIR/lib.sh" "$TEMP_DIR/installers/panel.sh" "$TEMP_DIR/installers/wings.sh"
    
    echo "* Files downloaded successfully"
}

download_lib
source "$TEMP_DIR/lib.sh"

export SCRIPT_DIR="$TEMP_DIR"

# -------------------- Menu Functions -------------------- #
show_menu() {
    clear
    print_banner

    print_brake 50
    output "What would you like to do?"
    print_brake 50
    echo ""
    echo "  [1] Install Panel"
    echo "  [2] Install Wings (Daemon)"
    echo ""
    echo "  [3] Install Both (Panel + Wings)"
    echo ""
    echo "  [4] Uninstall Panel"
    echo "  [5] Uninstall Wings"
    echo ""
    echo "  [0] Exit"
    echo ""
    print_brake 50
}

install_panel() {
    output "Starting Panel installation..."
    echo ""
    bash "$TEMP_DIR/installers/panel.sh"
}

install_wings() {
    output "Starting Wings installation..."
    echo ""
    bash "$TEMP_DIR/installers/wings.sh"
}

install_both() {
    output "Installing Panel first, then Wings..."
    echo ""
    install_panel

    echo ""
    print_brake 50
    output "Panel installation complete. Now installing Wings..."
    print_brake 50
    echo ""

    install_wings
}

uninstall_panel() {
    print_brake 50
    output "UNINSTALLING PANEL"
    print_brake 50

    warning "This will remove:"
    echo "  - Panel files at /var/www/schnuffelll"
    echo "  - Nginx configuration"
    echo "  - Crontab entry"
    echo "  - Queue worker service"
    echo ""
    warning "This will NOT remove:"
    echo "  - Database and database user"
    echo "  - PHP, MariaDB, Redis, Nginx packages"
    echo ""

    if ! confirm "Are you sure you want to uninstall the panel?"; then
        output "Uninstall cancelled."
        return
    fi

    systemctl stop pteroq 2>/dev/null || true
    systemctl disable pteroq 2>/dev/null || true

    rm -rf /var/www/schnuffelll
    rm -f /etc/nginx/sites-enabled/schnuffelll.conf
    rm -f /etc/nginx/sites-available/schnuffelll.conf
    rm -f /etc/systemd/system/pteroq.service

    crontab -l 2>/dev/null | grep -v "artisan schedule:run" | crontab - 2>/dev/null || true

    systemctl daemon-reload
    systemctl reload nginx 2>/dev/null || true

    success "Panel uninstalled successfully"
}

uninstall_wings() {
    print_brake 50
    output "UNINSTALLING WINGS"
    print_brake 50

    warning "This will remove:"
    echo "  - Wings binary"
    echo "  - Wings configuration"
    echo "  - Wings systemd service"
    echo ""

    if ! confirm "Are you sure you want to uninstall Wings?"; then
        output "Uninstall cancelled."
        return
    fi

    systemctl stop wings 2>/dev/null || true
    systemctl disable wings 2>/dev/null || true

    rm -rf /etc/pterodactyl
    rm -f /usr/local/bin/wings
    rm -f /etc/systemd/system/wings.service

    systemctl daemon-reload

    success "Wings uninstalled successfully"
}

# -------------------- CLI Arguments -------------------- #
handle_args() {
    case "$1" in
    --panel | -p)
        check_os_supported
        install_panel
        exit 0
        ;;
    --wings | -w)
        check_os_supported
        install_wings
        exit 0
        ;;
    --both | -b)
        check_os_supported
        install_both
        exit 0
        ;;
    --uninstall-panel)
        check_os_supported
        uninstall_panel
        exit 0
        ;;
    --uninstall-wings)
        check_os_supported
        uninstall_wings
        exit 0
        ;;
    --help | -h)
        echo "Schnuffelll Panel Installer v${SCRIPT_VERSION}"
        echo ""
        echo "Usage: bash <(curl -s $GITHUB_BASE_URL/installer/install.sh) [OPTION]"
        echo ""
        echo "Options:"
        echo "  -p, --panel           Install Panel only"
        echo "  -w, --wings           Install Wings only"
        echo "  -b, --both            Install Panel and Wings"
        echo "      --uninstall-panel Uninstall Panel"
        echo "      --uninstall-wings Uninstall Wings"
        echo "  -h, --help            Show this help message"
        echo ""
        exit 0
        ;;
    esac
}

# -------------------- Main -------------------- #
main() {
    if [ -n "$1" ]; then
        handle_args "$@"
    fi

    check_os_supported

    while true; do
        show_menu

        local choice
        read -rp "* Enter your choice: " choice

        case "$choice" in
        1)
            install_panel
            break
            ;;
        2)
            install_wings
            break
            ;;
        3)
            install_both
            break
            ;;
        4)
            uninstall_panel
            read -rp "Press Enter to continue..."
            ;;
        5)
            uninstall_wings
            read -rp "Press Enter to continue..."
            ;;
        0)
            output "Goodbye!"
            exit 0
            ;;
        *)
            error "Invalid choice"
            sleep 2
            ;;
        esac
    done
}

main "$@"
