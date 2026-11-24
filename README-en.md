# Domain-System Plugins Hub 🚀

> Central directory and official index of extensions and modules for the **[Domain-System OS (CockPit)](https://github.com/hadelrachid/domain-system)**.

Each business module maintains an **independent, dedicated repository** on GitHub, ensuring decoupled semantic versioning, fast releases, and isolated life cycles from the operating system core.

All plugins are distributed under the **MIT** license and require the **Domain-System OS** to function.

---

## 📦 Official Plugin Repositories

| Plugin / Module | Dedicated Repository | Version | Description |
| :--- | :--- | :---: | :--- |
| **DomainCommerce** | [hadelrachid/commerce](https://github.com/hadelrachid/commerce) | `v2.8.6` | E-commerce engine, multi-gateway checkout (Pix, Credit Card, Mercado Pago, PayPal) and affiliate webhooks. |
| **DomainDrop** | [hadelrachid/domain_drop](https://github.com/hadelrachid/domain_drop) | `v2.8.6` | Autonomous Dropshipping engine with official AliExpress API integration and dynamic pricing. |
| **Academy LMS** | [hadelrachid/academy](https://github.com/hadelrachid/academy) | `v2.8.6` | Complete e-learning platform with courses, multimedia player, PDF viewer, and student certificates. |
| **FlexTheme & Builder** | [hadelrachid/flextheme](https://github.com/hadelrachid/flextheme) | `v2.8.6` | Frontend CMS engine with Full Site Editing (FSE), modular blocks, and dynamic layout builders. |
| **SEO & Theme Engine** | [hadelrachid/seo](https://github.com/hadelrachid/seo) | `v2.8.6` | Search engine optimization (SEO), PageSpeed audits, native asset minification, and OpenGraph tags. |
| **RachidD Analytics** | [hadelrachid/analytics](https://github.com/hadelrachid/analytics) | `v2.8.6` | Lightweight, privacy-focused traffic monitoring and dashboard analytics without external dependencies. |
| **Online Radio** | [hadelrachid/radio_online](https://github.com/hadelrachid/radio_online) | `v2.8.6` | Live streaming audio player widget integrated into layout bars and dashboard themes. |
| **Clock Widget** | [hadelrachid/clock_widget](https://github.com/hadelrachid/clock_widget) | `v2.8.6` | Customizable digital clock widget for CockPit header bars and admin dashboards. |

---

## 🚀 How to Install Any Plugin in Domain-System (CockPit)

1. Open the **dedicated repository** of the desired plugin in the table above and go to the **[Releases]** section.
2. Download the latest stable `.zip` asset (e.g., `commerce.zip`, `domain_drop.zip`).
3. Log in to your **Domain-System (CockPit)** admin dashboard at `/admin`.
4. In the sidebar menu, navigate to **Modules (Plugins)**.
5. At the top of the page, click the **Add New** button.
6. Select the downloaded `.zip` file and click **Install Now**.
7. The system will safely extract the files into `src/Plugins/{slug}`, run any database migrations, and activate the module hooks.

---

## 📄 License

All plugins are distributed under the **MIT** License. See the [LICENSE](LICENSE) file for more information.
