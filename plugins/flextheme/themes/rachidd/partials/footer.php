<footer style="padding: 60px 0 40px; border-top: 1px solid rgba(69, 243, 255, 0.1); margin-top: 80px;">
    <div class="container" style="text-align: center; color: var(--text-muted);">
        <?php $baseUrl = defined('BASE_URL') ? BASE_URL : ''; ?>
        <div style="display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 20px;">
            <img src="<?= $baseUrl ?>/assets/img/site-home/logo-rd.svg" class="logo-img" alt="RD Logo" style="height: 55px; width: auto; opacity: 0.8;">
            <div class="logo" style="font-size: 1.5rem; font-weight: 800; color: #fff;">
                Rachid<span class="text-primary">D.com</span>
            </div>
        </div>
        <p style="margin-bottom: 30px;">Construído com extrema otimização sobre o <span class="text-primary">Domain-System OS 2.0</span></p>
                <?php 
            $dynamicFooterMenu = '';
            if (isset($GLOBALS['app'])) {
                try {
                    $ctrl = $GLOBALS['app']->getContainer()->make(\DomainSystem\SystemApps\nav_menus\Controllers\MenuAdminController::class);
                    $dynamicFooterMenu = $ctrl->renderNavMenu('footer');
                } catch (\Exception $e) {}
            }
            if (!empty(trim(strip_tags($dynamicFooterMenu)))): 
                echo '<div style="margin-bottom: 20px; font-size: 0.9rem; justify-content: center; display: flex;">' . $dynamicFooterMenu . '</div>';
            else:
        ?>
        <div style="display: flex; gap: 20px; justify-content: center; margin-bottom: 20px; font-size: 0.9rem; flex-wrap: wrap;">
            <a href="<?= $baseUrl ?>/sobre" style="color: var(--text-muted); text-decoration: none;">Sobre Nós</a>
            <a href="<?= $baseUrl ?>/privacidade" style="color: var(--text-muted); text-decoration: none;">Privacidade</a>
            <a href="<?= $baseUrl ?>/termos" style="color: var(--text-muted); text-decoration: none;">Termos de Uso</a>
            <a href="javascript:void(0)" onclick="window.RachidDCookieConsent && window.RachidDCookieConsent.open()" style="color: var(--text-muted); text-decoration: none;">Preferências de Cookies</a>
        </div>
        <?php endif; ?>
        <div style="font-size: 0.9rem;">
            &copy; <?= date('Y') ?> RachidD. Todos os direitos reservados.
        </div>
    </div>
</footer>
