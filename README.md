# Domain-System Plugins Hub 🚀

> Catálogo central e diretório oficial de extensões e módulos para o **[Domain-System OS (CockPit)](https://github.com/hadelrachid/domain-system)**.

Cada módulo de negócio possui seu **repositório independente e dedicado** no GitHub, permitindo versionamento semântico desacoplado, releases ágeis e ciclos de vida isolados do núcleo do sistema.

Todos os módulos são distribuídos sob a licença **MIT** e exigem o **Domain-System OS** para funcionar.

---

## 📦 Repositórios Oficiais dos Plugins

| Plugin / Módulo | Repositório Dedicado | Versão | Descrição |
| :--- | :--- | :---: | :--- |
| **DomainCommerce** | [hadelrachid/commerce](https://github.com/hadelrachid/commerce) | `v2.8.4` | Motor de comércio eletrônico, checkout multigateway (Pix, Cartão, Boleto, Mercado Pago, PayPal) e webhooks de afiliados. |
| **DomainDrop** | [hadelrachid/domain_drop](https://github.com/hadelrachid/domain_drop) | `v2.8.4` | Módulo autônomo de Dropshipping com sincronização direta via API oficial do AliExpress e precificação inteligente. |
| **Academy LMS** | [hadelrachid/academy](https://github.com/hadelrachid/academy) | `v2.8.4` | Plataforma EAD completa com gestão de cursos, módulos, player multimídia, leitor de PDF e certificação. |
| **FlexTheme & Builder** | [hadelrachid/flextheme](https://github.com/hadelrachid/flextheme) | `v2.8.4` | Motor visual com Full Site Editing (FSE), blocos modulares dinâmicos e construtor visual de páginas. |
| **SEO & Theme Engine** | [hadelrachid/seo](https://github.com/hadelrachid/seo) | `v2.8.4` | Otimização para buscadores (SEO), auditoria PageSpeed, compressão nativa de assets e meta tags OpenGraph. |
| **RachidD Analytics** | [hadelrachid/analytics](https://github.com/hadelrachid/analytics) | `v2.8.4` | Monitoramento analítico de acessos em tempo real, privado e nativo, sem depender de scripts externos. |
| **Rádio Online** | [hadelrachid/radio_online](https://github.com/hadelrachid/radio_online) | `v2.8.4` | Reprodutor e widget de streaming de rádio ao vivo integrado às barras e temas do sistema. |
| **Clock Widget** | [hadelrachid/clock_widget](https://github.com/hadelrachid/clock_widget) | `v2.8.4` | Widget de relógio digital customizável para cabeçalhos e dashboards do CockPit. |

---

## 🚀 Como Instalar Qualquer Plugin no Domain-System (CockPit)

1. Acesse o **repositório dedicado** do plugin desejado na tabela acima e vá para a aba **[Releases]**.
2. Baixe o arquivo `.zip` da versão estável mais recente (ex: `commerce.zip`, `domain_drop.zip`).
3. Abra o painel administrativo do seu site **Domain-System (CockPit)** em `/admin`.
4. No menu lateral, clique em **Módulos (Plugins)**.
5. No topo da página, clique no botão **Adicionar Novo**.
6. Selecione o arquivo `.zip` baixado e clique em **Instalar Agora**.
7. O sistema descompactará o pacote na pasta canônica do plugin (`src/Plugins/{slug}`), executará as migrações de banco de dados e registrará os ganchos com segurança.

---

## 📄 Licença

Todos os módulos são distribuídos sob a licença **MIT**. Consulte o arquivo [LICENSE](LICENSE) para detalhes.
