#!/bin/bash
set -e

######################################################################################
#                                                                                    #
#                 Schnuffelll Panel Installer - Library Functions                     #
#                                                                                    #
#     Based on pterodactyl-installer by Vilhelm Prytz <vilhelm@prytznet.se>          #
#     Modified for Schnuffelll by @schnuffelll                                        #
#                                                                                    #
######################################################################################

# ----------------------- Versioning ----------------------- #
export SCRIPT_VERSION="4.0.0"
export GITHUB_SOURCE=${GITHUB_SOURCE:-master}
export GITHUB_URL="https://raw.githubusercontent.com/NinoNeoxus/schnuffelll-panel/$GITHUB_SOURCE"

# ----------------------- Paths ----------------------- #
export PATH="$PATH:/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin"

# ----------------------- OS Detection ----------------------- #
export OS=""
export OS_VER=""
export OS_VER_MAJOR=""
export CPU_ARCHITECTURE=""
export SUPPORTED=false

# ----------------------- Colors ----------------------- #
COLOR_RED='\033[0;31m'
COLOR_GREEN='\033[0;32m'
COLOR_YELLOW='\033[1;33m'
COLOR_BLUE='\033[0;34m'
COLOR_CYAN='\033[0;36m'
COLOR_NC='\033[0m' # No Color
COLOR_BOLD='\033[1m'

# ----------------------- Email Validation ----------------------- #
email_regex="^(([A-Za-z0-9]+((\.|-|_|\+)?[A-Za-z0-9]?)*[A-Za-z0-9]+)|[A-Za-z0-9]+)@(([A-Za-z0-9]+)+((\.|-|_)?([A-Za-z0-9]+)+)*)+\.([A-Za-z]{2,})+$"

# ----------------------- Password Charset ----------------------- #
password_charset='A-Za-z0-9!#%&()*+,-./:;<=>?@[]^_{|}~'

# -------------------- Lib Functions -------------------- #
lib_loaded() {
    return 0
}

# ---------------- Visual Functions ---------------- #
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

info() {
    output "${COLOR_CYAN}INFO${COLOR_NC}: $1"
}

print_brake() {
    for ((n = 0; n < $1; n++)); do
        echo -n "#"
    done
    echo ""
}

print_banner() {
    echo -e "${COLOR_CYAN}"
    echo "   ___      _                  __  __     _ _ "
    echo "  / __| ___| |_  _ _  _  _ ___/ _|/ _|___| | |"
    echo "  \__ \/ __| ' \| ' \| || |_  _|  _/ -_) | |"
    echo "  |___/\___|_||_|_||_|\_,_||_| |_| \___|_|_|"
    echo ""
    echo -e "${COLOR_NC}"
    echo -e "${COLOR_BOLD}Schnuffelll Panel Installer${COLOR_NC} v${SCRIPT_VERSION}"
    echo ""
}

# ---------------- User Input Functions ---------------- #
required_input() {
    local __resultvar=$1
    local prompt=$2
    local error_msg=$3
    local default=$4
    local result=''

    while [ -z "$result" ]; do
        echo -n "* ${prompt}"
        read -r result

        if [ -z "$result" ]; then
            if [ -n "$default" ]; then
                result="$default"
            else
                [ -n "$error_msg" ] && error "$error_msg"
            fi
        fi
    done

    eval "$__resultvar=\"$result\""
}

email_input() {
    local __resultvar=$1
    local prompt=$2
    local error_msg=$3
    local result=''

    while ! [[ "$result" =~ $email_regex ]]; do
        echo -n "* ${prompt}"
        read -r result

        if ! [[ "$result" =~ $email_regex ]]; then
            error "${error_msg:-Invalid email address}"
        fi
    done

    eval "$__resultvar=\"$result\""
}

