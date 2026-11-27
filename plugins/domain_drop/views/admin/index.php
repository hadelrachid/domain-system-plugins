<?php
/**
 * View Administrativa: Cockpit Principal do Hub DomainDrop
 *
 * @var array<int, \DomainSystem\Plugins\domain_drop\DTO\DropshipProductDto> $products
 * @var int $totalCount
 * @var array<int, string> $categories
 * @var float $rate
 * @var array<string, mixed> $config
 * @var string $csrfToken
 * @var string $baseUrl
 * @var string|null $flashType
 * @var string|null $flashMsg
 */
?>
<div class="domain-drop-container" style="max-width: 1400px; margin: 0 auto;">

    <!-- Top Header & Breadcrumbs -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 15px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span style="color: var(--text-muted, #94a3b8); font-size: 13px; display: inline-flex; align-items: center; gap: 4px;">
                    <i class="fas fa-boxes"></i> E-commerce
                </span>
                <span style="color: var(--text-muted, #94a3b8); font-size: 12px;">/</span>
                <span style="color: #ff4747; font-size: 13px; font-weight: 600;">DomainDrop</span>
            </div>
            <h1 style="margin: 0; font-size: 26px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-satellite-dish" style="color: #ff4747;"></i>
                DomainDrop Hub <span style="font-size: 13px; background: rgba(255, 71, 71, 0.15); color: #ff6b6b; padding: 2px 10px; border-radius: 12px; border: 1px solid rgba(255, 71, 71, 0.3);">AliExpress Dropship ISV</span>
            </h1>
            <p style="margin: 6px 0 0 0; color: var(--text-muted, #94a3b8); font-size: 13px;">
                Motor autônomo de mineração de produtos virais, cálculo de margens cambiais e enriquecimento de títulos por IA.
            </p>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button type="button" onclick="document.getElementById('modalClearCatalog').style.display='flex'" style="background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.4); color: #fca5a5; padding: 9px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: background 0.2s ease;">
                <i class="fas fa-trash-alt"></i> Limpar Tudo
            </button>
            <form action="<?= $baseUrl ?>/admin/dropship/publish-drafts" method="POST" id="formPublishDrafts" style="margin: 0; display: inline;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <button type="submit" style="background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.4); color: #86efac; padding: 9px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: background 0.2s ease;">
                    <i class="fas fa-bullhorn"></i> Publicar Todos (Ativar em Venda)
                </button>
            </form>
            <button type="button" onclick="openCatalogAuditModal()" style="background: rgba(69, 243, 255, 0.12); border: 1px solid rgba(69, 243, 255, 0.4); color: #45f3ff; padding: 9px 16px; border-radius: 6px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(69, 243, 255, 0.2); transition: all 0.2s ease;" title="Auditoria completa de estoque: produtos nacionais, importados e saúde do link">
                <i class="fas fa-file-medical-alt"></i> Relatório do Catálogo
            </button>
            <button type="button" onclick="document.getElementById('modalAcquireNew').style.display='flex'" style="background: linear-gradient(135deg, #F0D060 0%, #D4AF37 100%); color: #0b0f17; border: none; padding: 9px 18px; border-radius: 6px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(212,175,55,0.3); transition: transform 0.2s ease;">
                <i class="fas fa-bolt"></i> Adquirir Novos (24h)
            </button>
            <a href="<?= $baseUrl ?>/admin/dropship/settings" class="btn" style="background: rgba(255,255,255,0.06); border: 1px solid var(--border, rgba(255,255,255,0.15)); color: #fff; padding: 9px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fas fa-sliders-h"></i> Configurações & API
            </a>
        </div>
    </div>

    <!-- Flash Alerts -->
    <?php if (!empty($flashMsg) && $flashType === 'error'): ?>
        <div style="background: rgba(220,50,50,0.15); border: 1px solid #dc3232; color: #ff8080; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-exclamation-circle" style="font-size: 18px;"></i> <?= htmlspecialchars((string)$flashMsg) ?>
        </div>
    <?php elseif (!empty($flashMsg) && $flashType === 'success'): ?>
        <div style="background: rgba(40,167,69,0.15); border: 1px solid #28a745; color: #5cd67d; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-check-circle" style="font-size: 18px;"></i> <?= htmlspecialchars((string)$flashMsg) ?>
        </div>
    <?php endif; ?>

    <!-- Metrics Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div style="background: var(--bg-panel, #1d2327); border: 1px solid var(--border, rgba(255,255,255,0.1)); border-radius: 8px; padding: 18px 20px;">
            <div style="font-size: 12px; color: var(--text-muted, #94a3b8); font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">
                Produtos no Catálogo
            </div>
            <div style="font-size: 28px; font-weight: 700; color: #fff;">
                <?= number_format($totalCount, 0, ',', '.') ?>
            </div>
            <div style="font-size: 12px; color: #5cd67d; margin-top: 6px; display: flex; align-items: center; gap: 4px;">
                <i class="fas fa-check-double"></i> Prontos para venda
            </div>
        </div>

        <div style="background: var(--bg-panel, #1d2327); border: 1px solid var(--border, rgba(255,255,255,0.1)); border-radius: 8px; padding: 18px 20px;">
            <div style="font-size: 12px; color: var(--text-muted, #94a3b8); font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">
                Câmbio Comercial (USD/BRL)
            </div>
            <div style="font-size: 28px; font-weight: 700; color: #45f3ff;">
                R$ <?= number_format($rate, 2, ',', '.') ?>
            </div>
            <div style="font-size: 12px; color: var(--text-muted, #94a3b8); margin-top: 6px; display: flex; align-items: center; gap: 4px;">
                <i class="fas fa-sync-alt"></i> Atualização automática (AwesomeAPI)
            </div>
        </div>

        <div style="background: var(--bg-panel, #1d2327); border: 1px solid var(--border, rgba(255,255,255,0.1)); border-radius: 8px; padding: 18px 20px;">
            <div style="font-size: 12px; color: var(--text-muted, #94a3b8); font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">
                Markup Padrão
            </div>
            <div style="font-size: 28px; font-weight: 700; color: #f59e0b;">
                <?= (float)($config['default_markup_percent'] ?? 100.0) ?>%
            </div>
            <div style="font-size: 12px; color: var(--text-muted, #94a3b8); margin-top: 6px;">
                Margem mín: R$ <?= number_format((float)($config['min_profit_margin_brl'] ?? 30.0), 2, ',', '.') ?>
            </div>
        </div>

        <div style="background: var(--bg-panel, #1d2327); border: 1px solid var(--border, rgba(255,255,255,0.1)); border-radius: 8px; padding: 18px 20px;">
            <div style="font-size: 12px; color: var(--text-muted, #94a3b8); font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">
                AliExpress AppKey
            </div>
            <div style="font-size: 22px; font-weight: 700; color: #fff; font-family: monospace;">
                <?= htmlspecialchars((string)($config['app_key'] ?? '558628')) ?>
            </div>
            <div style="font-size: 12px; color: #5cd67d; margin-top: 6px; display: flex; align-items: center; gap: 4px;">
                <i class="fas fa-shield-alt"></i> HMAC-SHA256 Ativo
            </div>
        </div>
    </div>

    <!-- 24h Autonomous Daily Rotation Cycle Banner -->
    <?php
    $adminRemSeconds = isset($cycleInfo['seconds_remaining']) ? (int)$cycleInfo['seconds_remaining'] : 86400;
    $adminH = str_pad((string)floor($adminRemSeconds / 3600), 2, '0', STR_PAD_LEFT);
    $adminM = str_pad((string)floor(($adminRemSeconds % 3600) / 60), 2, '0', STR_PAD_LEFT);
    $adminS = str_pad((string)($adminRemSeconds % 60), 2, '0', STR_PAD_LEFT);
    $isDailyCycleActive = !empty($cycleInfo['auto_sync_enabled'] ?? true);
    ?>
    <div style="background: linear-gradient(135deg, rgba(212,175,55,0.12) 0%, rgba(20,24,30,0.95) 100%); border: 1px solid rgba(212,175,55,0.4); border-radius: 10px; padding: 22px 24px; margin-bottom: 26px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #F0D060 0%, #D4AF37 100%); display: flex; align-items: center; justify-content: center; font-size: 24px; box-shadow: 0 0 16px rgba(212,175,55,0.45); flex-shrink: 0;">
                ⚡
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                    <span style="font-size: 16px; font-weight: 800; color: #FFE082; text-transform: uppercase; letter-spacing: 0.5px;">
                        Ciclo Autônomo 24 Horas • Rotação de 9 Categorias
                    </span>
                    <span id="cycleActiveBadge" style="background: <?= $isDailyCycleActive ? 'rgba(34,197,94,0.2)' : 'rgba(239,68,68,0.2)' ?>; color: <?= $isDailyCycleActive ? '#4ade80' : '#f87171' ?>; border: 1px solid <?= $isDailyCycleActive ? 'rgba(34,197,94,0.4)' : 'rgba(239,68,68,0.4)' ?>; padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                        <?= $isDailyCycleActive ? 'Ativo' : 'Pausado' ?>
                    </span>
                </div>
                <div style="font-size: 13px; color: #cbd5e1;">
                    Renova o catálogo automaticamente minerando produtos virais em todas as 9 categorias oficiais a cada 24 horas.
                </div>
                <div style="font-size: 12px; color: #94a3b8; margin-top: 4px;">
                    Último ciclo: <strong style="color: #fff;"><?= htmlspecialchars((string)($cycleInfo['last_cycle_date'] ?? 'Pendente')) ?></strong>
                    &nbsp;•&nbsp; 
                    8 Categorias Oficiais: <span style="color: #F0D060;">Eletrônicos, Casa, Beleza, Ferramentas, Pet Shop, Veículos, Moda, Lingerie</span>
                    &nbsp;•&nbsp;
                    Capacidade: <span style="color: #10b981; font-weight: 600;">80 a 100 itens / 24h</span>
                    &nbsp;•&nbsp;
                    Destino: <span style="color: #60a5fa; font-weight: 600;">🇧🇷 Brasil (Preços em R$ BRL)</span>
                </div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
            <!-- Countdown Ticker -->
            <div style="display: flex; align-items: center; gap: 6px; background: rgba(0,0,0,0.5); padding: 8px 14px; border-radius: 8px; border: 1px solid rgba(212,175,55,0.3);">
                <div style="text-align: center; min-width: 32px;">
                    <div id="adminTimerH" style="font-size: 18px; font-weight: 800; color: #fff; font-family: monospace;"><?= $adminH ?></div>
                    <div style="font-size: 8px; color: #94a3b8; text-transform: uppercase;">Horas</div>
                </div>
                <span style="color: #F0D060; font-weight: 800;">:</span>
                <div style="text-align: center; min-width: 32px;">
                    <div id="adminTimerM" style="font-size: 18px; font-weight: 800; color: #fff; font-family: monospace;"><?= $adminM ?></div>
                    <div style="font-size: 8px; color: #94a3b8; text-transform: uppercase;">Min</div>
                </div>
                <span style="color: #F0D060; font-weight: 800;">:</span>
                <div style="text-align: center; min-width: 32px;">
                    <div id="adminTimerS" style="font-size: 18px; font-weight: 800; color: #FFE082; font-family: monospace;"><?= $adminS ?></div>
                    <div style="font-size: 8px; color: #94a3b8; text-transform: uppercase;">Seg</div>
                </div>
            </div>

            <!-- Botão Amarelo para Pausar / Desabilitar Rotação 24h -->
            <button type="button" id="btnToggleDailyCycle" onclick="toggleDailyCycleAutoSync()" style="background: <?= $isDailyCycleActive ? 'linear-gradient(135deg, #fbbf24 0%, #d97706 100%)' : 'linear-gradient(135deg, #10b981 0%, #059669 100%)' ?>; color: #0b0f17; border: none; padding: 11px 18px; border-radius: 6px; font-weight: 700; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(245,158,11,0.3); transition: all 0.2s ease;" title="<?= $isDailyCycleActive ? 'Pausar o ciclo autônomo de 24 horas' : 'Reativar o ciclo autônomo de 24 horas' ?>">
                <i class="fas <?= $isDailyCycleActive ? 'fa-pause-circle' : 'fa-play-circle' ?>" id="btnToggleDailyCycleIcon"></i>
                <span id="btnToggleDailyCycleText"><?= $isDailyCycleActive ? 'Pausar 24 Horas' : 'Ativar 24 Horas' ?></span>
            </button>

            <!-- Manual Trigger Form -->
            <form action="<?= $baseUrl ?>/admin/dropship/run-daily-cycle" method="POST" id="formRunDailyCycle" style="margin: 0; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                <div style="display: flex; align-items: center; gap: 6px; background: rgba(0,0,0,0.5); padding: 5px 12px; border-radius: 6px; border: 1px solid rgba(212,175,55,0.35);">
                    <label for="dailyCycleWarehouseOrigin" style="font-size: 11px; font-weight: 700; color: #FFE082; text-transform: uppercase; display: flex; align-items: center; gap: 4px;">
                        <i class="fas fa-warehouse"></i> Entrepostos:
                    </label>
                    <select name="warehouse_origin" id="dailyCycleWarehouseOrigin" style="background: transparent; border: none; color: #fff; font-size: 12px; font-weight: 600; cursor: pointer; outline: none;">
                        <option value="ALL" <?= (($config['mining_warehouse_origin'] ?? 'ALL') === 'ALL') ? 'selected' : '' ?> style="background: #1d2327; color: #fff;">🌐 Global (China + Brasil) [Recomendado]</option>
                        <option value="BR" <?= (($config['mining_warehouse_origin'] ?? '') === 'BR') ? 'selected' : '' ?> style="background: #1d2327; color: #fff;">🇧🇷 Apenas Brasil (Nacional)</option>
                        <option value="CN" <?= (($config['mining_warehouse_origin'] ?? '') === 'CN') ? 'selected' : '' ?> style="background: #1d2327; color: #fff;">🇨🇳 Apenas China (Importação)</option>
                    </select>
                </div>

                <button type="submit" id="btnRunDailyCycle" style="background: linear-gradient(135deg, #F0D060 0%, #D4AF37 100%); color: #0b0f17; border: none; padding: 11px 20px; border-radius: 6px; font-weight: 700; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(212,175,55,0.3); transition: transform 0.2s ease;">
                    <i class="fas fa-bolt"></i> Rodar Ciclo Diário 24h Agora
                </button>
            </form>
        </div>

        <!-- Barra de Status em Progresso (9 Categorias em Porcentagem até 100%) -->
        <div id="dailyCycleProgressBox" style="display: none; width: 100%; margin-top: 10px; background: rgba(0,0,0,0.5); border: 1px solid rgba(212,175,55,0.35); border-radius: 8px; padding: 14px 18px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-spinner fa-spin" style="color: #F0D060; font-size: 14px;"></i>
                    <span id="dailyCycleProgressTitle" style="font-size: 13px; font-weight: 700; color: #FFE082;">
                        Minerando 9 Categorias Oficiais no AliExpress...
                    </span>
                </div>
                <span id="dailyCycleProgressPercent" style="font-size: 15px; font-weight: 800; color: #4ade80; font-family: monospace;">0%</span>
            </div>
            
            <div style="width: 100%; height: 10px; background: rgba(255,255,255,0.1); border-radius: 5px; overflow: hidden; position: relative;">
                <div id="dailyCycleProgressBar" style="width: 0%; height: 100%; background: linear-gradient(90deg, #F0D060 0%, #10b981 100%); border-radius: 5px; transition: width 0.35s ease;"></div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px; font-size: 11px; color: #94a3b8;">
                <div>
                    Categoria ativa: <strong id="dailyCycleActiveCategory" style="color: #60a5fa;">Eletrônicos</strong>
                    &nbsp;•&nbsp;
                    <span id="dailyCycleCategoryStep" style="color: #cbd5e1;">(1 de 9)</span>
                </div>
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #10b981;"></span>
                    <span id="dailyCycleWarehouseBadge" style="color: #cbd5e1;">Entrepostos 🌐 Global (China + Brasil) & Envio Rastreável</span>
                </div>
            </div>
        </div>
    </div>

    <script>
    function initDomainDropAdminScripts() {
        var adminRemSeconds = <?= $adminRemSeconds ?>;
        var hElem = document.getElementById('adminTimerH');
        var mElem = document.getElementById('adminTimerM');
        var sElem = document.getElementById('adminTimerS');
        var form = document.getElementById('formRunDailyCycle');
        var btn = document.getElementById('btnRunDailyCycle');

        var categories = [
            'Eletrônicos & Gadget',
            'Smartphones, TVs & Projetores 4K',
            'Casa Inteligente & Organização',
            'Beleza, Cuidados & Estilo',
            'Ferramentas & Utilidades',
            'Pet Shop & Pets Virais',
            'Acessórios para Veículos',
            'Moda, Roupas & Vestuário',
            'Moda Fitness, Leggings & Academia',
            'Moda Íntima, Lingeries & Pijamas'
        ];

        window.runProgressiveMiningCycle = function(options) {
            options = options || {};
            var replaceCatalog = !!options.replaceCatalog;
            var itemsPerCategory = options.itemsPerCategory || 10;
            var originSelect = document.getElementById('dailyCycleWarehouseOrigin');
            var warehouseOrigin = options.warehouseOrigin || (originSelect ? originSelect.value : 'ALL');
            var csrfToken = <?= json_encode($csrfToken) ?>;
            var baseUrl = <?= json_encode($baseUrl) ?>;

            var badgeElem = document.getElementById('dailyCycleWarehouseBadge');
            if (badgeElem) {
                if (warehouseOrigin === 'BR') {
                    badgeElem.innerHTML = 'Entrepostos 🇧🇷 Brasil (Estoque Nacional)';
                } else if (warehouseOrigin === 'CN') {
                    badgeElem.innerHTML = 'Entrepostos 🇨🇳 China (Importação Direta)';
                } else {
                    badgeElem.innerHTML = 'Entrepostos 🌐 Global (China + Brasil) & Envio Rastreável';
                }
            }

            // Fechar modal de aquisição caso esteja aberto
            var modal = document.getElementById('modalAcquireNew');
            if (modal) modal.style.display = 'none';

            var btnBanner = document.getElementById('btnRunDailyCycle');
            var btnModal = document.getElementById('btnSubmitAcquireNew');
            if (btnBanner) {
                btnBanner.disabled = true;
                btnBanner.style.opacity = '0.6';
                btnBanner.style.pointerEvents = 'none';
                btnBanner.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Minerando 9 Categorias...';
            }
            if (btnModal) {
                btnModal.disabled = true;
                btnModal.style.opacity = '0.6';
            }

            var pBox = document.getElementById('dailyCycleProgressBox');
            var pBar = document.getElementById('dailyCycleProgressBar');
            var pPercent = document.getElementById('dailyCycleProgressPercent');
            var pTitle = document.getElementById('dailyCycleProgressTitle');
            var pCat = document.getElementById('dailyCycleActiveCategory');
            var pStep = document.getElementById('dailyCycleCategoryStep');

            if (pBox) {
                pBox.style.display = 'block';
                pBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            var totalCategories = categories.length;
            var currentCatIndex = 0;
            var totalMinedAll = 0;

            function processNextCategory() {
                if (currentCatIndex >= totalCategories) {
                    // Finalizado: dispara registro do ciclo diário de 24h
                    if (pBar) pBar.style.width = '100%';
                    if (pPercent) pPercent.textContent = '100%';
                    if (pTitle) pTitle.innerHTML = '<i class="fas fa-check-circle" style="color: #10b981;"></i> Ciclo de 9 Categorias Concluído com Sucesso!';
                    if (pCat) pCat.textContent = 'Catálogo atualizado com ' + totalMinedAll + ' novos produtos validados!';
                    if (pStep) pStep.textContent = '(100% Finalizado)';

                    var finishData = new FormData();
                    finishData.append('csrf_token', csrfToken);

                    fetch(baseUrl + '/admin/dropship/finish-daily-cycle', {
                        method: 'POST',
                        body: finishData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    }).finally(function() {
                        if (typeof window.showDropToast === 'function') {
                            window.showDropToast('🎉 Mineração concluída com sucesso! ' + totalMinedAll + ' novos produtos adicionados.', 'success');
                        }
                        setTimeout(function() {
                            window.location.reload();
                        }, 1400);
                    });
                    return;
                }

                var catName = categories[currentCatIndex];
                var isFirst = (currentCatIndex === 0 && replaceCatalog);
                var progressPercent = Math.round((currentCatIndex / totalCategories) * 100);

                if (pBar) pBar.style.width = Math.max(4, progressPercent) + '%';
                if (pPercent) pPercent.textContent = progressPercent + '%';
                if (pCat) pCat.textContent = catName;
                if (pStep) pStep.textContent = '(' + (currentCatIndex + 1) + ' de ' + totalCategories + ')';

                var stepData = new FormData();
                stepData.append('csrf_token', csrfToken);
                stepData.append('category', catName);
                stepData.append('items_per_category', itemsPerCategory);
                stepData.append('warehouse_origin', warehouseOrigin);
                if (isFirst) {
                    stepData.append('replace_catalog', '1');
                }

                stepData.append('ajax', '1');

                fetch(baseUrl + '/admin/dropship/mine-category-step', {
                    method: 'POST',
                    body: stepData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(function(res) {
                    return res.text().then(function(text) {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            var errMsg = 'Resposta inválida do servidor (HTTP ' + res.status + ')';
                            if (text.indexOf('<html') !== -1 || text.indexOf('<!DOCTYPE') !== -1) {
                                errMsg = 'O servidor retornou uma página HTML em vez de JSON (possível erro de sessão ou timeout).';
                            }
                            throw new Error(errMsg);
                        }
                    });
                })
                .then(function(data) {
                    if (data && data.success) {
                        totalMinedAll += (data.mined_count || 0);
                    }
                    currentCatIndex++;
                    var nextPercent = Math.round((currentCatIndex / totalCategories) * 100);
                    if (pBar) pBar.style.width = nextPercent + '%';
                    if (pPercent) pPercent.textContent = nextPercent + '%';
                    processNextCategory();
                })
                .catch(function(err) {
                    console.error('Falha temporária ao minerar ' + catName, err);
                    currentCatIndex++;
                    processNextCategory();
                });
            }

            processNextCategory();
        };

        if (form && btn) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                var originSelect = document.getElementById('dailyCycleWarehouseOrigin');
                var wOrigin = originSelect ? originSelect.value : 'ALL';
                window.runProgressiveMiningCycle({ replaceCatalog: false, itemsPerCategory: 10, warehouseOrigin: wOrigin });
            });
        }

        var adminAutoTickFired = false;
        function triggerAdminAutoCycle() {
            if (adminAutoTickFired) return;
            adminAutoTickFired = true;
            fetch('<?= $baseUrl ?>/dropship/cron-tick', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data && typeof data.seconds_remaining === 'number' && data.seconds_remaining > 0) {
                    adminRemSeconds = data.seconds_remaining;
                    if (data.rotated) {
                        window.location.reload();
                    }
                }
            })
            .catch(function() {});
        }

        setInterval(function() {
            if (adminRemSeconds <= 0) {
                if (hElem) hElem.textContent = '00';
                if (mElem) mElem.textContent = '00';
                if (sElem) sElem.textContent = '00';
                triggerAdminAutoCycle();
                return;
            }
            adminRemSeconds--;
            var h = Math.floor(adminRemSeconds / 3600);
            var m = Math.floor((adminRemSeconds % 3600) / 60);
            var s = adminRemSeconds % 60;
            if (hElem) hElem.textContent = String(h).padStart(2, '0');
            if (mElem) mElem.textContent = String(m).padStart(2, '0');
            if (sElem) sElem.textContent = String(s).padStart(2, '0');
        }, 1000);

        // -------------------------------------------------------------
        // Mineração 1-Click Interativa com Barra de Progresso Real (0% até 100%)
        // -------------------------------------------------------------
        var formMiner = document.getElementById('formOneClickMiner');
        var btnMiner = document.getElementById('btnOneClickMiner');
        var pBoxMiner = document.getElementById('oneClickMinerProgressBox');
        var pBarMiner = document.getElementById('oneClickMinerProgressBar');
        var pPercentMiner = document.getElementById('oneClickMinerPercent');
        var pStatusMiner = document.getElementById('oneClickMinerStatus');
        var pStageMiner = document.getElementById('oneClickMinerStage');
        var pCountMiner = document.getElementById('oneClickMinerCount');

        if (formMiner && btnMiner) {
            formMiner.addEventListener('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(formMiner);
                var category = formData.get('category') || 'Produtos em Alta';
                var limit = parseInt(formData.get('limit') || '10', 10);

                btnMiner.disabled = true;
                btnMiner.style.opacity = '0.6';
                btnMiner.style.pointerEvents = 'none';
                btnMiner.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Minerando ' + limit + ' itens...';

                if (pBoxMiner) {
                    pBoxMiner.style.display = 'block';
                    pBoxMiner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
                if (pBarMiner) {
                    pBarMiner.style.width = '12%';
                    pBarMiner.style.background = 'linear-gradient(90deg, #ff4747 0%, #ff8038 60%, #10b981 100%)';
                }
                if (pPercentMiner) pPercentMiner.textContent = '12%';
                if (pStatusMiner) pStatusMiner.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Buscando produtos mais vendidos no AliExpress...';
                if (pStageMiner) pStageMiner.textContent = 'Consultando categoria ' + category + '...';
                if (pCountMiner) pCountMiner.textContent = '0 / ' + limit + ' itens';

                var simulatedPct = 12;
                var progressInterval = setInterval(function() {
                    if (simulatedPct < 88) {
                        simulatedPct += Math.floor(Math.random() * 9) + 4;
                        if (simulatedPct > 88) simulatedPct = 88;
                        if (pBarMiner) pBarMiner.style.width = simulatedPct + '%';
                        if (pPercentMiner) pPercentMiner.textContent = simulatedPct + '%';

                        if (simulatedPct >= 30 && simulatedPct < 55) {
                            if (pStatusMiner) pStatusMiner.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Validando reputação, lojas oficiais e frete BR...';
                            if (pStageMiner) pStageMiner.textContent = 'Aplicando filtros contratuais e entrepostos...';
                            if (pCountMiner) pCountMiner.textContent = Math.max(1, Math.round(limit * 0.3)) + ' / ' + limit + ' avaliados';
                        } else if (simulatedPct >= 55 && simulatedPct < 75) {
                            if (pStatusMiner) pStatusMiner.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Higienizando títulos e precificando com IA...';
                            if (pStageMiner) pStageMiner.textContent = 'Convertendo moedas e aplicando margem de lucro...';
                            if (pCountMiner) pCountMiner.textContent = Math.max(1, Math.round(limit * 0.65)) + ' / ' + limit + ' processados';
                        } else if (simulatedPct >= 75) {
                            if (pStatusMiner) pStatusMiner.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Gravando no catálogo e ativando vitrine...';
                            if (pStageMiner) pStageMiner.textContent = 'Finalizando gravação no banco de dados...';
                            if (pCountMiner) pCountMiner.textContent = limit + ' / ' + limit + ' gravados';
                        }
                    }
                }, 350);

                var searchParams = new URLSearchParams(formData);
                searchParams.append('ajax', '1');

                fetch(formMiner.action, {
                    method: 'POST',
                    body: searchParams,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                })
                .then(function(res) {
                    return res.text().then(function(text) {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            var errMsg = 'Resposta inválida do servidor (HTTP ' + res.status + ')';
                            if (text.indexOf('<html') !== -1 || text.indexOf('<!DOCTYPE') !== -1) {
                                errMsg = 'O servidor retornou uma página HTML em vez de JSON (possível erro de sessão ou timeout).';
                            }
                            throw new Error(errMsg);
                        }
                    });
                })
                .then(function(data) {
                    clearInterval(progressInterval);
                    var minedTotal = (data && typeof data.mined_count !== 'undefined') ? data.mined_count : (data ? data.count : 0);

                    if (data && data.success && minedTotal > 0) {
                        if (pBarMiner) {
                            pBarMiner.style.width = '100%';
                            pBarMiner.style.background = '#10b981';
                        }
                        if (pPercentMiner) pPercentMiner.textContent = '100%';
                        if (pStatusMiner) pStatusMiner.innerHTML = '<i class="fas fa-check-circle" style="color: #10b981;"></i> Mineração Concluída!';
                        if (pStageMiner) pStageMiner.textContent = minedTotal + ' produtos de ' + category + ' minerados com fotos, links e preços.';
                        if (pCountMiner) pCountMiner.textContent = minedTotal + ' / ' + limit + ' itens ativos (100%)';

                        if (typeof window.showDropToast === 'function') {
                            window.showDropToast('🎉 ' + minedTotal + ' produtos de ' + category + ' minerados com sucesso!', 'success');
                        }
                        setTimeout(function() {
                            window.location.reload();
                        }, 1200);
                    } else {
                        var warnMsg = (data && (data.message || data.error)) ? (data.message || data.error) : 'Nenhum produto encontrado para ' + category + ' com os filtros selecionados.';
                        if (pBarMiner) {
                            pBarMiner.style.width = '100%';
                            pBarMiner.style.background = '#f59e0b';
                        }
                        if (pPercentMiner) pPercentMiner.textContent = 'Aviso';
                        if (pStatusMiner) pStatusMiner.innerHTML = '<i class="fas fa-exclamation-triangle" style="color: #f59e0b;"></i> Nenhum Produto Encontrado';
                        if (pStageMiner) pStageMiner.textContent = warnMsg;
                        if (pCountMiner) pCountMiner.textContent = '0 / ' + limit + ' itens';

                        if (typeof window.showDropToast === 'function') {
                            window.showDropToast(warnMsg, 'warning');
                        }
                        setTimeout(function() {
                            btnMiner.disabled = false;
                            btnMiner.style.opacity = '1';
                            btnMiner.style.pointerEvents = 'auto';
                            btnMiner.innerHTML = '<i class="fas fa-bolt"></i> Iniciar Mineração 1-Click';
                        }, 4000);
                    }
                })
                .catch(function(err) {
                    clearInterval(progressInterval);
                    if (pBarMiner) {
                        pBarMiner.style.width = '100%';
                        pBarMiner.style.background = '#ef4444';
                    }
                    if (pPercentMiner) pPercentMiner.textContent = 'Erro';
                    if (pStatusMiner) pStatusMiner.innerHTML = '<i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i> Erro na Mineração';
                    if (pStageMiner) pStageMiner.textContent = err.message || 'Falha na conexão com o banco ou API.';

                    if (typeof window.showDropToast === 'function') {
                        window.showDropToast(err.message || 'Erro ao minerar produtos.', 'error');
                    }
                    setTimeout(function() {
                        btnMiner.disabled = false;
                        btnMiner.style.opacity = '1';
                        btnMiner.style.pointerEvents = 'auto';
                        btnMiner.innerHTML = '<i class="fas fa-bolt"></i> Iniciar Mineração 1-Click';
                    }, 4000);
                });
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDomainDropAdminScripts);
    } else {
        initDomainDropAdminScripts();
    }
    </script>

    <!-- Quick Operations: 1-Click Miner, Single Product Import & Browser Code Ingestion -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-bottom: 28px;">
        
        <!-- 1-Click Mining Card -->
        <div style="background: var(--bg-panel, #1d2327); border: 1px solid var(--border, rgba(255,255,255,0.1)); border-radius: 8px; padding: 22px;">
            <h3 style="margin: 0 0 8px 0; font-size: 17px; font-weight: 600; color: #fff; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-radar" style="color: #ff4747;"></i> Mineração de Produtos em Alta
            </h3>
            <p style="margin: 0 0 16px 0; color: var(--text-muted, #94a3b8); font-size: 13px;">
                Busca os produtos mais vendidos e virais do AliExpress na categoria escolhida, converte a moeda e gera títulos profissionais automaticamente.
            </p>
            <form id="formOneClickMiner" action="<?= $baseUrl ?>/admin/dropship/mine" method="POST" style="display: flex; flex-direction: column; gap: 12px;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 10px;">
                    <div>
                        <label style="display: block; font-size: 12px; color: var(--text-muted, #94a3b8); margin-bottom: 4px;">Nicho / Categoria</label>
                        <select name="category" style="width: 100%; background: rgba(0,0,0,0.25); border: 1px solid var(--border, rgba(255,255,255,0.15)); color: #fff; padding: 8px 12px; border-radius: 6px; font-size: 13px;">
                            <option value="Eletrônicos & Gadget">Eletrônicos & Gadget</option>
                            <option value="Smartphones, TVs & Projetores 4K">Smartphones, TVs & Projetores 4K</option>
                            <option value="Casa Inteligente & Organização">Casa Inteligente & Organização</option>
                            <option value="Beleza, Cuidados & Estilo">Beleza, Cuidados & Estilo</option>
                            <option value="Ferramentas & Utilidades">Ferramentas & Utilidades</option>
                            <option value="Pet Shop & Pets Virais">Pet Shop & Pets Virais</option>
                            <option value="Acessórios para Veículos">Acessórios para Veículos</option>
                            <option value="Moda, Roupas & Vestuário">Moda, Roupas & Vestuário</option>
                            <option value="Moda Fitness, Leggings & Academia">Moda Fitness, Leggings & Academia</option>
                            <option value="Moda Íntima, Lingeries & Pijamas">Moda Íntima, Lingeries & Pijamas</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; color: var(--text-muted, #94a3b8); margin-bottom: 4px;">Quantidade</label>
                        <select name="limit" style="width: 100%; background: rgba(0,0,0,0.25); border: 1px solid var(--border, rgba(255,255,255,0.15)); color: #fff; padding: 8px 12px; border-radius: 6px; font-size: 13px;">
                            <option value="5">5 itens</option>
                            <option value="10" selected>10 itens</option>
                            <option value="20">20 itens</option>
                            <option value="30">30 itens</option>
                            <option value="50">50 itens</option>
                            <option value="80">80 itens</option>
                            <option value="100">100 itens</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; color: var(--text-muted, #94a3b8); margin-bottom: 4px;">Termômetro &amp; Reputação</label>
                        <select name="sort" style="width: 100%; background: rgba(0,0,0,0.25); border: 1px solid var(--border, rgba(255,255,255,0.15)); color: #fff; padding: 8px 12px; border-radius: 6px; font-size: 13px;">
                            <option value="volume_desc" selected>🏆 Mais Vendidos (Best Sellers)</option>
                            <option value="evaluate_rate_desc">⭐ Melhor Avaliados (4.8★ - 5.0★)</option>
                            <option value="choice_desc">⚡ Lojas Oficiais &amp; Choice</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; color: var(--text-muted, #94a3b8); margin-bottom: 4px;">Entreposto / Estoque</label>
                        <select name="warehouse_origin" style="width: 100%; background: rgba(0,0,0,0.25); border: 1px solid var(--border, rgba(255,255,255,0.15)); color: #fff; padding: 8px 12px; border-radius: 6px; font-size: 13px;">
                            <option value="ALL" <?= (($config['mining_warehouse_origin'] ?? 'ALL') === 'ALL') ? 'selected' : '' ?>>🌐 Global (China + Brasil)</option>
                            <option value="BR" <?= (($config['mining_warehouse_origin'] ?? '') === 'BR') ? 'selected' : '' ?>>🇧🇷 Apenas Brasil (Nacional)</option>
                            <option value="CN" <?= (($config['mining_warehouse_origin'] ?? '') === 'CN') ? 'selected' : '' ?>>🇨🇳 Apenas China (Importação)</option>
                        </select>
                    </div>
                </div>

                <div style="margin-top: 10px; margin-bottom: 12px; padding: 8px 12px; background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.25); border-radius: 6px; display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" id="minerQuarantineCheck" name="quarantine" value="1" checked style="width: 17px; height: 17px; accent-color: #f59e0b; cursor: pointer;">
                    <label for="minerQuarantineCheck" style="font-size: 12px; color: #fbbf24; cursor: pointer; user-select: none; line-height: 1.3;">
                        <strong>🛡️ Modo Esteira de Quarentena:</strong> Enviar para Revisão Manual antes de carregar no site (Recomendado para conferência humana de tamanhos, cores e fotos)
                    </label>
                </div>

                <button type="submit" id="btnOneClickMiner" style="background: linear-gradient(135deg, #ff4747 0%, #e02424 100%); border: none; color: #fff; padding: 10px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s ease;">
                    <i class="fas fa-bolt"></i> Iniciar Mineração 1-Click
                </button>
            </form>

            <!-- Barra de Progresso em Tempo Real (0% até 100%) -->
            <div id="oneClickMinerProgressBox" style="display: none; margin-top: 14px; padding: 14px 16px; background: rgba(0,0,0,0.55); border: 1px solid rgba(255, 71, 71, 0.45); border-radius: 8px; box-shadow: 0 4px 16px rgba(255, 71, 71, 0.15);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span id="oneClickMinerStatus" style="font-size: 13px; font-weight: 700; color: #ff8038; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-spinner fa-spin"></i> Conectando à AliExpress API...
                    </span>
                    <span id="oneClickMinerPercent" style="font-size: 14px; font-weight: 800; color: #10b981; font-family: monospace;">0%</span>
                </div>
                <div style="width: 100%; height: 10px; background: rgba(255,255,255,0.1); border-radius: 5px; overflow: hidden; position: relative;">
                    <div id="oneClickMinerProgressBar" style="width: 0%; height: 100%; background: linear-gradient(90deg, #ff4747 0%, #ff8038 50%, #10b981 100%); transition: width 0.35s ease; border-radius: 5px;"></div>
                </div>
                <div id="oneClickMinerDetail" style="margin-top: 8px; font-size: 12px; color: var(--text-muted, #94a3b8); display: flex; justify-content: space-between;">
                    <span id="oneClickMinerStage">Preparando mineração...</span>
                    <span id="oneClickMinerCount" style="color: #cbd5e1; font-weight: 600;">0 itens</span>
                </div>
            </div>
        </div>

        <!-- Import by URL Card -->
        <div style="background: var(--bg-panel, #1d2327); border: 1px solid var(--border, rgba(255,255,255,0.1)); border-radius: 8px; padding: 22px;">
            <h3 style="margin: 0 0 8px 0; font-size: 17px; font-weight: 600; color: #fff; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-link" style="color: #45f3ff;"></i> Importação por Link ou ID
            </h3>
            <p style="margin: 0 0 16px 0; color: var(--text-muted, #94a3b8); font-size: 13px;">
                Cole o link direto do produto no AliExpress ou o ID numérico. O sistema buscará fotos, preços, variantes e aplicará sua margem de lucro.
            </p>
            <form action="<?= $baseUrl ?>/admin/dropship/import-url" method="POST" style="display: flex; flex-direction: column; gap: 12px;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                
                <div>
                    <label style="display: block; font-size: 12px; color: var(--text-muted, #94a3b8); margin-bottom: 4px;">URL ou ID do Produto</label>
                    <input type="text" name="product_url_or_id" placeholder="https://pt.aliexpress.com/item/10050012345.html" required style="width: 100%; background: rgba(0,0,0,0.25); border: 1px solid var(--border, rgba(255,255,255,0.15)); color: #fff; padding: 8px 12px; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                </div>

                <button type="submit" style="background: rgba(69, 243, 255, 0.15); border: 1px solid rgba(69, 243, 255, 0.4); color: #45f3ff; padding: 10px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <i class="fas fa-download"></i> Importar Produto & Aplicar IA
                </button>
            </form>
        </div>

        <!-- Browser Source / Frame Ingestion Card (100% Anti-Bot Bypass) -->
        <div style="background: var(--bg-panel, #1d2327); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 8px; padding: 22px; position: relative;">
            <div style="position: absolute; top: 16px; right: 16px; background: rgba(16, 185, 129, 0.2); color: #34d399; font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 12px; border: 1px solid rgba(16, 185, 129, 0.4); text-transform: uppercase;">
                100% Anti-Robô
            </div>
            <h3 style="margin: 0 0 8px 0; font-size: 17px; font-weight: 600; color: #fff; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-code" style="color: #10b981;"></i> Código do Navegador
            </h3>
            <p style="margin: 0 0 12px 0; color: var(--text-muted, #94a3b8); font-size: 13px;">
                Abra o anúncio no navegador, copie o código-fonte (Ctrl+U ou F12) e cole aqui. Evita qualquer bloqueio de robô ou verificação deslizante.
            </p>
            <form action="<?= $baseUrl ?>/admin/dropship/import-browser-source" method="POST" style="display: flex; flex-direction: column; gap: 10px;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 10px;">
                    <div>
                        <label style="display: block; font-size: 11px; color: var(--text-muted, #94a3b8); margin-bottom: 2px;">Código HTML ou JSON do Anúncio</label>
                        <textarea name="raw_source" rows="2" placeholder="Cole aqui o HTML (Ctrl+U) ou JSON do anúncio..." required style="width: 100%; background: rgba(0,0,0,0.3); border: 1px solid rgba(16, 185, 129, 0.3); color: #fff; padding: 6px 10px; border-radius: 6px; font-size: 12px; font-family: monospace; box-sizing: border-box; resize: vertical;"></textarea>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; color: var(--text-muted, #94a3b8); margin-bottom: 2px;">Categoria</label>
                        <select name="category" style="width: 100%; background: rgba(0,0,0,0.3); border: 1px solid var(--border, rgba(255,255,255,0.15)); color: #fff; padding: 7px 8px; border-radius: 6px; font-size: 12px;">
                            <option value="">Inferir Auto</option>
                            <option value="Eletrônicos & Gadget">Eletrônicos</option>
                            <option value="Smartphones, TVs & Projetores 4K">Smartphones & Projetores</option>
                            <option value="Casa Inteligente & Organização">Casa Inteligente</option>
                            <option value="Beleza, Cuidados & Estilo">Beleza & Estilo</option>
                            <option value="Ferramentas & Utilidades">Ferramentas</option>
                            <option value="Pet Shop & Pets Virais">Pet Shop</option>
                            <option value="Acessórios para Veículos">Veículos</option>
                            <option value="Moda, Roupas & Vestuário">Moda & Roupas</option>
                            <option value="Moda Fitness, Leggings & Academia">Moda Fitness</option>
                            <option value="Moda Íntima, Lingeries & Pijamas">Moda Íntima</option>
                        </select>
                    </div>
                </div>

                <button type="submit" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); border: none; color: #fff; padding: 9px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);">
                    <i class="fas fa-file-import"></i> Processar Código & Importar
                </button>
            </form>
        </div>

    </div>

    <!-- Painel de Estratégia Comercial: Precificação Dinâmica, Margens & Sentinela de Volatilidade -->
    <div style="background: var(--bg-panel, #1d2327); border: 1px solid rgba(16, 185, 129, 0.35); border-radius: 8px; padding: 20px 24px; margin-bottom: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); color: #10b981; width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                    <i class="fas fa-coins"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #fff;">Estratégia de Precificação, Margens &amp; Sentinela de Volatilidade</h3>
                    <div style="font-size: 12px; color: var(--text-muted, #94a3b8); margin-top: 2px;">
                        Define a margem de lucro com que os produtos entram na loja e o ritmo da Sentinela que blinda seu negócio contra aumentos do AliExpress.
                    </div>
                </div>
            </div>
            <!-- Badge de Cotação Atual -->
            <div style="background: rgba(69, 243, 255, 0.08); border: 1px solid rgba(69, 243, 255, 0.25); color: #45f3ff; padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                <i class="fas fa-exchange-alt"></i> USD 1,00 = R$ <?= number_format($rate, 2, ',', '.') ?>
            </div>
        </div>

        <!-- Fórmula Oficial de Correção -->
        <div style="background: rgba(0,0,0,0.3); border: 1px dashed rgba(16, 185, 129, 0.3); border-radius: 6px; padding: 10px 14px; margin-bottom: 16px; font-size: 12px; color: #cbd5e1; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
            <div>
                <span style="color: #10b981; font-weight: 700;">📐 Fórmula de Proteção:</span>
                <code style="color: #fff; background: rgba(255,255,255,0.06); padding: 2px 8px; border-radius: 4px; margin-left: 6px;">Custo Protegido = Custo Fornecedor &times; (1 + &Iota;<sub>v</sub>)</code>
                <span style="color: #94a3b8; margin: 0 4px;">&bull;</span>
                <code style="color: #5cd67d; background: rgba(255,255,255,0.06); padding: 2px 8px; border-radius: 4px;">Preço de Venda = Custo Protegido &times; (1 + Markup %)</code>
            </div>
            <div style="color: #94a3b8; font-size: 11.5px;">
                Cobre IOF (4,38%) + Spread bancário de cartão PJ + oscilações do fornecedor chinês.
            </div>
        </div>

        <form action="<?= $baseUrl ?>/admin/dropship/save-pricing" method="POST" style="margin: 0;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; align-items: flex-end;">
                <div>
                    <label style="display: block; font-size: 12px; color: #fff; font-weight: 600; margin-bottom: 4px;">
                        Markup Padrão (Margem)
                    </label>
                    <div style="position: relative;">
                        <input type="number" step="1" min="10" name="default_markup_percent" value="<?= (float)($config['default_markup_percent'] ?? 100.0) ?>" required style="width: 100%; background: rgba(0,0,0,0.25); border: 1px solid var(--border, rgba(255,255,255,0.15)); color: #fff; padding: 8px 12px; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                        <span style="position: absolute; right: 10px; top: 9px; color: #94a3b8; font-size: 12px;">% (100% = 2x)</span>
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; color: #fff; font-weight: 600; margin-bottom: 4px;">
                        Lucro Mínimo por Item (Piso)
                    </label>
                    <div style="position: relative;">
                        <input type="number" step="0.5" min="0" name="min_profit_margin_brl" value="<?= (float)($config['min_profit_margin_brl'] ?? 30.0) ?>" required style="width: 100%; background: rgba(0,0,0,0.25); border: 1px solid var(--border, rgba(255,255,255,0.15)); color: #fff; padding: 8px 12px; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                        <span style="position: absolute; right: 10px; top: 9px; color: #94a3b8; font-size: 12px;">R$</span>
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; color: #fff; font-weight: 600; margin-bottom: 4px;">
                        Índice de Volatilidade (<span style="color: #fbbf24;">&Iota;<sub>v</sub></span>)
                    </label>
                    <div style="position: relative;">
                        <input type="number" step="0.5" min="0" max="50" name="volatility_index_percent" value="<?= (float)($config['volatility_index_percent'] ?? 12.0) ?>" required style="width: 100%; background: rgba(0,0,0,0.25); border: 1px solid var(--border, rgba(255,255,255,0.15)); color: #fff; padding: 8px 12px; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                        <span style="position: absolute; right: 10px; top: 9px; color: #94a3b8; font-size: 12px;">%</span>
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; color: #fff; font-weight: 600; margin-bottom: 4px;">
                        Varredura da Sentinela
                    </label>
                    <select name="price_sync_interval_hours" style="width: 100%; background: rgba(0,0,0,0.25); border: 1px solid var(--border, rgba(255,255,255,0.15)); color: #fff; padding: 8px 10px; border-radius: 6px; font-size: 12px; box-sizing: border-box;">
                        <option value="3" <?= ((int)($config['price_sync_interval_hours'] ?? 6) === 3) ? 'selected' : '' ?>>A cada 3 horas (8x/dia)</option>
                        <option value="4" <?= ((int)($config['price_sync_interval_hours'] ?? 6) === 4) ? 'selected' : '' ?>>A cada 4 horas (6x/dia)</option>
                        <option value="6" <?= ((int)($config['price_sync_interval_hours'] ?? 6) === 6) ? 'selected' : '' ?>>A cada 6 horas (Recomendado - 4x/dia)</option>
                        <option value="12" <?= ((int)($config['price_sync_interval_hours'] ?? 6) === 12) ? 'selected' : '' ?>>A cada 12 horas (2x/dia)</option>
                    </select>
                </div>

                <div>
                    <button type="submit" style="width: 100%; background: linear-gradient(135deg, #059669 0%, #10b981 100%); border: none; color: #fff; padding: 9px 16px; border-radius: 6px; font-size: 13px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25); transition: all 0.2s ease;">
                        <i class="fas fa-check"></i> Salvar Estratégia de Margens
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Catalog Filter Bar -->
    <div style="background: var(--bg-panel, #1d2327); border: 1px solid var(--border, rgba(255,255,255,0.1)); border-radius: 8px; padding: 16px; margin-bottom: 16px;">
        <?php 
        $currStatus = (string)($_GET['status'] ?? ''); 
        $catParam = !empty($_GET['category']) ? '&category=' . urlencode((string)$_GET['category']) : '';
        $qParam = !empty($_GET['q']) ? '&q=' . urlencode((string)$_GET['q']) : '';
        ?>
        <!-- Abas de Auditoria de Status: Todos vs No Site vs Quarentena -->
        <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 14px; padding-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.08); flex-wrap: wrap;">
            <a href="<?= $baseUrl ?>/admin/dropship?<?= ltrim($catParam . $qParam, '&') ?>" style="padding: 7px 14px; border-radius: 6px; font-size: 12.5px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s ease; <?= ($currStatus === '') ? 'background: #3b82f6; color: #fff; box-shadow: 0 2px 8px rgba(59, 130, 246, 0.35);' : 'background: rgba(255,255,255,0.05); color: #94a3b8;' ?>">
                <i class="fas fa-boxes"></i> Todos os Produtos (<?= (int)($totalAll ?? $totalCount) ?>)
            </a>
            <a href="<?= $baseUrl ?>/admin/dropship?status=published<?= $catParam . $qParam ?>" style="padding: 7px 14px; border-radius: 6px; font-size: 12.5px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s ease; <?= ($currStatus === 'published') ? 'background: #10b981; color: #fff; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.35);' : 'background: rgba(16, 185, 129, 0.12); color: #34d399;' ?>">
                <i class="fas fa-check-circle"></i> ✅ No Ar / No Site (<?= (int)($totalPublished ?? 0) ?>)
            </a>
            <a href="<?= $baseUrl ?>/admin/dropship?status=draft<?= $catParam . $qParam ?>" style="padding: 7px 14px; border-radius: 6px; font-size: 12.5px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s ease; <?= ($currStatus === 'draft') ? 'background: #f59e0b; color: #000; box-shadow: 0 2px 8px rgba(245, 158, 11, 0.35);' : 'background: rgba(245, 158, 11, 0.12); color: #fbbf24;' ?>">
                <i class="fas fa-hourglass-half"></i> ⏳ Quarentena / Em Revisão (<?= (int)($totalDraft ?? 0) ?>)
            </a>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <form action="<?= $baseUrl ?>/admin/dropship" method="GET" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap; flex: 1;">
                <?php if ($currStatus !== ''): ?>
                    <input type="hidden" name="status" value="<?= htmlspecialchars($currStatus) ?>">
                <?php endif; ?>
                <div style="position: relative; min-width: 260px; flex: 1;">
                    <i class="fas fa-search" style="position: absolute; left: 12px; top: 11px; color: var(--text-muted, #94a3b8); font-size: 13px;"></i>
                    <input type="text" name="q" value="<?= htmlspecialchars((string)($_GET['q'] ?? '')) ?>" placeholder="Buscar por título ou ID..." style="width: 100%; background: rgba(0,0,0,0.25); border: 1px solid var(--border, rgba(255,255,255,0.15)); color: #fff; padding: 8px 12px 8px 34px; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                </div>

                <?php if (!empty($categories)): ?>
                    <select name="category" onchange="this.form.submit()" style="background: rgba(0,0,0,0.25); border: 1px solid var(--border, rgba(255,255,255,0.15)); color: #fff; padding: 8px 12px; border-radius: 6px; font-size: 13px;">
                        <option value="">Todas as Categorias</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>" <?= (isset($_GET['category']) && $_GET['category'] === $cat) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>

                <button type="submit" style="background: rgba(255,255,255,0.06); border: 1px solid var(--border, rgba(255,255,255,0.15)); color: #fff; padding: 8px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;">
                    Filtrar
                </button>
                <?php if (!empty($_GET['q']) || !empty($_GET['category']) || !empty($_GET['status'])): ?>
                    <a href="<?= $baseUrl ?>/admin/dropship" style="color: var(--text-muted, #94a3b8); font-size: 12px; text-decoration: none;">Limpar</a>
                <?php endif; ?>
            </form>

            <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                <button type="button" onclick="openBatchMediaSyncModal()" title="Sincroniza produto por produto em tempo real com barra de progresso e log de status" style="background: linear-gradient(135deg, rgba(69, 243, 255, 0.16) 0%, rgba(59, 130, 246, 0.22) 100%); border: 1.5px solid rgba(69, 243, 255, 0.45); color: #45f3ff; padding: 7px 14px; border-radius: 7px; font-size: 12.5px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(69, 243, 255, 0.15); transition: all 0.2s ease;">
                    <i class="fas fa-sync-alt"></i> Atualizar Todas as Imagens (Fila & Status)
                </button>
                <div style="font-size: 13px; color: var(--text-muted, #94a3b8);">
                    <?php
                    $perPageAdmin = 48;
                    $fromItem = $totalCount > 0 ? ((($page - 1) * $perPageAdmin) + 1) : 0;
                    $toItem = min((($page - 1) * $perPageAdmin) + count($products), $totalCount);
                    ?>
                    Mostrando <strong style="color: #fff;"><?= $fromItem ?>-<?= $toItem ?></strong> de <strong style="color: #fff;"><?= $totalCount ?></strong> produtos (Página <?= $page ?> de <?= $totalPages ?> • <?= $perPageAdmin ?> por página)
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de Ações em Lote e Auditoria Rápida (Sticky Toolbar) -->
    <div id="catalogBatchActionBar" style="background: rgba(15, 23, 42, 0.96); backdrop-filter: blur(10px); border: 1.5px solid rgba(69, 243, 255, 0.35); border-radius: 8px; padding: 12px 18px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; box-shadow: 0 6px 24px rgba(0,0,0,0.5);">
        <div style="display: flex; align-items: center; gap: 14px;">
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: #fff; font-size: 13px; font-weight: 600; user-select: none;">
                <input type="checkbox" id="selectAllCardsCheckbox" onchange="toggleSelectAllCards(this)" style="width: 18px; height: 18px; accent-color: #45f3ff; cursor: pointer;">
                <span>Marcar Todos na Página</span>
            </label>
            <span id="batchSelectedCounter" style="background: rgba(69, 243, 255, 0.15); color: #45f3ff; border: 1px solid rgba(69, 243, 255, 0.3); padding: 3px 12px; border-radius: 12px; font-size: 12px; font-weight: 700;">
                0 selecionados
            </span>
        </div>

        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <button type="button" id="btnBatchPublish" onclick="executeBatchStatus('publish')" disabled style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); border: none; color: #fff; padding: 8px 16px; border-radius: 6px; font-size: 12.5px; font-weight: 700; cursor: not-allowed; display: inline-flex; align-items: center; gap: 6px; opacity: 0.45; transition: all 0.2s ease; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.25);" title="Carregar os produtos selecionados com ✅ direto para a vitrine da loja">
                <i class="fas fa-check-double"></i> 🚀 Carregar para Site (Aplicar)
            </button>
            <button type="button" id="btnBatchUnpublish" onclick="executeBatchStatus('unpublish')" disabled style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.4); color: #fbbf24; padding: 8px 14px; border-radius: 6px; font-size: 12.5px; font-weight: 700; cursor: not-allowed; display: inline-flex; align-items: center; gap: 6px; opacity: 0.45; transition: all 0.2s ease;" title="Mover produtos selecionados de volta para Quarentena / Revisão (ocultar da vitrine)">
                <i class="fas fa-pause-circle"></i> ⏸️ Mover para Quarentena
            </button>
            <button type="button" id="btnBatchDelete" onclick="executeBatchDelete()" disabled style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.35); color: #ef4444; padding: 8px 14px; border-radius: 6px; font-size: 12.5px; font-weight: 700; cursor: not-allowed; display: inline-flex; align-items: center; gap: 6px; opacity: 0.45; transition: all 0.2s ease;" title="Excluir do catálogo todos os produtos selecionados">
                <i class="fas fa-trash-alt"></i> 🗑️ Excluir Selecionados
            </button>
        </div>
    </div>

    <!-- Products Catalog Grid -->
    <?php if (empty($products)): ?>
        <div style="background: var(--bg-panel, #1d2327); border: 1px dashed var(--border, rgba(255,255,255,0.15)); border-radius: 8px; padding: 60px 20px; text-align: center;">
            <i class="fas fa-box-open" style="font-size: 48px; color: var(--text-muted, #94a3b8); margin-bottom: 16px;"></i>
            <h3 style="margin: 0 0 8px 0; color: #fff; font-size: 18px;">Nenhum produto minerado ainda</h3>
            <p style="margin: 0 0 20px 0; color: var(--text-muted, #94a3b8); font-size: 13px; max-width: 480px; margin-left: auto; margin-right: auto;">
                Utilize o botão de "Mineração 1-Click" acima ou importe diretamente via URL do AliExpress para abastecer sua vitrine de dropshipping.
            </p>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 18px; margin-bottom: 30px;">
            <?php foreach ($products as $prod): ?>
                <?php $isProdLive = ($prod->status === 'published'); ?>
                <div data-product-id="<?= (int)$prod->id ?>" id="prod-card-<?= (int)$prod->id ?>" style="background: var(--bg-panel, #1d2327); border: 1.5px solid <?= $isProdLive ? 'rgba(16, 185, 129, 0.35)' : 'rgba(245, 158, 11, 0.35)' ?>; border-radius: 8px; overflow: hidden; display: flex; flex-direction: column; transition: all 0.2s ease; position: relative;">
                    <!-- Image with badges -->
                    <div style="position: relative; width: 100%; height: 200px; background: #0b0e11; overflow: hidden;">
                        <!-- Checkbox Individual de Seleção do Card -->
                        <div style="position: absolute; top: 10px; left: 10px; z-index: 10;">
                            <input type="checkbox" class="prod-card-checkbox" id="card-check-<?= (int)$prod->id ?>" data-product-id="<?= (int)$prod->id ?>" onchange="onCardCheckboxChange(<?= (int)$prod->id ?>)" style="width: 20px; height: 20px; accent-color: #45f3ff; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.8); border-radius: 4px;">
                        </div>

                        <?php if (!empty($prod->mainImageUrl)): ?>
                            <img id="prod-main-img-<?= (int)($prod->id ?? 0) ?>" src="<?= htmlspecialchars($prod->mainImageUrl) ?>" alt="<?= htmlspecialchars($prod->title) ?>" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1526738549149-8e07eca6c147?w=600&auto=format&fit=crop&q=80';" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <div id="prod-main-img-placeholder-<?= (int)($prod->id ?? 0) ?>" style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: var(--text-muted, #94a3b8);">
                                <i class="fas fa-image" style="font-size: 32px;"></i>
                            </div>
                        <?php endif; ?>

                        <?php 
                        $isNational = (!empty($prod->attributes['origem_brasil']) || (isset($prod->attributes['ship_from']) && $prod->attributes['ship_from'] === 'BR'));
                        $isChina = (!empty($prod->attributes['origem_china']) || (isset($prod->attributes['ship_from']) && $prod->attributes['ship_from'] === 'CN'));
                        $prodVideoUrl = !empty($prod->attributes['video_url']) ? (string)$prod->attributes['video_url'] : '';
                        if ($prodVideoUrl === '' && !empty($prod->description) && preg_match('/<source\s+src="([^"]+\.mp4[^"]*)"/i', $prod->description, $mVm)) {
                            $prodVideoUrl = $mVm[1];
                        }
                        ?>

                        <div id="prod-category-badge-<?= (int)$prod->id ?>" style="position: absolute; top: 10px; left: 38px; background: rgba(0,0,0,0.75); backdrop-filter: blur(4px); color: #F0D060; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; z-index: 2; box-shadow: 0 2px 6px rgba(0,0,0,0.4);" title="Categoria: <?= htmlspecialchars($prod->category ?: 'Dropship') ?>">
                            <i class="fas fa-tag" style="color: #fbbf24; font-size: 10px;"></i>
                            <span id="prod-category-text-<?= (int)$prod->id ?>"><?= htmlspecialchars($prod->category ?: 'Dropship') ?></span>
                        </div>

                        <?php if (!empty($prodVideoUrl)): ?>
                            <div style="position: absolute; top: 38px; left: 10px; background: rgba(147, 51, 234, 0.9); backdrop-filter: blur(4px); color: #fff; padding: 2px 7px; border-radius: 4px; font-size: 10px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.4); z-index: 2;" title="Vídeo demonstrativo em alta definição disponível">
                                <i class="fas fa-play-circle"></i> Vídeo HD
                            </div>
                        <?php endif; ?>

                        <?php $galleryCount = !empty($prod->images) ? count($prod->images) : (!empty($prod->mainImageUrl) ? 1 : 0); ?>
                        <div id="prod-imgs-badge-<?= (int)($prod->id ?? 0) ?>" style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); color: #45f3ff; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; display: <?= $galleryCount > 1 ? 'flex' : 'none' ?>; align-items: center; gap: 4px;">
                            <i class="fas fa-images"></i> <span id="prod-imgs-count-<?= (int)($prod->id ?? 0) ?>"><?= $galleryCount ?></span> fotos
                        </div>

                        <!-- Badge de Status: No Ar vs Em Revisão -->
                        <div id="prod-status-badge-<?= (int)$prod->id ?>" style="position: absolute; bottom: 10px; left: 10px; background: <?= $isProdLive ? 'rgba(16, 185, 129, 0.95)' : 'rgba(245, 158, 11, 0.95)' ?>; backdrop-filter: blur(4px); color: <?= $isProdLive ? '#fff' : '#000' ?>; padding: 2.5px 8px; border-radius: 4px; font-size: 10.5px; font-weight: 800; display: flex; align-items: center; gap: 4px; z-index: 2; box-shadow: 0 2px 6px rgba(0,0,0,0.4);" title="Status no Site: <?= $isProdLive ? 'Ativo na Vitrine Pública' : 'Em Quarentena / Aguardando Aprovação Manual' ?>">
                            <?= $isProdLive ? '<i class="fas fa-check-circle"></i> ✅ No Ar' : '<i class="fas fa-hourglass-half"></i> ⏳ Em Revisão' ?>
                        </div>

                        <?php if ($prod->rating > 0): ?>
                            <div style="position: absolute; bottom: 10px; left: 95px; background: rgba(0,0,0,0.75); color: #fbbf24; padding: 3px 7px; border-radius: 4px; font-size: 10.5px; font-weight: 600; display: flex; align-items: center; gap: 4px; z-index: 2;">
                                <i class="fas fa-star"></i> <?= number_format($prod->rating, 1) ?>
                            </div>
                        <?php endif; ?>

                        <div id="prod-origin-badge-<?= (int)$prod->id ?>" style="position: absolute; bottom: 10px; right: 10px; background: <?= $isNational ? 'rgba(16, 185, 129, 0.9)' : ($isChina ? 'rgba(59, 130, 246, 0.9)' : 'rgba(100, 116, 139, 0.9)') ?>; backdrop-filter: blur(4px); color: #fff; padding: 2px 7px; border-radius: 4px; font-size: 10px; font-weight: 700; display: flex; align-items: center; gap: 4px; z-index: 2;" title="Procedência e Entreposto Físico">
                            <?= $isNational ? '<span>🇧🇷</span> BR' : ($isChina ? '<span>🇨🇳</span> CN' : '<span>🌐</span> GLOBAL') ?>
                        </div>
                    </div>
                    </div>

                    <!-- Content -->
                    <div style="padding: 16px; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <?php if (!empty($prod->attributes['store_name'])): ?>
                                <div style="font-size: 11px; color: #a78bfa; margin-bottom: 6px; display: flex; align-items: center; gap: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <i class="fas fa-store" style="font-size: 10px;"></i>
                                    <?php if (!empty($prod->attributes['store_url'])): ?>
                                        <a href="<?= htmlspecialchars($prod->attributes['store_url']) ?>" target="_blank" rel="noopener noreferrer" style="color: #a78bfa; text-decoration: none; font-weight: 500;" title="Abrir loja do vendedor no AliExpress"><?= htmlspecialchars($prod->attributes['store_name']) ?></a>
                                    <?php else: ?>
                                        <span style="font-weight: 500;"><?= htmlspecialchars($prod->attributes['store_name']) ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Controles Rápidos no Card: Categoria e Procedência (Origem) -->
                            <div style="margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;">
                                <div style="display: flex; align-items: center; gap: 4px;">
                                    <span style="font-size: 10px; color: #94a3b8; font-weight: 700; text-transform: uppercase;" title="Clique para alterar a categoria deste produto">
                                        <i class="fas fa-tag" style="color: #fbbf24;"></i> Cat:
                                    </span>
                                    <select class="admin-prod-category-select" id="prod-category-select-<?= (int)$prod->id ?>" data-product-id="<?= (int)$prod->id ?>" onchange="updateProductCategory(<?= (int)$prod->id ?>, this.value)" style="background: rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.18); color: #F0D060; font-size: 11px; font-weight: 600; padding: 2px 6px; border-radius: 4px; outline: none; cursor: pointer; max-width: 140px; text-overflow: ellipsis; white-space: nowrap;">
                                        <?php 
                                        $officialCommercialCats = \DomainSystem\Plugins\domain_drop\Services\CategoryTaxonomyService::getCommercialCategories();
                                        $availableCats = !empty($categories) ? array_unique(array_merge($categories, $officialCommercialCats)) : $officialCommercialCats;
                                        foreach ($availableCats as $catOpt): 
                                        ?>
                                            <option value="<?= htmlspecialchars($catOpt) ?>" <?= ($prod->category === $catOpt) ? 'selected' : '' ?> style="background: #1d2327; color: #fff;">
                                                <?= htmlspecialchars($catOpt) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <?php $prodOrigin = $isNational ? 'BR' : ($isChina ? 'CN' : 'GLOBAL'); ?>
                                <div style="display: flex; align-items: center; gap: 4px;">
                                    <span style="font-size: 10px; color: #94a3b8; font-weight: 700; text-transform: uppercase;" title="Clique para alterar a procedência/entreposto deste produto (Brasil vs China)">
                                        <i class="fas fa-map-marker-alt" style="color: #60a5fa;"></i> Origem:
                                    </span>
                                    <select class="admin-prod-origin-select" id="prod-origin-select-<?= (int)$prod->id ?>" data-product-id="<?= (int)$prod->id ?>" onchange="updateProductOrigin(<?= (int)$prod->id ?>, this.value)" style="background: rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.18); color: <?= $prodOrigin === 'BR' ? '#34d399' : ($prodOrigin === 'CN' ? '#60a5fa' : '#cbd5e1') ?>; font-size: 11px; font-weight: 700; padding: 2px 6px; border-radius: 4px; outline: none; cursor: pointer;">
                                        <option value="BR" <?= $prodOrigin === 'BR' ? 'selected' : '' ?> style="background: #1d2327; color: #34d399;">🇧🇷 Brasil</option>
                                        <option value="CN" <?= $prodOrigin === 'CN' ? 'selected' : '' ?> style="background: #1d2327; color: #60a5fa;">🇨🇳 China</option>
                                        <option value="GLOBAL" <?= $prodOrigin === 'GLOBAL' ? 'selected' : '' ?> style="background: #1d2327; color: #cbd5e1;">🌐 Global</option>
                                    </select>
                                </div>
                            </div>

                            <h4 style="margin: 0 0 10px 0; font-size: 14px; font-weight: 600; color: #fff; line-height: 1.4; max-height: 2.8em; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;" title="<?= htmlspecialchars($prod->title) ?>">
                                <?= htmlspecialchars($prod->title) ?>
                            </h4>

                            <!-- Price Breakdown -->
                            <div style="background: rgba(0,0,0,0.25); border-radius: 6px; padding: 10px 12px; margin-bottom: 12px;">
                                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 4px;">
                                    <span style="font-size: 11px; color: var(--text-muted, #94a3b8);">Custo (USD):</span>
                                    <span id="prod-cost-usd-<?= (int)$prod->id ?>" style="font-size: 12px; color: #94a3b8; font-family: monospace;">$<?= number_format($prod->costPriceUsd, 2) ?></span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 4px;">
                                    <span style="font-size: 11px; color: var(--text-muted, #94a3b8);">Custo Convertido:</span>
                                    <span id="prod-cost-brl-<?= (int)$prod->id ?>" style="font-size: 12px; color: #94a3b8; font-family: monospace;">R$ <?= number_format($prod->costPriceUsd * $rate, 2, ',', '.') ?></span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: baseline; border-top: 1px dashed rgba(255,255,255,0.1); padding-top: 6px; margin-top: 4px;">
                                    <span style="font-size: 12px; font-weight: 600; color: #5cd67d;">Venda Sugerida:</span>
                                    <span id="prod-sale-brl-<?= (int)$prod->id ?>" style="font-size: 16px; font-weight: 700; color: #5cd67d; font-family: monospace;">R$ <?= number_format($prod->salePriceBrl, 2, ',', '.') ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Actions -->
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.06); gap: 6px;">
                            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                <a href="<?= $baseUrl ?>/produto/<?= urlencode($prod->slug) ?>" target="_blank" style="height: 26px; padding: 0 8px; font-size: 11px; font-weight: 600; color: #45f3ff; background: rgba(69, 243, 255, 0.08); border: 1px solid rgba(69, 243, 255, 0.25); border-radius: 4px; display: inline-flex; align-items: center; gap: 4px; text-decoration: none; box-sizing: border-box; line-height: 1;" title="Visualizar na loja">
                                    <i class="fas fa-eye"></i> Loja
                                </a>
                                <?php
                                $directAeUrl = !empty($prod->attributes['aliexpress_url']) ? $prod->attributes['aliexpress_url'] : '';
                                $isRealAeId = !empty($prod->aliexpressId) && preg_match('/^\d{8,24}$/', (string)$prod->aliexpressId);
                                if (empty($directAeUrl) && $isRealAeId) {
                                    $directAeUrl = 'https://pt.aliexpress.com/item/' . htmlspecialchars((string)$prod->aliexpressId) . '.html?country=BR&locale=pt_BR&currency=BRL';
                                }
                                $aeLink = !empty($directAeUrl)
                                    ? $directAeUrl
                                    : 'https://www.aliexpress.com/wholesale?SearchText=' . urlencode($prod->title);
                                ?>
                                <a href="<?= $aeLink ?>" target="_blank" rel="noopener noreferrer" style="height: 26px; padding: 0 8px; font-size: 11px; font-weight: 600; color: #ff6b6b; background: rgba(255, 107, 107, 0.08); border: 1px solid rgba(255, 107, 107, 0.25); border-radius: 4px; display: inline-flex; align-items: center; gap: 4px; text-decoration: none; box-sizing: border-box; line-height: 1;" title="<?= !empty($directAeUrl) ? 'Abrir anúncio original do vendedor no AliExpress' : 'Buscar fornecedores deste item no AliExpress' ?>">
                                    <i class="fas fa-external-link-alt"></i> Link
                                </a>
                                <?php if ($prod->id !== null): ?>
                                    <button type="button" id="prod-video-btn-<?= (int)$prod->id ?>" onclick="downloadCardVideo(<?= (int)$prod->id ?>, event)" style="height: 26px; padding: 0 8px; font-size: 11px; font-weight: 700; color: #c084fc; background: rgba(168, 85, 247, 0.15); border: 1px solid rgba(168, 85, 247, 0.4); border-radius: 4px; display: <?= !empty($prodVideoUrl) ? 'inline-flex' : 'none' ?>; align-items: center; gap: 4px; cursor: pointer; box-sizing: border-box; line-height: 1;" title="Baixar Vídeo MP4 do fornecedor para anúncios no TikTok, Reels e WhatsApp">
                                        <i class="fas fa-video"></i> Vídeo
                                    </button>
                                    <button type="button" onclick="openPriceModal(<?= (int)$prod->id ?>, <?= htmlspecialchars(json_encode($prod->title)) ?>, <?= (float)$prod->costPriceUsd ?>, <?= (float)round($prod->costPriceUsd * $rate, 2) ?>, <?= (float)$prod->salePriceBrl ?>)" style="height: 26px; padding: 0 8px; font-size: 11px; font-weight: 600; color: #fbbf24; background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.35); border-radius: 4px; display: inline-flex; align-items: center; gap: 4px; cursor: pointer; box-sizing: border-box; line-height: 1;" title="Editar Custo e Preço de Venda">
                                        <i class="fas fa-tags"></i> Preço
                                    </button>
                                    <button type="button" onclick="openImagesModal(<?= (int)$prod->id ?>)" style="height: 26px; padding: 0 8px; font-size: 11px; font-weight: 600; color: #45f3ff; background: rgba(69, 243, 255, 0.08); border: 1px solid rgba(69, 243, 255, 0.25); border-radius: 4px; display: inline-flex; align-items: center; gap: 4px; cursor: pointer; box-sizing: border-box; line-height: 1;" title="Visualizar, editar e sincronizar imagens do produto">
                                        <i class="fas fa-images"></i> Imagens
                                    </button>
                                    <button type="button" onclick="openProductConfigModal(<?= (int)$prod->id ?>)" style="height: 26px; padding: 0 8px; font-size: 11px; font-weight: 600; color: #c084fc; background: rgba(192, 132, 252, 0.12); border: 1px solid rgba(192, 132, 252, 0.35); border-radius: 4px; display: inline-flex; align-items: center; gap: 4px; cursor: pointer; box-sizing: border-box; line-height: 1;" title="Configurações do Card: Tamanhos, Cores, Frete e Fornecedor">
                                        <i class="fas fa-cog"></i> ⚙️ Ajustes
                                    </button>
                                <?php endif; ?>
                            </div>

                            <?php if ($prod->id !== null): ?>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <button type="button" id="prod-toggle-status-btn-<?= (int)$prod->id ?>" onclick="toggleProductLiveStatus(<?= (int)$prod->id ?>, '<?= $isProdLive ? 'draft' : 'published' ?>')" style="height: 26px; padding: 0 9px; font-size: 11px; font-weight: 700; color: <?= $isProdLive ? '#fbbf24' : '#34d399' ?>; background: <?= $isProdLive ? 'rgba(245, 158, 11, 0.12)' : 'rgba(16, 185, 129, 0.15)' ?>; border: 1px solid <?= $isProdLive ? 'rgba(245, 158, 11, 0.35)' : 'rgba(16, 185, 129, 0.4)' ?>; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px; cursor: pointer; box-sizing: border-box; line-height: 1; transition: all 0.2s ease;" title="<?= $isProdLive ? 'Ocultar da vitrine e mover para Quarentena' : 'Aprovar e Carregar este produto para o Site' ?>">
                                        <i class="fas <?= $isProdLive ? 'fa-pause-circle' : 'fa-check-circle' ?>"></i> <?= $isProdLive ? '⏸️ Ocultar' : '✅ Carregar' ?>
                                    </button>
                                    <button type="button" onclick="openDeleteProductModal(<?= (int)$prod->id ?>, <?= htmlspecialchars(json_encode($prod->title), ENT_QUOTES, 'UTF-8') ?>)" style="height: 26px; padding: 0 8px; font-size: 11px; font-weight: 600; color: #ef4444; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.25); border-radius: 4px; display: inline-flex; align-items: center; gap: 4px; cursor: pointer; box-sizing: border-box; line-height: 1;" title="Excluir produto do catálogo">
                                        <i class="fas fa-trash-alt"></i> Excluir
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <?php
            $adminQueryParams = $_GET;
            $buildAdminPageUrl = function(int $p) use ($baseUrl, $adminQueryParams): string {
                $params = $adminQueryParams;
                $params['page'] = $p;
                return $baseUrl . '/admin/dropship?' . http_build_query($params);
            };
            ?>
            <div style="display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 32px; margin-bottom: 24px; flex-wrap: wrap;">
                <?php if ($page > 1): ?>
                    <a href="<?= htmlspecialchars($buildAdminPageUrl($page - 1)) ?>" style="padding: 9px 16px; background: rgba(255,255,255,0.06); border: 1px solid var(--border, rgba(255,255,255,0.15)); border-radius: 6px; color: #fff; text-decoration: none; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fas fa-chevron-left"></i> Anterior
                    </a>
                <?php endif; ?>

                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <?php if ($p === 1 || $p === $totalPages || abs($p - $page) <= 2): ?>
                        <?php if ($p === $page): ?>
                            <span style="padding: 9px 16px; background: linear-gradient(135deg, #ff4747 0%, #dc2626 100%); border: 1px solid #ff4747; border-radius: 6px; color: #fff; font-size: 13px; font-weight: 700; box-shadow: 0 4px 12px rgba(255, 71, 71, 0.3);">
                                <?= $p ?>
                            </span>
                        <?php else: ?>
                            <a href="<?= htmlspecialchars($buildAdminPageUrl($p)) ?>" style="padding: 9px 16px; background: rgba(255,255,255,0.06); border: 1px solid var(--border, rgba(255,255,255,0.15)); border-radius: 6px; color: #cbd5e1; text-decoration: none; font-size: 13px; font-weight: 600;">
                                <?= $p ?>
                            </a>
                        <?php endif; ?>
                    <?php elseif ($p === 2 && $page > 4): ?>
                        <span style="color: #64748b; padding: 0 4px;">...</span>
                    <?php elseif ($p === $totalPages - 1 && $page < $totalPages - 3): ?>
                        <span style="color: #64748b; padding: 0 4px;">...</span>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="<?= htmlspecialchars($buildAdminPageUrl($page + 1)) ?>" style="padding: 9px 16px; background: rgba(255,255,255,0.06); border: 1px solid var(--border, rgba(255,255,255,0.15)); border-radius: 6px; color: #fff; text-decoration: none; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                        Próxima <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Modal de Edição de Preço (Clean / Responsivo / Tempo Real) -->
    <div id="priceEditModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(4px); z-index: 9999; justify-content: center; align-items: center; padding: 20px;">
        <div style="background: #111722; border: 1px solid rgba(245, 158, 11, 0.35); border-radius: 12px; max-width: 520px; width: 100%; box-shadow: 0 20px 50px rgba(0,0,0,0.8); overflow: hidden; animation: dsModalIn 0.2s ease;">
            
            <div style="background: rgba(245, 158, 11, 0.08); border-bottom: 1px solid rgba(255,255,255,0.08); padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="background: #f59e0b; color: #000; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div>
                        <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #fff;">Ajuste de Preço e Margem</h3>
                        <div id="modalProductTitle" style="font-size: 12px; color: var(--text-muted, #94a3b8); max-width: 380px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"></div>
                    </div>
                </div>
                <button type="button" onclick="closePriceModal()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer; padding: 4px;">&times;</button>
            </div>

            <form id="priceEditForm" method="POST" action="" onsubmit="handlePriceSubmit(event)" style="padding: 24px;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" id="modalProductId" name="product_id" value="">

                <!-- Câmbio de Referência -->
                <div style="background: rgba(69, 243, 255, 0.06); border: 1px solid rgba(69, 243, 255, 0.2); border-radius: 8px; padding: 10px 14px; margin-bottom: 18px; display: flex; justify-content: space-between; align-items: center; font-size: 12px;">
                    <span style="color: #94a3b8;"><i class="fas fa-exchange-alt"></i> Câmbio Atual do Sistema:</span>
                    <strong style="color: #45f3ff;">USD 1,00 = R$ <?= number_format($rate, 2, ',', '.') ?></strong>
                </div>

                <!-- Input Custo Fornecedor (BRL) -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #fff; margin-bottom: 6px;">
                        Custo do Fornecedor (R$ na Loja do AliExpress):
                    </label>
                    <div style="display: flex; align-items: stretch; border: 1px solid rgba(255,255,255,0.18); border-radius: 8px; overflow: hidden; background: rgba(0,0,0,0.35); box-shadow: inset 0 2px 4px rgba(0,0,0,0.3);">
                        <span style="display: inline-flex; align-items: center; justify-content: center; padding: 0 16px; background: rgba(255,255,255,0.08); color: #94a3b8; font-weight: 700; font-size: 14px; border-right: 1px solid rgba(255,255,255,0.12); user-select: none;">
                            R$
                        </span>
                        <input type="number" step="0.01" min="0" id="modalCostBrl" name="cost_price_brl" oninput="recalcMargin()" required style="flex: 1; border: none; background: transparent; padding: 12px 14px; color: #fff; font-size: 16px; font-weight: 700; outline: none; box-sizing: border-box; font-family: monospace;">
                    </div>
                    <div style="font-size: 11px; color: #94a3b8; margin-top: 5px; display: flex; justify-content: space-between;">
                        <span>Equivalente em Dólar: <strong id="modalCostUsdHint" style="color: #fff; font-family: monospace;">$0.00</strong></span>
                        <span style="color: #f59e0b;">Atualize caso o produto esteja em promoção no AliExpress</span>
                    </div>
                </div>

                <!-- Atalhos de Markup Rápido -->
                <div style="margin-bottom: 16px;">
                    <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; margin-bottom: 6px; font-weight: 600;">
                        Atalhos de Margem Automática sobre o Custo:
                    </div>
                    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                        <button type="button" onclick="applyMarkup(30)" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: #fff; padding: 4px 10px; border-radius: 4px; font-size: 11px; cursor: pointer;">+30%</button>
                        <button type="button" onclick="applyMarkup(50)" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: #fff; padding: 4px 10px; border-radius: 4px; font-size: 11px; cursor: pointer;">+50%</button>
                        <button type="button" onclick="applyMarkup(75)" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: #fff; padding: 4px 10px; border-radius: 4px; font-size: 11px; cursor: pointer;">+75%</button>
                        <button type="button" onclick="applyMarkup(100)" style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.4); color: #fbbf24; padding: 4px 10px; border-radius: 4px; font-size: 11px; cursor: pointer; font-weight: 700;">+100% (2x Custo)</button>
                        <button type="button" onclick="roundTo90()" style="background: rgba(69, 243, 255, 0.1); border: 1px solid rgba(69, 243, 255, 0.3); color: #45f3ff; padding: 4px 10px; border-radius: 4px; font-size: 11px; cursor: pointer;">Terminar em ,90</button>
                    </div>
                </div>

                <!-- Input Preço de Venda Final (BRL) -->
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #5cd67d; margin-bottom: 6px;">
                        Preço de Venda na Loja RachidD.com (R$):
                    </label>
                    <div style="display: flex; align-items: stretch; border: 2px solid rgba(92, 214, 125, 0.45); border-radius: 8px; overflow: hidden; background: rgba(0,0,0,0.35); box-shadow: inset 0 2px 4px rgba(0,0,0,0.3);">
                        <span style="display: inline-flex; align-items: center; justify-content: center; padding: 0 16px; background: rgba(92, 214, 125, 0.12); color: #5cd67d; font-weight: 800; font-size: 15px; border-right: 1px solid rgba(92, 214, 125, 0.3); user-select: none;">
                            R$
                        </span>
                        <input type="number" step="0.01" min="0.01" id="modalSaleBrl" name="sale_price_brl" oninput="recalcMargin()" required style="flex: 1; border: none; background: transparent; padding: 12px 14px; color: #5cd67d; font-size: 18px; font-weight: 800; outline: none; box-sizing: border-box; font-family: monospace;">
                    </div>
                </div>

                <!-- Painel de Lucratividade em Tempo Real -->
                <div style="background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 14px; margin-bottom: 22px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; text-align: center;">
                        <div style="border-right: 1px solid rgba(255,255,255,0.08); padding-right: 12px;">
                            <div style="font-size: 11px; color: #94a3b8; text-transform: uppercase;">Lucro Bruto por Venda</div>
                            <div id="modalProfitBrl" style="font-size: 18px; font-weight: 800; color: #5cd67d; margin-top: 2px;">R$ 0,00</div>
                        </div>
                        <div>
                            <div style="font-size: 11px; color: #94a3b8; text-transform: uppercase;">Margem Real</div>
                            <div id="modalMarginPercent" style="font-size: 18px; font-weight: 800; color: #45f3ff; margin-top: 2px;">0%</div>
                        </div>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closePriceModal()" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15); color: #fff; padding: 10px 18px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;">
                        Cancelar
                    </button>
                    <button type="submit" id="btnSavePrice" style="background: linear-gradient(135deg, #f59e0b, #d97706); border: none; color: #000; padding: 10px 22px; border-radius: 6px; font-size: 13px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                        <i class="fas fa-check"></i> Salvar Preço
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de Galeria e Sincronização de Imagens -->
    <div id="imagesEditModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.78); backdrop-filter: blur(4px); z-index: 9999; justify-content: center; align-items: center; padding: 20px;">
        <div style="background: #111722; border: 1px solid rgba(69, 243, 255, 0.35); border-radius: 12px; max-width: 720px; width: 100%; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 20px 50px rgba(0,0,0,0.85); overflow: hidden; animation: dsModalIn 0.2s ease;">
            <div style="background: rgba(69, 243, 255, 0.08); border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 22px; display: flex; justify-content: space-between; align-items: center; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                    <div style="background: #45f3ff; color: #0b0f17; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0;">
                        <i class="fas fa-images"></i>
                    </div>
                    <div style="min-width: 0;">
                        <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #fff;">Galeria &amp; Imagens do Produto</h3>
                        <div id="imagesModalTitle" style="font-size: 12px; color: var(--text-muted, #94a3b8); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"></div>
                    </div>
                </div>
                <button type="button" onclick="closeImagesModal()" style="background: none; border: none; color: #94a3b8; font-size: 22px; cursor: pointer; padding: 4px;">&times;</button>
            </div>

            <div style="padding: 20px 22px; overflow-y: auto; flex: 1;">
                <div id="imagesModalStatus" style="display: none; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 14px;"></div>

                <div style="display: flex; flex-wrap: wrap; gap: 8px; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                    <div style="font-size: 12px; color: #94a3b8;">
                        Clique em <strong style="color: #fbbf24;">Definir Capa</strong> para escolher a foto principal exibida na loja.
                    </div>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <a id="imagesModalAeLink" href="#" target="_blank" rel="noopener noreferrer" style="background: rgba(255,107,107,0.1); border: 1px solid rgba(255,107,107,0.3); color: #ff6b6b; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                            <i class="fas fa-external-link-alt"></i> Abrir no AliExpress
                        </a>
                        <button type="button" id="btnSyncImagesNow" onclick="syncImagesNow()" style="background: rgba(69, 243, 255, 0.14); border: 1px solid rgba(69, 243, 255, 0.4); color: #45f3ff; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fas fa-sync-alt"></i> Sincronizar do AliExpress
                        </button>
                    </div>
                </div>

                <!-- Bloco de Vídeo Demonstrativo MP4 Oficial -->
                <div id="imagesModalVideoBox" style="margin-bottom: 16px; padding: 14px; background: rgba(147, 51, 234, 0.08); border: 1px solid rgba(147, 51, 234, 0.35); border-radius: 8px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 8px;">
                        <span style="font-size: 13px; font-weight: 700; color: #c084fc; display: flex; align-items: center; gap: 6px;">
                            <i class="fas fa-video"></i> Vídeo Demonstrativo do Produto (MP4)
                        </span>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <button type="button" id="imagesModalVideoDownloadBtn" onclick="downloadCurrentModalVideo(event)" style="display: none; background: linear-gradient(135deg, #a855f7, #7c3aed); border: none; color: #fff; padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer; align-items: center; gap: 6px; box-shadow: 0 2px 8px rgba(168, 85, 247, 0.3);">
                                <i class="fas fa-download"></i> Baixar Arquivo MP4
                            </button>
                            <button type="button" id="imagesModalVideoRemoveBtn" onclick="removeVideoUrl()" style="display: none; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; padding: 5px 10px; border-radius: 5px; font-size: 11px; font-weight: 600; cursor: pointer;">
                                Remover Vídeo
                            </button>
                        </div>
                    </div>
                    <div id="imagesModalVideoPlayerContainer" style="display: none; margin-bottom: 10px;">
                        <video id="imagesModalVideoPlayer" controls playsinline preload="metadata" style="width: 100%; max-height: 240px; border-radius: 6px; background: #000; display: block;"></video>
                    </div>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                        <input type="url" id="imagesModalVideoUrlInput" placeholder="URL do vídeo MP4 (ex: https://.../video.mp4)" style="flex: 1; min-width: 220px; background: rgba(0,0,0,0.35); border: 1px solid rgba(168, 85, 247, 0.3); color: #fff; padding: 8px 12px; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                        <button type="button" onclick="setManualVideoUrl()" style="background: rgba(168, 85, 247, 0.2); border: 1px solid rgba(168, 85, 247, 0.5); color: #d8b4fe; padding: 8px 14px; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer;">
                            <i class="fas fa-link"></i> Definir Vídeo
                        </button>
                    </div>
                </div>

                <div id="imagesModalGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(145px, 1fr)); gap: 12px; margin-bottom: 18px;"></div>

                <div style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 12px;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">
                        Adicionar nova foto por URL (https://...):
                    </label>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <input type="url" id="imagesModalNewUrl" placeholder="https://ae-pic-a1.aliexpress-media.com/kf/..." style="flex: 1; min-width: 220px; background: rgba(0,0,0,0.35); border: 1px solid rgba(255,255,255,0.16); color: #fff; padding: 8px 12px; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                        <button type="button" onclick="addManualImageUrl()" style="background: rgba(92,214,125,0.15); border: 1px solid rgba(92,214,125,0.4); color: #5cd67d; padding: 8px 14px; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer;">
                            <i class="fas fa-plus"></i> Adicionar
                        </button>
                    </div>
                </div>
            </div>

            <div style="border-top: 1px solid rgba(255,255,255,0.08); padding: 14px 22px; display: flex; justify-content: flex-end; gap: 10px; background: rgba(0,0,0,0.2);">
                <button type="button" onclick="closeImagesModal()" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15); color: #fff; padding: 9px 18px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;">
                    Fechar
                </button>
                <button type="button" id="btnSaveImagesGallery" onclick="handleImagesSave()" style="background: linear-gradient(135deg, #45f3ff, #0ea5e9); border: none; color: #081018; padding: 9px 20px; border-radius: 6px; font-size: 13px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fas fa-save"></i> Salvar Galeria
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de Sincronização em Lote de Imagens (Fila Assíncrona com Barra de Status em Tempo Real) -->
    <div id="batchSyncModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(5px); z-index: 10000; justify-content: center; align-items: center; padding: 20px;">
        <div style="background: #0f172a; border: 1px solid rgba(69, 243, 255, 0.4); border-radius: 14px; max-width: 680px; width: 100%; box-shadow: 0 25px 60px rgba(0,0,0,0.9); overflow: hidden; animation: dsModalIn 0.25s ease; display: flex; flex-direction: column;">
            
            <!-- Header -->
            <div style="background: linear-gradient(135deg, rgba(69, 243, 255, 0.12) 0%, rgba(37, 99, 235, 0.18) 100%); border-bottom: 1px solid rgba(255,255,255,0.08); padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="background: linear-gradient(135deg, #45f3ff, #2563eb); color: #0f172a; width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 800; box-shadow: 0 4px 12px rgba(69, 243, 255, 0.35);">
                        <i class="fas fa-sync-alt" id="batchSyncHeaderIcon"></i>
                    </div>
                    <div>
                        <h3 style="margin: 0; font-size: 17px; font-weight: 700; color: #fff;">Sincronização de Imagens &amp; Ofertas do Dia (Black Friday)</h3>
                        <div style="font-size: 12px; color: #94a3b8;">Varredura completa com o AliExpress: fotos em alta definição e preços atualizados pela regra oficial 2x</div>
                    </div>
                </div>
                <button type="button" onclick="closeBatchSyncModal()" style="background: none; border: none; color: #94a3b8; font-size: 22px; cursor: pointer; padding: 4px; line-height: 1;">&times;</button>
            </div>

            <!-- Body -->
            <div style="padding: 22px 24px; display: flex; flex-direction: column; gap: 16px;">
                
                <!-- Modo de Sincronização -->
                <div id="batchSyncConfigSection" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 14px 16px;">
                    <div style="font-size: 12px; font-weight: 700; color: #cbd5e1; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px;">
                        Escolha o Escopo da Fila:
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; padding: 8px 10px; border-radius: 8px; background: rgba(69, 243, 255, 0.05); border: 1px solid rgba(69, 243, 255, 0.25);">
                            <input type="radio" name="batchSyncMode" value="incomplete" checked style="margin-top: 3px; accent-color: #45f3ff;">
                            <div>
                                <strong style="color: #45f3ff; font-size: 13px;">⚡ Apenas Galerias Incompletas (Recomendado)</strong>
                                <div style="font-size: 11.5px; color: #94a3b8; margin-top: 2px;">
                                    Sincroniza apenas os produtos com 1 foto ou vazios (<strong id="batchCountSingle" style="color:#fff;">carregando...</strong>). Pula os que já têm mais de 1 foto.
                                </div>
                            </div>
                        </label>
                        <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; padding: 8px 10px; border-radius: 8px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08);">
                            <input type="radio" name="batchSyncMode" value="all" style="margin-top: 3px; accent-color: #45f3ff;">
                            <div>
                                <strong style="color: #cbd5e1; font-size: 13px;">🔥 Todos os Produtos do Catálogo (Forçar Varredura Geral)</strong>
                                <div style="font-size: 11.5px; color: #94a3b8; margin-top: 2px;">
                                    Varre todos os produtos do catálogo (<strong id="batchCountTotal" style="color:#fff;">carregando...</strong>), buscando galerias oficiais completas.
                                </div>
                            </div>
                        </label>
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 10px 12px; border-radius: 8px; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.3); margin-top: 4px;">
                            <input type="checkbox" id="batchSyncAutoPurgeDead" checked style="width: 16px; height: 16px; accent-color: #ef4444;">
                            <div>
                                <strong style="color: #fca5a5; font-size: 13px;">🗑️ Auto-excluir cards com erro / "Página Não Encontrada" (Limpeza Automática)</strong>
                                <div style="font-size: 11.5px; color: #94a3b8; margin-top: 2px;">
                                    Exclui automaticamente do catálogo os produtos sem fornecedor ativo no AliExpress, links desativados ou com falha persistente de imagens.
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Painel de Contadores de Status -->
                <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px;">
                    <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 10px; text-align: center;">
                        <div style="font-size: 11px; color: #94a3b8; text-transform: uppercase;">Total Fila</div>
                        <div id="batchStatTotal" style="font-size: 20px; font-weight: 800; color: #fff; font-family: monospace; margin-top: 2px;">0</div>
                    </div>
                    <div style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 8px; padding: 10px; text-align: center;">
                        <div style="font-size: 11px; color: #6ee7b7; text-transform: uppercase;">Atualizados</div>
                        <div id="batchStatSynced" style="font-size: 20px; font-weight: 800; color: #10b981; font-family: monospace; margin-top: 2px;">0</div>
                    </div>
                    <div style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.25); border-radius: 8px; padding: 10px; text-align: center;">
                        <div style="font-size: 11px; color: #fcd34d; text-transform: uppercase;">Preservados</div>
                        <div id="batchStatSkipped" style="font-size: 20px; font-weight: 800; color: #f59e0b; font-family: monospace; margin-top: 2px;">0</div>
                    </div>
                    <div style="background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.25); border-radius: 8px; padding: 10px; text-align: center;">
                        <div style="font-size: 11px; color: #fca5a5; text-transform: uppercase;">Excluídos (404)</div>
                        <div id="batchStatPurged" style="font-size: 20px; font-weight: 800; color: #ef4444; font-family: monospace; margin-top: 2px;">0</div>
                    </div>
                    <div style="background: rgba(148, 163, 184, 0.08); border: 1px solid rgba(148, 163, 184, 0.25); border-radius: 8px; padding: 10px; text-align: center;">
                        <div style="font-size: 11px; color: #cbd5e1; text-transform: uppercase;">Falhas Rede</div>
                        <div id="batchStatFailed" style="font-size: 20px; font-weight: 800; color: #94a3b8; font-family: monospace; margin-top: 2px;">0</div>
                    </div>
                </div>

                <!-- Barra de Progresso Animada -->
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <span id="batchProgressLabel" style="font-size: 12.5px; font-weight: 600; color: #cbd5e1;">Aguardando início...</span>
                        <strong id="batchProgressPercent" style="font-size: 13px; font-weight: 800; color: #45f3ff; font-family: monospace;">0%</strong>
                    </div>
                    <div style="width: 100%; height: 14px; background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; overflow: hidden; position: relative;">
                        <div id="batchProgressBar" style="width: 0%; height: 100%; background: linear-gradient(90deg, #0ea5e9, #45f3ff, #10b981); border-radius: 10px; transition: width 0.3s ease; box-shadow: 0 0 12px rgba(69, 243, 255, 0.5);"></div>
                    </div>
                </div>

                <!-- Console de Logs em Tempo Real -->
                <div style="background: #020617; border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 12px 14px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">Log de Execução em Tempo Real:</span>
                        <span id="batchSyncStatusBadge" style="font-size: 10.5px; color: #94a3b8; font-family: monospace;">Pronto</span>
                    </div>
                    <div id="batchSyncLogConsole" style="max-height: 180px; min-height: 110px; overflow-y: auto; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 11.5px; line-height: 1.55; color: #cbd5e1; display: flex; flex-direction: column; gap: 3px;">
                        <div style="color: #64748b;">[Status] Clique em "Iniciar Sincronização" para iniciar a fila de processamento produto por produto.</div>
                    </div>
                </div>

            </div>

            <!-- Footer -->
            <div style="border-top: 1px solid rgba(255,255,255,0.08); padding: 14px 24px; display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.25);">
                <button type="button" id="btnBatchSyncCancel" onclick="cancelBatchSync()" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15); color: #94a3b8; padding: 9px 18px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;">
                    Cancelar / Fechar
                </button>
                <div style="display: flex; gap: 10px;">
                    <button type="button" id="btnBatchSyncAction" onclick="startBatchSync()" style="background: linear-gradient(135deg, #45f3ff, #0ea5e9); border: none; color: #081018; padding: 9px 22px; border-radius: 6px; font-size: 13px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(69, 243, 255, 0.3);">
                        <i class="fas fa-play"></i> Iniciar Sincronização
                    </button>
                    <button type="button" id="btnBatchSyncDone" onclick="finishAndReload()" style="display: none; background: linear-gradient(135deg, #10b981, #059669); border: none; color: #fff; padding: 9px 22px; border-radius: 6px; font-size: 13px; font-weight: 800; cursor: pointer; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);">
                        <i class="fas fa-check"></i> Concluir e Atualizar Tela
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal de Configurações do Card (Tamanhos, Cores, Frete e Fornecedor) -->
    <div id="modalProductConfig" style="position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(8px); display: none; align-items: center; justify-content: center; z-index: 10000; padding: 16px;">
        <div style="background: #0f172a; border: 1.5px solid rgba(192, 132, 252, 0.45); border-radius: 14px; width: 100%; max-width: 680px; max-height: 90vh; overflow-y: auto; display: flex; flex-direction: column; box-shadow: 0 25px 60px rgba(0,0,0,0.7), 0 0 35px rgba(168,85,247,0.2); animation: dsModalIn 0.2s ease;">
            
            <!-- Header -->
            <div style="padding: 18px 22px; border-bottom: 1px solid rgba(255,255,255,0.08); display: flex; justify-content: space-between; align-items: flex-start; background: rgba(168, 85, 247, 0.06);">
                <div style="flex: 1; padding-right: 14px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 18px;">⚙️</span>
                        <h3 style="margin: 0; color: #fff; font-size: 16px; font-weight: 700;">Ajustes de Variações, Tamanhos, Cores & Frete</h3>
                    </div>
                    <div id="configModalProductTitle" style="color: #cbd5e1; font-size: 13px; font-weight: 600; margin-top: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 560px;"></div>
                </div>
                <button type="button" onclick="closeProductConfigModal()" style="background: transparent; border: none; color: #94a3b8; font-size: 20px; cursor: pointer; padding: 4px 8px; line-height: 1;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94a3b8'">&times;</button>
            </div>

            <!-- Body -->
            <div style="padding: 20px 22px; display: flex; flex-direction: column; gap: 18px;">
                
                <!-- Status Message Banner -->
                <div id="configModalStatus" style="display: none; padding: 10px 14px; border-radius: 8px; font-size: 13px; font-weight: 600;"></div>

                <!-- Link do Fornecedor / Botão de Inspeção Original -->
                <div style="background: rgba(255, 107, 107, 0.08); border: 1px solid rgba(255, 107, 107, 0.3); border-radius: 8px; padding: 12px 14px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <div style="font-size: 12px; font-weight: 700; color: #ff8787;">
                            <i class="fas fa-search-plus"></i> Fornecedor Original no AliExpress
                        </div>
                        <div style="font-size: 11px; color: #cbd5e1; margin-top: 2px;">
                            Verifique os tamanhos e medidas descritas na página original do vendedor
                        </div>
                    </div>
                    <a id="configModalSupplierLink" href="#" target="_blank" rel="noopener noreferrer" style="background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff; text-decoration: none; padding: 6px 12px; border-radius: 6px; font-size: 11.5px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 8px rgba(239, 68, 68, 0.35);">
                        <i class="fas fa-external-link-alt"></i> Abrir Anúncio AliExpress ↗
                    </a>
                </div>

                <!-- 1. BLOCO DE TAMANHOS (TAGS) -->
                <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 14px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 6px;">
                        <span style="font-size: 13px; font-weight: 700; color: #facc15; display: flex; align-items: center; gap: 6px;">
                            <span>📏</span> <span>Tamanhos Disponíveis para o Comprador</span>
                        </span>
                        <span id="configSizesCountBadge" style="font-size: 11px; color: #cbd5e1; background: rgba(250, 204, 21, 0.15); border: 1px solid rgba(250, 204, 21, 0.3); padding: 1px 7px; border-radius: 10px; font-weight: 600;">0 tamanhos</span>
                    </div>
                    <div style="font-size: 11.5px; color: #94a3b8; margin-bottom: 10px;">
                        O cliente será obrigado a escolher um destes tamanhos na loja antes de comprar.
                    </div>

                    <!-- Tags Container -->
                    <div id="configSizesContainer" style="display: flex; flex-wrap: wrap; gap: 6px; min-height: 34px; padding: 8px; background: rgba(0,0,0,0.35); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; margin-bottom: 10px; align-items: center;">
                        <span style="color: #64748b; font-size: 12px; font-style: italic;">Nenhum tamanho cadastrado. Adicione abaixo.</span>
                    </div>

                    <!-- Input + Adicionar -->
                    <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                        <input type="text" id="configNewSizeInput" placeholder="Digite um tamanho e tecle Enter (ex: P, M, 42, G1)..." onkeydown="if(event.key==='Enter'){event.preventDefault();addConfigSizeTag();}" style="flex: 1; background: rgba(0,0,0,0.3); border: 1px solid rgba(250, 204, 21, 0.3); color: #fff; padding: 7px 12px; border-radius: 6px; font-size: 12.5px; outline: none;">
                        <button type="button" onclick="addConfigSizeTag()" style="background: rgba(250, 204, 21, 0.15); border: 1px solid rgba(250, 204, 21, 0.4); color: #facc15; padding: 7px 14px; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer;">
                            <i class="fas fa-plus"></i> Adicionar
                        </button>
                    </div>

                    <!-- Presets Rápidos de Tamanhos -->
                    <div style="display: flex; align-items: center; gap: 5px; flex-wrap: wrap;">
                        <span style="font-size: 10.5px; color: #94a3b8; font-weight: 600;">Atalhos rápidos:</span>
                        <button type="button" onclick="addConfigSizeTag('P')" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: #cbd5e1; padding: 2px 7px; border-radius: 4px; font-size: 11px; cursor: pointer;">+ P</button>
                        <button type="button" onclick="addConfigSizeTag('M')" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: #cbd5e1; padding: 2px 7px; border-radius: 4px; font-size: 11px; cursor: pointer;">+ M</button>
                        <button type="button" onclick="addConfigSizeTag('G')" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: #cbd5e1; padding: 2px 7px; border-radius: 4px; font-size: 11px; cursor: pointer;">+ G</button>
                        <button type="button" onclick="addConfigSizeTag('GG')" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: #cbd5e1; padding: 2px 7px; border-radius: 4px; font-size: 11px; cursor: pointer;">+ GG</button>
                        <button type="button" onclick="addConfigSizeTag('XG')" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: #cbd5e1; padding: 2px 7px; border-radius: 4px; font-size: 11px; cursor: pointer;">+ XG</button>
                        <button type="button" onclick="addConfigSizeTag('Tamanho Único')" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: #cbd5e1; padding: 2px 7px; border-radius: 4px; font-size: 11px; cursor: pointer;">+ Único</button>
                        <button type="button" onclick="addSizesBatch(['P','M','G','GG'])" style="background: rgba(250, 204, 21, 0.12); border: 1px solid rgba(250, 204, 21, 0.35); color: #facc15; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; cursor: pointer;">+ Grade P/M/G/GG</button>
                        <button type="button" onclick="addSizesBatch(['36','37','38','39','40','41','42'])" style="background: rgba(69, 243, 255, 0.12); border: 1px solid rgba(69, 243, 255, 0.35); color: #45f3ff; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; cursor: pointer;">+ Calçados 36-42</button>
                    </div>
                </div>

                <!-- 2. BLOCO DE CORES (TAGS) -->
                <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 14px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 6px;">
                        <span style="font-size: 13px; font-weight: 700; color: #c084fc; display: flex; align-items: center; gap: 6px;">
                            <span>🎨</span> <span>Cores Disponíveis para Escolha</span>
                        </span>
                        <span id="configColorsCountBadge" style="font-size: 11px; color: #cbd5e1; background: rgba(192, 132, 252, 0.15); border: 1px solid rgba(192, 132, 252, 0.3); padding: 1px 7px; border-radius: 10px; font-weight: 600;">0 cores</span>
                    </div>
                    <div style="font-size: 11.5px; color: #94a3b8; margin-bottom: 10px;">
                        Cada cor aparecerá como botão com swatch na página do produto.
                    </div>

                    <!-- Tags Container -->
                    <div id="configColorsContainer" style="display: flex; flex-wrap: wrap; gap: 6px; min-height: 34px; padding: 8px; background: rgba(0,0,0,0.35); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; margin-bottom: 10px; align-items: center;">
                        <span style="color: #64748b; font-size: 12px; font-style: italic;">Nenhuma cor cadastrada. Adicione abaixo.</span>
                    </div>

                    <!-- Input + Adicionar -->
                    <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                        <input type="text" id="configNewColorInput" placeholder="Digite uma cor e tecle Enter (ex: Preto, Branco, Azul Royal)..." onkeydown="if(event.key==='Enter'){event.preventDefault();addConfigColorTag();}" style="flex: 1; background: rgba(0,0,0,0.3); border: 1px solid rgba(192, 132, 252, 0.3); color: #fff; padding: 7px 12px; border-radius: 6px; font-size: 12.5px; outline: none;">
                        <button type="button" onclick="addConfigColorTag()" style="background: rgba(192, 132, 252, 0.15); border: 1px solid rgba(192, 132, 252, 0.4); color: #c084fc; padding: 7px 14px; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer;">
                            <i class="fas fa-plus"></i> Adicionar
                        </button>
                    </div>

                    <!-- Presets Rápidos de Cores -->
                    <div style="display: flex; align-items: center; gap: 5px; flex-wrap: wrap;">
                        <span style="font-size: 10.5px; color: #94a3b8; font-weight: 600;">Atalhos rápidos:</span>
                        <button type="button" onclick="addConfigColorTag('Preto')" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: #cbd5e1; padding: 2px 7px; border-radius: 4px; font-size: 11px; cursor: pointer;">⚫ Preto</button>
                        <button type="button" onclick="addConfigColorTag('Branco')" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: #cbd5e1; padding: 2px 7px; border-radius: 4px; font-size: 11px; cursor: pointer;">⚪ Branco</button>
                        <button type="button" onclick="addConfigColorTag('Cinza')" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: #cbd5e1; padding: 2px 7px; border-radius: 4px; font-size: 11px; cursor: pointer;">🔘 Cinza</button>
                        <button type="button" onclick="addConfigColorTag('Azul')" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: #cbd5e1; padding: 2px 7px; border-radius: 4px; font-size: 11px; cursor: pointer;">🔵 Azul</button>
                        <button type="button" onclick="addConfigColorTag('Vermelho')" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: #cbd5e1; padding: 2px 7px; border-radius: 4px; font-size: 11px; cursor: pointer;">🔴 Vermelho</button>
                        <button type="button" onclick="addConfigColorTag('Rosa')" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: #cbd5e1; padding: 2px 7px; border-radius: 4px; font-size: 11px; cursor: pointer;">🌸 Rosa</button>
                        <button type="button" onclick="addConfigColorTag('Bege')" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: #cbd5e1; padding: 2px 7px; border-radius: 4px; font-size: 11px; cursor: pointer;">🟤 Bege</button>
                        <button type="button" onclick="addConfigColorTag('Verde')" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: #cbd5e1; padding: 2px 7px; border-radius: 4px; font-size: 11px; cursor: pointer;">🟢 Verde</button>
                    </div>
                </div>

                <!-- 3. BLOCO DE FRETE -->
                <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 14px;">
                    <div style="font-size: 13px; font-weight: 700; color: #34d399; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                        <span>🚚</span> <span>Política de Frete & Envio</span>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 10px;">
                        <div>
                            <label style="display: block; font-size: 11.5px; color: #cbd5e1; margin-bottom: 4px; font-weight: 600;">Tipo de Frete:</label>
                            <select id="configFreteTipo" onchange="toggleShippingTypeInput(this.value)" style="width: 100%; background: rgba(0,0,0,0.3); border: 1px solid rgba(52, 211, 153, 0.35); color: #fff; padding: 7px 10px; border-radius: 6px; font-size: 12.5px; outline: none;">
                                <option value="gratis">🚚 Frete Grátis para todo o Brasil (Padrão)</option>
                                <option value="pago">📦 Frete Fixo / Pago (R$)</option>
                                <option value="expresso">⚡ Frete Expresso Sedex</option>
                            </select>
                        </div>
                        <div id="configFreteValorGroup" style="display: none;">
                            <label style="display: block; font-size: 11.5px; color: #cbd5e1; margin-bottom: 4px; font-weight: 600;">Valor do Frete (R$):</label>
                            <input type="number" step="0.01" min="0" id="configFreteValor" placeholder="19.90" style="width: 100%; background: rgba(0,0,0,0.3); border: 1px solid rgba(52, 211, 153, 0.35); color: #fff; padding: 7px 10px; border-radius: 6px; font-size: 12.5px; outline: none; box-sizing: border-box;">
                        </div>
                    </div>
                </div>

                <!-- 4. BLOCO DE URL DO FORNECEDOR -->
                <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 14px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #60a5fa; margin-bottom: 6px;">
                        🔗 Link Direto do Fornecedor / Anúncio Original (AliExpress):
                    </label>
                    <input type="url" id="configAliexpressUrl" placeholder="https://pt.aliexpress.com/item/..." style="width: 100%; background: rgba(0,0,0,0.3); border: 1px solid rgba(96, 165, 250, 0.3); color: #fff; padding: 7px 12px; border-radius: 6px; font-size: 12.5px; outline: none; box-sizing: border-box;">
                    <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">
                        Usado para inspecionar fotos, descrições e detalhes do vendedor a qualquer momento.
                    </div>
                </div>

            </div>

            <!-- Footer -->
            <div style="padding: 14px 22px; border-top: 1px solid rgba(255,255,255,0.08); display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.25);">
                <button type="button" onclick="closeProductConfigModal()" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15); color: #cbd5e1; padding: 9px 18px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;">
                    Cancelar
                </button>
                <button type="button" id="btnSaveProductConfig" onclick="saveProductConfig()" style="background: linear-gradient(135deg, #a855f7 0%, #7c3aed 100%); border: none; color: #fff; padding: 9px 22px; border-radius: 6px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(168, 85, 247, 0.35);">
                    <i class="fas fa-save"></i> Salvar Ajustes & Atualizar Loja
                </button>
            </div>

        </div>
    </div>

    <style>
        @keyframes dsModalIn {
            from { opacity: 0; transform: scale(0.96); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>

    <script>
        const systemExchangeRate = <?= (float)$rate ?>;
        const baseAdminUrl = '<?= $baseUrl ?>/admin/dropship';

        function openPriceModal(id, title, costUsd, costBrl, saleBrl) {
            document.getElementById('modalProductId').value = id;
            document.getElementById('modalProductTitle').innerText = title;
            document.getElementById('modalProductTitle').title = title;
            document.getElementById('priceEditForm').action = baseAdminUrl + '/product/' + id + '/price';

            document.getElementById('modalCostBrl').value = parseFloat(costBrl).toFixed(2);
            document.getElementById('modalSaleBrl').value = parseFloat(saleBrl).toFixed(2);

            recalcMargin();
            var modal = document.getElementById('priceEditModal');
            modal.style.display = 'flex';
        }

        function closePriceModal() {
            var modal = document.getElementById('priceEditModal');
            modal.style.display = 'none';
        }

        window.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePriceModal();
                closeImagesModal();
                if (typeof closeProductConfigModal === 'function') {
                    closeProductConfigModal();
                }
            }
        });

        const priceModalEl = document.getElementById('priceEditModal');
        if (priceModalEl) {
            priceModalEl.addEventListener('click', function(e) {
                if (e.target === this) closePriceModal();
            });
        }

        const imagesModalEl = document.getElementById('imagesEditModal');
        if (imagesModalEl) {
            imagesModalEl.addEventListener('click', function(e) {
                if (e.target === this) closeImagesModal();
            });
        }

        function recalcMargin() {
            const costBrl = parseFloat(document.getElementById('modalCostBrl').value) || 0;
            const saleBrl = parseFloat(document.getElementById('modalSaleBrl').value) || 0;

            const costUsd = (systemExchangeRate > 0) ? (costBrl / systemExchangeRate) : 0;
            document.getElementById('modalCostUsdHint').innerText = '$' + costUsd.toFixed(2);

            const profit = saleBrl - costBrl;
            const margin = saleBrl > 0 ? ((profit / saleBrl) * 100).toFixed(1) : 0;

            const profitEl = document.getElementById('modalProfitBrl');
            const marginEl = document.getElementById('modalMarginPercent');

            profitEl.innerText = (profit >= 0 ? '+R$ ' : '-R$ ') + Math.abs(profit).toFixed(2).replace('.', ',');
            profitEl.style.color = profit >= 0 ? '#5cd67d' : '#ef4444';

            marginEl.innerText = margin + '%';
            marginEl.style.color = margin >= 0 ? '#45f3ff' : '#ef4444';
        }

        function applyMarkup(percent) {
            const costBrl = parseFloat(document.getElementById('modalCostBrl').value) || 0;
            if (costBrl > 0) {
                const newSale = costBrl * (1 + (percent / 100));
                document.getElementById('modalSaleBrl').value = newSale.toFixed(2);
                recalcMargin();
            }
        }

        function roundTo90() {
            let sale = parseFloat(document.getElementById('modalSaleBrl').value) || 0;
            if (sale > 0) {
                let rounded = Math.floor(sale) + 0.90;
                if (rounded < sale && (sale - rounded) > 0.4) {
                    rounded += 1;
                }
                document.getElementById('modalSaleBrl').value = rounded.toFixed(2);
                recalcMargin();
            }
        }

        function handlePriceSubmit(e) {
            e.preventDefault();
            const form = document.getElementById('priceEditForm');
            const id = document.getElementById('modalProductId').value;
            const btn = document.getElementById('btnSavePrice');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';

            const formData = new FormData(form);
            formData.append('ajax', '1');

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                if (data && data.success) {
                    // Atualiza os valores visuais no card na hora
                    const saleEl = document.getElementById('prod-sale-brl-' + id);
                    if (saleEl) saleEl.innerText = data.sale_formatted;

                    const costBrlEl = document.getElementById('prod-cost-brl-' + id);
                    if (costBrlEl) costBrlEl.innerText = data.cost_formatted;

                    const costUsdEl = document.getElementById('prod-cost-usd-' + id);
                    if (costUsdEl) costUsdEl.innerText = '$' + parseFloat(data.cost_price_usd).toFixed(2);

                    closePriceModal();
                    alert(data.message);
                } else {
                    alert((data && data.message) ? data.message : 'Falha ao salvar preço.');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                // Fallback para envio padrão se fetch falhar
                form.submit();
            });
        }

        <?php
        $mediaMap = [];
        foreach ($products as $p) {
            if ($p->id === null) {
                continue;
            }
            $imgs = !empty($p->images) ? array_values($p->images) : [];
            if (!empty($p->mainImageUrl) && !in_array($p->mainImageUrl, $imgs, true)) {
                array_unshift($imgs, $p->mainImageUrl);
            }
            $vUrl = !empty($p->attributes['video_url']) ? (string)$p->attributes['video_url'] : '';
            if ($vUrl === '' && !empty($p->description) && preg_match('/<source\s+src="([^"]+\.mp4[^"]*)"/i', $p->description, $mVm)) {
                $vUrl = $mVm[1];
            }
            $isBr = (!empty($p->attributes['origem_brasil']) || (($p->attributes['ship_from'] ?? '') === 'BR'));
            $isCn = (!empty($p->attributes['origem_china']) || (($p->attributes['ship_from'] ?? '') === 'CN'));
            $isReal = !empty($p->aliexpressId) && preg_match('/^\d{10,24}$/', (string)$p->aliexpressId);

            $directAeUrl = !empty($p->attributes['aliexpress_url']) ? (string)$p->attributes['aliexpress_url'] : '';
            if (empty($directAeUrl) && $isReal) {
                $directAeUrl = 'https://pt.aliexpress.com/item/' . (string)$p->aliexpressId . '.html?country=BR&locale=pt_BR&currency=BRL';
            }
            if (empty($directAeUrl)) {
                $directAeUrl = 'https://www.aliexpress.com/wholesale?SearchText=' . urlencode($p->title);
            }

            $sizesArr = [];
            if (!empty($p->attributes['variation_axes']['Tamanho']) && is_array($p->attributes['variation_axes']['Tamanho'])) {
                $sizesArr = $p->attributes['variation_axes']['Tamanho'];
            } elseif (!empty($p->attributes['variation_axes']['Size']) && is_array($p->attributes['variation_axes']['Size'])) {
                $sizesArr = $p->attributes['variation_axes']['Size'];
            } elseif (!empty($p->attributes['tamanhos_disponiveis']) && is_array($p->attributes['tamanhos_disponiveis'])) {
                $sizesArr = $p->attributes['tamanhos_disponiveis'];
            } else {
                foreach ($p->getVariants() as $v) {
                    $s = $v->options['Tamanho'] ?? ($v->options['tamanho'] ?? ($v->options['Size'] ?? ''));
                    if ($s !== '' && !in_array($s, $sizesArr, true)) {
                        $sizesArr[] = $s;
                    }
                }
            }

            $colorsArr = [];
            if (!empty($p->attributes['variation_axes']['Cor']) && is_array($p->attributes['variation_axes']['Cor'])) {
                $colorsArr = $p->attributes['variation_axes']['Cor'];
            } elseif (!empty($p->attributes['variation_axes']['Color']) && is_array($p->attributes['variation_axes']['Color'])) {
                $colorsArr = $p->attributes['variation_axes']['Color'];
            } elseif (!empty($p->attributes['cores_disponiveis']) && is_array($p->attributes['cores_disponiveis'])) {
                $colorsArr = $p->attributes['cores_disponiveis'];
            } else {
                foreach ($p->getVariants() as $v) {
                    $c = $v->options['Cor'] ?? ($v->options['cor'] ?? ($v->options['Color'] ?? ''));
                    if ($c !== '' && !in_array($c, $colorsArr, true)) {
                        $colorsArr[] = $c;
                    }
                }
            }

            $freteGratis = !isset($p->attributes['frete_gratis']) || (bool)$p->attributes['frete_gratis'];
            $freteTipo = (string)($p->attributes['frete_tipo'] ?? ($freteGratis ? 'gratis' : 'pago'));
            $freteValor = (float)($p->attributes['frete_valor'] ?? 0.0);

            $mediaMap[(string)(int)$p->id] = [
                'id'               => (int)$p->id,
                'title'            => (string)$p->title,
                'aliexpressId'     => (string)$p->aliexpressId,
                'isRealId'         => (bool)$isReal,
                'isNational'       => (bool)$isBr,
                'isChina'          => (bool)$isCn,
                'origin'           => $isBr ? 'BR' : ($isCn ? 'CN' : 'GLOBAL'),
                'category'         => (string)($p->category ?? 'Geral'),
                'salePriceBrl'     => (float)($p->salePriceBrl ?? 0.0),
                'saleFormatted'    => 'R$ ' . number_format((float)($p->salePriceBrl ?? 0.0), 2, ',', '.'),
                'mainImageUrl'     => (string)($p->mainImageUrl ?: ($imgs[0] ?? '')),
                'videoUrl'         => $vUrl,
                'downloadVideoUrl' => $baseUrl . '/admin/dropship/download-video?id=' . (int)$p->id,
                'images'           => $imgs,
                'imagesCount'      => count($imgs),
                'directAeUrl'      => $directAeUrl,
                'aliexpressUrl'    => (string)($p->attributes['aliexpress_url'] ?? ''),
                'sizes'            => array_values($sizesArr),
                'colors'           => array_values($colorsArr),
                'freteGratis'      => (bool)$freteGratis,
                'freteTipo'        => (string)$freteTipo,
                'freteValor'       => (float)$freteValor,
            ];
        }
        ?>
        const dropshipCsrfToken = <?= json_encode($csrfToken, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        const dropshipProductsMedia = <?= json_encode($mediaMap, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES) ?>;
        window.dropshipProductsMedia = dropshipProductsMedia;
        window.dropshipCsrfToken = dropshipCsrfToken;
        let activeImagesState = { id: null, title: '', aliexpressId: '', mainImageUrl: '', videoUrl: '', downloadVideoUrl: '', images: [] };

        function setImagesModalStatus(msg, type) {
            const el = document.getElementById('imagesModalStatus');
            if (!el) return;
            if (!msg) {
                el.style.display = 'none';
                el.textContent = '';
                return;
            }
            el.style.display = 'block';
            el.textContent = msg;
            if (type === 'error') {
                el.style.background = 'rgba(220,50,50,0.16)';
                el.style.border = '1px solid #dc3232';
                el.style.color = '#ff8080';
            } else if (type === 'info') {
                el.style.background = 'rgba(69,243,255,0.12)';
                el.style.border = '1px solid rgba(69,243,255,0.4)';
                el.style.color = '#45f3ff';
            } else {
                el.style.background = 'rgba(40,167,69,0.16)';
                el.style.border = '1px solid #28a745';
                el.style.color = '#5cd67d';
            }
        }

        function openImagesModal(id) {
            const item = dropshipProductsMedia[String(id)];
            if (!item) return;

            activeImagesState = {
                id: item.id,
                title: item.title,
                aliexpressId: item.aliexpressId,
                mainImageUrl: item.mainImageUrl,
                videoUrl: item.videoUrl || '',
                downloadVideoUrl: item.downloadVideoUrl || '',
                images: Array.isArray(item.images) ? item.images.slice() : []
            };

            document.getElementById('imagesModalTitle').textContent = activeImagesState.title;
            document.getElementById('imagesModalTitle').title = activeImagesState.title;
            var directUrl = (item.attributes && item.attributes.aliexpress_url) ? item.attributes.aliexpress_url : '';
            var isRealId = activeImagesState.aliexpressId && /^\d{8,24}$/.test(String(activeImagesState.aliexpressId));
            document.getElementById('imagesModalAeLink').href = directUrl
                ? directUrl
                : (isRealId
                    ? 'https://pt.aliexpress.com/item/' + encodeURIComponent(activeImagesState.aliexpressId) + '.html?country=BR&locale=pt_BR&currency=BRL'
                    : 'https://www.aliexpress.com/wholesale?SearchText=' + encodeURIComponent(activeImagesState.title));
            document.getElementById('imagesModalNewUrl').value = '';
            setImagesModalStatus('', 'info');

            const videoPlayer = document.getElementById('imagesModalVideoPlayer');
            if (videoPlayer) {
                videoPlayer.pause();
                videoPlayer.removeAttribute('src');
                videoPlayer.load();
            }
            updateVideoModalUI(activeImagesState.videoUrl || '');

            renderImagesModalGrid();

            document.getElementById('imagesEditModal').style.display = 'flex';
        }

        function setManualVideoUrl() {
            const input = document.getElementById('imagesModalVideoUrlInput');
            const url = input ? input.value.trim() : '';
            if (!url) {
                setImagesModalStatus('Por favor, informe ou cole uma URL de vídeo MP4 válida.', 'error');
                return;
            }
            activeImagesState.videoUrl = url;
            updateVideoModalUI(url);
            setImagesModalStatus('Vídeo carregado na prévia! Clique no botão azul "Salvar Galeria" para gravar permanentemente no catálogo.', 'info');
        }

        function removeVideoUrl() {
            activeImagesState.videoUrl = '';
            const input = document.getElementById('imagesModalVideoUrlInput');
            if (input) input.value = '';
            updateVideoModalUI('');
            setImagesModalStatus('Vídeo removido da prévia! Clique em "Salvar Galeria" para confirmar a exclusão.', 'warning');
        }

        function updateVideoModalUI(videoUrl) {
            const playerContainer = document.getElementById('imagesModalVideoPlayerContainer');
            const player = document.getElementById('imagesModalVideoPlayer');
            const dlBtn = document.getElementById('imagesModalVideoDownloadBtn');
            const rmBtn = document.getElementById('imagesModalVideoRemoveBtn');
            const input = document.getElementById('imagesModalVideoUrlInput');

            if (input) input.value = videoUrl || '';
            if (videoUrl) {
                if (playerContainer) playerContainer.style.display = 'block';
                if (player) {
                    player.src = videoUrl;
                    player.load();
                }
                if (dlBtn) dlBtn.style.display = 'inline-flex';
                if (rmBtn) rmBtn.style.display = 'inline-flex';
            } else {
                if (playerContainer) playerContainer.style.display = 'none';
                if (player) {
                    player.pause();
                    player.removeAttribute('src');
                    player.load();
                }
                if (dlBtn) dlBtn.style.display = 'none';
                if (rmBtn) rmBtn.style.display = 'none';
            }
        }

        function downloadCurrentModalVideo(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            const input = document.getElementById('imagesModalVideoUrlInput');
            const videoUrl = (activeImagesState.videoUrl || (input ? input.value.trim() : '')).trim();
            if (!videoUrl) {
                setImagesModalStatus('Nenhum vídeo MP4 disponível para download neste produto.', 'error');
                alert('Nenhum vídeo MP4 disponível para download neste produto.');
                return false;
            }
            const targetUrl = baseAdminUrl + '/download-video?id=' + (activeImagesState.id || 0) + '&url=' + encodeURIComponent(videoUrl);
            window.location.href = targetUrl;
            return false;
        }

        function downloadCardVideo(id, e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            const item = (window.dropshipProductsMedia && window.dropshipProductsMedia[String(id)]) ? window.dropshipProductsMedia[String(id)] : null;
            const videoUrl = item ? (item.videoUrl || '') : '';
            const targetUrl = baseAdminUrl + '/download-video?id=' + id + (videoUrl ? '&url=' + encodeURIComponent(videoUrl) : '');
            window.location.href = targetUrl;
            return false;
        }

        function closeImagesModal() {
            const modal = document.getElementById('imagesEditModal');
            if (modal) modal.style.display = 'none';
            const videoPlayer = document.getElementById('imagesModalVideoPlayer');
            if (videoPlayer) {
                videoPlayer.pause();
                videoPlayer.removeAttribute('src');
                videoPlayer.load();
            }
        }

        function renderImagesModalGrid() {
            const grid = document.getElementById('imagesModalGrid');
            if (!grid) return;
            grid.innerHTML = '';

            if (!activeImagesState.images.length) {
                const empty = document.createElement('div');
                empty.style.cssText = 'grid-column: 1 / -1; text-align: center; padding: 26px; background: rgba(0,0,0,0.25); border: 1px dashed rgba(255,255,255,0.12); border-radius: 8px; color: #94a3b8; font-size: 13px;';
                empty.textContent = 'Nenhuma imagem cadastrada ainda. Clique em "Sincronizar do AliExpress" ou cole uma URL abaixo.';
                grid.appendChild(empty);
                return;
            }

            activeImagesState.images.forEach(function(url, idx) {
                const isMain = (url === activeImagesState.mainImageUrl);

                const card = document.createElement('div');
                card.style.cssText = 'background: #0b0f17; border: 1px solid ' + (isMain ? '#fbbf24' : 'rgba(255,255,255,0.1)') + '; border-radius: 8px; overflow: hidden; display: flex; flex-direction: column;';

                const thumbWrap = document.createElement('div');
                thumbWrap.style.cssText = 'position: relative; width: 100%; height: 120px; background: #070a10;';

                const img = document.createElement('img');
                img.src = url;
                img.alt = 'Foto ' + (idx + 1);
                img.style.cssText = 'width: 100%; height: 100%; object-fit: cover; display: block;';
                thumbWrap.appendChild(img);

                if (isMain) {
                    const badge = document.createElement('span');
                    badge.style.cssText = 'position: absolute; top: 6px; left: 6px; background: #fbbf24; color: #0b0f17; font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 4px;';
                    badge.textContent = 'CAPA';
                    thumbWrap.appendChild(badge);
                }

                const openLink = document.createElement('a');
                openLink.href = url;
                openLink.target = '_blank';
                openLink.rel = 'noopener noreferrer';
                openLink.title = 'Abrir imagem em tamanho original';
                openLink.style.cssText = 'position: absolute; top: 6px; right: 6px; background: rgba(0,0,0,0.7); color: #45f3ff; width: 24px; height: 24px; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; text-decoration: none;';
                openLink.innerHTML = '<i class="fas fa-external-link-alt"></i>';
                thumbWrap.appendChild(openLink);

                const actions = document.createElement('div');
                actions.style.cssText = 'padding: 8px; display: flex; gap: 6px; justify-content: space-between; align-items: center;';

                const coverBtn = document.createElement('button');
                coverBtn.type = 'button';
                coverBtn.disabled = isMain;
                coverBtn.style.cssText = 'flex: 1; padding: 4px 6px; font-size: 11px; font-weight: 700; border-radius: 4px; cursor: ' + (isMain ? 'default' : 'pointer') + '; border: 1px solid rgba(251,191,36,0.35); background: ' + (isMain ? 'rgba(251,191,36,0.18)' : 'rgba(255,255,255,0.05)') + '; color: ' + (isMain ? '#fbbf24' : '#cbd5e1') + ';';
                coverBtn.textContent = isMain ? 'Capa Atual' : 'Definir Capa';
                coverBtn.onclick = function() {
                    activeImagesState.mainImageUrl = url;
                    renderImagesModalGrid();
                };

                const delBtn = document.createElement('button');
                delBtn.type = 'button';
                delBtn.title = 'Remover foto da galeria';
                delBtn.style.cssText = 'padding: 4px 8px; font-size: 11px; font-weight: 700; border-radius: 4px; cursor: pointer; border: 1px solid rgba(239,68,68,0.35); background: rgba(239,68,68,0.1); color: #ef4444;';
                delBtn.innerHTML = '<i class="fas fa-trash-alt"></i>';
                delBtn.onclick = function() {
                    activeImagesState.images.splice(idx, 1);
                    if (activeImagesState.mainImageUrl === url) {
                        activeImagesState.mainImageUrl = activeImagesState.images[0] || '';
                    }
                    renderImagesModalGrid();
                };

                actions.appendChild(coverBtn);
                actions.appendChild(delBtn);
                card.appendChild(thumbWrap);
                card.appendChild(actions);
                grid.appendChild(card);
            });
        }

        function addManualImageUrl() {
            const input = document.getElementById('imagesModalNewUrl');
            const raw = (input ? input.value : '').trim();
            if (!/^https?:\/\/\S+$/i.test(raw)) {
                setImagesModalStatus('Informe uma URL válida começando com http:// ou https://', 'error');
                return;
            }
            if (!activeImagesState.images.includes(raw)) {
                activeImagesState.images.push(raw);
            }
            if (!activeImagesState.mainImageUrl) {
                activeImagesState.mainImageUrl = raw;
            }
            input.value = '';
            setImagesModalStatus('Imagem adicionada à prévia. Clique em "Salvar Galeria" para gravar.', 'info');
            renderImagesModalGrid();
        }

        function updateCardImagesUi(id, mainUrl, imagesList, videoUrl) {
            const imgEl = document.getElementById('prod-main-img-' + id);
            if (imgEl && mainUrl) {
                imgEl.src = mainUrl;
            }
            const badge = document.getElementById('prod-imgs-badge-' + id);
            const countEl = document.getElementById('prod-imgs-count-' + id);
            const count = Array.isArray(imagesList) ? imagesList.length : 0;
            if (countEl) countEl.textContent = String(count);
            if (badge) badge.style.display = count > 1 ? 'flex' : 'none';

            const vidBtn = document.getElementById('prod-video-btn-' + id);
            if (vidBtn) {
                vidBtn.style.display = (videoUrl && videoUrl !== '') ? 'inline-flex' : 'none';
            }

            if (dropshipProductsMedia[String(id)]) {
                dropshipProductsMedia[String(id)].mainImageUrl = mainUrl;
                dropshipProductsMedia[String(id)].images = imagesList.slice();
                if (typeof videoUrl !== 'undefined') {
                    dropshipProductsMedia[String(id)].videoUrl = videoUrl;
                }
            }
        }

        function syncImagesNow() {
            if (!activeImagesState.id) return;
            const id = activeImagesState.id;
            const btn = document.getElementById('btnSyncImagesNow');
            const origHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sincronizando...';
            setImagesModalStatus('Consultando fotos oficiais direto do AliExpress...', 'info');

            const fd = new FormData();
            fd.append('csrf_token', dropshipCsrfToken);
            fd.append('ajax', '1');

            fetch(baseAdminUrl + '/product/' + id + '/sync-images', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: fd
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = origHtml;
                if (data && data.success) {
                    activeImagesState.mainImageUrl = data.main_image_url || '';
                    activeImagesState.images = Array.isArray(data.images) ? data.images : [];
                    renderImagesModalGrid();
                    updateCardImagesUi(id, activeImagesState.mainImageUrl, activeImagesState.images);
                    setImagesModalStatus(data.message || 'Imagens sincronizadas com sucesso!', 'success');
                } else {
                    setImagesModalStatus((data && data.message) ? data.message : 'O AliExpress bloqueou a coleta automática neste item. Você pode adicionar URLs de fotos manualmente abaixo.', 'error');
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerHTML = origHtml;
                setImagesModalStatus('Erro de comunicação ao sincronizar imagens.', 'error');
            });
        }

        function handleImagesSave() {
            if (!activeImagesState.id) return;
            const id = activeImagesState.id;
            const btn = document.getElementById('btnSaveImagesGallery');
            const origHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';

            // Captura qualquer URL digitada ou colada no campo de vídeo, mesmo sem ter clicado em "Definir Vídeo"
            const urlInput = document.getElementById('imagesModalVideoUrlInput');
            if (urlInput && urlInput.value.trim() !== '') {
                activeImagesState.videoUrl = urlInput.value.trim();
            }

            const fd = new FormData();
            fd.append('csrf_token', dropshipCsrfToken);
            fd.append('ajax', '1');
            fd.append('main_image_url', activeImagesState.mainImageUrl || '');
            fd.append('images_json', JSON.stringify(activeImagesState.images || []));
            fd.append('video_url', activeImagesState.videoUrl || '');

            fetch(baseAdminUrl + '/product/' + id + '/images', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: fd
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = origHtml;
                if (data && data.success) {
                    activeImagesState.mainImageUrl = data.main_image_url || '';
                    activeImagesState.images = Array.isArray(data.images) ? data.images : [];
                    if (typeof data.video_url !== 'undefined') {
                        activeImagesState.videoUrl = data.video_url || '';
                        updateVideoModalUI(activeImagesState.videoUrl);
                    }
                    renderImagesModalGrid();
                    updateCardImagesUi(id, activeImagesState.mainImageUrl, activeImagesState.images, activeImagesState.videoUrl);
                    setImagesModalStatus(data.message || 'Galeria e vídeo gravados com sucesso!', 'success');
                } else {
                    setImagesModalStatus((data && data.message) ? data.message : 'Falha ao salvar galeria.', 'error');
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerHTML = origHtml;
                setImagesModalStatus('Erro de comunicação ao salvar galeria.', 'error');
            });
        }

        // ========================================================
        // FILA DE SINCRONIZAÇÃO EM LOTE COM BARRA DE STATUS
        // ========================================================
        let batchSyncQueue = [];
        let batchSyncRunning = false;
        let batchSyncCancelled = false;
        let batchSyncedCount = 0;
        let batchSkippedCount = 0;
        let batchPurgedCount = 0;
        let batchFailedCount = 0;

        function escapeBatchHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str).replace(/[&<>"']/g, function(m) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m];
            });
        }

        function openBatchMediaSyncModal() {
            const modal = document.getElementById('batchSyncModal');
            if (!modal) return;
            modal.style.display = 'flex';
            resetBatchSyncUI();

            appendBatchLog('Carregando inventário de produtos do catálogo...', 'info');
            fetch(baseAdminUrl + '/sync-queue')
                .then(res => res.json())
                .then(data => {
                    if (data && data.success && Array.isArray(data.items)) {
                        batchSyncQueue = data.items;
                        document.getElementById('batchCountTotal').textContent = data.total;
                        document.getElementById('batchCountSingle').textContent = data.single_or_empty_count;
                        document.getElementById('batchStatTotal').textContent = data.total;
                        appendBatchLog('Inventário carregado: ' + data.total + ' produtos (' + data.single_or_empty_count + ' precisam de galeria completa).', 'success');
                    } else {
                        appendBatchLog('Aviso: Não foi possível carregar a lista de produtos.', 'warning');
                    }
                })
                .catch(err => {
                    appendBatchLog('Erro ao carregar fila: ' + err.message, 'error');
                });
        }

        function closeBatchSyncModal() {
            if (batchSyncRunning) {
                if (!confirm('A sincronização está em andamento. Deseja realmente interromper e fechar?')) {
                    return;
                }
                batchSyncCancelled = true;
            }
            const modal = document.getElementById('batchSyncModal');
            if (modal) modal.style.display = 'none';
        }

        function resetBatchSyncUI() {
            batchSyncRunning = false;
            batchSyncCancelled = false;
            batchSyncedCount = 0;
            batchSkippedCount = 0;
            batchPurgedCount = 0;
            batchFailedCount = 0;

            document.getElementById('batchStatSynced').textContent = '0';
            document.getElementById('batchStatSkipped').textContent = '0';
            const elPurged = document.getElementById('batchStatPurged');
            if (elPurged) elPurged.textContent = '0';
            document.getElementById('batchStatFailed').textContent = '0';
            document.getElementById('batchProgressBar').style.width = '0%';
            document.getElementById('batchProgressPercent').textContent = '0%';
            document.getElementById('batchProgressLabel').textContent = 'Pronto para iniciar';
            document.getElementById('batchSyncStatusBadge').textContent = 'Aguardando';
            document.getElementById('batchSyncStatusBadge').style.color = '#94a3b8';

            const logBox = document.getElementById('batchSyncLogConsole');
            logBox.innerHTML = '';

            document.getElementById('batchSyncConfigSection').style.display = 'block';
            document.getElementById('btnBatchSyncAction').style.display = 'inline-flex';
            document.getElementById('btnBatchSyncAction').innerHTML = '<i class="fas fa-play"></i> Iniciar Sincronização';
            document.getElementById('btnBatchSyncAction').disabled = false;
            document.getElementById('btnBatchSyncDone').style.display = 'none';
            document.getElementById('btnBatchSyncCancel').textContent = 'Cancelar / Fechar';
        }

        function appendBatchLog(text, type = 'info') {
            const logBox = document.getElementById('batchSyncLogConsole');
            if (!logBox) return;
            const time = new Date().toLocaleTimeString();
            const line = document.createElement('div');
            
            let color = '#cbd5e1';
            let icon = 'ℹ️';
            if (type === 'success') { color = '#10b981'; icon = '✅'; }
            else if (type === 'warning' || type === 'skip') { color = '#f59e0b'; icon = '⏭️'; }
            else if (type === 'error') { color = '#ef4444'; icon = '❌'; }
            else if (type === 'active') { color = '#38bdf8'; icon = '⏳'; }

            line.style.color = color;
            line.innerHTML = `<span style="color:#64748b;">[${time}]</span> ${icon} ${escapeBatchHtml(text)}`;
            logBox.appendChild(line);
            logBox.scrollTop = logBox.scrollHeight;
        }

        async function startBatchSync() {
            if (batchSyncRunning) return;
            if (!batchSyncQueue.length) {
                alert('A fila de produtos ainda não foi carregada. Tente novamente em instantes.');
                return;
            }

            const modeRadio = document.querySelector('input[name="batchSyncMode"]:checked');
            const mode = modeRadio ? modeRadio.value : 'incomplete';
            const autoPurge = document.getElementById('batchSyncAutoPurgeDead')?.checked ?? true;

            batchSyncRunning = true;
            batchSyncCancelled = false;
            batchSyncedCount = 0;
            batchSkippedCount = 0;
            batchPurgedCount = 0;
            batchFailedCount = 0;

            const elPurged = document.getElementById('batchStatPurged');
            if (elPurged) elPurged.textContent = '0';

            document.getElementById('batchSyncConfigSection').style.display = 'none';
            document.getElementById('btnBatchSyncAction').disabled = true;
            document.getElementById('btnBatchSyncAction').style.display = 'none';
            document.getElementById('btnBatchSyncCancel').textContent = 'Pausar / Interromper';
            document.getElementById('batchSyncStatusBadge').textContent = 'Executando em fila...';
            document.getElementById('batchSyncStatusBadge').style.color = '#38bdf8';

            const headerIcon = document.getElementById('batchSyncHeaderIcon');
            if (headerIcon) headerIcon.classList.add('fa-spin');

            const total = batchSyncQueue.length;
            appendBatchLog(`🚀 Fila iniciada (${mode === 'incomplete' ? 'Apenas galerias incompletas' : 'Todos os produtos'}). Auto-exclusão 404: ${autoPurge ? 'Ativa' : 'Desativada'}. Total: ${total} itens.`, 'info');

            for (let i = 0; i < total; i++) {
                if (batchSyncCancelled) {
                    appendBatchLog('⏸️ Sincronização interrompida pelo usuário.', 'warning');
                    break;
                }

                const item = batchSyncQueue[i];
                const stepNum = i + 1;
                const percent = Math.round((stepNum / total) * 100);

                // Se o modo for apenas incompletos e o produto já tem mais de 1 imagem, verifica disponibilidade antes de preservar!
                if (mode === 'incomplete' && item.images_count > 1) {
                    let wasDeleted = false;
                    if (item.aliexpress_id && /^\d{10,20}$/.test(item.aliexpress_id)) {
                        try {
                            const chkRes = await fetch(<?= json_encode($baseUrl . '/api/drop/check-availability', JSON_UNESCAPED_SLASHES) ?>, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                                body: JSON.stringify({ product_id: item.id })
                            });
                            const chkData = await chkRes.json();
                            if (chkData && chkData.available === false && chkData.reason === 'not_found') {
                                wasDeleted = true;
                                batchPurgedCount++;
                                if (elPurged) elPurged.textContent = batchPurgedCount;
                                appendBatchLog(`[${stepNum}/${total}] 🗑️ Produto #${item.id} (${item.title}): Página Não Encontrada no fornecedor — excluído automaticamente!`, 'warning');
                                const cardEl = document.querySelector(`[data-product-id="${item.id}"]`) || document.getElementById(`prod-card-${item.id}`);
                                if (cardEl) cardEl.remove();
                                delete dropshipProductsMedia[String(item.id)];
                            }
                        } catch (e) {}
                    }
                    if (!wasDeleted) {
                        batchSkippedCount++;
                        document.getElementById('batchStatSkipped').textContent = batchSkippedCount;
                        appendBatchLog(`[${stepNum}/${total}] Produto #${item.id} (${item.title}): Já possui ${item.images_count} fotos. Preservado!`, 'skip');
                        updateBatchProgress(stepNum, total, percent, `Preservado: #${item.id} (${item.images_count} fotos)`);
                    }
                    continue;
                }

                // Valida se possui identificador oficial numérico
                if (!item.aliexpress_id || !/^\d{10,20}$/.test(item.aliexpress_id)) {
                    if (autoPurge) {
                        batchPurgedCount++;
                        if (elPurged) elPurged.textContent = batchPurgedCount;
                        appendBatchLog(`[${stepNum}/${total}] 🗑️ Produto #${item.id} (${item.title}): ID de fornecedor inexistente/simulado -> Excluído automaticamente!`, 'warning');
                        updateBatchProgress(stepNum, total, percent, `Excluído: #${item.id}`);
                        try {
                            const delForm = new FormData();
                            delForm.append('csrf_token', dropshipCsrfToken);
                            delForm.append('ajax', '1');
                            await fetch(baseAdminUrl + '/product/' + item.id + '/delete', {
                                method: 'POST',
                                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                                body: delForm
                            });
                            const cardEl = document.querySelector(`[data-product-id="${item.id}"]`) || document.getElementById(`prod-card-${item.id}`);
                            if (cardEl) cardEl.remove();
                            delete dropshipProductsMedia[String(item.id)];
                        } catch (e) {}
                        continue;
                    } else {
                        batchSkippedCount++;
                        document.getElementById('batchStatSkipped').textContent = batchSkippedCount;
                        appendBatchLog(`[${stepNum}/${total}] Produto #${item.id} (${item.title}): ID do fornecedor simulado ou inválido (${item.aliexpress_id}). Ignorado.`, 'warning');
                        updateBatchProgress(stepNum, total, percent, `Ignorado: #${item.id}`);
                        continue;
                    }
                }

                // Executa a requisição assíncrona unitária para este produto
                updateBatchProgress(stepNum, total, percent, `Sincronizando #${item.id}: ${item.title}...`);
                appendBatchLog(`[${stepNum}/${total}] Sincronizando Produto #${item.id} com o AliExpress (${item.aliexpress_id})...`, 'active');

                try {
                    const formData = new FormData();
                    formData.append('csrf_token', dropshipCsrfToken);
                    formData.append('ajax', '1');
                    formData.append('auto_purge', autoPurge ? '1' : '0');

                    const res = await fetch(baseAdminUrl + '/product/' + item.id + '/sync-images', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await res.json();
                    if (data && data.deleted) {
                        batchPurgedCount++;
                        if (elPurged) elPurged.textContent = batchPurgedCount;
                        appendBatchLog(`[${stepNum}/${total}] 🗑️ Produto #${item.id}: ${data.message || 'Página Não Encontrada — excluído automaticamente.'}`, 'warning');
                        const cardEl = document.querySelector(`[data-product-id="${item.id}"]`) || document.getElementById(`prod-card-${item.id}`);
                        if (cardEl) cardEl.remove();
                        delete dropshipProductsMedia[String(item.id)];
                    } else if (data && data.temporary_block) {
                        batchSkippedCount++;
                        document.getElementById('batchStatSkipped').textContent = batchSkippedCount;
                        appendBatchLog(`[${stepNum}/${total}] ${data.message || 'Captcha temporário do AliExpress — produto preservado.'}`, 'skip');
                    } else if (res.ok && data.success) {
                        batchSyncedCount++;
                        item.images_count = data.count || (Array.isArray(data.images) ? data.images.length : 1);
                        document.getElementById('batchStatSynced').textContent = batchSyncedCount;
                        const priceLog = data.sale_formatted ? ` • Preço Venda: ${data.sale_formatted}` : '';
                        appendBatchLog(`[${stepNum}/${total}] Produto #${item.id} atualizado com sucesso! ${item.images_count} fotos${priceLog}`, 'success');
                        updateCardImagesUi(item.id, data.main_image_url || '', data.images || []);
                    } else {
                        if (autoPurge) {
                            batchPurgedCount++;
                            if (elPurged) elPurged.textContent = batchPurgedCount;
                            appendBatchLog(`[${stepNum}/${total}] 🗑️ Produto #${item.id} (${item.title}): Falha ao obter imagens no fornecedor -> Excluído automaticamente!`, 'warning');
                            try {
                                const delForm = new FormData();
                                delForm.append('csrf_token', dropshipCsrfToken);
                                delForm.append('ajax', '1');
                                await fetch(baseAdminUrl + '/product/' + item.id + '/delete', {
                                    method: 'POST',
                                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                                    body: delForm
                                });
                                const cardEl = document.querySelector(`[data-product-id="${item.id}"]`) || document.getElementById(`prod-card-${item.id}`);
                                if (cardEl) cardEl.remove();
                                delete dropshipProductsMedia[String(item.id)];
                            } catch (e) {}
                        } else {
                            batchFailedCount++;
                            document.getElementById('batchStatFailed').textContent = batchFailedCount;
                            appendBatchLog(`[${stepNum}/${total}] Falha no Produto #${item.id}: ${(data && data.message) ? data.message : 'Erro na requisição'}`, 'error');
                        }
                    }
                } catch (err) {
                    if (autoPurge) {
                        batchPurgedCount++;
                        if (elPurged) elPurged.textContent = batchPurgedCount;
                        appendBatchLog(`[${stepNum}/${total}] 🗑️ Produto #${item.id}: Erro de rede/anúncio inacessível -> Excluído automaticamente!`, 'warning');
                        try {
                            const delForm = new FormData();
                            delForm.append('csrf_token', dropshipCsrfToken);
                            delForm.append('ajax', '1');
                            await fetch(baseAdminUrl + '/product/' + item.id + '/delete', {
                                method: 'POST',
                                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                                body: delForm
                            });
                            const cardEl = document.querySelector(`[data-product-id="${item.id}"]`) || document.getElementById(`prod-card-${item.id}`);
                            if (cardEl) cardEl.remove();
                            delete dropshipProductsMedia[String(item.id)];
                        } catch (e) {}
                    } else {
                        batchFailedCount++;
                        document.getElementById('batchStatFailed').textContent = batchFailedCount;
                        appendBatchLog(`[${stepNum}/${total}] Erro de rede no Produto #${item.id}: ${err.message}`, 'error');
                    }
                }

                // Pausa suave de 250ms entre produtos para estabilidade da conexão
                await new Promise(r => setTimeout(r, 250));
            }

            // Finalização
            batchSyncRunning = false;
            if (headerIcon) headerIcon.classList.remove('fa-spin');

            document.getElementById('batchProgressBar').style.width = '100%';
            document.getElementById('batchProgressPercent').textContent = '100%';
            document.getElementById('batchProgressLabel').textContent = 'Sincronização 100% Concluída!';
            document.getElementById('batchSyncStatusBadge').textContent = 'Concluído 100%';
            document.getElementById('batchSyncStatusBadge').style.color = '#10b981';

            appendBatchLog(`🎉 Processo 100% finalizado! Atualizados: ${batchSyncedCount} | Preservados: ${batchSkippedCount} | Excluídos (404/Mortos): ${batchPurgedCount} | Falhas: ${batchFailedCount}.`, 'success');

            document.getElementById('btnBatchSyncDone').style.display = 'inline-flex';
            document.getElementById('btnBatchSyncCancel').textContent = 'Fechar';
        }

        function updateBatchProgress(current, total, percent, statusText) {
            document.getElementById('batchProgressBar').style.width = percent + '%';
            document.getElementById('batchProgressPercent').textContent = percent + '%';
            document.getElementById('batchProgressLabel').textContent = `[${current}/${total}] ${statusText}`;
        }

        function cancelBatchSync() {
            if (batchSyncRunning) {
                batchSyncCancelled = true;
            } else {
                closeBatchSyncModal();
            }
        }

        function finishAndReload() {
            window.location.reload();
        }

        // Exportação explícita e defensiva para o escopo global (window)
        window.openPriceModal = openPriceModal;
        window.closePriceModal = closePriceModal;
        window.recalcMargin = recalcMargin;
        window.applyMarkup = applyMarkup;
        window.roundTo90 = roundTo90;
        window.handlePriceSubmit = handlePriceSubmit;
        window.openImagesModal = openImagesModal;
        window.closeImagesModal = closeImagesModal;
        window.addManualImageUrl = addManualImageUrl;
        window.setManualVideoUrl = setManualVideoUrl;
        window.removeVideoUrl = removeVideoUrl;
        window.syncImagesNow = syncImagesNow;
        window.handleImagesSave = handleImagesSave;
        window.openBatchMediaSyncModal = openBatchMediaSyncModal;
        window.closeBatchSyncModal = closeBatchSyncModal;
        window.startBatchSync = startBatchSync;
        window.cancelBatchSync = cancelBatchSync;
        window.finishAndReload = finishAndReload;

        function updateProductCategory(productId, newCategory) {
            if (!productId || !newCategory) return;
            var fd = new FormData();
            fd.append('csrf_token', dropshipCsrfToken);
            fd.append('category', newCategory);

            fetch(baseAdminUrl + '/product/' + productId + '/category', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fd
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    showToastFeedback(data.message, 'success');
                    var updatedCat = data.category || newCategory;

                    // 1. Atualiza o texto do badge de categoria na imagem do card
                    var catText = document.getElementById('prod-category-text-' + productId);
                    if (catText) {
                        catText.textContent = updatedCat;
                    }

                    // 2. Feedback visual com brilho suave no badge de categoria
                    var catBadge = document.getElementById('prod-category-badge-' + productId);
                    if (catBadge) {
                        catBadge.title = 'Categoria: ' + updatedCat;
                        catBadge.style.transition = 'all 0.3s ease';
                        catBadge.style.background = 'rgba(240, 208, 96, 0.4)';
                        catBadge.style.boxShadow = '0 0 12px rgba(240, 208, 96, 0.6)';
                        setTimeout(function() {
                            catBadge.style.background = 'rgba(0,0,0,0.75)';
                            catBadge.style.boxShadow = '0 2px 6px rgba(0,0,0,0.4)';
                        }, 700);
                    }

                    // 3. Atualiza o valor selecionado no combobox caso tenha sido normalizado
                    var catSel = document.getElementById('prod-category-select-' + productId);
                    if (catSel && catSel.value !== updatedCat) {
                        catSel.value = updatedCat;
                    }
                } else {
                    showToastFeedback(data.message || 'Erro ao alterar categoria', 'error');
                }
            })
            .catch(function(err) {
                showToastFeedback('Erro de conexão ao salvar categoria.', 'error');
            });
        }

        function updateProductOrigin(productId, newOrigin) {
            if (!productId || !newOrigin) return;
            var fd = new FormData();
            fd.append('csrf_token', dropshipCsrfToken);
            fd.append('origin', newOrigin);

            var sel = document.getElementById('prod-origin-select-' + productId);
            if (sel) {
                sel.style.color = (newOrigin === 'BR') ? '#34d399' : ((newOrigin === 'CN') ? '#60a5fa' : '#cbd5e1');
            }

            fetch(baseAdminUrl + '/product/' + productId + '/origin', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fd
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    showToastFeedback(data.message, 'success');
                    var badge = document.getElementById('prod-origin-badge-' + productId);
                    if (badge) {
                        badge.innerHTML = data.badge_flag;
                        badge.style.background = data.badge_bg;
                    }
                } else {
                    showToastFeedback(data.message || 'Erro ao alterar procedência', 'error');
                }
            })
            .catch(function(err) {
                showToastFeedback('Erro de conexão ao salvar procedência.', 'error');
            });
        }

        function toggleDailyCycleAutoSync() {
            var btn = document.getElementById('btnToggleDailyCycle');
            var icon = document.getElementById('btnToggleDailyCycleIcon');
            var text = document.getElementById('btnToggleDailyCycleText');
            var badge = document.getElementById('cycleActiveBadge');

            var fd = new FormData();
            fd.append('csrf_token', dropshipCsrfToken);

            fetch(baseAdminUrl + '/toggle-daily-cycle', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fd
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    showToastFeedback(data.message, 'success');
                    var isEnabled = data.auto_sync_enabled;
                    if (badge) {
                        badge.textContent = isEnabled ? 'Ativo' : 'Pausado';
                        badge.style.background = isEnabled ? 'rgba(34,197,94,0.2)' : 'rgba(239,68,68,0.2)';
                        badge.style.color = isEnabled ? '#4ade80' : '#f87171';
                        badge.style.borderColor = isEnabled ? 'rgba(34,197,94,0.4)' : 'rgba(239,68,68,0.4)';
                    }
                    if (btn) {
                        btn.style.background = isEnabled 
                            ? 'linear-gradient(135deg, #fbbf24 0%, #d97706 100%)' 
                            : 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
                        btn.title = isEnabled ? 'Pausar o ciclo autônomo de 24 horas' : 'Reativar o ciclo autônomo de 24 horas';
                    }
                    if (icon) {
                        icon.className = isEnabled ? 'fas fa-pause-circle' : 'fas fa-play-circle';
                    }
                    if (text) {
                        text.textContent = isEnabled ? 'Pausar 24 Horas' : 'Ativar 24 Horas';
                    }
                } else {
                    showToastFeedback(data.message || 'Erro ao alterar estado do ciclo 24h', 'error');
                }
            })
            .catch(function(err) {
                showToastFeedback('Erro de conexão ao alternar ciclo 24h.', 'error');
            });
        }

        // -------------------------------------------------------------
        // Auditoria & Esteira de Aprovação de Produtos (Ações em Lote e 1-Click)
        // -------------------------------------------------------------
        function getSelectedCardIds() {
            var checkboxes = document.querySelectorAll('.prod-card-checkbox:checked');
            var ids = [];
            checkboxes.forEach(function(cb) {
                var pid = parseInt(cb.getAttribute('data-product-id') || '0', 10);
                if (pid > 0) ids.push(pid);
            });
            return ids;
        }

        function updateBatchBarState() {
            var ids = getSelectedCardIds();
            var totalChecks = document.querySelectorAll('.prod-card-checkbox').length;
            var counterEl = document.getElementById('batchSelectedCounter');
            if (counterEl) {
                counterEl.textContent = ids.length + ' selecionado' + (ids.length === 1 ? '' : 's');
            }

            var btnPub = document.getElementById('btnBatchPublish');
            var btnUnpub = document.getElementById('btnBatchUnpublish');
            var btnDel = document.getElementById('btnBatchDelete');
            var masterCheck = document.getElementById('selectAllCardsCheckbox');

            var hasSelection = ids.length > 0;
            [btnPub, btnUnpub, btnDel].forEach(function(btn) {
                if (btn) {
                    btn.disabled = !hasSelection;
                    btn.style.opacity = hasSelection ? '1' : '0.45';
                    btn.style.cursor = hasSelection ? 'pointer' : 'not-allowed';
                }
            });

            if (masterCheck) {
                masterCheck.checked = (totalChecks > 0 && ids.length === totalChecks);
                masterCheck.indeterminate = (ids.length > 0 && ids.length < totalChecks);
            }

            // Atualiza destaque visual dos cards selecionados
            document.querySelectorAll('.prod-card-checkbox').forEach(function(cb) {
                var pid = cb.getAttribute('data-product-id');
                var card = document.getElementById('prod-card-' + pid);
                if (card) {
                    if (cb.checked) {
                        card.style.boxShadow = '0 0 0 2px #45f3ff, 0 8px 24px rgba(69, 243, 255, 0.2)';
                    } else {
                        card.style.boxShadow = 'none';
                    }
                }
            });
        }

        function onCardCheckboxChange(productId) {
            updateBatchBarState();
        }

        function toggleSelectAllCards(masterCheckbox) {
            var isChecked = masterCheckbox.checked;
            document.querySelectorAll('.prod-card-checkbox').forEach(function(cb) {
                cb.checked = isChecked;
            });
            updateBatchBarState();
        }

        function toggleProductLiveStatus(productId, targetStatus) {
            if (!productId) return;
            var btn = document.getElementById('prod-toggle-status-btn-' + productId);
            var origHtml = btn ? btn.innerHTML : '';
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            }

            var fd = new FormData();
            fd.append('csrf_token', dropshipCsrfToken);
            fd.append('status', targetStatus);

            fetch(baseAdminUrl + '/product/' + productId + '/status', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fd
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (btn) btn.disabled = false;
                if (data.success) {
                    showToastFeedback(data.message, 'success');
                    var isLive = data.is_live;
                    var badge = document.getElementById('prod-status-badge-' + productId);
                    var card = document.getElementById('prod-card-' + productId);

                    if (badge) {
                        badge.style.background = isLive ? 'rgba(16, 185, 129, 0.95)' : 'rgba(245, 158, 11, 0.95)';
                        badge.style.color = isLive ? '#fff' : '#000';
                        badge.innerHTML = isLive ? '<i class="fas fa-check-circle"></i> ✅ No Ar' : '<i class="fas fa-hourglass-half"></i> ⏳ Em Revisão';
                        badge.title = isLive ? 'Status no Site: Ativo na Vitrine Pública' : 'Status no Site: Em Quarentena / Aguardando Aprovação Manual';
                    }

                    if (card) {
                        card.style.borderColor = isLive ? 'rgba(16, 185, 129, 0.35)' : 'rgba(245, 158, 11, 0.35)';
                    }

                    if (btn) {
                        var nextStatus = isLive ? 'draft' : 'published';
                        btn.onclick = function() { toggleProductLiveStatus(productId, nextStatus); };
                        btn.style.color = isLive ? '#fbbf24' : '#34d399';
                        btn.style.background = isLive ? 'rgba(245, 158, 11, 0.12)' : 'rgba(16, 185, 129, 0.15)';
                        btn.style.borderColor = isLive ? 'rgba(245, 158, 11, 0.35)' : 'rgba(16, 185, 129, 0.4)';
                        btn.title = isLive ? 'Ocultar da vitrine e mover para Quarentena' : 'Aprovar e Carregar este produto para o Site';
                        btn.innerHTML = isLive ? '<i class="fas fa-pause-circle"></i> ⏸️ Ocultar' : '<i class="fas fa-check-circle"></i> ✅ Carregar';
                    }
                } else {
                    if (btn) btn.innerHTML = origHtml;
                    showToastFeedback(data.message || 'Erro ao alterar status', 'error');
                }
            })
            .catch(function() {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = origHtml;
                }
                showToastFeedback('Erro de conexão ao salvar status.', 'error');
            });
        }

        function executeBatchStatus(action) {
            var ids = getSelectedCardIds();
            if (ids.length === 0) {
                showToastFeedback('Selecione ao menos um produto.', 'warning');
                return;
            }

            var btn = (action === 'publish') ? document.getElementById('btnBatchPublish') : document.getElementById('btnBatchUnpublish');
            var origHtml = btn ? btn.innerHTML : '';
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Aplicando...';
            }

            var fd = new FormData();
            fd.append('csrf_token', dropshipCsrfToken);
            fd.append('action', action);
            ids.forEach(function(id) {
                fd.append('ids[]', id);
            });

            fetch(baseAdminUrl + '/batch-status', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fd
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = origHtml;
                }
                if (data.success) {
                    showToastFeedback(data.message, 'success');
                    var isLive = data.is_live;
                    ids.forEach(function(pid) {
                        var badge = document.getElementById('prod-status-badge-' + pid);
                        var card = document.getElementById('prod-card-' + pid);
                        var bBtn = document.getElementById('prod-toggle-status-btn-' + pid);

                        if (badge) {
                            badge.style.background = isLive ? 'rgba(16, 185, 129, 0.95)' : 'rgba(245, 158, 11, 0.95)';
                            badge.style.color = isLive ? '#fff' : '#000';
                            badge.innerHTML = isLive ? '<i class="fas fa-check-circle"></i> ✅ No Ar' : '<i class="fas fa-hourglass-half"></i> ⏳ Em Revisão';
                        }
                        if (card) {
                            card.style.borderColor = isLive ? 'rgba(16, 185, 129, 0.35)' : 'rgba(245, 158, 11, 0.35)';
                        }
                        if (bBtn) {
                            var nextStatus = isLive ? 'draft' : 'published';
                            bBtn.onclick = function() { toggleProductLiveStatus(pid, nextStatus); };
                            bBtn.style.color = isLive ? '#fbbf24' : '#34d399';
                            bBtn.style.background = isLive ? 'rgba(245, 158, 11, 0.12)' : 'rgba(16, 185, 129, 0.15)';
                            bBtn.style.borderColor = isLive ? 'rgba(245, 158, 11, 0.35)' : 'rgba(16, 185, 129, 0.4)';
                            bBtn.title = isLive ? 'Ocultar da vitrine e mover para Quarentena' : 'Aprovar e Carregar este produto para o Site';
                            bBtn.innerHTML = isLive ? '<i class="fas fa-pause-circle"></i> ⏸️ Ocultar' : '<i class="fas fa-check-circle"></i> ✅ Carregar';
                        }
                    });
                } else {
                    showToastFeedback(data.message || 'Erro ao aplicar alteração em lote', 'error');
                }
            })
            .catch(function() {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = origHtml;
                }
                showToastFeedback('Erro de conexão ao processar ação em lote.', 'error');
            });
        }

        function executeBatchDelete() {
            var ids = getSelectedCardIds();
            if (ids.length === 0) {
                showToastFeedback('Selecione ao menos um produto para excluir.', 'warning');
                return;
            }

            if (!confirm('Tem certeza que deseja excluir os ' + ids.length + ' produtos selecionados do catálogo?')) {
                return;
            }

            var btn = document.getElementById('btnBatchDelete');
            var origHtml = btn ? btn.innerHTML : '';
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Excluindo...';
            }

            var fd = new FormData();
            fd.append('csrf_token', dropshipCsrfToken);
            ids.forEach(function(id) {
                fd.append('ids[]', id);
            });

            fetch(baseAdminUrl + '/batch-delete', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fd
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = origHtml;
                }
                if (data.success) {
                    showToastFeedback(data.message, 'success');
                    ids.forEach(function(pid) {
                        var card = document.getElementById('prod-card-' + pid);
                        if (card) {
                            card.style.transition = 'all 0.3s ease';
                            card.style.opacity = '0';
                            card.style.transform = 'scale(0.9)';
                            setTimeout(function() { card.remove(); updateBatchBarState(); }, 350);
                        }
                    });
                } else {
                    showToastFeedback(data.message || 'Erro ao excluir produtos selecionados', 'error');
                }
            })
            .catch(function() {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = origHtml;
                }
                showToastFeedback('Erro de conexão ao excluir produtos em lote.', 'error');
            });
        }

        window.onCardCheckboxChange = onCardCheckboxChange;
        window.toggleSelectAllCards = toggleSelectAllCards;
        window.toggleProductLiveStatus = toggleProductLiveStatus;
        window.executeBatchStatus = executeBatchStatus;
        window.executeBatchDelete = executeBatchDelete;

        window.updateProductCategory = updateProductCategory;
        window.updateProductOrigin = updateProductOrigin;
        window.toggleDailyCycleAutoSync = toggleDailyCycleAutoSync;
        let targetDeleteProductId = null;

        function openDeleteProductModal(id, title) {
            targetDeleteProductId = id;
            const titleEl = document.getElementById('modalDeleteProductTitle');
            if (titleEl) titleEl.textContent = title || ('Produto #' + id);
            const modal = document.getElementById('modalDeleteProduct');
            if (modal) modal.style.display = 'flex';
        }

        function closeDeleteProductModal() {
            targetDeleteProductId = null;
            const modal = document.getElementById('modalDeleteProduct');
            if (modal) modal.style.display = 'none';
        }

        async function executeProductDelete() {
            if (!targetDeleteProductId) return;
            const id = targetDeleteProductId;
            const btn = document.getElementById('btnConfirmDeleteProduct');
            const origHtml = btn ? btn.innerHTML : '';
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Excluindo...';
            }

            try {
                const fd = new FormData();
                fd.append('csrf_token', dropshipCsrfToken);
                fd.append('ajax', '1');

                const res = await fetch(baseAdminUrl + '/product/' + id + '/delete', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body: fd
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    closeDeleteProductModal();
                    const cardEl = document.querySelector(`[data-product-id="${id}"]`) || document.getElementById(`prod-card-${id}`);
                    if (cardEl) {
                        cardEl.style.transition = 'all 0.3s ease';
                        cardEl.style.opacity = '0';
                        cardEl.style.transform = 'scale(0.95)';
                        setTimeout(() => cardEl.remove(), 300);
                    }
                    if (window.dropshipProductsMedia) {
                        delete window.dropshipProductsMedia[String(id)];
                    }
                    showToastFeedback('🗑️ ' + (data.message || 'Produto excluído com sucesso do catálogo!'), 'success');
                } else {
                    showToastFeedback('Falha ao excluir produto: ' + ((data && data.message) ? data.message : 'Erro na requisição'), 'error');
                }
            } catch (e) {
                showToastFeedback('Erro de comunicação: ' + e.message, 'error');
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = origHtml;
                }
            }
        }

        function showToastFeedback(message, type) {
            const toast = document.getElementById('dsToastFeedback');
            const icon = document.getElementById('dsToastIcon');
            const msg = document.getElementById('dsToastMsg');
            if (!toast) return;

            if (icon) icon.textContent = (type === 'error') ? '❌' : ((type === 'warning') ? '⚠️' : '✅');
            if (msg) msg.textContent = message || '';
            toast.style.borderColor = (type === 'error') ? 'rgba(239,68,68,0.6)' : ((type === 'warning') ? 'rgba(245,158,11,0.6)' : 'rgba(69,243,255,0.4)');
            toast.style.display = 'inline-flex';

            clearTimeout(window._dsToastTimeout);
            window._dsToastTimeout = setTimeout(() => {
                toast.style.display = 'none';
            }, 3500);
        }

        // =========================================================================
        // Configurações do Card (Tamanhos, Cores, Frete e Fornecedor AliExpress)
        // =========================================================================
        let activeProductConfigId = null;
        let currentConfigSizes = [];
        let currentConfigColors = [];

        function escapeHtml(str) {
            if (!str) return '';
            var div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        function openProductConfigModal(id) {
            const prod = dropshipProductsMedia ? dropshipProductsMedia[String(id)] : null;
            if (!prod) return;

            activeProductConfigId = id;
            currentConfigSizes = Array.isArray(prod.sizes) ? [...prod.sizes] : [];
            currentConfigColors = Array.isArray(prod.colors) ? [...prod.colors] : [];

            const titleEl = document.getElementById('configModalProductTitle');
            if (titleEl) {
                titleEl.textContent = (prod.title || 'Produto #' + id) + ' (ID AliExpress: ' + (prod.aliexpressId || 'N/A') + ')';
                titleEl.title = prod.title || '';
            }

            const suppLink = document.getElementById('configModalSupplierLink');
            if (suppLink) {
                suppLink.href = prod.directAeUrl || (prod.aliexpressUrl || '#');
            }

            const urlInput = document.getElementById('configAliexpressUrl');
            if (urlInput) {
                urlInput.value = prod.aliexpressUrl || prod.directAeUrl || '';
            }

            const freteTipoSelect = document.getElementById('configFreteTipo');
            const freteValorInput = document.getElementById('configFreteValor');
            const fTipo = prod.freteTipo || (prod.freteGratis ? 'gratis' : 'pago');
            if (freteTipoSelect) {
                freteTipoSelect.value = fTipo;
            }
            if (freteValorInput) {
                freteValorInput.value = (prod.freteValor !== undefined && prod.freteValor !== null) ? parseFloat(prod.freteValor).toFixed(2) : '0.00';
            }
            toggleShippingTypeInput(fTipo);

            renderConfigSizeTags();
            renderConfigColorTags();

            const st = document.getElementById('configModalStatus');
            if (st) {
                st.style.display = 'none';
                st.textContent = '';
            }

            const modal = document.getElementById('modalProductConfig');
            if (modal) modal.style.display = 'flex';
        }

        function closeProductConfigModal() {
            const modal = document.getElementById('modalProductConfig');
            if (modal) modal.style.display = 'none';
            activeProductConfigId = null;
        }

        const configModalEl = document.getElementById('modalProductConfig');
        if (configModalEl) {
            configModalEl.addEventListener('click', function(e) {
                if (e.target === this) closeProductConfigModal();
            });
        }

        function toggleShippingTypeInput(val) {
            const group = document.getElementById('configFreteValorGroup');
            if (group) {
                group.style.display = (val === 'pago' || val === 'expresso') ? 'block' : 'none';
            }
        }

        function renderConfigSizeTags() {
            const container = document.getElementById('configSizesContainer');
            const badge = document.getElementById('configSizesCountBadge');
            if (!container) return;

            if (badge) {
                badge.textContent = currentConfigSizes.length + (currentConfigSizes.length === 1 ? ' tamanho' : ' tamanhos');
            }

            if (currentConfigSizes.length === 0) {
                container.innerHTML = '<span style="color: #64748b; font-size: 12px; font-style: italic;">Nenhum tamanho cadastrado. Adicione abaixo.</span>';
                return;
            }

            let html = '';
            currentConfigSizes.forEach((sz, idx) => {
                html += `<span style="background: rgba(250, 204, 21, 0.15); border: 1px solid rgba(250, 204, 21, 0.45); color: #facc15; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px;">
                    <span>${escapeHtml(sz)}</span>
                    <button type="button" onclick="removeConfigSizeTag(${idx})" style="background: none; border: none; color: #facc15; font-size: 14px; cursor: pointer; padding: 0; line-height: 1;" title="Remover">&times;</button>
                </span>`;
            });
            container.innerHTML = html;
        }

        function addConfigSizeTag(val) {
            const input = document.getElementById('configNewSizeInput');
            let sizeVal = val ? String(val).trim() : (input ? input.value.trim() : '');
            if (!sizeVal) return;

            if (/^[a-z]{1,4}$/i.test(sizeVal)) {
                sizeVal = sizeVal.toUpperCase();
            }

            if (!currentConfigSizes.includes(sizeVal)) {
                currentConfigSizes.push(sizeVal);
                renderConfigSizeTags();
            }
            if (input && !val) input.value = '';
        }

        function addSizesBatch(arr) {
            if (!Array.isArray(arr)) return;
            arr.forEach(s => {
                let v = String(s).trim();
                if (/^[a-z]{1,4}$/i.test(v)) v = v.toUpperCase();
                if (v && !currentConfigSizes.includes(v)) {
                    currentConfigSizes.push(v);
                }
            });
            renderConfigSizeTags();
        }

        function removeConfigSizeTag(idx) {
            if (idx >= 0 && idx < currentConfigSizes.length) {
                currentConfigSizes.splice(idx, 1);
                renderConfigSizeTags();
            }
        }

        function renderConfigColorTags() {
            const container = document.getElementById('configColorsContainer');
            const badge = document.getElementById('configColorsCountBadge');
            if (!container) return;

            if (badge) {
                badge.textContent = currentConfigColors.length + (currentConfigColors.length === 1 ? ' cor' : ' cores');
            }

            if (currentConfigColors.length === 0) {
                container.innerHTML = '<span style="color: #64748b; font-size: 12px; font-style: italic;">Nenhuma cor cadastrada. Adicione abaixo.</span>';
                return;
            }

            const colorHexMap = {
                'preto': '#18181b', 'preta': '#18181b', 'black': '#18181b', 'grafite': '#374151',
                'branco': '#f8fafc', 'branca': '#f8fafc', 'white': '#f8fafc',
                'cinza': '#64748b', 'prata': '#cbd5e1',
                'azul': '#3b82f6', 'marinho': '#1e3a8a',
                'vermelho': '#ef4444', 'vermelha': '#ef4444', 'vinho': '#881337',
                'rosa': '#f472b6', 'pink': '#ec4899', 'rose': '#fb7185',
                'verde': '#22c55e', 'oliva': '#65a30d',
                'bege': '#d6d3d1', 'marrom': '#78350f',
                'amarelo': '#facc15', 'dourado': '#f59e0b',
                'roxo': '#a855f7', 'roxa': '#a855f7', 'laranja': '#f97316'
            };

            let html = '';
            currentConfigColors.forEach((col, idx) => {
                const cLower = col.toLowerCase();
                let swatchHex = '#a855f7';
                for (let k in colorHexMap) {
                    if (cLower.includes(k)) {
                        swatchHex = colorHexMap[k];
                        break;
                    }
                }

                html += `<span style="background: rgba(192, 132, 252, 0.15); border: 1px solid rgba(192, 132, 252, 0.45); color: #e9d5ff; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px;">
                    <span style="width: 10px; height: 10px; border-radius: 50%; background: ${swatchHex}; border: 1px solid rgba(255,255,255,0.4);"></span>
                    <span>${escapeHtml(col)}</span>
                    <button type="button" onclick="removeConfigColorTag(${idx})" style="background: none; border: none; color: #e9d5ff; font-size: 14px; cursor: pointer; padding: 0; line-height: 1;" title="Remover">&times;</button>
                </span>`;
            });
            container.innerHTML = html;
        }

        function addConfigColorTag(val) {
            const input = document.getElementById('configNewColorInput');
            let colVal = val ? String(val).trim() : (input ? input.value.trim() : '');
            if (!colVal) return;

            colVal = colVal.charAt(0).toUpperCase() + colVal.slice(1);

            if (!currentConfigColors.includes(colVal)) {
                currentConfigColors.push(colVal);
                renderConfigColorTags();
            }
            if (input && !val) input.value = '';
        }

        function removeConfigColorTag(idx) {
            if (idx >= 0 && idx < currentConfigColors.length) {
                currentConfigColors.splice(idx, 1);
                renderConfigColorTags();
            }
        }

        async function saveProductConfig() {
            if (!activeProductConfigId) return;

            const btn = document.getElementById('btnSaveProductConfig');
            const statusEl = document.getElementById('configModalStatus');
            const originalText = btn ? btn.innerHTML : '';

            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando Ajustes...';
            }

            const fTipo = document.getElementById('configFreteTipo').value;
            const fValor = parseFloat(document.getElementById('configFreteValor').value) || 0.0;
            const fGratis = (fTipo === 'gratis') ? '1' : '0';
            const aeUrl = document.getElementById('configAliexpressUrl').value.trim();

            const payload = new URLSearchParams();
            payload.append('csrf_token', dropshipCsrfToken);
            payload.append('_token', dropshipCsrfToken);
            payload.append('sizes', currentConfigSizes.join(', '));
            payload.append('colors', currentConfigColors.join(', '));
            payload.append('frete_tipo', fTipo);
            payload.append('frete_gratis', fGratis);
            payload.append('frete_valor', fValor);
            payload.append('aliexpress_url', aeUrl);

            try {
                const resp = await fetch(baseAdminUrl + '/product/' + activeProductConfigId + '/config', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: payload.toString()
                });

                const data = await resp.json();

                if (data.success) {
                    const prod = dropshipProductsMedia[String(activeProductConfigId)];
                    if (prod) {
                        prod.sizes = data.sizes || currentConfigSizes;
                        prod.colors = data.colors || currentConfigColors;
                        prod.freteTipo = data.frete_tipo || fTipo;
                        prod.freteGratis = data.frete_gratis;
                        prod.freteValor = data.frete_valor;
                        if (data.aliexpress_url) prod.aliexpressUrl = data.aliexpress_url;
                    }

                    if (statusEl) {
                        statusEl.style.display = 'block';
                        statusEl.style.background = 'rgba(16, 185, 129, 0.15)';
                        statusEl.style.border = '1px solid rgba(16, 185, 129, 0.4)';
                        statusEl.style.color = '#34d399';
                        statusEl.textContent = data.message || 'Configurações e variações salvas com sucesso!';
                    }

                    if (typeof showToastFeedback === 'function') {
                        showToastFeedback(data.message, 'success');
                    }

                    setTimeout(() => {
                        closeProductConfigModal();
                    }, 1400);
                } else {
                    if (statusEl) {
                        statusEl.style.display = 'block';
                        statusEl.style.background = 'rgba(239, 68, 68, 0.15)';
                        statusEl.style.border = '1px solid rgba(239, 68, 68, 0.4)';
                        statusEl.style.color = '#f87171';
                        statusEl.textContent = data.message || 'Erro ao salvar ajustes.';
                    }
                }
            } catch (err) {
                if (statusEl) {
                    statusEl.style.display = 'block';
                    statusEl.style.background = 'rgba(239, 68, 68, 0.15)';
                    statusEl.style.border = '1px solid rgba(239, 68, 68, 0.4)';
                    statusEl.style.color = '#f87171';
                    statusEl.textContent = 'Erro de rede ou servidor ao salvar ajustes: ' + err.message;
                }
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            }
        }

        window.openProductConfigModal = openProductConfigModal;
        window.closeProductConfigModal = closeProductConfigModal;
        window.addConfigSizeTag = addConfigSizeTag;
        window.addSizesBatch = addSizesBatch;
        window.removeConfigSizeTag = removeConfigSizeTag;
        window.addConfigColorTag = addConfigColorTag;
        window.removeConfigColorTag = removeConfigColorTag;
        window.toggleShippingTypeInput = toggleShippingTypeInput;
        window.saveProductConfig = saveProductConfig;

        window.openDeleteProductModal = openDeleteProductModal;
        window.closeDeleteProductModal = closeDeleteProductModal;
        window.executeProductDelete = executeProductDelete;
        window.showToastFeedback = showToastFeedback;
        window.downloadCurrentModalVideo = downloadCurrentModalVideo;
        window.downloadCardVideo = downloadCardVideo;
    </script>

    <!-- Modal Profissional de Exclusão Individual de Produto -->
    <div id="modalDeleteProduct" style="display: none; position: fixed; inset: 0; z-index: 99999; background: rgba(0,0,0,0.82); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 20px;">
        <div style="background: #111722; border: 1px solid rgba(239, 68, 68, 0.4); border-radius: 12px; width: 100%; max-width: 480px; padding: 24px; box-shadow: 0 20px 50px rgba(0,0,0,0.85); animation: dsModalIn 0.2s ease;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
                <div style="width: 42px; height: 42px; border-radius: 10px; background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); display: flex; align-items: center; justify-content: center; color: #ef4444; font-size: 18px; flex-shrink: 0;">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <div>
                    <h3 style="margin: 0; color: #fff; font-size: 16px; font-weight: 700;">Remover Produto do Catálogo</h3>
                    <p style="margin: 2px 0 0 0; color: #94a3b8; font-size: 12px;">Confirme se deseja desativar e excluir este item da loja.</p>
                </div>
            </div>
            <div id="modalDeleteProductTitle" style="background: rgba(0,0,0,0.35); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 10px 14px; font-size: 13px; color: #cbd5e1; font-weight: 600; margin-bottom: 18px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"></div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeDeleteProductModal()" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15); color: #fff; padding: 8px 16px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;">
                    Cancelar
                </button>
                <button type="button" id="btnConfirmDeleteProduct" onclick="executeProductDelete()" style="background: linear-gradient(135deg, #ef4444, #dc2626); border: none; color: #fff; padding: 8px 20px; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 14px rgba(239,68,68,0.35);">
                    <i class="fas fa-trash-alt"></i> Excluir Produto
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Flutuante Profissional -->
    <div id="dsToastFeedback" style="display: none; position: fixed; bottom: 24px; right: 24px; z-index: 100000; background: #0f172a; border: 1px solid rgba(69, 243, 255, 0.4); border-radius: 8px; padding: 12px 18px; color: #fff; font-size: 13px; font-weight: 600; box-shadow: 0 10px 30px rgba(0,0,0,0.7); align-items: center; gap: 10px; animation: dsModalIn 0.25s ease;">
        <span id="dsToastIcon">✅</span>
        <span id="dsToastMsg">Operação realizada com sucesso!</span>
    </div>

    <!-- Modal Limpar Catálogo -->
    <div id="modalClearCatalog" style="display: none; position: fixed; inset: 0; z-index: 99999; background: rgba(0,0,0,0.85); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 20px;">
        <div style="background: #1d2327; border: 1px solid rgba(239,68,68,0.4); border-radius: 12px; width: 100%; max-width: 520px; padding: 26px; box-shadow: 0 10px 30px rgba(0,0,0,0.6);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="margin: 0; color: #ff6b6b; font-size: 18px; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-trash-alt"></i> Limpar Catálogo DomainDrop
                </h3>
                <button type="button" onclick="document.getElementById('modalClearCatalog').style.display='none'" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">✕</button>
            </div>
            <p style="color: #cbd5e1; font-size: 14px; line-height: 1.5; margin-bottom: 20px;">
                Escolha a forma de limpeza do catálogo. <strong>Pedidos reais de clientes são sempre preservados</strong> por segurança e rastreabilidade fiscal.
            </p>
            <form action="<?= $baseUrl ?>/admin/dropship/clear-catalog" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px;">
                    <label style="display: flex; align-items: flex-start; gap: 10px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 12px; cursor: pointer;">
                        <input type="radio" name="action" value="clear_all" checked style="margin-top: 3px;">
                        <div>
                            <strong style="color: #fff; font-size: 14px;">Zerar Catálogo Completo (Recomendado)</strong>
                            <div style="color: #94a3b8; font-size: 12px; margin-top: 2px;">Limpa todos os itens não vinculados a pedidos, deixando o catálogo limpinho para a próxima aquisição de 100 itens.</div>
                        </div>
                    </label>
                    <label style="display: flex; align-items: flex-start; gap: 10px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 12px; cursor: pointer;">
                        <input type="radio" name="action" value="purge_drafts" style="margin-top: 3px;">
                        <div>
                            <strong style="color: #fff; font-size: 14px;">Purgar Apenas Rascunhos / Despublicados</strong>
                            <div style="color: #94a3b8; font-size: 12px; margin-top: 2px;">Remove somente itens que não estão ativos para venda na loja, mantendo os produtos já publicados.</div>
                        </div>
                    </label>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="document.getElementById('modalClearCatalog').style.display='none'" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.2); color: #fff; padding: 10px 18px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;">Cancelar</button>
                    <button type="submit" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border: none; color: #fff; padding: 10px 20px; border-radius: 6px; font-size: 13px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                        <i class="fas fa-trash-alt"></i> Confirmar e Limpar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Adquirir Novos Itens (24h) -->
    <div id="modalAcquireNew" style="display: none; position: fixed; inset: 0; z-index: 99999; background: rgba(0,0,0,0.85); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 20px;">
        <div style="background: #1d2327; border: 1px solid rgba(212,175,55,0.4); border-radius: 12px; width: 100%; max-width: 520px; padding: 26px; box-shadow: 0 10px 30px rgba(0,0,0,0.6);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="margin: 0; color: #FFE082; font-size: 18px; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-bolt"></i> Adquirir Novos Produtos (Ciclo 24 Horas)
                </h3>
                <button type="button" onclick="document.getElementById('modalAcquireNew').style.display='none'" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">✕</button>
            </div>
            <p style="color: #cbd5e1; font-size: 14px; line-height: 1.5; margin-bottom: 16px;">
                O motor autônomo irá buscar os produtos com maior reputação, volume de vendas e menor taxa de disputa nas 10 categorias oficiais do AliExpress. Todos os produtos entrarão <strong>100% como Publicados (Em Venda)</strong>.
            </p>
            <form action="<?= $baseUrl ?>/admin/dropship/acquire-new" method="POST" id="formAcquireNew">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <div style="margin-bottom: 18px;">
                    <label style="display: block; color: #94a3b8; font-size: 13px; margin-bottom: 6px;">Produtos por Categoria Oficial:</label>
                    <select name="items_per_category" style="width: 100%; background: #0c0f12; border: 1px solid rgba(255,255,255,0.15); color: #fff; padding: 10px 12px; border-radius: 6px; font-size: 14px;">
                        <option value="10" selected>10 itens / categoria (90 a 100 produtos no total - Recomendado)</option>
                        <option value="5">5 itens / categoria (45 a 50 produtos no total)</option>
                        <option value="15">15 itens / categoria (135 a 150 produtos no total)</option>
                        <option value="20">20 itens / categoria (180 a 200 produtos no total)</option>
                    </select>
                </div>
                <div style="margin-bottom: 18px;">
                    <label style="display: block; color: #94a3b8; font-size: 13px; margin-bottom: 6px;">Origem dos Entrepostos de Estoque:</label>
                    <select name="warehouse_origin" style="width: 100%; background: #0c0f12; border: 1px solid rgba(255,255,255,0.15); color: #fff; padding: 10px 12px; border-radius: 6px; font-size: 14px;">
                        <option value="ALL" <?= (($config['mining_warehouse_origin'] ?? 'ALL') === 'ALL') ? 'selected' : '' ?>>🌐 Global: China (CN) + Brasil (BR) [Recomendado — Máxima Variedade]</option>
                        <option value="BR" <?= (($config['mining_warehouse_origin'] ?? '') === 'BR') ? 'selected' : '' ?>>🇧🇷 Apenas Estoque no Brasil (Nacional — Envio em 3 a 7 dias)</option>
                        <option value="CN" <?= (($config['mining_warehouse_origin'] ?? '') === 'CN') ? 'selected' : '' ?>>🇨🇳 Apenas Entrepostos na China (Importação Direta DDP)</option>
                    </select>
                </div>
                <div style="background: rgba(212,175,55,0.08); border: 1px solid rgba(212,175,55,0.25); border-radius: 8px; padding: 14px; margin-bottom: 24px;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="replace_catalog" value="1" checked style="width: 16px; height: 16px;">
                        <span style="color: #FFE082; font-size: 13px; font-weight: 600;">
                            Zerar catálogo anterior antes de minerar (mantém catálogo 100% limpo)
                        </span>
                    </label>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="document.getElementById('modalAcquireNew').style.display='none'" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.2); color: #fff; padding: 10px 18px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;">Cancelar</button>
                    <button type="submit" id="btnSubmitAcquireNew" style="background: linear-gradient(135deg, #F0D060 0%, #D4AF37 100%); border: none; color: #0b0f17; padding: 10px 22px; border-radius: 6px; font-size: 13px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(212,175,55,0.3);">
                        <i class="fas fa-play"></i> Iniciar Mineração Limpa para 24h
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
    document.getElementById('formAcquireNew')?.addEventListener('submit', function(e) {
        e.preventDefault();
        var form = document.getElementById('formAcquireNew');
        var rep = form.querySelector('input[name="replace_catalog"]')?.checked;
        var items = form.querySelector('select[name="items_per_category"]')?.value || 10;
        var origin = form.querySelector('select[name="warehouse_origin"]')?.value || 'ALL';
        if (typeof window.runProgressiveMiningCycle === 'function') {
            window.runProgressiveMiningCycle({
                replaceCatalog: !!rep,
                itemsPerCategory: parseInt(items, 10),
                warehouseOrigin: origin
            });
        }
    });
    </script>

    <!-- Modal Relatório de Diagnóstico & Auditoria do Catálogo -->
    <div id="modalCatalogAudit" style="display: none; position: fixed; inset: 0; z-index: 99999; background: rgba(0,0,0,0.85); backdrop-filter: blur(6px); align-items: center; justify-content: center; padding: 20px;">
        <div style="background: #0f172a; border: 1px solid rgba(69, 243, 255, 0.4); border-radius: 14px; width: 100%; max-width: 820px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 25px 60px rgba(0,0,0,0.9); overflow: hidden;">
            
            <!-- Header -->
            <div style="background: linear-gradient(135deg, rgba(69, 243, 255, 0.12) 0%, rgba(37, 99, 235, 0.18) 100%); border-bottom: 1px solid rgba(255,255,255,0.08); padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="background: linear-gradient(135deg, #45f3ff, #2563eb); color: #0f172a; width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 800; box-shadow: 0 4px 12px rgba(69, 243, 255, 0.35);">
                        <i class="fas fa-file-medical-alt"></i>
                    </div>
                    <div>
                        <h3 style="margin: 0; font-size: 17px; font-weight: 700; color: #fff;">Relatório Automático de Auditoria do Catálogo</h3>
                        <div style="font-size: 12px; color: #94a3b8;">Classificação de procedência logística (Brasil / China), saúde de links e integridade de fotos</div>
                    </div>
                </div>
                <button type="button" onclick="closeCatalogAuditModal()" style="background: none; border: none; color: #94a3b8; font-size: 22px; cursor: pointer; padding: 4px; line-height: 1;">&times;</button>
            </div>

            <!-- Body -->
            <div style="padding: 20px 24px; overflow-y: auto; display: flex; flex-direction: column; gap: 16px; flex: 1;">
                
                <!-- KPI Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 10px;">
                    <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 12px; text-align: center;">
                        <div style="font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: 600;">Total Analisado</div>
                        <div id="auditKpiTotal" style="font-size: 22px; font-weight: 800; color: #fff; font-family: monospace; margin-top: 2px;">0</div>
                    </div>
                    <div style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 8px; padding: 12px; text-align: center;">
                        <div style="font-size: 11px; color: #6ee7b7; text-transform: uppercase; font-weight: 600;">🇧🇷 Do Brasil</div>
                        <div id="auditKpiBrazil" style="font-size: 22px; font-weight: 800; color: #10b981; font-family: monospace; margin-top: 2px;">0</div>
                    </div>
                    <div style="background: rgba(56, 189, 248, 0.08); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 8px; padding: 12px; text-align: center;">
                        <div style="font-size: 11px; color: #7dd3fc; text-transform: uppercase; font-weight: 600;">🇨🇳 Do China</div>
                        <div id="auditKpiChina" style="font-size: 22px; font-weight: 800; color: #38bdf8; font-family: monospace; margin-top: 2px;">0</div>
                    </div>
                    <div style="background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 8px; padding: 12px; text-align: center;">
                        <div style="font-size: 11px; color: #fca5a5; text-transform: uppercase; font-weight: 600;">⚠️ Não Encontrados</div>
                        <div id="auditKpiDead" style="font-size: 22px; font-weight: 800; color: #ef4444; font-family: monospace; margin-top: 2px;">0</div>
                    </div>
                    <div style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 8px; padding: 12px; text-align: center;">
                        <div style="font-size: 11px; color: #fde68a; text-transform: uppercase; font-weight: 600;">🛒 Prontos p/ Venda</div>
                        <div id="auditKpiReady" style="font-size: 22px; font-weight: 800; color: #fbbf24; font-family: monospace; margin-top: 2px;">0</div>
                    </div>
                </div>

                <!-- Lista Formatada em Linhas Numeradas -->
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12px; font-weight: 700; color: #cbd5e1; text-transform: uppercase; letter-spacing: 0.5px;">
                            Levantamento Item por Item (Auditoria Operacional):
                        </span>
                        <span id="auditCopyFeedback" style="display: none; font-size: 12px; color: #10b981; font-weight: 700;">
                            <i class="fas fa-check"></i> Relatório copiado para a área de transferência!
                        </span>
                    </div>
                    <div id="auditReportListContainer" style="max-height: 380px; overflow-y: auto; background: #070a10; border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 13px; line-height: 1.8; color: #e2e8f0;">
                        <!-- Injetado dinamicamente -->
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div style="background: rgba(0,0,0,0.3); border-top: 1px solid rgba(255,255,255,0.08); padding: 14px 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <button type="button" id="btnPurgeInvalidAudit" onclick="purgeAllInvalidAuditProducts()" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border: none; color: #fff; padding: 9px 18px; border-radius: 6px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(239,68,68,0.3);">
                    <i class="fas fa-trash-alt"></i> Excluir Todos os Inválidos (<span id="auditPurgeCount">0</span> itens)
                </button>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <button type="button" onclick="exportCatalogAuditReport('txt')" style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.4); color: #fbbf24; padding: 9px 14px; border-radius: 6px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;" title="Baixar relatório completo em arquivo .txt">
                        <i class="fas fa-file-alt"></i> Exportar TXT
                    </button>
                    <button type="button" onclick="exportCatalogAuditReport('csv')" style="background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.4); color: #4ade80; padding: 9px 14px; border-radius: 6px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;" title="Baixar planilha formatada para Excel (.csv)">
                        <i class="fas fa-file-excel"></i> Exportar Excel (CSV)
                    </button>
                    <button type="button" onclick="copyCatalogAuditReport()" style="background: rgba(69, 243, 255, 0.15); border: 1px solid rgba(69, 243, 255, 0.4); color: #45f3ff; padding: 9px 14px; border-radius: 6px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fas fa-copy"></i> Copiar
                    </button>
                    <button type="button" onclick="closeCatalogAuditModal()" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.2); color: #fff; padding: 9px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    let auditDeadProductIds = [];
    let lastGeneratedAuditText = '';

    function openCatalogAuditModal() {
        const modal = document.getElementById('modalCatalogAudit');
        if (!modal) return;

        const mediaSource = window.dropshipProductsMedia || (typeof dropshipProductsMedia !== 'undefined' ? dropshipProductsMedia : {});
        const items = Object.values(mediaSource);
        auditDeadProductIds = [];
        let total = items.length;
        let brCount = 0;
        let cnCount = 0;
        let globalCount = 0;
        let deadCount = 0;

        let htmlRows = [];
        let textRows = [];

        items.forEach(function(item, idx) {
            const num = idx + 1;
            const isReal = item.isRealId && /^\d{10,24}$/.test(String(item.aliexpressId || ''));
            let status = '';
            let badgeHtml = '';

            // Se não possuir ID oficial válido, é considerado anúncio não encontrado / inativo
            if (!isReal) {
                status = 'Página não encontrada.';
                badgeHtml = '<span style="color: #ef4444; font-weight: 700;">❌ Página não encontrada.</span>';
                deadCount++;
                auditDeadProductIds.push(item.id);
            } else if (item.isNational || item.origin === 'BR') {
                status = 'Do Brasil.';
                badgeHtml = '<span style="color: #10b981; font-weight: 700;">🇧🇷 Do Brasil.</span>';
                brCount++;
            } else if (item.isChina || item.origin === 'CN') {
                status = 'Do China.';
                badgeHtml = '<span style="color: #38bdf8; font-weight: 700;">🇨🇳 Do China.</span>';
                cnCount++;
            } else {
                status = 'Do Global.';
                badgeHtml = '<span style="color: #cbd5e1; font-weight: 700;">🌐 Do Global.</span>';
                globalCount++;
            }

            const escapedTitle = escapeHtml(item.title || 'Produto');
            textRows.push(`${num}. ${item.title}: ${status}`);
            htmlRows.push(`
                <div style="display: flex; justify-content: space-between; align-items: baseline; gap: 12px; padding: 4px 0; border-bottom: 1px dashed rgba(255,255,255,0.05);">
                    <div style="flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${escapedTitle}">
                        <strong style="color: #94a3b8;">${num}.</strong> ${escapedTitle}
                    </div>
                    <div style="white-space: nowrap;">${badgeHtml}</div>
                </div>
            `);
        });

        const readyCount = total - deadCount;
        textRows.push(`\nTotal: ${readyCount} Prontos para Venda.`);

        lastGeneratedAuditText = textRows.join('\n');

        // Atualiza contadores visuais
        document.getElementById('auditKpiTotal').textContent = total;
        document.getElementById('auditKpiBrazil').textContent = brCount;
        document.getElementById('auditKpiChina').textContent = cnCount;
        document.getElementById('auditKpiDead').textContent = deadCount;
        document.getElementById('auditKpiReady').textContent = readyCount;
        document.getElementById('auditPurgeCount').textContent = deadCount;

        const btnPurge = document.getElementById('btnPurgeInvalidAudit');
        if (btnPurge) {
            btnPurge.disabled = (deadCount === 0);
            btnPurge.style.opacity = (deadCount === 0) ? '0.5' : '1';
            btnPurge.style.cursor = (deadCount === 0) ? 'not-allowed' : 'pointer';
        }

        const container = document.getElementById('auditReportListContainer');
        if (container) {
            if (htmlRows.length) {
                container.innerHTML = htmlRows.join('') + `
                    <div style="margin-top: 14px; padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.15); display: flex; justify-content: space-between; align-items: center; font-weight: 800;">
                        <span style="color: #fbbf24;">Total Prontos para Venda:</span>
                        <span style="color: #10b981; font-size: 15px;">${readyCount} itens ativos</span>
                    </div>
                `;
            } else {
                container.innerHTML = '<div style="text-align: center; color: #94a3b8; padding: 20px;">Nenhum produto cadastrado no catálogo para auditoria.</div>';
            }
        }

        modal.style.display = 'flex';
    }

    function closeCatalogAuditModal() {
        const modal = document.getElementById('modalCatalogAudit');
        if (modal) modal.style.display = 'none';
    }

    function copyCatalogAuditReport() {
        if (!lastGeneratedAuditText) return;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(lastGeneratedAuditText).then(() => {
                showCopyFeedback();
            }).catch(() => {
                fallbackCopy(lastGeneratedAuditText);
            });
        } else {
            fallbackCopy(lastGeneratedAuditText);
        }
    }

    function exportCatalogAuditReport(format = 'txt') {
        const now = new Date().toISOString().slice(0, 10);
        let blob, filename;

        if (format === 'csv') {
            const mediaSource = window.dropshipProductsMedia || (typeof dropshipProductsMedia !== 'undefined' ? dropshipProductsMedia : {});
            const items = Object.values(mediaSource);

            // BOM (\uFEFF) para garantir abertura com caracteres acentuados corretos no Excel
            let csvContent = "\uFEFF";
            csvContent += "Item;ID AliExpress;Título;Procedência;Preço Custo (R$);Preço Venda (R$);Lucro (R$);Status\n";

            items.forEach((item, idx) => {
                const isReal = item.isRealId && /^\d{10,24}$/.test(String(item.aliexpressId || ''));
                const status = !isReal ? 'Página não encontrada' : (item.isNational || item.origin === 'BR' ? 'Do Brasil' : 'Do Global');
                const cleanTitle = (item.title || '').replace(/"/g, '""');
                const cost = (parseFloat(item.costBrl) || 0).toFixed(2).replace('.', ',');
                const price = (parseFloat(item.saleBrl) || 0).toFixed(2).replace('.', ',');
                const profit = (parseFloat(item.profitBrl) || 0).toFixed(2).replace('.', ',');

                csvContent += `${idx + 1};"${item.aliexpressId || item.id || ''}";"${cleanTitle}";"${status}";"${cost}";"${price}";"${profit}";"${isReal ? 'Ativo' : 'Inválido'}"\n`;
            });

            blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            filename = `auditoria-catalogo-dropship-${now}.csv`;
        } else {
            const textToExport = lastGeneratedAuditText || 'Nenhum dado auditado.';
            blob = new Blob([textToExport], { type: 'text/plain;charset=utf-8;' });
            filename = `auditoria-catalogo-dropship-${now}.txt`;
        }

        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(link.href);
    }

    function fallbackCopy(text) {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        try {
            document.execCommand('copy');
            showCopyFeedback();
        } catch (e) {
            alert('Não foi possível copiar automaticamente. Selecione e copie o texto manualmente.');
        }
        document.body.removeChild(ta);
    }

    function showCopyFeedback() {
        const fb = document.getElementById('auditCopyFeedback');
        if (fb) {
            fb.style.display = 'inline-flex';
            setTimeout(() => { fb.style.display = 'none'; }, 3000);
        }
    }

    async function purgeAllInvalidAuditProducts() {
        if (!auditDeadProductIds.length) {
            alert('Nenhum produto inválido identificado para exclusão.');
            return;
        }

        if (!confirm(`Deseja realmente excluir todos os ${auditDeadProductIds.length} produtos inválidos/com página não encontrada do seu catálogo?`)) {
            return;
        }

        const btn = document.getElementById('btnPurgeInvalidAudit');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Excluindo produtos inválidos...';
        }

        try {
            const formData = new FormData();
            formData.append('csrf_token', dropshipCsrfToken);
            formData.append('ajax', '1');
            formData.append('ids', JSON.stringify(auditDeadProductIds));

            const res = await fetch(<?= json_encode($baseUrl . '/admin/dropship/purge-invalid-products', JSON_UNESCAPED_SLASHES) ?>, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: formData
            });

            const data = await res.json();
            if (res.ok && data.success) {
                // Remove todos os cards da tela
                auditDeadProductIds.forEach(function(id) {
                    const cardEl = document.querySelector(`[data-product-id="${id}"]`) || document.getElementById(`prod-card-${id}`);
                    if (cardEl) cardEl.remove();
                    delete dropshipProductsMedia[String(id)];
                });

                alert(`Sucesso: ${data.deleted_count || auditDeadProductIds.length} produtos inválidos foram expurgados com sucesso!`);
                openCatalogAuditModal(); // re-renderiza o modal atualizado
            } else {
                alert('Erro ao excluir produtos: ' + ((data && data.message) ? data.message : 'Erro na requisição'));
            }
        } catch (err) {
            alert('Erro de rede ao excluir: ' + err.message);
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-trash-alt"></i> Excluir Todos os Inválidos (' + auditDeadProductIds.length + ' itens)';
            }
        }
    }

    // Exportação explícita e defensiva do modal de auditoria para o escopo global
    window.openCatalogAuditModal = openCatalogAuditModal;
    window.closeCatalogAuditModal = closeCatalogAuditModal;
    window.copyCatalogAuditReport = copyCatalogAuditReport;
    window.exportCatalogAuditReport = exportCatalogAuditReport;
    window.purgeAllInvalidAuditProducts = purgeAllInvalidAuditProducts;
    </script>
</div>
