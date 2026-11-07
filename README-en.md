# Domain System Plugins

Official repository for modular plugins, application modules, and extensions for [Domain System](https://github.com/hadelrachid/domain-system).

This ecosystem was designed to keep the Domain System core engine lightweight, fast, and predictable, allowing heavy business features — such as full learning management systems (LMS) and payment gateways — to operate in a decoupled and modular fashion.

---

## 📦 Available Plugins

| Plugin / Slug | Official Name | Version | Description |
| :--- | :--- | :---: | :--- |
| **`academy`** | Academy LMS | `v2.6.0` | Complete learning platform: courses, modules, video lessons, embedded PDF reader, completion certificates, and student portal. |
| **`commerce`** | DomainCommerce | `v2.6.0` | Payment engine featuring instant Pix, credit cards, and bank slips via Mercado Pago, global PayPal checkout, and affiliate webhooks. |
| **`seo`** | SEO Engine | `v2.6.0` | Meta tags, OpenGraph, sitemaps, and search engine optimization management. |
| **`analytics`** | RachidD Analytics | `v2.6.0` | Lightweight traffic metrics and analytics without third-party tracking scripts. |
| **`radio_online`** | Radio Online | `v2.6.0` | Streaming audio player with controls for layout bars. |
| **`clock_widget`** | Clock Widget | `v2.6.0` | Customizable digital clock widget for headers and sidebars. |

---

## 🧩 Philosophy & Architecture

All plugins in this repository follow Domain System architecture principles:

1. **Ring 3 Isolation:** Plugins never touch the database directly; they interact through contracts and services exposed by `$runtime` (`OsRuntimeInterface`).
2. **Circuit Breaker Protection:** If a plugin encounters an unhandled exception in production, the **No-Break Shield** isolates the failure to ensure the rest of the site remains operational.
3. **Manifest Driven (`plugin.json`):** Every extension declares its dependencies, minimum OS version (`os_version`), and metadata.
4. **Self-Contained Migrations:** Plugins requiring database tables maintain their own `migrations/` folder, run automatically upon installation or update.

---

## 🚀 How to Install a Plugin in Domain System

### Method 1: Through Admin Panel (Recommended)
1. Go to your site's admin area: `/admin/plugins`.
2. Download the desired plugin `.zip` from the [Releases](https://github.com/hadelrachid/domain-system-plugins/releases) page.
3. Upload the ZIP file in the installation area.
4. The system audits code security, extracts files into `src/Plugins/{slug}`, and applies database migrations automatically.

### Method 2: Manual Installation
1. Clone this repository or download the source code.
2. Copy the desired plugin folder into `src/Plugins/` within your Domain System installation:
   ```bash
   cp -r plugins/academy /path/to/domain-system/src/Plugins/academy
   ```
3. Go to `/admin/plugins` and activate the plugin with 1 click.

---

## 🛠️ Developer Tools

### Packaging Plugins
To package plugins into `.zip` archives and update the `marketplace.json` catalog:

```bash
php tools/build-plugins.php
```

The archives and their SHA-256 hashes will be generated inside the `dist/` folder.

---

## 📄 License

Distributed under the **MIT** License. See [LICENSE](LICENSE) for details.