password_input() {
    local __resultvar=$1
    local prompt=$2
    local error_msg=$3
    local default=$4
    local result=''

    while [ -z "$result" ]; do
        echo -n "* ${prompt}"

        # Read password with hidden input
        while IFS= read -r -s -n1 char; do
            [[ -z $char ]] && {
                printf '\n'
                break
            }
            if [[ $char == $'\x7f' ]]; then
                if [ -n "$result" ]; then
                    result=${result%?}
                    printf '\b \b'
                fi
            else
                result+=$char
                printf '*'
            fi
        done

        if [ -z "$result" ] && [ -n "$default" ]; then
            result="$default"
        fi
        [ -z "$result" ] && [ -n "$error_msg" ] && error "$error_msg"
    done

    eval "$__resultvar=\"$result\""
}

confirm() {
    local prompt=$1
    local default=${2:-N}
    local result

    if [ "$default" = "y" ] || [ "$default" = "Y" ]; then
        prompt="${prompt} (Y/n): "
    else
        prompt="${prompt} (y/N): "
    fi

    echo -n "* ${prompt}"
    read -r result

    if [ -z "$result" ]; then
        result=$default
    fi

    [[ "$result" =~ ^[Yy]$ ]]
}

# -------------------- Utility Functions -------------------- #
valid_email() {
    [[ $1 =~ $email_regex ]]
}

gen_passwd() {
    local length=${1:-32}
    tr -dc "$password_charset" </dev/urandom | head -c "$length"
}

# -------------------- MySQL Functions -------------------- #
get_mysql_cmd() {
    if command -v mariadb &>/dev/null; then
        echo "mariadb"
    else
        echo "mysql"
    fi
}

create_db_user() {
    local db_user_name="$1"
    local db_user_password="$2"
    local db_host="${3:-127.0.0.1}"
    local mysql_cmd=$(get_mysql_cmd)

    output "Creating database user $db_user_name..."

    $mysql_cmd -u root -e "DROP USER IF EXISTS '$db_user_name'@'$db_host';"
    $mysql_cmd -u root -e "CREATE USER '$db_user_name'@'$db_host' IDENTIFIED BY '$db_user_password';"

    # Also create for localhost and wildcard
    if [ "$db_host" != "localhost" ]; then
        $mysql_cmd -u root -e "DROP USER IF EXISTS '$db_user_name'@'localhost';"
        $mysql_cmd -u root -e "CREATE USER '$db_user_name'@'localhost' IDENTIFIED BY '$db_user_password';"
    fi

    $mysql_cmd -u root -e "FLUSH PRIVILEGES;"

    output "Database user $db_user_name created"
}

grant_all_privileges() {
    local db_name="$1"
    local db_user_name="$2"
    local db_host="${3:-127.0.0.1}"
    local mysql_cmd=$(get_mysql_cmd)

    output "Granting privileges on $db_name to $db_user_name..."

    $mysql_cmd -u root -e "GRANT ALL PRIVILEGES ON $db_name.* TO '$db_user_name'@'$db_host' WITH GRANT OPTION;"
    
    if [ "$db_host" != "localhost" ]; then
        $mysql_cmd -u root -e "GRANT ALL PRIVILEGES ON $db_name.* TO '$db_user_name'@'localhost' WITH GRANT OPTION;"
    fi

    $mysql_cmd -u root -e "FLUSH PRIVILEGES;"

    output "Privileges granted"
}

create_db() {
    local db_name="$1"
    local db_user_name="$2"
    local db_host="${3:-127.0.0.1}"
    local mysql_cmd=$(get_mysql_cmd)

    output "Creating database $db_name..."

    $mysql_cmd -u root -e "DROP DATABASE IF EXISTS $db_name;"
    $mysql_cmd -u root -e "CREATE DATABASE $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    grant_all_privileges "$db_name" "$db_user_name" "$db_host"

    output "Database $db_name created"
}

# -------------------- Package Manager -------------------- #
update_repos() {
    local quiet=${1:-false}
    local args=""

    [ "$quiet" = true ] && args="-qq"

    case "$OS" in
    ubuntu | debian)
        output "Updating package repositories..."
        apt-get update -y $args || {
            error "Failed to update repositories"
            return 1
        }
        ;;
    centos | almalinux | rockylinux)
        output "Skipping repository update (handled automatically on $OS)"
        ;;
    esac
}

