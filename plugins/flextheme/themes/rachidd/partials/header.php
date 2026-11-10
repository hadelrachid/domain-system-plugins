<header style="padding: 15px 0; border-bottom: 1px solid rgba(69, 243, 255, 0.1); position: sticky; top: 0; z-index: 1000; background: rgba(11, 12, 16, 0.85); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);">
    <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
        <?php $baseUrl = defined('BASE_URL') ? BASE_URL : ''; ?>
        <a href="<?= $baseUrl ?>/" style="display: flex; align-items: center; gap: 12px; text-decoration: none;">
            <img src="<?= $baseUrl ?>/assets/img/site-home/logo-lampada.svg" class="logo-img" alt="RachidD Lâmpada de Aladim" style="height: 48px; width: auto; filter: drop-shadow(0 0 8px rgba(212, 175, 55, 0.4));">
            <div class="logo" style="font-size: 1.5rem; font-weight: 800; letter-spacing: -1px; color: #fff;">
                Rachid<span class="text-primary">D.com</span>
            </div>
        </a>
        <button class="mobile-menu-toggle" id="mobile-menu-btn">☰</button>
        <nav class="nav-menu" id="nav-menu">
            <?php 
                $dynamicMenu = '';
                if (isset($GLOBALS['app'])) {
                    try {
                        $ctrl = $GLOBALS['app']->getContainer()->make(\DomainSystem\SystemApps\nav_menus\Controllers\MenuAdminController::class);
                        $dynamicMenu = $ctrl->renderNavMenu('header');
                    } catch (\Exception $e) {}
                }
                
                if (!empty(trim(strip_tags($dynamicMenu)))) {
                    echo $dynamicMenu;
                } else {
                    $currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH); 
                    $baseUrl = defined('BASE_URL') ? BASE_URL : '';
            ?>
                <ul style="list-style: none; gap: 24px; display: flex; flex-direction: inherit; margin:0; padding:0; align-items: center;">
                    <li><a href="<?= $baseUrl ?>/" class="nav-link" style="<?= ($currentUri === $baseUrl . '/' || $currentUri === $baseUrl) ? 'color: var(--primary);' : '' ?>">Início</a></li>
                    <li><a href="<?= $baseUrl ?>/loja" class="nav-link" style="<?= strpos($currentUri, $baseUrl . '/loja') === 0 ? 'background: rgba(212, 175, 55, 0.25); border-color: #D4AF37;' : '' ?> color: #F0D060; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; background: rgba(212, 175, 55, 0.12); border: 1px solid rgba(212, 175, 55, 0.35); padding: 5px 14px; border-radius: 20px; text-decoration: none; transition: all 0.2s ease;">✨ Loja Oficial</a></li>
                    <li><a href="<?= $baseUrl ?>/docs/index" class="nav-link" style="<?= strpos($currentUri, $baseUrl . '/docs') === 0 ? 'color: var(--primary);' : '' ?>">OS Docs</a></li>
                    <li><a href="<?= $baseUrl ?>/tutoriais" class="nav-link" style="<?= $currentUri === $baseUrl . '/tutoriais' ? 'color: var(--primary);' : '' ?>">Cursos & Tutoriais</a></li>
                    <li><a href="<?= $baseUrl ?>/#livro" class="nav-link" style="color: var(--text-main);">Livro SOLID</a></li>
                    <li><a href="<?= $baseUrl ?>/sobre" class="nav-link" style="<?= $currentUri === $baseUrl . '/sobre' ? 'color: var(--primary);' : '' ?>">Sobre</a></li>
                </ul>
            <?php } ?>
        </nav>
    </div>
</header>

<script>
    document.getElementById('mobile-menu-btn').addEventListener('click', function() {
        document.getElementById('nav-menu').classList.toggle('active');
    });
</script>
