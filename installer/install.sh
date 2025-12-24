#!/bin/bash

# Schnuffelll Magic Installer (v2.1 - Remote Fixed)

set -e

GITHUB_BASE_URL="https://raw.githubusercontent.com/NinoNeoxus/schnuffelll-panel/master"

# Download Lib
if [ ! -f /tmp/schnuffelll_lib.sh ]; then
    curl -sSL -o /tmp/schnuffelll_lib.sh "$GITHUB_BASE_URL/installer/lib.sh"
fi
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

menu() {
  echo "Select an installation:"
  echo "[0] Install Panel"
  echo "[1] Install Wings"
  echo "[2] Install Both"
  
  read -p "Input 0-2: " action
  
  case $action in
    0)
      run_remote_script "installer/installers/panel.sh"
      ;;
    1)
      run_remote_script "installer/installers/wings.sh"
      ;;
    2)
      run_remote_script "installer/installers/panel.sh"
      run_remote_script "installer/installers/wings.sh"
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
