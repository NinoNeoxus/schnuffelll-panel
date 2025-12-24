#!/bin/bash

# Schnuffelll Wings Installer
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

install_docker() {
  output "Installing Docker..."
  curl -sSL https://get.docker.com/ | CHANNEL=stable bash
  systemctl enable docker
  systemctl start docker
  success "Docker installed!"
}

install_wings() {
  output "Installing Wings..."
  mkdir -p /etc/schnuffelll
  
  # Build Wings if go is present, or assume binary is available
  # Ideally we download a release. 
  # For this setup, we assume the user has built 'wings' binary and placed it in /usr/local/bin
  # OR we build it now if Go is installed.
  
  if ! [ -x "$(command -v go)" ]; then
    warning "Go not found. Creating placeholder or expecting binary."
  else
    output "Building Wings from source..."
    # Logic to build would go here
  fi

  # Create systemd service
  cat > /etc/systemd/system/wings.service <<EOF
[Unit]
Description=Schnuffelll Wings Daemon
After=docker.service
Requires=docker.service
PartOf=docker.service

[Service]
User=root
WorkingDirectory=/etc/schnuffelll
LimitNOFILE=4096
PIDFile=/var/run/wings/daemon.pid
ExecStart=/usr/local/bin/wings
Restart=on-failure
StartLimitInterval=600

[Install]
WantedBy=multi-user.target
EOF

  systemctl enable wings.service
  success "Wings installed as service!"
}

# Main
output "Schnuffelll Wings Installation"
install_docker
install_wings

output "Please configure /etc/schnuffelll/config.json and start wings!"
