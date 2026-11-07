<header>
    <a href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/docs/index" class="lang-toggle">← Voltar ao Índice</a>
    <img src="<?= defined('BASE_URL') ? BASE_URL : '' ?>/docs/images/logo.svg" alt="Logo" class="logo-docs">
    <h1>Instalação e Primeiros Passos</h1>
    <p>Como baixar, configurar e testar o Domain-System OS localmente ou em produção.</p>
</header>

<div class="content">
    <h2>1. O Repositório Oficial no GitHub</h2>
    <p>O <strong>Domain-System OS</strong> é um projeto open-source. Você pode encontrar o código-fonte, reportar problemas e contribuir no nosso repositório oficial:</p>
    <p style="text-align: center; margin: 20px 0;">
        <a href="https://github.com/hadelrachid/domain-system" target="_blank" style="font-size: 1.2rem; font-weight: bold;">🔗 github.com/hadelrachid/domain-system</a>
    </p>

    <h2 style="margin-top: 30px;">2. Como Baixar (Download)</h2>
    <p>Você tem duas opções principais para obter o código do sistema:</p>
    
    <h3>Opção A: Via Git (Recomendado para Devs)</h3>
    <p>Abra o seu terminal e clone o repositório diretamente. Isso facilitará a atualização posterior.</p>
    <pre><code>git clone https://github.com/hadelrachid/domain-system.git</code></pre>
    
    <h3>Opção B: Download do ZIP</h3>
    <p>Acesse o repositório acima, clique no botão verde <strong>"Code"</strong> e selecione <strong>"Download ZIP"</strong>. Após o download, descompacte o arquivo na pasta desejada.</p>

    <h2 style="margin-top: 30px;">3. Testando Localmente (XAMPP / Laragon)</h2>
    <p>Para testar o sistema na sua própria máquina, recomendamos o uso de pacotes locais como o <strong>XAMPP</strong> (Windows/Mac/Linux) ou <strong>Laragon</strong> (Windows).</p>
    <ul style="line-height: 1.8; margin-left: 20px; margin-bottom: 20px;">
        <li><strong>Passo 1:</strong> Mova a pasta extraída (ou clonada) para dentro da sua pasta pública local (ex: <code>C:\xampp\htdocs\domain-system</code>).</li>
        <li><strong>Passo 2:</strong> Certifique-se de ter o <strong>PHP 8.3 ou superior</strong> ativo no seu servidor.</li>
        <li><strong>Passo 3:</strong> Inicie o serviço do Apache pelo painel de controle do XAMPP/Laragon.</li>
        <li><strong>Passo 4:</strong> Abra o navegador e acesse <code>http://localhost/domain-system/public/</code>. Você deverá ver a tela inicial do FlexTheme funcionando instantaneamente!</li>
    </ul>

    <div style="background: rgba(16, 185, 129, 0.1); border-left: 4px solid #10b981; padding: 15px; margin: 20px 0; border-radius: 4px;">
        <strong style="color: #10b981;">Dica de Ouro:</strong> O sistema possui um roteador próprio avançado que redireciona as requisições limpas através do arquivo <code>public/index.php</code> e do arquivo <code>.htaccess</code>.
    </div>

    <h2 style="margin-top: 30px;">4. Hospedando em Produção (Ex: Hostinger)</h2>
    <p>A Hostinger (e outros servidores de hospedagem modernos) possui suporte nativo ao nosso sistema. Siga os passos para publicar seu projeto online:</p>
    <ul style="line-height: 1.8; margin-left: 20px; margin-bottom: 20px;">
        <li><strong>Passo 1:</strong> Acesse o Painel de Controle da hospedagem (hPanel).</li>
        <li><strong>Passo 2:</strong> Vá em "Configurações do PHP" e certifique-se de selecionar a versão <strong>PHP 8.3+</strong> (O suporte de migração para 8.4 já está previsto na arquitetura).</li>
        <li><strong>Passo 3:</strong> Use o Gerenciador de Arquivos ou FTP para fazer o upload dos arquivos para o servidor.</li>
        <li><strong>Passo 4:</strong> <strong>Diretório Público:</strong> O diretório <code>public/</code> deve atuar como a sua raiz pública (equivalente ao <code>public_html</code>). O restante do sistema (pastas vitais como <code>src/</code>, <code>logs/</code> e o arquivo <code>.env</code>) deve ficar fora de alcance e invisível aos visitantes por segurança!</li>
        <li><strong>Passo 5:</strong> <strong>Banco de Dados (Importante!):</strong> Antes de acessar o seu site pela primeira vez, vá na seção "Bancos de Dados MySQL" da Hostinger e crie um novo banco. <strong>Anote o Nome do Banco de Dados, Usuário e a Senha!</strong> Sem esses três dados em mãos, o Wizard Automático de Instalação do Domain-System não conseguirá conectar e criar as tabelas.</li>
    </ul>
    
    <p>A equipe da Hostinger validou e aceitou a hospedagem do código-fonte PHP modular, validando os padrões de segurança nativos e a adoção futura de middlewares de Cache Headers, descartando a necessidade de plugins pesados de WordPress como o LiteSpeed Cache.</p>

    <div style="margin-top: 40px; text-align: center;">
        <a href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/docs/tutorial-dev" style="display: inline-block; padding: 12px 25px; background: rgba(59, 130, 246, 0.2); border: 1px solid #3b82f6; border-radius: 6px; font-weight: bold; color: #60a5fa; text-decoration: none; transition: 0.3s;">
            Próximo Passo: Criando o seu Primeiro Plugin →
        </a>
    </div>
</div>

<footer>
    &copy; 2026 Domain-System OS. Desenvolvido com foco absoluto em segurança, performance e código limpo.
</footer>
