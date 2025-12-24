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

## 📥 Quick Installation

### One-Line Install (Root Required)

```bash
bash <(curl -s https://raw.githubusercontent.com/NinoNeoxus/schnuffelll-panel/master/installer/install.sh)
```

### Supported Operating Systems

| OS | Versions |
|----|----------|
| Ubuntu | 20.04, 22.04, 24.04 |
| Debian | 10, 11, 12 |
| Rocky Linux | 8, 9 |
| AlmaLinux | 8, 9 |

### Installation Options

| Option | Description |
|--------|-------------|
| `[0] Install Panel (with SSL)` | Full panel installation with Let's Encrypt SSL |
| `[1] Install Panel (no SSL)` | Panel installation without SSL (for local/testing) |
| `[2] Install Wings` | Wings daemon only |
| `[3] Install Both` | Panel + Wings on same server |
| `[4] Uninstall` | Remove all Schnuffelll components |

---

## 🛠 Manual Setup

If you prefer manual installation, refer to the standard Pterodactyl documentation but use this repository.

### Requirements
- PHP 8.2+
- MySQL/MariaDB
- Redis
- Nginx
- Docker (for Wings)

---

## 🔧 Post-Installation

### Panel
1. Access your panel at `https://your-domain.com`
2. Login with default credentials
3. **Change your password immediately!**

### Wings
1. Go to Admin → Nodes → Create Node
2. Copy the configuration from the Configuration tab
3. Paste into `/etc/pterodactyl/config.yml`
4. Start Wings: `systemctl start wings`

---

## 👨‍💻 Default Credentials

| Field | Value |
|-------|-------|
| Email | `admin@schnuffelll.com` |
| Password | `password` |

> ⚠️ **CHANGE YOUR PASSWORD IMMEDIATELY AFTER FIRST LOGIN!**

---

## 🔄 Updating

### Panel
```bash
cd /var/www/schnuffelll/panel
git pull origin master
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan view:clear
php artisan config:clear
systemctl restart schnuffelll
```

### Wings
```bash
systemctl stop wings
curl -L -o /usr/local/bin/wings https://github.com/pterodactyl/wings/releases/latest/download/wings_linux_amd64
chmod +x /usr/local/bin/wings
systemctl start wings
```

---

## ❤️ Credits

- **@schnuffelll** - Lead Developer & German Engineering
- **Pterodactyl** - The base software we all love

---

## 📄 License

This software is licensed under the MIT License.
