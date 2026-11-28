/**
 * Theme RachidD Store — JavaScript Oficial Consolidado
 * Otimizado com carregamento não-bloqueante (defer) pelo DomainSEO AssetPipeline
 */
(function() {
    'use strict';

    /* ==========================================================================
       1. PROTEÇÃO DE IMAGENS E CONTEÚDO
       ========================================================================== */
    document.addEventListener('contextmenu', function(e) {
        if (e.target && (e.target.tagName === 'IMG' || (e.target.closest && e.target.closest('img')))) {
            e.preventDefault();
            return false;
        }
    }, false);

    document.addEventListener('dragstart', function(e) {
        if (e.target && (e.target.tagName === 'IMG' || (e.target.closest && e.target.closest('img')))) {
            e.preventDefault();
            return false;
        }
    }, false);

    /* ==========================================================================
       2. DRAWER LATERAL DE DEPARTAMENTOS & HAMBÚRGUER MOBILE
       ========================================================================== */
    var btnToggle = document.getElementById('btnToggleCatDrawer');
    var btnMobileToggle = document.getElementById('btnMobileHeaderToggle');
    var btnClose = document.getElementById('btnCloseCatDrawer');
    var overlay = document.getElementById('catDrawerOverlay');
    var panel = document.getElementById('catDrawerPanel');

    function openDrawer() {
        if (overlay && panel) {
            overlay.classList.add('active');
            panel.classList.add('active');
            document.body.style.overflow = 'hidden';
            if (btnToggle) btnToggle.setAttribute('aria-expanded', 'true');
            if (btnMobileToggle) btnMobileToggle.setAttribute('aria-expanded', 'true');
        }
    }

    function closeDrawer() {
        if (overlay && panel) {
            overlay.classList.remove('active');
            panel.classList.remove('active');
            document.body.style.overflow = '';
            if (btnToggle) btnToggle.setAttribute('aria-expanded', 'false');
            if (btnMobileToggle) btnMobileToggle.setAttribute('aria-expanded', 'false');
        }
    }

    window.closeCatDrawer = closeDrawer;

    if (btnToggle) btnToggle.addEventListener('click', openDrawer);
    if (btnMobileToggle) btnMobileToggle.addEventListener('click', openDrawer);
    if (btnClose) btnClose.addEventListener('click', closeDrawer);
    if (overlay) overlay.addEventListener('click', closeDrawer);

    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeDrawer();
    });

    /* ==========================================================================
       3. SINCRONIZAÇÃO DE FILTROS E ROLAGEM SUAVE
       ========================================================================== */
    function normalizeText(str) {
        return String(str || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    }

    function resolveDepartmentCode(catStr) {
        var s = normalizeText(catStr);
        if (!s) return '';
        if (s === 'auto' || s.indexOf('automotiv') !== -1 || s.indexOf('veicular') !== -1 || s.indexOf('veiculo') !== -1 || (s.indexOf('pecas') !== -1 && s.indexOf('acessorios') !== -1 && s.indexOf('ferramenta') === -1)) return 'auto';
        if (s === 'lingerie' || s.indexOf('intima') !== -1 || s.indexOf('lingerie') !== -1 || s.indexOf('pijama') !== -1) return 'lingerie';
        if (s === 'fitness' || s.indexOf('fitness') !== -1 || s.indexOf('legging') !== -1 || s.indexOf('academia') !== -1) return 'fitness';
        if (s === 'fashion' || s.indexOf('roupa') !== -1 || s.indexOf('vestuario') !== -1 || s.indexOf('moda') !== -1 || s.indexOf('bolsa') !== -1 || s.indexOf('calcado') !== -1 || s.indexOf('sapato') !== -1) return 'fashion';
        if (s === 'mobile_tv' || s.indexOf('celular') !== -1 || s.indexOf('smartphone') !== -1 || s.indexOf('projetor') !== -1 || s.indexOf('tv box') !== -1) return 'mobile_tv';
        if (s === 'smartwatch' || s.indexOf('relogio') !== -1 || s.indexOf('smartwatch') !== -1) return 'smartwatch';
        if (s === 'tools' || s.indexOf('ferramenta') !== -1 || s.indexOf('alicate') !== -1 || s.indexOf('crimpador') !== -1) return 'tools';
        if (s === 'beauty' || s.indexOf('beleza') !== -1 || s.indexOf('maquiagem') !== -1 || s.indexOf('saude') !== -1) return 'beauty';
        if (s === 'pets' || s.indexOf('pet') !== -1 || s.indexOf('cachorro') !== -1 || s.indexOf('gato') !== -1) return 'pets';
        if (s === 'home' || s.indexOf('casa') !== -1 || s.indexOf('iluminacao') !== -1 || s.indexOf('eletro') !== -1) return 'home';
        if (s === 'tech' || s.indexOf('eletron') !== -1 || s.indexOf('telefonia') !== -1 || s.indexOf('audio') !== -1) return 'tech';
        return s;
    }

    function matchesCategoryFilter(linkCat, currentCat) {
        var a = normalizeText(linkCat);
        var b = normalizeText(currentCat);
        if (!a || !b) return false;
        if (a === b) return true;

        var codeA = resolveDepartmentCode(a);
        var codeB = resolveDepartmentCode(b);
        if (codeA && codeB && codeA === codeB) return true;

        return false;
    }

    window.syncHeaderFilterActiveState = function(selectedCat, selectedSort, searchVal) {
        var cat = String(selectedCat || '').trim();
        var sort = String(selectedSort || '').trim();

        var searchInput = document.querySelector('.header-search-input');
        if (searchInput && typeof searchVal === 'string') {
            searchInput.value = searchVal;
        }

        document.querySelectorAll('.cat-drawer-item, .header-quick-chips a').forEach(function(el) {
            var isMatch = false;
            try {
                var parsed = new URL(el.href, window.location.origin);
                var linkCat = (parsed.searchParams.get('cat') || '').trim();
                var linkSort = (parsed.searchParams.get('sort') || '').trim();

                if (cat !== '') {
                    isMatch = matchesCategoryFilter(linkCat, cat);
                } else if (sort === 'sales') {
                    isMatch = (linkSort === 'sales' && linkCat === '');
                } else if (linkCat === '' && linkSort === '') {
                    isMatch = true;
                }
            } catch (err) {
                isMatch = false;
            }
            el.classList.toggle('active', isMatch);
        });
    };

    window.smoothScrollToProducts = function() {
        var target = document.getElementById('produtos') || document.getElementById('storeDynamicStage') || document.querySelector('.store-combobox-toolbar');
        if (!target) return;
        var header = document.querySelector('.site-header');
        var headerHeight = header ? header.offsetHeight : 80;
        var targetTop = target.getBoundingClientRect().top + window.pageYOffset - (headerHeight + 15);
        window.scrollTo({
            top: Math.max(0, targetTop),
            behavior: 'smooth'
        });
    };

    // Busca dinâmica instantânea no Palco quando já estiver na /loja
    var searchForm = document.querySelector('.header-search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            var stage = document.getElementById('storeDynamicStage');
            if (stage && window.filterCatalogDynamically) {
                e.preventDefault();
                var input = searchForm.querySelector('input[name="q"]');
                var qVal = input ? input.value.trim() : '';
                var targetUrl = searchForm.action + (qVal !== '' ? '?q=' + encodeURIComponent(qVal) : '') + '#produtos';
                window.filterCatalogDynamically(targetUrl, true, true);
            }
        });
    }

    // Filtragem dinâmica instantânea ao clicar em itens da gaveta
    document.addEventListener('click', function(e) {
        var link = e.target.closest('.cat-drawer-item, .header-quick-chips a');
        if (link && link.href) {
            var stage = document.getElementById('storeDynamicStage');
            if (stage && window.filterCatalogDynamically) {
                e.preventDefault();
                closeDrawer();
                window.filterCatalogDynamically(link.href, true, true);
            }
        }
    });

    /* ==========================================================================
       4. MÁSCARAS DE ENTRADA (CPF & TELEFONE) & UTILITÁRIOS DE FORMULÁRIO
       ========================================================================== */
    window.homeMaskCpf = function(el) {
        if (!el) return;
        var v = el.value.replace(/\D/g, '').slice(0, 11);
        if (v.length > 9) el.value = v.replace(/(\d{3})(\d{3})(\d{3})(\d{1,2})/, '$1.$2.$3-$4');
        else if (v.length > 6) el.value = v.replace(/(\d{3})(\d{3})(\d{1,3})/, '$1.$2.$3');
        else if (v.length > 3) el.value = v.replace(/(\d{3})(\d{1,3})/, '$1.$2');
        else el.value = v;
    };

    window.homeMaskPhone = function(el) {
        if (!el) return;
        var v = el.value.replace(/\D/g, '').slice(0, 11);
        if (v.length > 10) el.value = v.replace(/(\d{2})(\d{5})(\d{1,4})/, '($1) $2-$3');
        else if (v.length > 6) el.value = v.replace(/(\d{2})(\d{4})(\d{1,4})/, '($1) $2-$3');
        else if (v.length > 2) el.value = v.replace(/(\d{2})(\d{1,5})/, '($1) $2');
        else if (v.length > 0) el.value = '(' + v;
        else el.value = '';
    };

    window.togglePasswordVisibility = function(buttonEl, inputId) {
        var input = document.getElementById(inputId);
        if (!input) return;
        var isPass = input.type === 'password';
        input.type = isPass ? 'text' : 'password';
        if (buttonEl) {
            buttonEl.textContent = isPass ? '🙈' : '👁️';
            buttonEl.style.color = isPass ? '#F0D060' : '#94a3b8';
        }
    };

    /* ==========================================================================
       5. TELEMETRIA DE DEMANDA & CRONÔMETRO DE CICLO 24H (CONSOLIDADO)
       ========================================================================== */
    window.trackDropAction = function(productId, title, category, actionType) {
        if (!productId) return;
        var payload = JSON.stringify({
            product_id: String(productId),
            title: String(title || ''),
            category: String(category || 'geral'),
            action: String(actionType || 'view_details'),
            timestamp: Math.floor(Date.now() / 1000)
        });
        var base = (window.RACHIDD_BASE_URL || '').replace(/\/+$/, '');
        var endpoint = base + '/api/drop/track-demand';
        try {
            if (navigator.sendBeacon) {
                var blob = new Blob([payload], { type: 'application/json' });
                navigator.sendBeacon(endpoint, blob);
            } else {
                fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: payload,
                    keepalive: true
                }).catch(function() {});
            }
        } catch (e) {
            try {
                fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: payload,
                    keepalive: true
                }).catch(function() {});
            } catch (err) {}
        }
    };

    window.initRachidDCycleCountdown = function(cfg) {
        if (!cfg) return;
        var remSeconds = Math.max(0, parseInt(cfg.remainingSeconds, 10) || 0);
        var singleElem = cfg.singleElementId ? document.getElementById(cfg.singleElementId) : null;
        var hElem = cfg.hElementId ? document.getElementById(cfg.hElementId) : null;
        var mElem = cfg.mElementId ? document.getElementById(cfg.mElementId) : null;
        var sElem = cfg.sElementId ? document.getElementById(cfg.sElementId) : null;
        var cronUrl = cfg.cronUrl || '/dropship/cron-tick';
        var autoTickFired = false;
        var intervalId = null;

        function triggerTick() {
            if (autoTickFired) return;
            autoTickFired = true;
            fetch(cronUrl, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data && typeof data.seconds_remaining === 'number' && data.seconds_remaining > 0) {
                    remSeconds = data.seconds_remaining;
                    if (data.rotated) {
                        window.location.reload();
                        return;
                    }
                    if (intervalId === null) {
                        intervalId = setInterval(update, 1000);
                    }
                }
            })
            .catch(function() {});
        }

        function update() {
            if (remSeconds <= 0) {
                if (singleElem) singleElem.textContent = '00:00:00';
                if (hElem) hElem.textContent = '00';
                if (mElem) mElem.textContent = '00';
                if (sElem) sElem.textContent = '00';
                if (intervalId !== null) {
                    clearInterval(intervalId);
                    intervalId = null;
                }
                triggerTick();
                return;
            }
            remSeconds--;
            var h = String(Math.floor(remSeconds / 3600)).padStart(2, '0');
            var m = String(Math.floor((remSeconds % 3600) / 60)).padStart(2, '0');
            var s = String(remSeconds % 60).padStart(2, '0');

            if (singleElem) singleElem.textContent = h + ':' + m + ':' + s;
            if (hElem) hElem.textContent = h;
            if (mElem) mElem.textContent = m;
            if (sElem) sElem.textContent = s;
        }

        if (remSeconds > 0) {
            intervalId = setInterval(update, 1000);
        } else {
            triggerTick();
        }
    };

})();
