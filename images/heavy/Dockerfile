# Schnuffelll Runner: Heavy
# Supports: Node.js, Python, FFmpeg, Chromium (Puppeteer/Baileys)
FROM node:20-bookworm

LABEL author="Schnuffelll Team" maintainer="admin@schnuffelll.com"

# 1. Install Dependencies
# We use DEBIAN_FRONTEND=noninteractive to avoid prompts
RUN apt-get update && apt-get install -y --no-install-recommends \
    python3 \
    python3-pip \
    python3-venv \
    ffmpeg \
    git \
    curl \
    wget \
    unzip \
    iproute2 \
    ca-certificates \
    fonts-liberation \
    libappindicator3-1 \
    libasound2 \
    libatk-bridge2.0-0 \
    libatk1.0-0 \
    libc6 \
    libcairo2 \
    libcups2 \
    libdbus-1-3 \
    libexpat1 \
    libfontconfig1 \
    libgbm1 \
    libgcc1 \
    libglib2.0-0 \
    libgtk-3-0 \
    libnspr4 \
    libnss3 \
    libpango-1.0-0 \
    libpangocairo-1.0-0 \
    libstdc++6 \
    libx11-6 \
    libx11-xcb1 \
    libxcb1 \
    libxcomposite1 \
    libxcursor1 \
    libxdamage1 \
    libxext6 \
    libxfixes3 \
    libxi6 \
    libxrandr2 \
    libxrender1 \
    libxss1 \
    libxtst6 \
    lsb-release \
    xdg-utils \
    && rm -rf /var/lib/apt/lists/*

# 2. Setup Container User (Pterodactyl Standard)
# Usually container runs as 'container' user (UID 998 or similar), but for general use we can create a generic user.
# Pterodactyl mounts the volume and sets the user via Docker run command (--user). 
# So we just need to ensure tools are available in path.

# 3. Install Global Tools
RUN npm install -g pm2 yarn typescript ts-node

# 4. Copy Entrypoint
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# 5. Environment Defaults
ENV USER=container HOME=/home/container
WORKDIR /home/container

# 6. Define Entrypoint
CMD ["/bin/bash", "/entrypoint.sh"]
