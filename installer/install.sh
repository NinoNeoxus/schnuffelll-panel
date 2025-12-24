#!/bin/bash

# Schnuffelll Magic Installer (v2.0 - Pterodactyl Style)

set -e

# Load Lib
source $(dirname "$0")/lib/lib.sh

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
  output "Schnuffelll Installer - Inspired by Pterodactyl"
  output "OS: $OS $OS_VER"
  echo ""
}

menu() {
  echo "Select an installation:"
  echo "[0] Install Panel"
  echo "[1] Install Wings"
  echo "[2] Install Both"
  
  read -p "Input 0-2: " action
  
  case $action in
    0)
      bash $(dirname "$0")/installers/panel.sh
      ;;
    1)
      bash $(dirname "$0")/installers/wings.sh
      ;;
    2)
      bash $(dirname "$0")/installers/panel.sh
      bash $(dirname "$0")/installers/wings.sh
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
