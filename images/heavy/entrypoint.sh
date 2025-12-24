#!/bin/bash

# Schnuffelll Auto-Installer Entrypoint
# Handles dynamic dependency installation for Node.js and Python

cd /home/container

# Output styling
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}>>> SCHNUFFELLL UNIVERSAL RUNNER <<<${NC}"
echo -e "${YELLOW}Checking module dependencies...${NC}"

# 1. Update/Install Dependencies based on AUTO_UPDATE env var
if [ "${AUTO_UPDATE}" == "1" ] || [ "${AUTO_UPDATE}" == "true" ]; then
    echo "Auto-Update is ENABLED."
    
    # Node.js
    if [ -f "package.json" ]; then
        echo -e "${GREEN}[Node.js] package.json detected. Installing modules...${NC}"
        npm install --production --no-audit --no-fund
    fi

    # Python
    if [ -f "requirements.txt" ]; then
        echo -e "${GREEN}[Python] requirements.txt detected. Installing requirements...${NC}"
        pip3 install -r requirements.txt --break-system-packages
    fi
else
    echo "Auto-Update is DISABLED. Skipping dependency check."
fi

# 2. Fix Permissions (Optional, usually handled by Daemon/Docker mount)
# chown -R container:container /home/container

# 3. Parse Startup Command
# The Panel sends the startup command in the 'STARTUP' env var
# We need to replace {{VAR}} placeholders if the Daemon didn't do it (ours does, but just in case)
# Actually, our Daemon+EggService does the replacing BEFORE sending to Docker cmd.
# So we usually just run the command provided as arguments to this script or simply Exec.

echo -e "${GREEN}>>> Starting Application...${NC}"
echo -e "${YELLOW}$STARTUP${NC}"

# Run the command
# We use 'eval' to handle complex commands with pipes or &&
# Ensure STARTUP is set
if [ -z "$STARTUP" ]; then
    echo "Error: STARTUP environment variable is missing!"
    exit 1
fi

eval ${STARTUP}
