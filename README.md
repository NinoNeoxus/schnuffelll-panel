# Schnuffelll Panel 🦖

![Schnuffelll Panel](https://raw.githubusercontent.com/NinoNeoxus/schnuffelll-panel/master/public/logo.png)

> **"Pterodactyl, but German Engineering"**

Schnuffelll Panel is a modified version of the Pterodactyl Panel, designed for enterprise game server management with enhanced UI/UX, better installer support, and custom features.

## 🚀 Key Features

- **Dynamic Dashboard**: Real-time statistics for servers, nodes, and users.
- **Enterprise UI**: Sleek, dark-themed interface built with TailwindCSS.
- **Enhanced Installer**: One-click install script for Panel and Wings.
- **Location Management**: Organize nodes by geographic location.
- **Advanced Admin Tools**: Full control over nodes, servers, and users.

---

## 📥 Installation

You can install Schnuffelll Panel on a fresh Ubuntu 20.04/22.04/24.04, Debian 10/11/12, or Rocky Linux 8/9 server using our magic installer.

### Quick Install (Root Required)

```bash
bash <(curl -s https://raw.githubusercontent.com/NinoNeoxus/schnuffelll-panel/master/installer/install.sh)
```

**What this does:**
1. Installs all dependencies (PHP, Nginx, MariaDB, Redis, Docker).
2. Sets up the Panel website.
3. Sets up SSL/TLS certificates via Let's Encrypt.
4. (Optional) Installs Wings Daemon on the same server.

---

## 🛠 Manual Setup

If you prefer to install manually, please refer to the standard Pterodactyl documentation, but use this repository instead.

### Requirements
- PHP 8.2+
- MySQL/MariaDB
- Redis
- Web Server (Nginx/Apache)

---

## 👨‍💻 Authentication

**Default Admin Credentials** (Created by Installer):
- **Email**: `admin@schnuffelll.com`
- **Password**: `password` (Change immediately!)

---

## ❤️ Credits

- **@schnuffelll** - Lead Developer & German Engineering
- **Pterodactyl** - The base software we all love

---

## 📄 License

This software is licensed under the MIT License.
