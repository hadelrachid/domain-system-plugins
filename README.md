# Domain-System Plugins Hub 🚀

> Catálogo central e diretório oficial de módulos e extensões do ecossistema **[Domain-System OS (CockPit)](https://github.com/hadelrachid/domain-system)**.

Este repositório atua como o **Portal Central** que reúne, documenta e direciona para os repositórios individuais de cada plugin. Cada extensão possui seu próprio ciclo de vida, versionamento semântico, código-fonte aberto para estudo e releases dedicadas no GitHub.

Todos os módulos são distribuídos sob a licença **MIT** e foram arquitetados para funcionar perfeitamente com o **Domain-System OS**.

---

## 📊 Matriz de Plugins e Status de Desenvolvimento

| Plugin / Módulo | Repositório no GitHub | Status | Versão | Foco Principal |
| :--- | :--- | :---: | :---: | :--- |
| **DomainDrop** | [hadelrachid/domain_drop](https://github.com/hadelrachid/domain_drop) | ⚡ Em Desenvolvimento Ativo | `v2.8.6` | Motor autônomo de dropshipping integrado à API oficial do AliExpress (App ISV), importação de produtos, precificação dinâmica e pedidos. |
| **DomainCommerce** | [hadelrachid/commerce](https://github.com/hadelrachid/commerce) | ⚡ Em Desenvolvimento Ativo | `v2.8.6` | Motor de comércio eletrônico e liquidação multigateway (Pix, Cartão, Boleto, Mercado Pago, PayPal) com webhooks reativos. |
| **Academy LMS** | [hadelrachid/academy](https://github.com/hadelrachid/academy) | ✅ Estável / Aperfeiçoamento | `v2.8.6` | Plataforma EAD completa com gestão de cursos, módulos, player multimídia, leitor de PDF e certificação. |
| **FlexTheme & Builder** | [hadelrachid/flextheme](https://github.com/hadelrachid/flextheme) | ✅ Estável / Aperfeiçoamento | `v2.8.6` | Motor de CMS frontend com Full Site Editing (FSE), 14 blocos modulares dinâmicos e editor Drag & Drop. |
| **SEO & Theme Engine** | [hadelrachid/seo](https://github.com/hadelrachid/seo) | ✅ Estável / Aperfeiçoamento | `v2.8.6` | Otimização para buscadores (SEO), auditoria PageSpeed, compressão nativa de assets e meta tags OpenGraph. |
| **RachidD Analytics** | [hadelrachid/analytics](https://github.com/hadelrachid/analytics) | ✅ Estável / Aperfeiçoamento | `v2.8.6` | Monitoramento analítico de acessos em tempo real, privado e nativo, sem dependência de scripts de terceiros. |
| **Rádio Online** | [hadelrachid/radio_online](https://github.com/hadelrachid/radio_online) | ✅ Estável / Aperfeiçoamento | `v2.8.6` | Reprodutor e widget de streaming de áudio ao vivo integrado aos layouts e barras do sistema. |
| **Clock Widget** | [hadelrachid/clock_widget](https://github.com/hadelrachid/clock_widget) | ✅ Estável / Aperfeiçoamento | `v2.8.6` | Widget de relógio digital personalizável para cabeçalhos e dashboards do CockPit. |

---

## 🎯 Foco Atual do Desenvolvimento: E-Commerce & Dropshipping

No momento, o foco principal de engenharia está concentrado nos dois motores que formam a **Solução de Loja Virtual com Dropshipping**:

1. **[DomainDrop](https://github.com/hadelrachid/domain_drop):**
   - Sincronização automatizada com o catálogo global do AliExpress.
   - Cálculo automático de margens de lucro, taxas e fretes locais.
   - Processamento resiliente de pedidos com tolerância a falhas.
2. **[DomainCommerce](https://github.com/hadelrachid/commerce):**
   - Checkout limpo e transparente integrado ao tema ativo da loja.
   - Liquidação instantânea via Pix e parcelamento com cartão.
   - Despacho imediato de notificações e webhooks de entrega.

---

## 🔬 Para Estudo e Análise de Código

Todos os módulos são construídos seguindo a arquitetura **Ring 3 (Espaço de Usuário/Módulos)** do Domain-System:

* **Isolamento de Núcleo:** Plugins não tocam no banco de dados diretamente; consomem contratos definidos (`Contracts/`) e o runtime seguro (`OsRuntimeInterface`).
* **Proteção por Circuit Breaker:** Se um plugin apresentar falha não tratada, o Disjuntor do sistema desconecta o módulo para preservar o sistema no ar.
* **Migrations Integradas:** Cada plugin gerencia seu próprio esquema de tabelas através de migrações em `migrations/`.

Para clonar e estudar o código de qualquer extensão:
```bash
# Exemplo: clonar o motor de dropshipping
git clone https://github.com/hadelrachid/domain_drop.git

# Exemplo: clonar o motor de e-commerce
git clone https://github.com/hadelrachid/commerce.git
```

---

## 📥 Como Baixar os Pacotes Oficiais

Cada repositório compila e disponibiliza automaticamente o arquivo `.zip` correspondente em cada nova tag:

1. Acesse o repositório do plugin desejado (veja os links na tabela acima).
2. Clique na aba **Releases** na lateral direita do repositório (ex: `https://github.com/hadelrachid/{slug}/releases`).
3. Baixe o arquivo `{slug}.zip` da versão mais recente.

---

## 🚀 Como Importar e Instalar no Domain-System (CockPit)

1. Acesse o painel administrativo do seu site **Domain-System** em `/admin`.
2. No menu lateral, acesse **Módulos (Plugins)**.
3. No canto superior da página, clique no botão **Adicionar Novo**.
4. Na área de upload, selecione o arquivo compactado (`.zip`) baixado do repositório oficial.
5. Clique no botão **Instalar Agora**.
6. O instalador do sistema fará a validação de segurança, descompactará os arquivos na pasta correta (`src/Plugins/{slug}`) e registrará o plugin imediatamente.
7. Clique em **Ativar** para ligar o módulo.

---

## 📄 Licença

Todos os plugins deste ecossistema são distribuídos sob a licença livre e permissiva **MIT**. Consulte o arquivo [LICENSE](LICENSE) para detalhes.
