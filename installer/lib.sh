#!/bin/bash

# Schnuffelll Installer Library
# Ported from Pterodactyl Installer v1.2.0

set -e

# ------------------ Variables ----------------- #

export GITHUB_BASE_URL="https://github.com/schnuffelll"
export PATH="$PATH:/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin"

# Colors
COLOR_YELLOW='\033[1;33m'
COLOR_GREEN='\033[0;32m'
COLOR_RED='\033[0;31m'
COLOR_NC='\033[0m'

# email input validation regex
email_regex="^(([A-Za-z0-9]+((\.|\-|\_|\+)?[A-Za-z0-9]?)*[A-Za-z0-9]+)|[A-Za-z0-9]+)@(([A-Za-z0-9]+)+((\.|\-|\_)?([A-Za-z0-9]+)+)*)+\.([A-Za-z]{2,})+$"
password_charset='A-Za-z0-9!"#%&()*+,-./:;<=>?@[\]^_`{|}~'

# -------------- Visual functions -------------- #

output() {
  echo -e "* $1"
}

success() {
  echo ""
  output "${COLOR_GREEN}SUCCESS${COLOR_NC}: $1"
  echo ""
}

error() {
  echo ""
  echo -e "* ${COLOR_RED}ERROR${COLOR_NC}: $1" 1>&2
  echo ""
}

warning() {
  echo ""
  output "${COLOR_YELLOW}WARNING${COLOR_NC}: $1"
  echo ""
}

print_brake() {
  for ((n = 0; n < $1; n++)); do
    echo -n "#"
  done
  echo ""
}

# ---------------- Lib functions --------------- #

valid_email() {
  [[ $1 =~ ${email_regex} ]]
}

gen_passwd() {
  local length=$1
  local password=""
  while [ ${#password} -lt "$length" ]; do
    password=$(echo "$password""$(head -c 100 /dev/urandom | LC_ALL=C tr -dc "$password_charset")" | fold -w "$length" | head -n 1)
  done
  echo "$password"
}

# -------------------- MYSQL ------------------- #

create_db_user() {
  local db_user_name="$1"
  local db_user_password="$2"
  local db_host="${3:-127.0.0.1}"

  output "Creating database user $db_user_name..."
  mariadb -u root -e "CREATE USER '$db_user_name'@'$db_host' IDENTIFIED BY '$db_user_password';"
  mariadb -u root -e "FLUSH PRIVILEGES;"
  output "Database user $db_user_name created"
}

grant_all_privileges() {
  local db_name="$1"
  local db_user_name="$2"
  local db_host="${3:-127.0.0.1}"

  output "Granting all privileges on $db_name to $db_user_name..."
  mariadb -u root -e "GRANT ALL PRIVILEGES ON $db_name.* TO '$db_user_name'@'$db_host' WITH GRANT OPTION;"
  mariadb -u root -e "FLUSH PRIVILEGES;"
  output "Privileges granted"
}

create_db() {
  local db_name="$1"
  local db_user_name="$2"
  local db_host="${3:-127.0.0.1}"

  output "Creating database $db_name..."
  mariadb -u root -e "CREATE DATABASE $db_name;"
  grant_all_privileges "$db_name" "$db_user_name" "$db_host"
  output "Database $db_name created"
}

# --------------- Package Manager -------------- #

update_repos() {
  local args=""
  [[ $1 == true ]] && args="-qq"
  case "$OS" in
  ubuntu | debian)
    apt-get -y $args update
    ;;
  *)
    # Do nothing as AlmaLinux and RockyLinux update metadata before installing packages.
    ;;
  esac
}

install_packages() {
  local args=""
  if [[ $2 == true ]]; then
    case "$OS" in
    ubuntu | debian) args="-qq" ;;
    *) args="-q" ;;
    esac
  fi

  case "$OS" in
  ubuntu | debian)
    eval apt-get -y $args install "$1"
    ;;
  rocky | almalinux)
    eval dnf -y $args install "$1"
    ;;
  esac
}

# ------------------ Firewall ------------------ #

install_firewall() {
  case "$OS" in
  ubuntu | debian)
    output "Installing UFW"
    if ! [ -x "$(command -v ufw)" ]; then
      update_repos true
      install_packages "ufw" true
    fi
    ufw --force enable
    ;;
  rocky | almalinux)
    output "Installing FirewallD"
    if ! [ -x "$(command -v firewall-cmd)" ]; then
      install_packages "firewalld" true
    fi
    systemctl --now enable firewalld >/dev/null
    ;;
  esac
}

firewall_allow_ports() {
  case "$OS" in
  ubuntu | debian)
    for port in $1; do
      ufw allow "$port"
    done
    ufw --force reload
    ;;
  rocky | almalinux)
    for port in $1; do
      firewall-cmd --zone=public --add-port="$port"/tcp --permanent
    done
    firewall-cmd --reload -q
    ;;
  esac
}

# ---------------- System checks --------------- #

# Exit with error status code if user is not root
if [[ $EUID -ne 0 ]]; then
  error "This script must be executed with root privileges."
  exit 1
fi

# Detect OS
if [ -f /etc/os-release ]; then
  . /etc/os-release
  OS=$(echo "$ID" | awk '{print tolower($0)}')
  OS_VER=$VERSION_ID
elif type lsb_release >/dev/null 2>&1; then
  OS=$(lsb_release -si | awk '{print tolower($0)}')
  OS_VER=$(lsb_release -sr)
elif [ -f /etc/lsb-release ]; then
  . /etc/lsb-release
  OS=$(echo "$DISTRIB_ID" | awk '{print tolower($0)}')
  OS_VER=$DISTRIB_RELEASE
elif [ -f /etc/debian_version ]; then
  OS="debian"
  OS_VER=$(cat /etc/debian_version)
else
  OS=$(uname -s)
  OS_VER=$(uname -r)
fi

OS=$(echo "$OS" | awk '{print tolower($0)}')
OS_VER_MAJOR=$(echo "$OS_VER" | cut -d. -f1)
CPU_ARCHITECTURE=$(uname -m)

# Strict support check
case "$OS" in
ubuntu)
  [ "$OS_VER_MAJOR" == "20" ] && SUPPORTED=true
  [ "$OS_VER_MAJOR" == "22" ] && SUPPORTED=true
  [ "$OS_VER_MAJOR" == "24" ] && SUPPORTED=true
  ;;
debian)
  [ "$OS_VER_MAJOR" == "10" ] && SUPPORTED=true
  [ "$OS_VER_MAJOR" == "11" ] && SUPPORTED=true
  [ "$OS_VER_MAJOR" == "12" ] && SUPPORTED=true
  ;;
rocky | almalinux)
  [ "$OS_VER_MAJOR" == "8" ] && SUPPORTED=true
  [ "$OS_VER_MAJOR" == "9" ] && SUPPORTED=true
  ;;
*)
  SUPPORTED=false
  ;;
esac

if [ "$SUPPORTED" == false ]; then
  error "$OS is not supported"
  exit 1
fi
