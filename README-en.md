# Domain-System Plugins Hub 🚀

> Central directory and official index of modules and extensions for the **[Domain-System OS (CockPit)](https://github.com/hadelrachid/domain-system)** ecosystem.

This repository serves as the **Central Hub**, collecting, documenting, and routing to the individual repositories of each plugin. Each extension maintains its own lifecycle, semantic versioning, open-source codebase for exploration, and dedicated GitHub Releases.

All modules are distributed under the **MIT** license and engineered to run seamlessly on top of **Domain-System OS**.

---

## 📊 Plugin Matrix & Development Status

| Plugin / Module | GitHub Repository | Status | Version | Core Focus |
| :--- | :--- | :---: | :---: | :--- |
| **DomainDrop** | [hadelrachid/domain_drop](https://github.com/hadelrachid/domain_drop) | ⚡ Active Development | `v2.8.6` | Autonomous dropshipping engine with official AliExpress Open Platform (ISV App) sync, catalog import, dynamic pricing, and orders. |
| **DomainCommerce** | [hadelrachid/commerce](https://github.com/hadelrachid/commerce) | ⚡ Active Development | `v2.8.6` | E-commerce and multi-gateway checkout engine (Pix, Credit Card, Mercado Pago, PayPal) with reactive webhooks. |
| **Academy LMS** | [hadelrachid/academy](https://github.com/hadelrachid/academy) | ✅ Stable / Refinement | `v2.8.6` | Full e-learning platform with course management, multimedia lessons, embedded PDF reader, and certification. |
| **FlexTheme & Builder** | [hadelrachid/flextheme](https://github.com/hadelrachid/flextheme) | ✅ Stable / Refinement | `v2.8.6` | Frontend CMS engine with Full Site Editing (FSE), 14 modular blocks, and visual Drag & Drop editor. |
| **SEO & Theme Engine** | [hadelrachid/seo](https://github.com/hadelrachid/seo) | ✅ Stable / Refinement | `v2.8.6` | Search engine optimization (SEO), PageSpeed audits, native asset compression, and OpenGraph meta tags. |
| **RachidD Analytics** | [hadelrachid/analytics](https://github.com/hadelrachid/analytics) | ✅ Stable / Refinement | `v2.8.6` | Lightweight, native real-time traffic monitoring and private dashboard metrics without third-party tracking scripts. |
| **Online Radio** | [hadelrachid/radio_online](https://github.com/hadelrachid/radio_online) | ✅ Stable / Refinement | `v2.8.6` | Live streaming audio player widget integrated into layout bars and dashboard themes. |
| **Clock Widget** | [hadelrachid/clock_widget](https://github.com/hadelrachid/clock_widget) | ✅ Stable / Refinement | `v2.8.6` | Customizable digital clock widget for CockPit header bars and admin dashboards. |

---

## 🎯 Current Engineering Focus: E-Commerce & Dropshipping

At present, core development is concentrated on the two engines powering the **Online Dropshipping Store**:

1. **[DomainDrop](https://github.com/hadelrachid/domain_drop):**
   - Automated product catalog synchronization with AliExpress.
   - Intelligent calculation of profit margins, taxes, and shipping rates.
   - Resilient, fault-tolerant order dispatching.
2. **[DomainCommerce](https://github.com/hadelrachid/commerce):**
   - Clean, transparent checkout matching the active storefront theme.
   - Instant payment settlement via Pix and credit card installment plans.
   - Automated dispatch of customer notifications and fulfillment webhooks.

---

## 🔬 Code Study & Architecture Inspection

All extensions adhere to Domain-System's **Ring 3 (User/Module Space)** architecture:

* **Kernel Isolation:** Plugins do not access database drivers directly; they interact through structured contracts (`Contracts/`) and the safe runtime (`OsRuntimeInterface`).
* **Circuit Breaker Protection:** If an unhandled exception occurs inside a plugin, the OS Circuit Breaker disarms the faulted module to preserve core system uptime.
* **Self-Contained Migrations:** Each plugin maintains and executes its own database schema versioning in `migrations/`.

To clone and inspect the source code of any module:
```bash
# Example: clone the dropshipping engine
git clone https://github.com/hadelrachid/domain_drop.git

# Example: clone the e-commerce engine
git clone https://github.com/hadelrachid/commerce.git
```

---

## 📥 How to Download Official Release Bundles

Each dedicated repository automatically packages and publishes ready-to-use `.zip` archives with every tagged release:

1. Navigate to the plugin repository of interest (links in the matrix above).
2. Click the **Releases** tab on the right sidebar (e.g., `https://github.com/hadelrachid/{slug}/releases`).
3. Download the `{slug}.zip` bundle for the latest release.

---

## 🚀 How to Import and Install in Domain-System (CockPit)

1. Open your **Domain-System** admin panel at `/admin`.
2. From the main sidebar menu, click **Modules (Plugins)**.
3. At the top of the page, click the **Add New** button.
4. In the upload section, select the downloaded `.zip` file from the official repository.
5. Click **Install Now**.
6. The system installer will verify package integrity, unpack the files into `src/Plugins/{slug}`, and register the extension hooks.
7. Click **Activate** to enable the module.

---

## 📄 License

All plugins in this ecosystem are distributed under the permissive **MIT** license. See the [LICENSE](LICENSE) file for details.
