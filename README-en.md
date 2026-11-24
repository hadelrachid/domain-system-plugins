# Domain-System Plugins Hub 🚀

> Central directory, official catalog, and curated distribution channel for **[Domain-System OS (CockPit)](https://github.com/hadelrachid/domain-system)** extensions.

This repository serves as the **Central Hub and Official Stability Channel** of the ecosystem:

1. **Agile & Independent Development:** Every plugin maintains its own dedicated GitHub repository for rapid iteration, testing, and granular lifecycle control.
2. **Curated Official Channel:** Releases published on this aggregator repository represent **stable, validated, and certified** packages tested alongside corresponding Core OS releases.

All plugins are distributed under the **MIT** license and require **Domain-System OS** to run.

---

## 📊 Plugin Matrix & Development Status

| Plugin / Module | Dedicated Repository | Current Status | Stable Version | Core Focus |
| :--- | :--- | :---: | :---: | :--- |
| **DomainDrop** | [hadelrachid/domain_drop](https://github.com/hadelrachid/domain_drop) | ⚡ Active Development | `v2.8.6` | Autonomous dropshipping engine with official AliExpress API (ISV App) sync, dynamic pricing, and orders. |
| **DomainCommerce** | [hadelrachid/commerce](https://github.com/hadelrachid/commerce) | ⚡ Active Development | `v2.8.6` | E-commerce and multi-gateway checkout engine (Pix, Credit Card, PayPal) with reactive webhooks. |
| **Academy LMS** | [hadelrachid/academy](https://github.com/hadelrachid/academy) | ✅ Stable & Certified | `v2.8.6` | Full e-learning platform with courses, modules, multimedia player, PDF viewer, and student certificates. |
| **FlexTheme & Builder** | [hadelrachid/flextheme](https://github.com/hadelrachid/flextheme) | ✅ Stable & Certified | `v2.8.6` | Frontend CMS engine with Full Site Editing (FSE), 14 modular blocks, and visual Drag & Drop editor. |
| **SEO & Theme Engine** | [hadelrachid/seo](https://github.com/hadelrachid/seo) | ✅ Stable & Certified | `v2.8.6` | Search engine optimization (SEO), PageSpeed audits, native asset compression, and OpenGraph meta tags. |
| **RachidD Analytics** | [hadelrachid/analytics](https://github.com/hadelrachid/analytics) | ✅ Stable & Certified | `v2.8.6` | Privacy-first real-time traffic monitoring and native dashboard metrics without third-party tracking scripts. |
| **Online Radio** | [hadelrachid/radio_online](https://github.com/hadelrachid/radio_online) | ✅ Stable & Certified | `v2.8.6` | Live streaming audio player widget integrated into layout bars and dashboard themes. |
| **Clock Widget** | [hadelrachid/clock_widget](https://github.com/hadelrachid/clock_widget) | ✅ Stable & Certified | `v2.8.6` | Customizable digital clock widget for CockPit header bars and admin dashboards. |

---

## 🎯 Current Engineering Focus: E-Commerce & Dropshipping

Active engineering is concentrated on the two core engines powering the **Online Dropshipping Store**:

* **[DomainDrop](https://github.com/hadelrachid/domain_drop):** Automated AliExpress product catalog synchronization, dynamic profit margins, and resilient order fulfillment.
* **[DomainCommerce](https://github.com/hadelrachid/commerce):** Streamlined storefront checkout, instant Pix/card payment settlement, and automated dispatch webhooks.

---

## 🏛️ Consolidated & Stable Releases

Whenever a Core OS milestone is achieved, this repository compiles and attaches to the **[Releases](https://github.com/hadelrachid/domain-system-plugins/releases)** tab a curated bundle of `.zip` packages that have passed full integration test suites, guaranteeing stability for production environments.

---

## 🔬 Code Exploration & Architecture (Ring 3)

All modules adhere to Domain-System's **Ring 3 (User/Module Space)** architecture:

* **Kernel Decoupling:** Plugins communicate exclusively through interfaces (`Contracts/`) and the secure runtime (`OsRuntimeInterface`).
* **Circuit Breaker Protection:** If a plugin encounters an unhandled exception, the OS Circuit Breaker disarms the faulted component to protect host availability.
* **Self-Contained Migrations:** Schema changes are automated and kept within each plugin's `migrations/` directory.

To clone and study the code:
```bash
# Example: clone the dropshipping engine
git clone https://github.com/hadelrachid/domain_drop.git

# Example: clone the e-commerce engine
git clone https://github.com/hadelrachid/commerce.git
```

---

## 🚀 How to Import and Install in Domain-System (CockPit)

1. Download the stable `.zip` bundle from the **Releases** section of this repository or from the individual plugin repository.
2. Log in to your **Domain-System** administration panel at `/admin`.
3. In the sidebar menu, select **Modules (Plugins)**.
4. Click the **Add New** button at the top of the page.
5. Select the downloaded `.zip` file and click **Install Now**.
6. The system unpacks the files into `src/Plugins/{slug}`, executes migrations, and registers extension hooks.
7. Click **Activate** to finalize.

---

## 📄 License

All plugins are released under the **MIT** license. See the [LICENSE](LICENSE) file for more details.
