#!/bin/bash
set -e

######################################################################################
#                                                                                    #
#                     Schnuffelll Panel - Unified Installer                          #
#                                                                                    #
#     Based on pterodactyl-installer by Vilhelm Prytz <vilhelm@prytznet.se>          #
#     Modified for Schnuffelll by @schnuffelll                                        #
#                                                                                    #
#     This script is not associated with the official Pterodactyl Project.           #
#                                                                                    #
#          https://github.com/NinoNeoxus/schnuffelll-panel                           #
#                                                                                    #
######################################################################################

# -------------------- Paths -------------------- #
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
export SCRIPT_DIR

# -------------------- Source Library -------------------- #
source_lib() {
    if [ -f "$SCRIPT_DIR/lib.sh" ]; then
        source "$SCRIPT_DIR/lib.sh"
    elif [ -f "${SCRIPT_DIR}/installer/lib.sh" ]; then
        source "${SCRIPT_DIR}/installer/lib.sh"
    else
        echo "ERROR: Cannot find lib.sh"
        exit 1
    fi
}

source_lib

# -------------------- Check Requirements -------------------- #
check_requirements() {
    check_root
    check_os_supported
}

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

    if [ -f "$SCRIPT_DIR/installers/panel.sh" ]; then
        bash "$SCRIPT_DIR/installers/panel.sh"
    else
        error "Panel installer not found at: $SCRIPT_DIR/installers/panel.sh"
        exit 1
    fi
}

install_wings() {
    output "Starting Wings installation..."
    echo ""

    if [ -f "$SCRIPT_DIR/installers/wings.sh" ]; then
        bash "$SCRIPT_DIR/installers/wings.sh"
    else
        error "Wings installer not found at: $SCRIPT_DIR/installers/wings.sh"
        exit 1
    fi
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

    # Stop services
    systemctl stop pteroq 2>/dev/null || true
    systemctl disable pteroq 2>/dev/null || true

    # Remove files
    rm -rf /var/www/schnuffelll
    rm -f /etc/nginx/sites-enabled/schnuffelll.conf
    rm -f /etc/nginx/sites-available/schnuffelll.conf
    rm -f /etc/systemd/system/pteroq.service

    # Remove crontab entry
    crontab -l 2>/dev/null | grep -v "artisan schedule:run" | crontab - 2>/dev/null || true

    # Reload
    systemctl daemon-reload
    systemctl reload nginx 2>/dev/null || true

    success "Panel uninstalled successfully"

    echo ""
    output "To completely remove the database, run:"
    echo "  DROP DATABASE schnuffelll;"
    echo "  DROP USER 'schnuffelll'@'127.0.0.1';"
    echo ""
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
    warning "This will NOT remove:"
    echo "  - Docker (needed for other services)"
    echo "  - Server data at /var/lib/pterodactyl"
    echo ""

    if ! confirm "Are you sure you want to uninstall Wings?"; then
        output "Uninstall cancelled."
        return
    fi

    # Stop service
    systemctl stop wings 2>/dev/null || true
    systemctl disable wings 2>/dev/null || true

    # Remove files
    rm -rf /etc/pterodactyl
    rm -f /usr/local/bin/wings
    rm -f /etc/systemd/system/wings.service

    # Reload
    systemctl daemon-reload

    success "Wings uninstalled successfully"

    echo ""
    output "To completely remove server data, run:"
    echo "  rm -rf /var/lib/pterodactyl"
    echo ""
}

# -------------------- CLI Arguments -------------------- #
handle_args() {
    case "$1" in
    --panel | -p)
        check_requirements
        install_panel
        exit 0
        ;;
    --wings | -w)
        check_requirements
        install_wings
        exit 0
        ;;
    --both | -b)
        check_requirements
        install_both
        exit 0
        ;;
    --uninstall-panel)
        check_requirements
        uninstall_panel
        exit 0
        ;;
    --uninstall-wings)
        check_requirements
        uninstall_wings
        exit 0
        ;;
    --help | -h)
        echo "Schnuffelll Panel Installer v${SCRIPT_VERSION}"
        echo ""
        echo "Usage: $0 [OPTION]"
        echo ""
        echo "Options:"
        echo "  -p, --panel           Install Panel only"
        echo "  -w, --wings           Install Wings only"
        echo "  -b, --both            Install Panel and Wings"
        echo "      --uninstall-panel Uninstall Panel"
        echo "      --uninstall-wings Uninstall Wings"
        echo "  -h, --help            Show this help message"
        echo ""
        echo "Without options, an interactive menu will be shown."
        exit 0
        ;;
    esac
}

# -------------------- Main -------------------- #
main() {
    # Handle CLI arguments
    if [ -n "$1" ]; then
        handle_args "$@"
    fi

    check_requirements

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
