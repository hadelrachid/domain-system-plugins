<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Cards;

use DomainSystem\Plugins\domain_drop\Contracts\ProductCardInterface;
use DomainSystem\Plugins\domain_drop\DTO\DropshipProductDto;
use DomainSystem\Plugins\domain_drop\Services\CategoryTaxonomyService;

/**
 * Classe Abstrata Pai para Cards de Produtos do Catálogo DomainDrop (SOLID / Template Method / OCP).
 * 
 * Define a estrutura unificada, balanceamento semântico de tags HTML, cálculos de preço,
 * cabeçalho de imagem com badges posicionados, controles de status e ações rápidas.
 * As especializações filhas estendem e melhoram este card com atributos específicos de nicho.
 */
abstract class AbstractProductCard implements ProductCardInterface
{
    protected DropshipProductDto $product;
    protected array $options;

    public function __construct(DropshipProductDto $product, array $options = [])
    {
        $this->product = $product;
        $this->options = $options;
    }

    public function getProduct(): DropshipProductDto
    {
        return $this->product;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Renderização principal via Template Method Pattern.
     */
    public function render(): string
    {
        $id = (int)($this->product->id ?? 0);
        $isLive = ($this->product->status === 'published');
        $borderColor = $this->getCardBorderColor($isLive);
        $specializedClass = $this->getCardCssClass();

        $html = '<div data-product-id="' . $id . '" id="prod-card-' . $id . '" class="domain-drop-card ' . htmlspecialchars($specializedClass) . '" style="background: var(--bg-panel, #1d2327); border: 1.5px solid ' . $borderColor . '; border-radius: 8px; overflow: hidden; display: flex; flex-direction: column; transition: all 0.25s ease; position: relative; box-sizing: border-box;">';
        $html .= $this->renderImageSection($id, $isLive);
        $html .= $this->renderContentSection($id, $isLive);
        $html .= '</div>';

        return $html;
    }

    /**
     * Hook para classes filhas customizarem a cor de borda do card.
     */
    protected function getCardBorderColor(bool $isLive): string
    {
        return $isLive ? 'rgba(16, 185, 129, 0.45)' : 'rgba(245, 158, 11, 0.45)';
    }

    /**
     * Hook para classes filhas informarem classe CSS adicional.
     */
    protected function getCardCssClass(): string
    {
        return 'card-product-base';
    }

    /**
     * Renderiza o container da imagem com os badges organizados nos 4 cantos.
     */
    protected function renderImageSection(int $id, bool $isLive): string
    {
        $imgUrl = $this->product->mainImageUrl;
        $titleEsc = htmlspecialchars((string)$this->product->title, ENT_QUOTES, 'UTF-8');

        $html = '<div style="position: relative; width: 100%; height: 200px; background: #0b0e11; overflow: hidden;">';

        // 1. Canto Superior Esquerdo: Checkbox de Seleção + Badge de Status (No Ar / Em Revisão)
        $html .= '<div style="position: absolute; top: 10px; left: 10px; z-index: 5; display: flex; align-items: center; gap: 8px;">';
        $html .= '<input type="checkbox" class="prod-card-checkbox" id="card-check-' . $id . '" data-product-id="' . $id . '" onchange="onCardCheckboxChange(' . $id . ')" style="width: 18px; height: 18px; accent-color: #45f3ff; cursor: pointer; box-shadow: 0 2px 6px rgba(0,0,0,0.8); border-radius: 4px; margin: 0;">';
        
        $badgeBg = $isLive ? 'rgba(16, 185, 129, 0.95)' : 'rgba(245, 158, 11, 0.95)';
        $badgeColor = $isLive ? '#fff' : '#000';
        $badgeText = $isLive ? '<i class="fas fa-check-circle"></i> ✅ No Ar' : '<i class="fas fa-hourglass-half"></i> ⏳ Em Revisão';
        $badgeTitle = $isLive ? 'Status no Site: Ativo na Vitrine Pública' : 'Status no Site: Em Quarentena / Aguardando Aprovação Manual';
        $html .= '<div id="prod-status-badge-' . $id . '" style="background: ' . $badgeBg . '; backdrop-filter: blur(4px); color: ' . $badgeColor . '; padding: 2.5px 8px; border-radius: 4px; font-size: 10.5px; font-weight: 800; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 2px 6px rgba(0,0,0,0.4);" title="' . $badgeTitle . '">' . $badgeText . '</div>';
        $html .= '</div>';

        // 2. Imagem Principal com Fallback seguro
        if (!empty($imgUrl)) {
            $html .= '<img id="prod-main-img-' . $id . '" src="' . htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8') . '" alt="' . $titleEsc . '" onerror="this.onerror=null; this.src=\'https://images.unsplash.com/photo-1526738549149-8e07eca6c147?w=600&auto=format&fit=crop&q=80\';" style="width: 100%; height: 100%; object-fit: cover;">';
        } else {
            $html .= '<div id="prod-main-img-placeholder-' . $id . '" style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: var(--text-muted, #94a3b8);"><i class="fas fa-image" style="font-size: 32px;"></i></div>';
        }

        // 3. Canto Superior Direito: Badges Especializados e Contador de Fotos
        $html .= '<div style="position: absolute; top: 10px; right: 10px; display: flex; align-items: center; gap: 5px; z-index: 4;">';
        $html .= $this->renderSpecializedTopBadges();

        $galleryCount = !empty($this->product->images) ? count($this->product->images) : (!empty($this->product->mainImageUrl) ? 1 : 0);
        $displayCount = $galleryCount > 1 ? 'flex' : 'none';
        $html .= '<div id="prod-imgs-badge-' . $id . '" style="background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); color: #45f3ff; padding: 2px 7px; border-radius: 4px; font-size: 10.5px; font-weight: 600; display: ' . $displayCount . '; align-items: center; gap: 4px;"><i class="fas fa-images"></i> <span id="prod-imgs-count-' . $id . '">' . $galleryCount . '</span></div>';
        $html .= '</div>';

        // 4. Canto Inferior Esquerdo: Categoria
        $cat = htmlspecialchars($this->product->category ?: 'Dropship', ENT_QUOTES, 'UTF-8');
        $html .= '<div id="prod-category-badge-' . $id . '" style="position: absolute; bottom: 10px; left: 10px; background: rgba(0,0,0,0.8); backdrop-filter: blur(4px); color: #F0D060; padding: 3px 8px; border-radius: 4px; font-size: 10.5px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; z-index: 4; box-shadow: 0 2px 6px rgba(0,0,0,0.4);" title="Categoria: ' . $cat . '">';
        $html .= $this->getCategoryIconHtml();
        $html .= '<span id="prod-category-text-' . $id . '">' . $cat . '</span></div>';

        // 5. Canto Inferior Direito: Rating e Procedência (Origem)
        $html .= '<div style="position: absolute; bottom: 10px; right: 10px; display: flex; align-items: center; gap: 5px; z-index: 4;">';
        if ($this->product->rating > 0) {
            $html .= '<div style="background: rgba(0,0,0,0.8); color: #fbbf24; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 700; display: flex; align-items: center; gap: 3px;"><i class="fas fa-star" style="font-size: 9px;"></i> ' . number_format($this->product->rating, 1) . '</div>';
        }
        $html .= $this->renderOriginBadge($id);
        $html .= '</div>';

        $html .= '</div>'; // Fecha image section

        return $html;
    }

    protected function getCategoryIconHtml(): string
    {
        return '<i class="fas fa-tag" style="color: #fbbf24; font-size: 9px;"></i> ';
    }

    /**
     * Hook para classes filhas adicionarem badges específicos no topo direito.
     */
    protected function renderSpecializedTopBadges(): string
    {
        return '';
    }

    /**
     * Renderiza o badge de origem física / entreposto.
     */
    protected function renderOriginBadge(int $id): string
    {
        $isNational = (!empty($this->product->attributes['origem_brasil']) || (isset($this->product->attributes['ship_from']) && $this->product->attributes['ship_from'] === 'BR'));
        $isChina = (!empty($this->product->attributes['origem_china']) || (isset($this->product->attributes['ship_from']) && $this->product->attributes['ship_from'] === 'CN'));

        $originLabel = $isNational ? '<span>🇧🇷</span> BR' : ($isChina ? '<span>🇨🇳</span> CN' : '<span>🌐</span> GLOBAL');
        $originBg = $isNational ? 'rgba(16, 185, 129, 0.9)' : ($isChina ? 'rgba(59, 130, 246, 0.9)' : 'rgba(100, 116, 139, 0.9)');

        return '<div id="prod-origin-badge-' . $id . '" style="background: ' . $originBg . '; backdrop-filter: blur(4px); color: #fff; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 700; display: flex; align-items: center; gap: 3px;" title="Procedência e Entreposto Físico">' . $originLabel . '</div>';
    }

    /**
     * Renderiza a seção de conteúdo (dados do vendedor, controles, títulos, precificação e ações).
     */
    protected function renderContentSection(int $id, bool $isLive): string
    {
        $html = '<div style="padding: 14px 16px; flex: 1; display: flex; flex-direction: column; justify-content: space-between; gap: 12px;">';
        $html .= '<div>';
        $html .= $this->renderStoreInfo();
        $html .= $this->renderCategoryAndOriginSelects($id);
        $html .= $this->renderTitle();
        $html .= $this->renderSpecializedAttributes();
        $html .= $this->renderPriceBox($id);
        $html .= '</div>';

        $html .= $this->renderActionFooter($id, $isLive);
        $html .= '</div>'; // Fecha content section

        return $html;
    }

    protected function renderStoreInfo(): string
    {
        if (empty($this->product->attributes['store_name'])) {
            return '';
        }
        $name = htmlspecialchars((string)$this->product->attributes['store_name'], ENT_QUOTES, 'UTF-8');
        $url = !empty($this->product->attributes['store_url']) ? htmlspecialchars((string)$this->product->attributes['store_url'], ENT_QUOTES, 'UTF-8') : '';

        $html = '<div style="font-size: 11px; color: #a78bfa; margin-bottom: 6px; display: flex; align-items: center; gap: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">';
        $html .= '<i class="fas fa-store" style="font-size: 10px;"></i> ';
        if ($url !== '') {
            $html .= '<a href="' . $url . '" target="_blank" rel="noopener noreferrer" style="color: #a78bfa; text-decoration: none; font-weight: 500;" title="Abrir loja do vendedor no AliExpress">' . $name . '</a>';
        } else {
            $html .= '<span style="font-weight: 500;">' . $name . '</span>';
        }
        $html .= '</div>';

        return $html;
    }

    protected function renderCategoryAndOriginSelects(int $id): string
    {
        $categories = $this->options['categories'] ?? [];
        $officialCommercialCats = CategoryTaxonomyService::getCommercialCategories();
        $availableCats = !empty($categories) ? array_unique(array_merge($categories, $officialCommercialCats)) : $officialCommercialCats;

        $isNational = (!empty($this->product->attributes['origem_brasil']) || (isset($this->product->attributes['ship_from']) && $this->product->attributes['ship_from'] === 'BR'));
        $isChina = (!empty($this->product->attributes['origem_china']) || (isset($this->product->attributes['ship_from']) && $this->product->attributes['ship_from'] === 'CN'));
        $prodOrigin = $isNational ? 'BR' : ($isChina ? 'CN' : 'GLOBAL');
        $originColor = ($prodOrigin === 'BR') ? '#34d399' : (($prodOrigin === 'CN') ? '#60a5fa' : '#cbd5e1');

        $html = '<div style="margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;">';
        
        // Select Categoria
        $html .= '<div style="display: flex; align-items: center; gap: 4px; flex: 1; min-width: 120px;">';
        $html .= '<span style="font-size: 10px; color: #94a3b8; font-weight: 700; text-transform: uppercase;"><i class="fas fa-tag" style="color: #fbbf24;"></i> Cat:</span>';
        $html .= '<select class="admin-prod-category-select" id="prod-category-select-' . $id . '" data-product-id="' . $id . '" onchange="updateProductCategory(' . $id . ', this.value)" style="width: 100%; background: rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.18); color: #F0D060; font-size: 11px; font-weight: 600; padding: 2px 4px; border-radius: 4px; outline: none; cursor: pointer; text-overflow: ellipsis; white-space: nowrap;">';
        foreach ($availableCats as $catOpt) {
            $sel = ($this->product->category === $catOpt) ? 'selected' : '';
            $catEsc = htmlspecialchars($catOpt, ENT_QUOTES, 'UTF-8');
            $html .= '<option value="' . $catEsc . '" ' . $sel . ' style="background: #1d2327; color: #fff;">' . $catEsc . '</option>';
        }
        $html .= '</select></div>';

        // Select Origem
        $html .= '<div style="display: flex; align-items: center; gap: 4px;">';
        $html .= '<span style="font-size: 10px; color: #94a3b8; font-weight: 700; text-transform: uppercase;"><i class="fas fa-map-marker-alt" style="color: #60a5fa;"></i> Origem:</span>';
        $html .= '<select class="admin-prod-origin-select" id="prod-origin-select-' . $id . '" data-product-id="' . $id . '" onchange="updateProductOrigin(' . $id . ', this.value)" style="background: rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.18); color: ' . $originColor . '; font-size: 11px; font-weight: 700; padding: 2px 4px; border-radius: 4px; outline: none; cursor: pointer;">';
        $html .= '<option value="BR" ' . ($prodOrigin === 'BR' ? 'selected' : '') . ' style="background: #1d2327; color: #34d399;">🇧🇷 Brasil</option>';
        $html .= '<option value="CN" ' . ($prodOrigin === 'CN' ? 'selected' : '') . ' style="background: #1d2327; color: #60a5fa;">🇨🇳 China</option>';
        $html .= '<option value="GLOBAL" ' . ($prodOrigin === 'GLOBAL' ? 'selected' : '') . ' style="background: #1d2327; color: #cbd5e1;">🌐 Global</option>';
        $html .= '</select></div>';

        $html .= '</div>';

        return $html;
    }

    protected function renderTitle(): string
    {
        $titleEsc = htmlspecialchars((string)$this->product->title, ENT_QUOTES, 'UTF-8');
        return '<h4 style="margin: 0 0 10px 0; font-size: 13.5px; font-weight: 600; color: #fff; line-height: 1.4; max-height: 2.8em; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;" title="' . $titleEsc . '">' . $titleEsc . '</h4>';
    }

    /**
     * Hook para classes filhas injetarem atributos visuais específicos de nicho (tamanhos, cores, specs).
     */
    protected function renderSpecializedAttributes(): string
    {
        return '';
    }

    protected function renderPriceBox(int $id): string
    {
        $rate = (float)($this->options['rate'] ?? 5.75);
        $costUsd = (float)$this->product->costPriceUsd;
        $costBrl = $costUsd * $rate;
        $saleBrl = (float)$this->product->salePriceBrl;

        $html = '<div style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.06); border-radius: 6px; padding: 8px 12px; margin-bottom: 4px;">';
        $html .= '<div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 3px;">';
        $html .= '<span style="font-size: 11px; color: var(--text-muted, #94a3b8);">Custo Fornecedor:</span>';
        $html .= '<span id="prod-cost-usd-' . $id . '" style="font-size: 11.5px; color: #94a3b8; font-family: monospace;">$' . number_format($costUsd, 2) . ' (~R$ ' . number_format($costBrl, 2, ',', '.') . ')</span>';
        $html .= '</div>';
        $html .= '<div style="display: flex; justify-content: space-between; align-items: baseline; border-top: 1px dashed rgba(255,255,255,0.08); padding-top: 5px; margin-top: 3px;">';
        $html .= '<span style="font-size: 11.5px; font-weight: 600; color: #5cd67d;">Venda Sugerida:</span>';
        $html .= '<span id="prod-sale-brl-' . $id . '" style="font-size: 15px; font-weight: 800; color: #5cd67d; font-family: monospace;">R$ ' . number_format($saleBrl, 2, ',', '.') . '</span>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Renderiza o rodapé de ações organizadas em 2 linhas estruturadas.
     */
    protected function renderActionFooter(int $id, bool $isLive): string
    {
        $baseUrl = (string)($this->options['baseUrl'] ?? '');
        $rate = (float)($this->options['rate'] ?? 5.75);
        $titleJson = htmlspecialchars(json_encode((string)$this->product->title, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');

        $html = '<div style="border-top: 1px solid rgba(255,255,255,0.08); padding-top: 10px; display: flex; flex-direction: column; gap: 8px;">';

        // Linha 1: Botão Principal de Curadoria (Carregar no Site / Ocultar) + Ajustes + Excluir
        $html .= '<div style="display: flex; align-items: center; justify-content: space-between; gap: 6px;">';
        
        $btnStatusBg = $isLive ? 'rgba(245, 158, 11, 0.15)' : 'linear-gradient(135deg, #059669 0%, #10b981 100%)';
        $btnStatusBorder = $isLive ? 'rgba(245, 158, 11, 0.4)' : '#10b981';
        $btnStatusColor = $isLive ? '#fbbf24' : '#fff';
        $btnStatusText = $isLive ? '<i class="fas fa-pause-circle"></i> Ocultar' : '<i class="fas fa-check-circle"></i> Carregar no Site';
        $btnStatusTitle = $isLive ? 'Ocultar da vitrine e mover para Quarentena' : 'Aprovar e Carregar este produto para o Site';
        $nextStatus = $isLive ? 'draft' : 'published';

        $html .= '<button type="button" id="prod-toggle-status-btn-' . $id . '" onclick="toggleProductLiveStatus(' . $id . ', \'' . $nextStatus . '\')" style="flex: 1; height: 28px; padding: 0 10px; font-size: 11.5px; font-weight: 700; color: ' . $btnStatusColor . '; background: ' . $btnStatusBg . '; border: 1px solid ' . $btnStatusBorder . '; border-radius: 5px; display: inline-flex; align-items: center; justify-content: center; gap: 5px; cursor: pointer; box-sizing: border-box; line-height: 1; transition: all 0.2s ease;" title="' . $btnStatusTitle . '">' . $btnStatusText . '</button>';

        $html .= '<button type="button" onclick="openProductConfigModal(' . $id . ')" style="height: 28px; padding: 0 9px; font-size: 11px; font-weight: 700; color: #c084fc; background: rgba(192, 132, 252, 0.12); border: 1px solid rgba(192, 132, 252, 0.35); border-radius: 5px; display: inline-flex; align-items: center; gap: 4px; cursor: pointer; box-sizing: border-box; line-height: 1;" title="Configurações do Card: Tamanhos, Cores, Frete e Fornecedor"><i class="fas fa-cog"></i> Ajustes</button>';

        $html .= '<button type="button" onclick="openDeleteProductModal(' . $id . ', ' . $titleJson . ')" style="height: 28px; padding: 0 8px; font-size: 11px; font-weight: 600; color: #ef4444; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.25); border-radius: 5px; display: inline-flex; align-items: center; gap: 4px; cursor: pointer; box-sizing: border-box; line-height: 1;" title="Excluir produto do catálogo"><i class="fas fa-trash-alt"></i></button>';

        $html .= '</div>';

        // Linha 2: Links Rápidos (Loja, Fornecedor, Preço, Fotos, Vídeo)
        $directAeUrl = !empty($this->product->attributes['aliexpress_url']) ? (string)$this->product->attributes['aliexpress_url'] : '';
        $isRealAeId = !empty($this->product->aliexpressId) && preg_match('/^\d{8,24}$/', (string)$this->product->aliexpressId);
        if ($directAeUrl === '' && $isRealAeId) {
            $directAeUrl = 'https://pt.aliexpress.com/item/' . htmlspecialchars((string)$this->product->aliexpressId) . '.html?country=BR&locale=pt_BR&currency=BRL';
        }
        $aeLink = $directAeUrl !== '' ? $directAeUrl : 'https://www.aliexpress.com/wholesale?SearchText=' . urlencode((string)$this->product->title);
        $prodSlug = urlencode((string)$this->product->slug);

        $html .= '<div style="display: flex; align-items: center; justify-content: space-between; gap: 4px; flex-wrap: wrap;">';
        $html .= '<div style="display: flex; align-items: center; gap: 4px;">';
        $html .= '<a href="' . $baseUrl . '/produto/' . $prodSlug . '" target="_blank" style="height: 24px; padding: 0 7px; font-size: 10.5px; font-weight: 600; color: #45f3ff; background: rgba(69, 243, 255, 0.08); border: 1px solid rgba(69, 243, 255, 0.2); border-radius: 4px; display: inline-flex; align-items: center; gap: 3px; text-decoration: none; box-sizing: border-box; line-height: 1;" title="Visualizar na loja virtual"><i class="fas fa-eye"></i> Loja</a>';
        $html .= '<a href="' . htmlspecialchars($aeLink, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener noreferrer" style="height: 24px; padding: 0 7px; font-size: 10.5px; font-weight: 600; color: #ff6b6b; background: rgba(255, 107, 107, 0.08); border: 1px solid rgba(255, 107, 107, 0.2); border-radius: 4px; display: inline-flex; align-items: center; gap: 3px; text-decoration: none; box-sizing: border-box; line-height: 1;" title="Abrir fornecedor no AliExpress"><i class="fas fa-external-link-alt"></i> Fornecedor</a>';
        $html .= '</div>';

        $html .= '<div style="display: flex; align-items: center; gap: 4px;">';
        $html .= '<button type="button" onclick="openPriceModal(' . $id . ', ' . $titleJson . ', ' . (float)$this->product->costPriceUsd . ', ' . (float)round((float)$this->product->costPriceUsd * $rate, 2) . ', ' . (float)$this->product->salePriceBrl . ')" style="height: 24px; padding: 0 7px; font-size: 10.5px; font-weight: 600; color: #fbbf24; background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.25); border-radius: 4px; display: inline-flex; align-items: center; gap: 3px; cursor: pointer; box-sizing: border-box; line-height: 1;" title="Editar Custo e Preço"><i class="fas fa-dollar-sign"></i> Preço</button>';
        $html .= '<button type="button" onclick="openImagesModal(' . $id . ')" style="height: 24px; padding: 0 7px; font-size: 10.5px; font-weight: 600; color: #45f3ff; background: rgba(69, 243, 255, 0.08); border: 1px solid rgba(69, 243, 255, 0.2); border-radius: 4px; display: inline-flex; align-items: center; gap: 3px; cursor: pointer; box-sizing: border-box; line-height: 1;" title="Gerenciar Fotos"><i class="fas fa-images"></i> Fotos</button>';

        $prodVideoUrl = !empty($this->product->attributes['video_url']) ? (string)$this->product->attributes['video_url'] : '';
        if ($prodVideoUrl !== '') {
            $html .= '<button type="button" id="prod-video-btn-' . $id . '" onclick="downloadCardVideo(' . $id . ', event)" style="height: 24px; padding: 0 7px; font-size: 10.5px; font-weight: 700; color: #c084fc; background: rgba(168, 85, 247, 0.15); border: 1px solid rgba(168, 85, 247, 0.35); border-radius: 4px; display: inline-flex; align-items: center; gap: 3px; cursor: pointer; box-sizing: border-box; line-height: 1;" title="Baixar Vídeo MP4 do fornecedor"><i class="fas fa-video"></i> Vídeo</button>';
        }

        $html .= '</div>';
        $html .= '</div>';

        $html .= '</div>'; // Fecha action footer

        return $html;
    }
}