install_packages() {
    local packages="$1"
    local quiet=${2:-false}
    local args=""

    if [ "$quiet" = true ]; then
        case "$OS" in
        ubuntu | debian) args="-qq" ;;
        *) args="-q" ;;
        esac
    fi

    case "$OS" in
    ubuntu | debian)
        eval apt-get -y $args install $packages
        ;;
    rocky | almalinux)
        eval dnf -y $args install $packages
        ;;
    esac
}

# -------------------- Firewall Functions -------------------- #
ask_firewall() {
    local __resultvar=$1

    case "$OS" in
    ubuntu | debian)
        if confirm "Do you want to automatically configure UFW (firewall)?"; then
            eval "$__resultvar=true"
        else
            eval "$__resultvar=false"
        fi
        ;;
    rocky | almalinux)
        if confirm "Do you want to automatically configure firewall-cmd?"; then
            eval "$__resultvar=true"
        else
            eval "$__resultvar=false"
        fi
        ;;
    esac
}

install_firewall() {
    case "$OS" in
    ubuntu | debian)
        output "Installing and enabling UFW..."

        if ! command -v ufw &>/dev/null; then
            update_repos true
            install_packages "ufw" true
        fi

        ufw --force enable
        success "Enabled UFW firewall"
        ;;
    rocky | almalinux)
        output "Installing and enabling FirewallD..."

        if ! command -v firewall-cmd &>/dev/null; then
            install_packages "firewalld" true
        fi

        systemctl --now enable firewalld >/dev/null
        success "Enabled FirewallD"
        ;;
    esac
}

firewall_allow_ports() {
    local ports="$1"

    case "$OS" in
    ubuntu | debian)
        for port in $ports; do
            ufw allow "$port"
        done
        ufw --force reload
        ;;
    rocky | almalinux)
        for port in $ports; do
            firewall-cmd --zone=public --add-port="$port"/tcp --permanent
        done
        firewall-cmd --reload -q
        ;;
    esac

    output "Firewall ports opened: $ports"
}

# -------------------- System Checks -------------------- #
detect_os() {
    if [ -f /etc/os-release ]; then
        . /etc/os-release
        OS=$(echo "$ID" | tr '[:upper:]' '[:lower:]')
        OS_VER=$VERSION_ID
        OS_VER_MAJOR=$(echo "$VERSION_ID" | cut -d. -f1)
    elif command -v lsb_release &>/dev/null; then
        OS=$(lsb_release -si | tr '[:upper:]' '[:lower:]')
        OS_VER=$(lsb_release -sr)
        OS_VER_MAJOR=$(echo "$OS_VER" | cut -d. -f1)
    else
        error "Unable to detect OS"
        exit 1
    fi

    # Detect architecture
    CPU_ARCHITECTURE=$(uname -m)
    case "$CPU_ARCHITECTURE" in
    x86_64) export ARCH="amd64" ;;
    aarch64 | arm64) export ARCH="arm64" ;;
    *) export ARCH="$CPU_ARCHITECTURE" ;;
    esac

    # Check if supported
    case "$OS" in
    ubuntu)
        [ "$OS_VER_MAJOR" -ge 20 ] && SUPPORTED=true
        ;;
    debian)
        [ "$OS_VER_MAJOR" -ge 10 ] && SUPPORTED=true
        ;;
    rocky | almalinux)
        [ "$OS_VER_MAJOR" -ge 8 ] && SUPPORTED=true
        ;;
    esac

    export OS OS_VER OS_VER_MAJOR CPU_ARCHITECTURE SUPPORTED
}

check_root() {
    if [[ $EUID -ne 0 ]]; then
        error "This script must be run as root"
        exit 1
    fi
}

check_os_supported() {
    if [ "$SUPPORTED" != true ]; then
        error "Unsupported OS: $OS $OS_VER"
        error "Supported: Ubuntu 20.04+, Debian 10+, Rocky/Alma 8+"
        exit 1
    fi
    
    output "Detected OS: $OS $OS_VER ($ARCH)"
}

# -------------------- Initialize -------------------- #
check_root
detect_os
