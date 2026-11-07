# Domain System Plugins

Repositório oficial de plugins, módulos de aplicação e extensões para o [Domain System](https://github.com/hadelrachid/domain-system).

Este ecossistema foi projetado para manter o núcleo (*Core*) do Domain System leve, rápido e previsível, permitindo que funcionalidades pesadas de negócio — como plataformas completas de cursos (LMS) e motores de pagamento — operem de forma desacoplada e modular.

---

## 📦 Plugins Disponíveis

| Plugin / Slug | Nome Oficial | Versão | Descrição |
| :--- | :--- | :---: | :--- |
| **`academy`** | Academy LMS | `v2.6.0` | Plataforma EAD completa: cursos, módulos, aulas em vídeo, leitor de PDF embutido, emissão de certificados e portal do aluno. |
| **`commerce`** | DomainCommerce | `v2.6.0` | Liquidação financeira com Pix automático, Cartão e Boleto via Mercado Pago, checkout internacional PayPal e webhooks de afiliados. |
| **`flextheme`** | FlexTheme & Builder | `v2.6.0` | Motor visual do CMS: Full Site Editing (FSE), 14 blocos semânticos, editor visual Drag & Drop (Flex Builder), cabeçalhos e rodapés dinâmicos. |
| **`seo`** | SEO Engine | `v2.6.0` | Gerenciamento de meta tags, OpenGraph, sitemap e otimização para mecanismos de busca. |
| **`analytics`** | RachidD Analytics | `v2.6.0` | Coleta de métricas e visualização de tráfego sem depender de scripts invasivos externos. |
| **`radio_online`** | Rádio Online | `v2.6.0` | Player de áudio em streaming com controles para barras de layout. |
| **`clock_widget`** | Clock Widget | `v2.6.0` | Relógio digital personalizável para cabeçalhos e barras laterais. |

---

## 🧩 Filosofia e Arquitetura

Todos os plugins deste repositório seguem os padrões do **Domain System**:

1. **Isolamento em Ring 3:** Plugins nunca acessam o banco de dados diretamente; utilizam os contratos e serviços expostos pelo `$runtime` (`OsRuntimeInterface`).
2. **Proteção por Circuit Breaker:** Se um plugin falhar ou disparar uma exceção não tratada em produção, o **No-Break Shield** isola a falha, preservando o site e o checkout no ar.
3. **Manifesto `plugin.json`:** Cada extensão declara suas dependências, versão mínima do sistema operacional (`os_version`) e metadados.
4. **Migrations Próprias:** Módulos que exigem persistência possuem sua própria pasta `migrations/` gerenciada automaticamente pelo instalador.

---

## 🚀 Como Instalar um Plugin no Domain System

### Método 1: Pelo Painel Administrativo (Recomendado)
1. Acesse o painel administrativo do seu site: `/admin/plugins`.
2. Baixe o arquivo `.zip` do plugin desejado na aba de [Releases](https://github.com/hadelrachid/domain-system-plugins/releases).
3. Faça o upload do ZIP na área de instalação.
4. O sistema valida a segurança do código, descompacta os arquivos em `src/Plugins/{slug}` e executa as migrations automaticamente.

### Método 2: Instalação Manual
1. Baixe o código do plugin ou clone este repositório.
2. Copie a pasta do plugin desejado para `src/Plugins/` na raiz da sua instalação do Domain System:
   ```bash
   cp -r plugins/academy /caminho/do/domain-system/src/Plugins/academy
   ```
3. Acesse `/admin/plugins` e ative o plugin com um clique.

---

## 🛠️ Ferramentas para Desenvolvedores

### Empacotamento de Plugins
Para gerar os arquivos `.zip` para distribuição e atualizar o catálogo `marketplace.json`:

```bash
php tools/build-plugins.php
```

Os pacotes serão gerados na pasta `dist/` com seus respectivos hashes SHA-256 de integridade.

---

## 📄 Licença

Distribuído sob a licença **MIT**. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.
