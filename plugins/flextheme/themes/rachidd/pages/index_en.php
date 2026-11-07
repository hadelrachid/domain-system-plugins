
    <header>
        <a href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/docs/index" class="lang-toggle">🇧🇷 Português</a>
        <h1>📚 Domain-System Documentation</h1>
        <p>The Modern, Modular, and Event-Driven PHP Kernel.</p>
    </header>

    <div class="content">
        <h2>Official Table of Contents</h2>
        <p>Welcome to the official <strong>Domain-System</strong> documentation. Choose a topic below to explore the architecture and learn how to use and extend our engine.</p>

        <ul>
            <li><strong><a href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/docs/proposito_en">Vision, Purpose and Target Audience</a></strong><br>
            Understand why we built this system, the pain it solves, and who it is for.</li>
            <br>
            <li><strong><a href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/docs/arquitetura_en">How the Engine Works (Architecture)</a></strong><br>
            The macro vision of the system: Dependency Injection Container, Routing, MVC and SSR.</li>
            <br>
            <li><strong><a href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/docs/contratos_en">Contracts and Interfaces (SOLID)</a></strong><br>
            How strict rules keep the core shielded. (PluginInterface, ExtractorInterface, etc).</li>
            <br>
            <li><strong><a href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/docs/circuit-breaker_en">Resilience & Circuit Breaker ⚡</a></strong><br>
            How the engine intercepts fatal errors and disarms faulty plugins to protect the core.</li>
        </ul>

        <h2>Domain-System OS (New Standard 2.0)</h2>
        <p>The next-generation architecture based on the Principle of Least Privilege and the Verifiable Trust Cycle.</p>
        
        <ul>
            <li><strong><a href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/docs/tutorial-dev">Developer Guide: Building Plugins 🧩</a></strong><br>
                Interactive tutorial on how to build business logic for the system using the new <strong>OsExtensionInterface</strong> and Dependency Injection. <i>(Bilingual)</i></li>
            <br>
            <li><strong><a href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/docs/tutorial-theme">Developer Guide: Building Themes 🎨</a></strong><br>
                Interactive tutorial on how to build high-performance layouts with MVC, Output Buffering, and Automatic SEO. <i>(Bilingual)</i></li>
            <br>
            <li><strong><a href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/docs/tutoriais/02-a-escotilha-de-emergencia">Priority System & Firewalls 🛡️</a></strong><br>
                Understand Hook priorities and the "Emergency Hatch" pattern. <i>(Bilingual)</i></li>
            <br>
            <li><strong><a href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/docs/tutoriais/03-pacotes-de-plugins">Plugin Packages (Hubs) 📦</a></strong><br>
                Learn how to cleanly structure a package that bundles multiple independent modules. <i>(Bilingual)</i></li>
            <br>
            <li><strong><a href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/docs/lifecycle">The Plugin Lifecycle (Visual Analogy) 🛂</a></strong><br>
                Understand the Kernel's abstraction and the boot process using the didactic "Immigration Analogy". <i>(Bilingual)</i></li>
            <br>
            <li><strong><a href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/docs/01-o-contrato-do-sistema">Manifesto: The System Contract</a></strong><br>
                Read why we adopted Dependency Injection instead of hunting for global variables. <i>(Bilingual)</i></li>
            <br>
            <li><strong><a href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/docs/architecture/01-ciclo-de-confianca-verificavel">The Verifiable Trust Cycle (AI)</a></strong><br>
                The architectural foundation that allows future AIs to create and audit plugins with 100% security. <i>(Bilingual)</i></li>
        </ul>
    </div>

    <footer>
        &copy; 2026 Developed with absolute focus on quality and SOLID principles.
    </footer>
