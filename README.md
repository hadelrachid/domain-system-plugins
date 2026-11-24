# Domain-System Plugins Hub 🚀

> Catálogo central, diretório oficial e canal de curadoria de extensões para o **[Domain-System OS (CockPit)](https://github.com/hadelrachid/domain-system)**.

Este repositório atua como o **Portal Central e Canal Oficial de Estabilidade** do ecossistema:

1. **Desenvolvimento Ágil e Independente:** Cada plugin possui seu próprio repositório dedicado no GitHub para evolução rápida, testes e controle de ciclo de vida granular.
2. **Canal Oficial de Curadoria:** As releases publicadas neste repositório agregador representam os pacotes **estáveis, homologados e certificados** para produção junto às versões correspondentes do Core OS.

Todos os módulos são distribuídos sob a licença **MIT** e exigem o **Domain-System OS** para funcionar.

---

## 📊 Matriz de Plugins e Status de Desenvolvimento

| Plugin / Módulo | Repositório Dedicado | Status Atual | Versão Estável | Foco Principal |
| :--- | :--- | :---: | :---: | :--- |
| **DomainDrop** | [hadelrachid/domain_drop](https://github.com/hadelrachid/domain_drop) | ⚡ Em Desenvolvimento Ativo | `v2.8.6` | Motor de dropshipping autônomo com API oficial do AliExpress (App ISV), precificação dinâmica e pedidos. |
| **DomainCommerce** | [hadelrachid/commerce](https://github.com/hadelrachid/commerce) | ⚡ Em Desenvolvimento Ativo | `v2.8.6` | Motor de comércio eletrônico, checkout desacoplado e liquidação financeira multigateway (Pix, Cartão, PayPal). |
| **Academy LMS** | [hadelrachid/academy](https://github.com/hadelrachid/academy) | ✅ Estável e Homologado | `v2.8.6` | Plataforma EAD completa com gestão de cursos, módulos, player multimídia, leitor de PDF e certificação. |
| **FlexTheme & Builder** | [hadelrachid/flextheme](https://github.com/hadelrachid/flextheme) | ✅ Estável e Homologado | `v2.8.6` | Motor visual com Full Site Editing (FSE), 14 blocos modulares dinâmicos e editor visual Drag & Drop. |
| **SEO & Theme Engine** | [hadelrachid/seo](https://github.com/hadelrachid/seo) | ✅ Estável e Homologado | `v2.8.6` | Otimização para buscadores (SEO), auditoria PageSpeed, compressão nativa de assets e meta tags OpenGraph. |
| **RachidD Analytics** | [hadelrachid/analytics](https://github.com/hadelrachid/analytics) | ✅ Estável e Homologado | `v2.8.6` | Monitoramento analítico de acessos em tempo real, privado e nativo, sem dependência de scripts externos. |
| **Rádio Online** | [hadelrachid/radio_online](https://github.com/hadelrachid/radio_online) | ✅ Estável e Homologado | `v2.8.6` | Reprodutor e widget de streaming de áudio ao vivo integrado aos layouts e barras do sistema. |
| **Clock Widget** | [hadelrachid/clock_widget](https://github.com/hadelrachid/clock_widget) | ✅ Estável e Homologado | `v2.8.6` | Widget de relógio digital customizável para cabeçalhos e dashboards do CockPit. |

---

## 🎯 Foco Atual da Engenharia: Loja Virtual com Dropshipping

No momento, o ciclo mais ativo de desenvolvimento está concentrado nos dois motores que formam a **Solução de E-Commerce Dropshipping**:

* **[DomainDrop](https://github.com/hadelrachid/domain_drop):** Mineração de produtos virais, cálculo automatizado de margens de lucro/fretes e conexão direta com a API do AliExpress.
* **[DomainCommerce](https://github.com/hadelrachid/commerce):** Checkout limpo e transparente, liquidação automática via Pix e cartão, e envio de webhooks de entrega.

---

## 🏛️ Releases Consolidadas e Estáveis

Quando uma versão do sistema operacional é lançada, este repositório compila e anexa na aba **[Releases](https://github.com/hadelrachid/domain-system-plugins/releases)** o conjunto de pacotes `.zip` que foram exaustivamente validados pela suíte de testes de integração, garantindo risco zero para ambientes em produção.

---

## 🔬 Para Estudo e Análise de Código (Arquitetura Ring 3)

Todos os módulos operam sob a arquitetura **Ring 3 (Espaço de Usuário/Módulos)** do Domain-System:

* **Isolamento Total:** Plugins comunicam-se via contratos estritos (`Contracts/`) e o runtime seguro (`OsRuntimeInterface`), sem acesso direto e inseguro a drivers de banco.
* **Resiliência por Disjuntor:** Em caso de exceção crítica não tratada, o Circuit Breaker isola a falha, mantendo o site e o checkout no ar.
* **Migrações Autônomas:** Cada plugin executa e mantém seu próprio esquema de tabelas em `migrations/`.

Para clonar e estudar qualquer extensão:
```bash
# Exemplo: clonar o motor de dropshipping
git clone https://github.com/hadelrachid/domain_drop.git

# Exemplo: clonar o motor de e-commerce
git clone https://github.com/hadelrachid/commerce.git
```

---

## 🚀 Como Importar e Instalar no Domain-System (CockPit)

1. Baixe o pacote compactado (`.zip`) da versão estável na aba de **Releases** deste repositório ou no repositório individual do plugin.
2. Acesse o painel administrativo do seu site **Domain-System (CockPit)** em `/admin`.
3. No menu lateral, acesse **Módulos (Plugins)**.
4. No canto superior da página, clique no botão **Adicionar Novo**.
5. Selecione o arquivo `.zip` e clique em **Instalar Agora**.
6. O sistema descompactará o pacote na pasta canônica (`src/Plugins/{slug}`), executará as migrações e ligará o módulo.
7. Clique em **Ativar** para finalizar.

---

## 📄 Licença

Todos os módulos são distribuídos sob a licença livre **MIT**. Consulte o arquivo [LICENSE](LICENSE) para detalhes.
