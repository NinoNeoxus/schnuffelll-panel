#!/bin/bash

# Schnuffelll Magic Installer (v2.2 - Uninstall Added)

set -e

GITHUB_BASE_URL="https://raw.githubusercontent.com/NinoNeoxus/schnuffelll-panel/master"

# Download Lib (always fresh)
rm -f /tmp/schnuffelll_lib.sh
curl -sSL -o /tmp/schnuffelll_lib.sh "$GITHUB_BASE_URL/installer/lib.sh"
source /tmp/schnuffelll_lib.sh

welcome() {
  clear
  echo "
   _____      _                     __  __      _ _ _ 
  / ____|    | |                   / _|/ _|    | | | |
 | (___   ___| |__  _ __  _   _ __| |_| |_ ___| | | |
  \___ \ / __| '_ \| '_ \| | | |/ _\` |  _|/ _ \ | | |
  ____) | (__| | | | | | | |_| | (_| | | |  __/ | | |
 |_____/ \___|_| |_|_| |_|\__,_|\__,_|_| |_|  \___|_|_|
                                                       
  "
  output "Schnuffelll Installer - Remote Edition"
  output "OS: $OS $OS_VER"
  echo ""
}

run_remote_script() {
    local script_path=$1
    local temp_file="/tmp/schnuff_inst_$(basename "$script_path")"
    local url="$GITHUB_BASE_URL/$script_path"

    echo "Fetching script: $url"
    
    curl -sSL -o "$temp_file" "$url"
    
    if grep -q "^404: Not Found" "$temp_file" || grep -q "^Not Found" "$temp_file"; then
        echo "CRITICAL ERROR: Could not fetch script!"
        echo "URL: $url"
        echo "Response: $(cat "$temp_file")"
        rm -f "$temp_file"
        exit 1
    fi

    # Execute
    bash "$temp_file"
    rm -f "$temp_file"
}

uninstall() {
  output "WARNING: This will FULLY UNINSTALL Schnuffelll Panel & Wings!"
  output "This includes deleting:"
  output "- All Panel files (/var/www/schnuffelll)"
  output "- All Wings files (/etc/pterodactyl, /var/lib/pterodactyl)"
  output "- Nginx configurations"
  output "- Systemd services"
  
  read -p "Are you absolutely sure? (type 'yes' to confirm): " confirm
  if [[ "$confirm" != "yes" ]]; then
    output "Aborted."
    return
  fi

  output "Stopping services..."
  systemctl stop schnuffelll wings nginx redis-server 2>/dev/null || true
  
  output "Removing Panel files..."
  rm -rf /var/www/schnuffelll
  rm -f /etc/nginx/sites-available/schnuffelll.conf
  rm -f /etc/nginx/sites-enabled/schnuffelll.conf
  rm -f /etc/systemd/system/schnuffelll.service
  
  output "Removing Wings files..."
  rm -f /usr/local/bin/wings
  rm -f /etc/systemd/system/wings.service
  rm -rf /etc/pterodactyl
  rm -rf /var/lib/pterodactyl
  
  output "Reloading system..."
  systemctl daemon-reload
  systemctl reload nginx 2>/dev/null || true
  
  success "Uninstallation complete! You can now install clean."
}

install_panel() {
    run_remote_script "installer/installers/panel.sh?v=DB_FIX_$(date +%s)"
}

install_wings() {
    run_remote_script "installer/installers/wings.sh?v=DB_FIX_$(date +%s)"
}

menu() {
  echo "Select an installation:"
  echo "[0] Install Panel"
  echo "[1] Install Wings"
  echo "[2] Install Both"
  echo "[3] Uninstall All (Clean)"
  
  read -p "Input 0-3: " action
  
  case $action in
    0)
      install_panel
      ;;
    1)
      install_wings
      ;;
    2)
      install_panel
      install_wings
      ;;
    3)
      uninstall
      ;;
    *)
      error "Invalid option"
      exit 1
      ;;
  esac
}

# Main
welcome
menu
