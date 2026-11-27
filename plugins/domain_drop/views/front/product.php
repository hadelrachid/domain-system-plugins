<?php
/**
 * View Pública da Página Dedicada do Produto (/produto/{slug})
 *
 * @var \DomainSystem\Plugins\domain_drop\DTO\DropshipProductDto $product
 * @var string $checkoutUrl
 * @var string $baseUrl
 */
$refPrice = $product->salePriceBrl * 1.35;
$sellerInfo = $product->getSellerInfo();
$allImages = !empty($product->images) ? array_values(array_filter($product->images)) : [];
if ($product->hasVariants()) {
    foreach ($product->getVariants() as $v) {
        if ($v->isAvailable() && !empty($v->imageUrl) && !in_array($v->imageUrl, $allImages, true)) {
            $allImages[] = $v->imageUrl;
        }
    }
}
if (empty($allImages) && !empty($product->mainImageUrl)) {
    $allImages = [$product->mainImageUrl];
}
$isAdult = false;
$productWeightGrams = method_exists($product, 'getEstimatedWeightInGrams') ? $product->getEstimatedWeightInGrams() : 200;
$productExpressPrice = method_exists($product, 'calculateExpressShippingPrice') ? $product->calculateExpressShippingPrice(1) : 19.90;
$productFormattedWeight = method_exists($product, 'getFormattedWeight') ? $product->getFormattedWeight(1) : '200g';
$eanCode = method_exists($product, 'getEanCode') ? $product->getEanCode() : '';

$productVideoUrl = !empty($product->attributes['video_url']) ? (string)$product->attributes['video_url'] : '';
if ($productVideoUrl === '' && !empty($product->attributes['product_video_url'])) {
    $productVideoUrl = (string)$product->attributes['product_video_url'];
}
if ($productVideoUrl === '' && !empty($product->description) && preg_match('/<source\s+src="([^"]+\.mp4[^"]*)"/i', $product->description, $mVidP)) {
    $productVideoUrl = $mVidP[1];
}

$schemaOffers = [
    '@type'           => 'Offer',
    'url'             => (str_starts_with($baseUrl, 'http') ? $baseUrl : 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $baseUrl) . '/produto/' . $product->slug,
    'priceCurrency'   => 'BRL',
    'price'           => number_format($product->salePriceBrl, 2, '.', ''),
    'priceValidUntil' => date('Y-12-31', strtotime('+1 year')),
    'itemCondition'   => 'https://schema.org/NewCondition',
    'availability'    => $product->status === 'published' ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
    'seller'          => [
        '@type' => 'Organization',
        'name'  => $sellerInfo['store_name'] ?? 'Loja Oficial',
    ],
];

$schemaData = [
    '@context'    => 'https://schema.org/',
    '@type'       => 'Product',
    'name'        => $product->title,
    'image'       => $allImages,
    'description' => strip_tags(method_exists($product, 'getEnrichedDescription') ? ($product->getEnrichedDescription() ?: $product->description) : $product->description),
    'sku'         => (string)($product->id ?? $product->aliexpressId),
    'brand'       => [
        '@type' => 'Brand',
        'name'  => $product->attributes['brand'] ?? ($sellerInfo['store_name'] ?? 'Marca Original'),
    ],
    'offers'      => $schemaOffers,
];

if ($eanCode !== '') {
    $schemaData['gtin13'] = $eanCode;
}
?>
<!-- Dados Estruturados Schema.org / Google Shopping & Rich Snippets -->
<script type="application/ld+json">
<?= json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
</script>

<div class="domain-drop-product-page" style="max-width: 1200px; margin: 0 auto; padding: 24px 15px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #cbd5e1; box-sizing: border-box;">

    <!-- Breadcrumbs -->
    <nav style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #94a3b8; margin-bottom: 24px; flex-wrap: wrap;">
        <a href="<?= $baseUrl ?>/" style="color: #94a3b8; text-decoration: none;">Início</a>
        <span>/</span>
        <a href="<?= $baseUrl ?>/loja" style="color: #94a3b8; text-decoration: none;">Loja</a>
        <span>/</span>
        <a href="<?= $baseUrl ?>/loja?cat=<?= urlencode($product->category) ?>" style="color: #ff6b6b; text-decoration: none;">
            <?= htmlspecialchars($product->category) ?>
        </a>
        <span>/</span>
        <span style="color: #fff; max-width: 320px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
            <?= htmlspecialchars($product->title) ?>
        </span>
    </nav>

    <!-- Product Layout: Gallery + Buy Box -->
    <div class="product-top-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 340px), 1fr)); gap: 32px; margin-bottom: 45px;">
        
        <!-- Gallery Column -->
        <div>
            <!-- Main Image Stage with Zoom trigger and Integrated Video Player -->
            <div id="main-product-stage" onclick="handleStageClick(event)" class="main-stage-box" style="background: #0c0f12; border: 1px solid rgba(255,255,255,0.12); border-radius: 12px; overflow: hidden; height: 420px; display: flex; align-items: center; justify-content: center; margin-bottom: 14px; position: relative; cursor: zoom-in; box-shadow: 0 10px 30px rgba(0,0,0,0.4);">
                <?php if (!empty($product->mainImageUrl)): ?>
                    <img id="main-product-img" src="<?= htmlspecialchars($product->mainImageUrl) ?>" alt="<?= htmlspecialchars($product->title) ?>" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1526738549149-8e07eca6c147?w=600&auto=format&fit=crop&q=80';" style="max-width: 100%; max-height: 100%; object-fit: contain; transition: transform 0.25s ease;">
                    <div id="main-stage-zoom-badge" style="position: absolute; bottom: 12px; right: 12px; background: rgba(11,15,23,0.85); color: #F0D060; border: 1px solid rgba(212,175,55,0.4); padding: 5px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; display: flex; align-items: center; gap: 5px; pointer-events: none; backdrop-filter: blur(4px);">
                        <span>🔍</span> <span>Toque para Ampliar</span>
                    </div>
                <?php else: ?>
                    <div id="main-product-fallback" style="font-size: 50px;">📦</div>
                <?php endif; ?>

                <?php if (!empty($productVideoUrl)): ?>
                    <video id="main-product-video" controls playsinline preload="metadata" style="display: none; width: 100%; height: 100%; max-height: 420px; background: #000; object-fit: contain;" poster="<?= htmlspecialchars($product->mainImageUrl ?? '') ?>">
                        <source src="<?= htmlspecialchars($productVideoUrl) ?>" type="video/mp4">
                        Seu navegador não suporta reprodução de vídeo.
                    </video>
                    <button type="button" id="btnPlayMainStageVideo" onclick="selectProductVideo('<?= htmlspecialchars($productVideoUrl, ENT_QUOTES) ?>', event)" style="position: absolute; top: 14px; left: 14px; background: linear-gradient(135deg, #9333ea 0%, #7c3aed 100%); color: #fff; border: 1px solid rgba(168,85,247,0.6); padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 800; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; box-shadow: 0 4px 15px rgba(147,51,234,0.4); z-index: 10; transition: transform 0.2s ease;">
                        <span>▶</span> <span>Assistir Vídeo</span>
                    </button>
                <?php endif; ?>
            </div>

            <!-- Horizontal Thumbnails Strip (Fotos + Vídeo) -->
            <?php if (count($allImages) > 1 || !empty($productVideoUrl)): ?>
                <div style="display: flex; gap: 10px; overflow-x: auto; padding-bottom: 8px;" class="custom-scroll">
                    <?php if (!empty($productVideoUrl)): ?>
                        <div id="thumb-video-item" class="product-thumb-item" onclick="selectProductVideo('<?= htmlspecialchars($productVideoUrl, ENT_QUOTES) ?>', event)" style="width: 72px; height: 72px; background: #180e28; border: 2px solid rgba(168, 85, 247, 0.7); border-radius: 8px; overflow: hidden; cursor: pointer; flex-shrink: 0; position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 3px; box-shadow: 0 2px 8px rgba(147,51,234,0.3); transition: all 0.2s ease;" title="Assistir Vídeo do Produto">
                            <span style="font-size: 22px; color: #c084fc; line-height: 1;">▶</span>
                            <span style="font-size: 10px; font-weight: 800; color: #e9d5ff; text-transform: uppercase; letter-spacing: 0.5px;">Vídeo</span>
                        </div>
                    <?php endif; ?>
                    <?php foreach ($allImages as $idx => $thumb): ?>
                        <div class="product-thumb-item photo-thumb-item <?= ($idx === 0) ? 'active' : '' ?>" data-index="<?= $idx ?>" onclick="selectProductImage(<?= $idx ?>, '<?= htmlspecialchars($thumb, ENT_QUOTES) ?>')" style="width: 72px; height: 72px; background: #0c0f12; border: 2px solid <?= ($idx === 0) ? '#D4AF37' : 'rgba(255,255,255,0.12)' ?>; border-radius: 8px; overflow: hidden; cursor: pointer; flex-shrink: 0; transition: border-color 0.2s ease, transform 0.2s ease;">
                            <img src="<?= htmlspecialchars($thumb) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Product Buy Box Column -->
        <div style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <?php 
                $isNational = (!empty($product->attributes['origem_brasil']) || (isset($product->attributes['ship_from']) && $product->attributes['ship_from'] === 'BR'));
                $publicoAlvo = (string)($product->attributes['publico_alvo'] ?? ($isAdult ? 'Adulto' : 'Infantil (Kids)'));
                ?>
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 12px;">
                    <span style="background: <?= $isAdult ? 'rgba(239,68,68,0.2)' : 'rgba(255,71,71,0.15)' ?>; color: <?= $isAdult ? '#f87171' : '#ff6b6b' ?>; border: <?= $isAdult ? '1px solid rgba(239,68,68,0.4)' : 'none' ?>; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; display: inline-block;">
                        <?= $isAdult ? '🔞 ' : '' ?><?= htmlspecialchars($product->category) ?>
                    </span>
                    <span style="background: rgba(59,130,246,0.14); border: 1px solid rgba(59,130,246,0.35); color: #93c5fd; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px;">
                        <span>🧑 Público: <?= htmlspecialchars($publicoAlvo) ?></span>
                    </span>
                    <?php if ($isNational): ?>
                        <span style="background: rgba(34,197,94,0.14); border: 1px solid rgba(34,197,94,0.4); color: #4ade80; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px;">
                            <?= \DomainSystem\Plugins\domain_drop\Helpers\FlagHelper::br(16, 11) ?> <span>Estoque no Brasil (Envio 3 a 7 dias)</span>
                        </span>
                    <?php else: ?>
                        <span style="background: rgba(168,85,247,0.14); border: 1px solid rgba(168,85,247,0.35); color: #c084fc; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px;">
                            <span>🌐 Importação Oficial (Rastreamento Oficial)</span>
                        </span>
                    <?php endif; ?>
                    <?php
                    $isFreeShipping = !isset($product->attributes['frete_gratis']) || (bool)$product->attributes['frete_gratis'];
                    $shippingType = (string)($product->attributes['frete_tipo'] ?? ($isFreeShipping ? 'gratis' : 'pago'));
                    $shippingFee = (float)($product->attributes['frete_valor'] ?? 0.0);
                    ?>
                    <span style="background: <?= $isFreeShipping ? 'rgba(16,185,129,0.12)' : 'rgba(56,189,248,0.12)' ?>; border: 1px solid <?= $isFreeShipping ? 'rgba(16,185,129,0.3)' : 'rgba(56,189,248,0.3)' ?>; color: <?= $isFreeShipping ? '#34d399' : '#38bdf8' ?>; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px;">
                        <span>🚚 <?= $isFreeShipping ? 'Frete Grátis Disponível • Opção Sedex' : ('Frete R$ ' . number_format($shippingFee, 2, ',', '.') . ' • Opção Sedex') ?></span>
                    </span>
                </div>

                <h1 style="font-size: clamp(20px, 3vw, 28px); font-weight: 700; color: #fff; margin: 0 0 14px 0; line-height: 1.3;">
                    <?= htmlspecialchars($product->title) ?>
                </h1>

                <?php
                $thermometerVal = \DomainSystem\Plugins\domain_drop\Services\ProductMiningService::calculateThermometerScore((float)$product->rating, (int)$product->ordersCount);
                $salesInformed = (string)($product->attributes['Vendas_Informadas'] ?? '');
                $starCount = (int)max(1, min(5, round((float)$product->rating)));
                $starsEmoji = str_repeat('⭐', $starCount);
                ?>
                <?php if ($product->rating > 0 || $product->ordersCount > 0 || $salesInformed !== ''): ?>
                    <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap; font-size: 13px; color: #94a3b8; margin-bottom: 22px; padding-bottom: 16px; border-bottom: 1px solid rgba(255,255,255,0.08);">
                        <?php if ($product->rating > 0): ?>
                            <span style="color: #fbbf24; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                                <span style="font-size: 14px; letter-spacing: 1px;"><?= $starsEmoji ?></span>
                                <span style="color: #fff; font-weight: 800; font-family: monospace; font-size: 14px;"><?= number_format($product->rating, 1) ?></span>
                                <?php if (!empty($product->attributes['reviews_count'])): ?>
                                    <span style="color: #cbd5e1; font-weight: 500; font-size: 12.5px;">(<?= (int)$product->attributes['reviews_count'] ?> avaliações)</span>
                                <?php endif; ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($product->ordersCount > 0 && $salesInformed !== 'nao'): ?>
                            <span>• <?= number_format($product->ordersCount, 0, ',', '.') ?> aprovações / vendidos</span>
                        <?php elseif ($salesInformed === 'sim'): ?>
                            <span>• 0 vendidos</span>
                        <?php elseif ($salesInformed === 'nao'): ?>
                            <span>• Vendas não informadas</span>
                        <?php endif; ?>
                        <span style="background: rgba(249,115,22,0.14); border: 1px solid rgba(249,115,22,0.4); color: #fdba74; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 700;">
                            🔥 Termômetro: <?= (int)$thermometerVal ?>%
                        </span>
                    </div>
                <?php endif; ?>

                <!-- Price Card -->
                <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 20px; margin-bottom: 16px;">
                    <div style="font-size: 13px; color: #94a3b8; text-decoration: line-through; margin-bottom: 4px;">
                        De: R$ <?= number_format($refPrice, 2, ',', '.') ?>
                    </div>
                    <div style="display: flex; align-items: baseline; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                        <div id="product-display-price" style="font-size: 32px; font-weight: 800; color: #5cd67d; font-family: monospace;">
                            R$ <?= number_format($product->salePriceBrl, 2, ',', '.') ?>
                        </div>
                        <div id="product-qty-subtotal" style="display: none; font-size: 13px; color: #F0D060; font-weight: 700; background: rgba(212,175,55,0.12); border: 1px solid rgba(212,175,55,0.3); padding: 4px 10px; border-radius: 6px;"></div>
                    </div>
                    <div style="font-size: 13px; color: #94a3b8; margin-top: 4px;">
                        em até <strong>12x sem juros</strong> no cartão ou Pix à vista • <span style="color: #cbd5e1;">Inclui serviço de curadoria e busca verificada</span>
                    </div>
                </div>

                <!-- Identificação do Vendedor / Fornecedor Exclusivo & Transparência da Curadoria -->
                <?php
                $storeLocation = $product->attributes['store_info']['location'] ?? ($product->attributes['store_location'] ?? ($sellerInfo['warehouse'] ?? ''));
                $storeOpenDate = $product->attributes['store_info']['open_date'] ?? ($product->attributes['store_open_date'] ?? '');
                $isStoreBrazil = stripos($storeLocation, 'Brasil') !== false || stripos($storeLocation, 'Brazil') !== false;
                ?>
                <div style="background: rgba(59, 130, 246, 0.06); border: 1px solid rgba(59, 130, 246, 0.25); border-radius: 10px; padding: 12px 14px; margin-bottom: 16px; display: flex; flex-direction: column; gap: 6px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; font-size: 13px;">
                        <span style="color: #fff; font-weight: 700;">🏪 <?= htmlspecialchars($sellerInfo['store_name']) ?></span>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <?php if ($storeLocation !== ''): ?>
                                <span style="font-size: 11px; background: <?= $isStoreBrazil ? 'rgba(16,185,129,0.15)' : 'rgba(59,130,246,0.15)' ?>; color: <?= $isStoreBrazil ? '#34d399' : '#93c5fd' ?>; border: 1px solid <?= $isStoreBrazil ? 'rgba(16,185,129,0.3)' : 'rgba(59,130,246,0.3)' ?>; padding: 2px 7px; border-radius: 4px; font-weight: 600;">
                                    <?= $isStoreBrazil ? '🇧🇷 Brasil' : '🌐 ' . htmlspecialchars($storeLocation) ?>
                                </span>
                            <?php endif; ?>
                            <span style="font-family: monospace; font-size: 11px; background: rgba(255,255,255,0.08); color: #cbd5e1; padding: 2px 8px; border-radius: 5px;">
                                ID: <?= htmlspecialchars($sellerInfo['seller_id']) ?>
                            </span>
                        </div>
                    </div>
                    <span style="font-size: 11.5px; color: #94a3b8; line-height: 1.45;">
                        <?= htmlspecialchars($sellerInfo['warehouse']) ?>
                        <?php if ($storeOpenDate !== ''): ?>
                            • Aberta desde: <strong style="color: #cbd5e1;"><?= htmlspecialchars($storeOpenDate) ?></strong>
                        <?php endif; ?>
                        • Vendido e enviado diretamente por este fornecedor com <strong>garantia de entrega RachidD.com</strong>.
                    </span>
                </div>

                <?php if ($product->hasVariants() || !empty($product->attributes['variation_axes'])): ?>
                    <?php
                    $axes = $product->getVariationAxes();
                    $variantsList = $product->getVariants();
                    $isFashionCategory = in_array($product->category, [
                        'Moda, Roupas & Vestuário',
                        'Roupas, Bolsas & Calçados',
                        'Moda Íntima, Lingeries & Pijamas',
                        'Moda Íntima & Lingerie',
                        'Roupa Íntima & Pijamas',
                        'Moda Fitness, Leggings & Academia',
                    ], true) || !empty($product->attributes['tabela_tamanhos']) || preg_match('/\b(jaqueta|casaco|calça|calca|tênis|tenis|sapato|vestido|camisa|camiseta|shorts|bermuda|agasalho|conjunto|meia|roupa|blusa|moletom|lingerie|sutiã|sutia|calcinha|cueca)\b/iu', $product->title);
                    $hasSizeOption = isset($axes['Tamanho']) || isset($axes['Tamanhos']) || isset($axes['Size']) || $isFashionCategory;
                    ?>
                    <!-- SELETORES DINÂMICOS DE VARIAÇÃO (CORES, TAMANHOS, MODELOS - PADRÃO SKU MATRIX) -->
                    <div id="product-variants-box" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 16px; margin-bottom: 20px;">
                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #F0D060; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                            <span>🎨 Opções & Variações</span>
                            <?php if ($hasSizeOption): ?>
                                <button type="button" onclick="openSizeGuideModal()" style="background: rgba(240, 208, 96, 0.12); border: 1px solid rgba(240, 208, 96, 0.4); color: #F0D060; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 5px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; text-transform: none;">
                                    <span>📏 Guia de Tamanhos</span>
                                </button>
                            <?php endif; ?>
                            <span id="active-variant-summary" style="color: #94a3b8; font-size: 11.5px; text-transform: none; font-weight: 600;"></span>
                        </div>

                        <?php foreach ($axes as $axisName => $options): ?>
                            <div class="variant-axis-block" style="margin-bottom: 14px;">
                                <div style="font-size: 12px; color: #cbd5e1; font-weight: 600; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                                    <span><?= htmlspecialchars($axisName) ?>:</span>
                                    <span class="axis-selected-label" data-axis="<?= htmlspecialchars($axisName) ?>" style="color: #5cd67d; font-weight: 700;"></span>
                                </div>
                                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                    <?php 
                                    $isColorAxis = (bool)preg_match('/(?:cor|color)/i', $axisName);
                                    $colorHexMap = [
                                        'preto' => '#18181b', 'preta' => '#18181b', 'black' => '#18181b', 'grafite' => '#374151', 'chumbo' => '#334155', 'meia-noite' => '#0f172a', 'onix' => '#111827', 'ônix' => '#111827',
                                        'branco' => '#f8fafc', 'branca' => '#f8fafc', 'white' => '#f8fafc', 'neve' => '#ffffff',
                                        'prata' => '#cbd5e1', 'prateado' => '#cbd5e1', 'prateada' => '#cbd5e1', 'silver' => '#cbd5e1', 'inox' => '#94a3b8', 'titânio' => '#94a3b8', 'titanio' => '#94a3b8',
                                        'cinza' => '#64748b', 'gray' => '#64748b', 'grey' => '#64748b', 'espacial' => '#475569',
                                        'dourado' => '#f59e0b', 'dourada' => '#f59e0b', 'gold' => '#f59e0b', 'ouro' => '#eab308', 'amarelo' => '#facc15', 'amarela' => '#facc15',
                                        'rosa' => '#f472b6', 'pink' => '#ec4899', 'rose' => '#fb7185', 'ouro rosa' => '#e0a899', 'rose gold' => '#e0a899',
                                        'azul' => '#3b82f6', 'marinho' => '#1e3a8a', 'celeste' => '#38bdf8', 'blue' => '#3b82f6', 'navy' => '#1e3a8a',
                                        'verde' => '#22c55e', 'green' => '#22c55e', 'oliva' => '#65a30d', 'militar' => '#4d7c0f',
                                        'vermelho' => '#ef4444', 'vermelha' => '#ef4444', 'red' => '#ef4444', 'vinho' => '#881337', 'bordô' => '#881337',
                                        'roxo' => '#a855f7', 'roxa' => '#a855f7', 'purple' => '#a855f7', 'violeta' => '#7c3aed',
                                        'laranja' => '#f97316', 'orange' => '#f97316', 'marrom' => '#78350f', 'bege' => '#d6d3d1',
                                    ];
                                    ?>
                                    <?php foreach ($options as $optIdx => $optionValue): ?>
                                        <?php 
                                        $isInStock = $product->isOptionAvailable($axisName, $optionValue);
                                        $swatchColor = null;
                                        if ($isColorAxis) {
                                            $vLower = mb_strtolower((string)$optionValue, 'UTF-8');
                                            foreach ($colorHexMap as $ckey => $chex) {
                                                if (str_contains($vLower, $ckey)) {
                                                    $swatchColor = $chex;
                                                    break;
                                                }
                                            }
                                        }
                                        ?>
                                        <button type="button"
                                                class="btn-variant-chip <?= !$isInStock ? 'is-disabled' : '' ?>"
                                                data-axis="<?= htmlspecialchars($axisName) ?>"
                                                data-value="<?= htmlspecialchars($optionValue) ?>"
                                                data-in-stock="<?= $isInStock ? '1' : '0' ?>"
                                                <?= !$isInStock ? 'disabled title="Opção indisponível / esgotada neste modelo"' : '' ?>
                                                onclick="if(!this.disabled) selectVariantOption(this.getAttribute('data-axis'), this.getAttribute('data-value'))"
                                                style="<?= $isInStock
                                                    ? 'background: rgba(255,255,255,0.04); border: 1.5px solid rgba(255,255,255,0.14); color: #fff; padding: 7px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 6px;'
                                                    : 'background: rgba(255,255,255,0.01); border: 1.5px dashed rgba(255,255,255,0.1); color: #64748b; padding: 7px 14px; border-radius: 8px; font-size: 13px; font-weight: 500; cursor: not-allowed; opacity: 0.4; display: inline-flex; align-items: center; gap: 6px; text-decoration: line-through; pointer-events: none;' ?>">
                                            <?php if ($swatchColor): ?>
                                                <span style="width: 12px; height: 12px; border-radius: 50%; background: <?= $swatchColor ?>; display: inline-block; border: 1.5px solid rgba(255,255,255,0.5); box-shadow: 0 0 4px rgba(0,0,0,0.6); flex-shrink: 0;"></span>
                                            <?php endif; ?>
                                            <span><?= htmlspecialchars($optionValue) ?></span>
                                            <?php if (!$isInStock): ?>
                                                <span style="font-size: 9.5px; background: rgba(239,68,68,0.18); border: 1px solid rgba(239,68,68,0.3); color: #f87171; padding: 1px 5px; border-radius: 4px; text-decoration: none; font-weight: 700;">Esgotado</span>
                                            <?php endif; ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Seletor de Quantidade + CTA Comprar Agora -->
                <div style="display: flex; gap: 12px; align-items: stretch; margin-bottom: 24px; flex-wrap: wrap;">
                    <div style="display: inline-flex; align-items: center; background: #0c0f12; border: 1px solid rgba(212,175,55,0.4); border-radius: 8px; overflow: hidden; height: 54px;">
                        <button type="button" onclick="changeProductQuantity(-1)" aria-label="Diminuir quantidade" style="width: 42px; height: 100%; background: rgba(255,255,255,0.04); border: none; color: #fff; font-size: 20px; font-weight: 700; cursor: pointer;">−</button>
                        <input type="number" id="product-qty-input" value="1" min="1" max="50" onchange="syncProductQuantity()" oninput="syncProductQuantity()" aria-label="Quantidade" style="width: 48px; height: 100%; background: transparent; border: none; color: #F0D060; text-align: center; font-size: 16px; font-weight: 800; font-family: monospace; outline: none;">
                        <button type="button" onclick="changeProductQuantity(1)" aria-label="Aumentar quantidade" style="width: 42px; height: 100%; background: rgba(255,255,255,0.04); border: none; color: #fff; font-size: 20px; font-weight: 700; cursor: pointer;">+</button>
                    </div>

                    <a id="btn-buy-now" href="<?= htmlspecialchars($checkoutUrl) ?>?qty=1" onclick="return handleBuyNowClick(event, this)" style="flex: 1; min-width: 210px; display: flex; align-items: center; justify-content: center; gap: 10px; box-sizing: border-box; background: linear-gradient(135deg, #F0D060 0%, #D4AF37 100%); color: #0b0f17; text-decoration: none; padding: 14px 24px; border-radius: 8px; font-size: 16px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 6px 20px rgba(212, 175, 55, 0.4); transition: transform 0.2s ease;">
                        <span>Comprar Agora</span>
                        <span>➔</span>
                    </a>
                </div>
                <div id="buy-availability-feedback" style="display: none; margin: -14px 0 20px; padding: 10px 14px; border-radius: 8px; font-size: 13px; font-weight: 600;"></div>

                <!-- Simulador de Frete & Prazos por CEP -->
                <div class="product-shipping-calc-box" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 10px; padding: 16px; margin-bottom: 22px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; flex-wrap: wrap; gap: 6px;">
                        <span style="font-size: 13px; font-weight: 700; color: #fff; display: inline-flex; align-items: center; gap: 7px;">
                            <span>🚚</span> <span>Calcular Frete &amp; Prazo de Entrega</span>
                        </span>
                        <a href="https://buscacepinter.correios.com.br/app/endereco/index.php" target="_blank" rel="noopener noreferrer" style="font-size: 11px; color: #94a3b8; text-decoration: underline;" onmouseover="this.style.color='#F0D060'" onmouseout="this.style.color='#94a3b8'">Não sei meu CEP</a>
                    </div>

                    <div style="display: flex; gap: 8px; align-items: center;">
                        <div style="position: relative; flex: 1;">
                            <input type="text" id="shipping-cep-input" maxlength="9" placeholder="Digite seu CEP..." oninput="formatShippingCep(this)" onkeydown="if(event.key==='Enter'){event.preventDefault();calculateProductShipping();}" style="width: 100%; background: #0c0f12; border: 1px solid rgba(255,255,255,0.15); border-radius: 6px; padding: 9px 12px; color: #fff; font-size: 13px; font-family: monospace; outline: none; box-sizing: border-box;" aria-label="Digite seu CEP">
                        </div>
                        <button type="button" id="btn-calc-shipping" onclick="calculateProductShipping()" style="background: rgba(212, 175, 55, 0.15); border: 1px solid #D4AF37; color: #F0D060; padding: 9px 16px; border-radius: 6px; font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.2s; white-space: nowrap;">
                            Calcular
                        </button>
                    </div>

                    <!-- Resultado do Frete e Opções de Envio -->
                    <div id="shipping-result-box" style="display: none; margin-top: 14px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.08);">
                        <div id="shipping-location-text" style="font-size: 12px; color: #38bdf8; margin-bottom: 10px; font-weight: 600;"></div>

                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <!-- Opção 1: Frete Padrão com Rastreio -->
                            <div style="display: flex; justify-content: space-between; align-items: center; background: <?= $isFreeShipping ? 'rgba(0,210,132,0.06)' : 'rgba(56,189,248,0.06)' ?>; border: 1px solid <?= $isFreeShipping ? 'rgba(0,210,132,0.3)' : 'rgba(56,189,248,0.3)' ?>; border-radius: 8px; padding: 10px 12px;">
                                <div>
                                    <div style="font-size: 12.5px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 6px;">
                                        <span>📦</span> <span>Frete Padrão com Rastreamento</span>
                                    </div>
                                    <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">
                                        <?= $isNational ? 'Prazo estimado de <strong>3 a 7 dias úteis</strong> (Estoque Nacional 🇧🇷)' : 'Prazo estimado de <strong>15 a 25 dias úteis</strong> (Envio Internacional 🇨🇳)' ?>
                                    </div>
                                </div>
                                <div style="text-align: right; padding-left: 10px;">
                                    <?php if ($isFreeShipping): ?>
                                        <span style="font-size: 12.5px; font-weight: 800; color: #00d284; text-transform: uppercase;">GRÁTIS</span>
                                    <?php else: ?>
                                        <span style="font-size: 12.5px; font-weight: 800; color: #38bdf8; font-weight: 800;">R$ <?= number_format($shippingFee, 2, ',', '.') ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Opção 2: Envio Expresso Prioritário -->
                            <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 10px 12px;">
                                <div>
                                    <div style="font-size: 12.5px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 6px;">
                                        <span>⚡</span> <span>Envio Expresso Prioritário (Sedex)</span>
                                        <span id="product-shipping-weight-badge" style="font-size: 11px; padding: 1px 6px; border-radius: 4px; background: rgba(69,243,255,0.12); color: #45f3ff; font-weight: 600;">
                                            <?= htmlspecialchars($productFormattedWeight) ?>
                                        </span>
                                    </div>
                                    <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">
                                        <?= $isNational ? 'Despacho prioritário no mesmo dia (<strong>2 a 4 dias úteis</strong>)' : 'Despacho urgente com prioridade aduaneira (<strong>10 a 15 dias úteis</strong>)' ?>
                                    </div>
                                </div>
                                <div style="text-align: right; padding-left: 10px;">
                                    <span id="product-shipping-express-badge" style="font-size: 12.5px; font-weight: 800; color: #45f3ff;">+ R$ <?= number_format($productExpressPrice, 2, ',', '.') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Trust Badges -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 12px; color: #94a3b8;">
                    <div style="background: rgba(255,255,255,0.02); padding: 10px 14px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.06); display: flex; align-items: center; gap: 8px;">
                        <span>🚚</span> <span>Envio Internacional com Rastreio</span>
                    </div>
                    <div style="background: rgba(255,255,255,0.02); padding: 10px 14px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.06); display: flex; align-items: center; gap: 8px;">
                        <span>🛡️</span> <span>Garantia de Satisfação ou Reembolso</span>
                    </div>
                    <div style="background: rgba(255,255,255,0.02); padding: 10px 14px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.06); display: flex; align-items: center; gap: 8px;">
                        <span>🔒</span> <span>Pagamento 100% Criptografado</span>
                    </div>
                    <div style="background: rgba(255,255,255,0.02); padding: 10px 14px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.06); display: flex; align-items: center; gap: 8px;">
                        <span>💬</span> <span>Suporte em Português</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- 📸 VISUAL BOOK OFICIAL DO PRODUTO (Lookbook de Ângulos & Detalhes) -->
    <div class="domain-drop-visual-book-box" style="background: #171d22; border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; padding: 24px; margin-bottom: 40px; box-shadow: 0 12px 30px rgba(0,0,0,0.3);">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin-bottom: 22px; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 16px;">
            <div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                    <span style="font-size: 20px;">📸</span>
                    <h2 style="font-size: 20px; font-weight: 700; color: #fff; margin: 0;">
                        Visual Book & Galeria de Detalhes
                    </h2>
                </div>
                <p style="font-size: 13px; color: #94a3b8; margin: 0;">
                    Fotos oficiais em alta resolução do fabricante internacional. Toque em qualquer ângulo para inspecionar em tela cheia.
                </p>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="background: rgba(212, 175, 55, 0.12); color: #F0D060; border: 1px solid rgba(212, 175, 55, 0.3); padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                    <span>✨</span> <?= count($allImages) ?> <?= count($allImages) === 1 ? 'Foto Full HD' : 'Fotos em Full HD' ?>
                </span>
            </div>
        </div>

        <!-- Visual Book Flex Container: 1 por linha no Mobile, 2 no Tablet, 4 no Desktop -->
        <div class="visual-book-flex">
            <?php foreach ($allImages as $idx => $imgUrl): ?>
                <div class="visual-book-card" onclick="openVisualBookLightbox(<?= $idx ?>)">
                    <img src="<?= htmlspecialchars($imgUrl) ?>" alt="Ângulo <?= $idx + 1 ?> - <?= htmlspecialchars($product->title) ?>" loading="lazy">
                    <div class="vb-card-overlay">
                        <span class="vb-card-badge">
                            🔍 Ampliar Ângulo #<?= $idx + 1 ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Dedicated Product Demonstration Video (HTML5) -->
    <?php
    $productVideoUrl = !empty($product->attributes['video_url']) ? (string)$product->attributes['video_url'] : '';
    if ($productVideoUrl === '' && !empty($product->description) && preg_match('/<source\s+src="([^"]+\.mp4[^"]*)"/i', $product->description, $mVidP)) {
        $productVideoUrl = $mVidP[1];
    }
    ?>
    <?php if (!empty($productVideoUrl)): ?>
        <div style="background: #171d22; border: 1px solid rgba(168, 85, 247, 0.35); border-radius: 12px; padding: 24px; margin-bottom: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.35);">
            <h3 style="margin: 0 0 16px 0; font-size: 18px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-play-circle" style="color: #a855f7;"></i> Vídeo Demonstrativo do Produto
            </h3>
            <div style="max-width: 680px; margin: 0 auto; border-radius: 8px; overflow: hidden; background: #000; box-shadow: 0 8px 24px rgba(0,0,0,0.6);">
                <video controls playsinline preload="metadata" style="width: 100%; height: auto; max-height: 440px; display: block;" poster="<?= htmlspecialchars($product->mainImageUrl ?? '') ?>">
                    <source src="<?= htmlspecialchars($productVideoUrl) ?>" type="video/mp4">
                    Seu navegador não suporta reprodução de vídeo.
                </video>
            </div>
        </div>
    <?php endif; ?>

    <!-- Ficha Técnica / Especificações Detalhadas (Tecido, Composição, Gênero, Artesanato, etc.) -->
    <?php 
    $techSpecs = $product->attributes['especificacoes'] ?? ($product->attributes['ficha_tecnica'] ?? []);
    ?>
    <?php if (!empty($techSpecs) && is_array($techSpecs)): ?>
        <div style="background: #171d22; border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 28px; margin-bottom: 30px;">
            <h2 style="font-size: 20px; font-weight: 700; color: #fff; margin: 0 0 16px 0; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                <span>📋</span> Ficha Técnica & Especificações
            </h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px;">
                <?php foreach ($techSpecs as $specKey => $specVal): ?>
                    <div style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); border-radius: 8px; padding: 10px 14px; display: flex; flex-direction: column; gap: 3px;">
                        <span style="font-size: 11px; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                            <?= htmlspecialchars((string)$specKey) ?>
                        </span>
                        <span style="font-size: 13.5px; color: #fff; font-weight: 600;">
                            <?= htmlspecialchars((string)$specVal) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Full Description & Persuasive Copy -->
    <div style="background: #171d22; border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 28px; margin-bottom: 40px;">
        <h2 style="font-size: 20px; font-weight: 700; color: #fff; margin: 0 0 20px 0; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 12px;">
            Descrição do Produto
        </h2>
        <div style="font-size: 15px; line-height: 1.8; color: #cbd5e1;">
            <?php
            $rawProductDesc = method_exists($product, 'getEnrichedDescription') ? (string)$product->getEnrichedDescription() : (string)$product->description;
            $safeProductDesc = strip_tags($rawProductDesc, '<p><br><ul><ol><li><strong><b><em><i><h3><h4><h5><h6><span><div><video><source>');
            $safeProductDesc = preg_replace('/\s+on[a-z]+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $safeProductDesc) ?? '';
            $safeProductDesc = preg_replace('/(?:javascript|vbscript|data):/i', '', $safeProductDesc) ?? '';
            echo $safeProductDesc;
            ?>
        </div>
    </div>

    <!-- Other Sellers & Similar Offers Section (Amazon / AliExpress / Shopee comparison style) -->
    <?php if (!empty($similarOffers) && is_array($similarOffers)): ?>
        <div class="other-sellers-container" style="background: #171d22; border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 28px; margin-bottom: 40px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 14px;">
                <div>
                    <h2 style="font-size: 20px; font-weight: 700; color: #fff; margin: 0 0 4px 0; display: flex; align-items: center; gap: 8px;">
                        <span>🏬</span> Outros Vendedores & Ofertas Mais Acessíveis
                    </h2>
                    <p style="margin: 0; font-size: 13px; color: #94a3b8;">
                        Compare preços e condições de diferentes fornecedores parceiros para itens similares desta categoria.
                    </p>
                </div>
                <span style="background: rgba(34,197,94,0.15); color: #4ade80; border: 1px solid rgba(34,197,94,0.3); padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                    <?= count($similarOffers) ?> opções disponíveis
                </span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php foreach ($similarOffers as $offer): ?>
                    <?php
                    $offerSeller = $offer->getSellerInfo();
                    $isCheaper = $offer->salePriceBrl < $product->salePriceBrl;
                    $diffAmount = abs($product->salePriceBrl - $offer->salePriceBrl);
                    $offerImg = $offer->mainImageUrl ?: ($offer->images[0] ?? '');
                    $offerUrl = $baseUrl . '/produto/' . htmlspecialchars($offer->slug);
                    $offerCheckoutUrl = $baseUrl . '/checkout/produto/' . (int)$offer->id;
                    ?>
                    <div style="display: flex; align-items: center; justify-content: space-between; background: #0e1317; border: 1px solid <?= $isCheaper ? 'rgba(34,197,94,0.3)' : 'rgba(255,255,255,0.06)' ?>; border-radius: 10px; padding: 14px 18px; gap: 16px; flex-wrap: wrap; transition: transform 0.2s ease, border-color 0.2s ease;">
                        <!-- Thumbnail & Title -->
                        <div style="display: flex; align-items: center; gap: 14px; flex: 1; min-width: 250px;">
                            <div style="width: 56px; height: 56px; border-radius: 8px; overflow: hidden; background: #1a2228; flex-shrink: 0; border: 1px solid rgba(255,255,255,0.1);">
                                <img src="<?= htmlspecialchars($offerImg) ?>" alt="<?= htmlspecialchars($offer->title) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                            <div>
                                <a href="<?= $offerUrl ?>" style="color: #f1f5f9; text-decoration: none; font-size: 14px; font-weight: 600; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden;">
                                    <?= htmlspecialchars($offer->title) ?>
                                </a>
                                <div style="display: flex; align-items: center; gap: 10px; margin-top: 4px; font-size: 12px; color: #94a3b8; flex-wrap: wrap;">
                                    <span>Vendedor: <strong style="color: #cbd5e1;"><?= htmlspecialchars((string)($offerSeller['name'] ?? 'Fornecedor Oficial AliExpress')) ?></strong></span>
                                    <span>•</span>
                                    <span style="color: #F0D060;">★ <?= number_format($offer->rating, 1) ?></span>
                                    <span>•</span>
                                    <span><?= number_format($offer->ordersCount, 0, ',', '.') ?> vendas</span>
                                </div>
                            </div>
                        </div>

                        <!-- Price & Badge -->
                        <div style="text-align: right; min-width: 140px;">
                            <div style="font-size: 18px; font-weight: 800; color: <?= $isCheaper ? '#4ade80' : '#fff' ?>;">
                                R$ <?= number_format($offer->salePriceBrl, 2, ',', '.') ?>
                            </div>
                            <?php if ($isCheaper): ?>
                                <div style="font-size: 11px; color: #4ade80; font-weight: 700; margin-top: 2px;">
                                    Economize R$ <?= number_format($diffAmount, 2, ',', '.') ?>
                                </div>
                            <?php else: ?>
                                <div style="font-size: 11px; color: #94a3b8;">
                                    Envio Oficial Rastreado
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- CTA Actions -->
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <a href="<?= $offerUrl ?>" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15); color: #cbd5e1; padding: 8px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; white-space: nowrap;">
                                Ver Oferta
                            </a>
                            <a href="<?= $offerCheckoutUrl ?>" style="background: <?= $isCheaper ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)' : 'linear-gradient(135deg, #ff4747 0%, #e03232 100%)' ?>; color: #fff; padding: 8px 16px; border-radius: 6px; font-size: 12px; font-weight: 700; text-decoration: none; white-space: nowrap; box-shadow: 0 2px 8px rgba(0,0,0,0.3);">
                                Comprar
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- Modal Tabela de Medidas & Guia de Tamanhos (Princípio da Razoabilidade na Compra) -->
<div id="sizeGuideModal" style="display: none; position: fixed; inset: 0; z-index: 999999; background: rgba(0, 0, 0, 0.85); backdrop-filter: blur(6px); align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #111722; border: 1px solid rgba(240, 208, 96, 0.4); border-radius: 14px; max-width: 640px; width: 100%; max-height: 88vh; overflow-y: auto; padding: 24px; box-shadow: 0 20px 50px rgba(0,0,0,0.85); position: relative;">
        <button type="button" onclick="closeSizeGuideModal()" aria-label="Fechar" style="position: absolute; top: 16px; right: 16px; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); color: #fff; width: 34px; height: 34px; border-radius: 50%; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center;">✕</button>

        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px;">
            <span style="font-size: 26px;">📏</span>
            <div>
                <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: #FFE082;">Tabela de Medidas & Guia de Tamanhos</h3>
                <span style="font-size: 12px; color: #94a3b8;">Consulte as medidas de referência para escolher com total precisão</span>
            </div>
        </div>

        <!-- Tabela Roupas & Vestuário Padrão Brasil -->
        <div style="margin-bottom: 20px;">
            <div style="font-size: 13px; font-weight: 700; color: #cbd5e1; margin-bottom: 8px;">👗 Roupas & Vestuário (Medidas Corporais em cm):</div>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: center;">
                    <thead>
                        <tr style="background: rgba(240, 208, 96, 0.15); color: #FFE082; font-weight: 700;">
                            <th style="padding: 10px 8px; border: 1px solid rgba(255,255,255,0.1);">Tamanho</th>
                            <th style="padding: 10px 8px; border: 1px solid rgba(255,255,255,0.1);">Busto (cm)</th>
                            <th style="padding: 10px 8px; border: 1px solid rgba(255,255,255,0.1);">Cintura (cm)</th>
                            <th style="padding: 10px 8px; border: 1px solid rgba(255,255,255,0.1);">Quadril (cm)</th>
                        </tr>
                    </thead>
                    <tbody style="color: #cbd5e1;">
                        <tr>
                            <td style="padding: 9px 8px; border: 1px solid rgba(255,255,255,0.08); font-weight: 700; color: #fff;">Pequeno (P)</td>
                            <td style="padding: 9px 8px; border: 1px solid rgba(255,255,255,0.08);">84 - 88</td>
                            <td style="padding: 9px 8px; border: 1px solid rgba(255,255,255,0.08);">66 - 70</td>
                            <td style="padding: 9px 8px; border: 1px solid rgba(255,255,255,0.08);">92 - 96</td>
                        </tr>
                        <tr style="background: rgba(255,255,255,0.02);">
                            <td style="padding: 9px 8px; border: 1px solid rgba(255,255,255,0.08); font-weight: 700; color: #fff;">Médio (M)</td>
                            <td style="padding: 9px 8px; border: 1px solid rgba(255,255,255,0.08);">88 - 92</td>
                            <td style="padding: 9px 8px; border: 1px solid rgba(255,255,255,0.08);">70 - 74</td>
                            <td style="padding: 9px 8px; border: 1px solid rgba(255,255,255,0.08);">96 - 100</td>
                        </tr>
                        <tr>
                            <td style="padding: 9px 8px; border: 1px solid rgba(255,255,255,0.08); font-weight: 700; color: #fff;">Grande (G)</td>
                            <td style="padding: 9px 8px; border: 1px solid rgba(255,255,255,0.08);">92 - 96</td>
                            <td style="padding: 9px 8px; border: 1px solid rgba(255,255,255,0.08);">74 - 78</td>
                            <td style="padding: 9px 8px; border: 1px solid rgba(255,255,255,0.08);">100 - 104</td>
                        </tr>
                        <tr style="background: rgba(255,255,255,0.02);">
                            <td style="padding: 9px 8px; border: 1px solid rgba(255,255,255,0.08); font-weight: 700; color: #fff;">Extra Grande (GG)</td>
                            <td style="padding: 9px 8px; border: 1px solid rgba(255,255,255,0.08);">96 - 102</td>
                            <td style="padding: 9px 8px; border: 1px solid rgba(255,255,255,0.08);">78 - 84</td>
                            <td style="padding: 9px 8px; border: 1px solid rgba(255,255,255,0.08);">104 - 110</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tabela Lingeries, Sutiãs & Moda Íntima -->
        <div style="margin-bottom: 20px;">
            <div style="font-size: 13px; font-weight: 700; color: #cbd5e1; margin-bottom: 8px;">👙 Lingeries, Sutiãs &amp; Moda Íntima (Tórax e Busto em cm):</div>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: center;">
                    <thead>
                        <tr style="background: rgba(236, 72, 153, 0.15); color: #f472b6; font-weight: 700;">
                            <th style="padding: 10px 8px; border: 1px solid rgba(255,255,255,0.1);">Tamanho</th>
                            <th style="padding: 10px 8px; border: 1px solid rgba(255,255,255,0.1);">Manequim</th>
                            <th style="padding: 10px 8px; border: 1px solid rgba(255,255,255,0.1);">Busto (cm)</th>
                            <th style="padding: 10px 8px; border: 1px solid rgba(255,255,255,0.1);">Abaixo do Busto (cm)</th>
                        </tr>
                    </thead>
                    <tbody style="color: #cbd5e1;">
                        <tr>
                            <td style="padding: 9px 8px; border: 1px solid rgba(255,255,255,0.08); font-weight: 700; color: #fff;">P</td>
                            <td style="padding: 9px 8px; border: 1px solid rgba(255,255,255,0.08);">38 - 40</td>
                            <td style="padding: 9px 8px; border: 1px solid rgba(255,255,255,0.08);">80 - 86</td>
                            <td style="padding: 9px 8px; border: 1px solid rgba(255,255,255,0.08);">68 - 72</td>
                        </tr>
                        <tr style="background: rgba(255,255,255,0.02);">
                            <td style="padding: 9px 8px; border: 1px solid rgba(255,255,255,0.08); font-weight: 700; color: #fff;">M</td>
                            <td style="padding: 9px 8px; border: 1px solid rgba(255,255,255,0.08);">42</td>
                            <td style="padding: 9px 8px; border: 1px solid rgba(255,255,255,0.08);">87 - 92</td>
                            <td style="padding: 9px 8px; border: 1px solid rgba(255,255,255,0.08);">73 - 77</td>
                        </tr>
                        <tr>
                            <td style="padding: 9px 8px; border: 1px solid rgba(255,255,255,0.08); font-weight: 700; color: #fff;">G</td>
                            <td style="padding: 9px 8px; border: 1px solid rgba(255,255,255,0.08);">44</td>
                            <td style="padding: 9px 8px; border: 1px solid rgba(255,255,255,0.08);">93 - 98</td>
                            <td style="padding: 9px 8px; border: 1px solid rgba(255,255,255,0.08);">78 - 82</td>
                        </tr>
                        <tr style="background: rgba(255,255,255,0.02);">
                            <td style="padding: 9px 8px; border: 1px solid rgba(255,255,255,0.08); font-weight: 700; color: #fff;">GG</td>
                            <td style="padding: 9px 8px; border: 1px solid rgba(255,255,255,0.08);">46</td>
                            <td style="padding: 9px 8px; border: 1px solid rgba(255,255,255,0.08);">99 - 105</td>
                            <td style="padding: 9px 8px; border: 1px solid rgba(255,255,255,0.08);">83 - 87</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tabela Calçados & Tênis -->
        <div style="margin-bottom: 16px;">
            <div style="font-size: 13px; font-weight: 700; color: #cbd5e1; margin-bottom: 8px;">👟 Calçados & Tênis (Padrão Brasil BR):</div>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: center;">
                    <thead>
                        <tr style="background: rgba(59, 130, 246, 0.15); color: #93c5fd; font-weight: 700;">
                            <th style="padding: 8px 6px; border: 1px solid rgba(255,255,255,0.1);">Numeração BR</th>
                            <th style="padding: 8px 6px; border: 1px solid rgba(255,255,255,0.1);">37</th>
                            <th style="padding: 8px 6px; border: 1px solid rgba(255,255,255,0.1);">38</th>
                            <th style="padding: 8px 6px; border: 1px solid rgba(255,255,255,0.1);">39</th>
                            <th style="padding: 8px 6px; border: 1px solid rgba(255,255,255,0.1);">40</th>
                            <th style="padding: 8px 6px; border: 1px solid rgba(255,255,255,0.1);">41</th>
                            <th style="padding: 8px 6px; border: 1px solid rgba(255,255,255,0.1);">42</th>
                            <th style="padding: 8px 6px; border: 1px solid rgba(255,255,255,0.1);">43</th>
                            <th style="padding: 8px 6px; border: 1px solid rgba(255,255,255,0.1);">44</th>
                        </tr>
                    </thead>
                    <tbody style="color: #cbd5e1;">
                        <tr>
                            <td style="padding: 7px 6px; border: 1px solid rgba(255,255,255,0.08); font-weight: 700; color: #fff;">Comprimento (cm)</td>
                            <td style="padding: 7px 6px; border: 1px solid rgba(255,255,255,0.08);">24.5</td>
                            <td style="padding: 7px 6px; border: 1px solid rgba(255,255,255,0.08);">25.0</td>
                            <td style="padding: 7px 6px; border: 1px solid rgba(255,255,255,0.08);">25.5</td>
                            <td style="padding: 7px 6px; border: 1px solid rgba(255,255,255,0.08);">26.5</td>
                            <td style="padding: 7px 6px; border: 1px solid rgba(255,255,255,0.08);">27.5</td>
                            <td style="padding: 7px 6px; border: 1px solid rgba(255,255,255,0.08);">28.0</td>
                            <td style="padding: 7px 6px; border: 1px solid rgba(255,255,255,0.08);">29.0</td>
                            <td style="padding: 7px 6px; border: 1px solid rgba(255,255,255,0.08);">30.0</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div style="font-size: 11.5px; color: #94a3b8; line-height: 1.5; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 12px;">
            💡 <strong>Dica RachidD.com:</strong> Caso suas medidas corporais estejam entre dois tamanhos, recomendamos escolher o tamanho maior para um caimento mais confortável.
        </div>
    </div>
</div>
<div id="visualBookLightbox" style="display: none; position: fixed; inset: 0; z-index: 999999; background: rgba(0, 0, 0, 0.94); backdrop-filter: blur(8px); align-items: center; justify-content: center; flex-direction: column;">
    <!-- Close button -->
    <button onclick="closeVisualBookLightbox()" aria-label="Fechar" style="position: absolute; top: 18px; right: 20px; background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.25); color: #fff; font-size: 24px; width: 44px; height: 44px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s ease; z-index: 10;">
        ✕
    </button>

    <!-- Navigation Chevrons -->
    <?php if (count($allImages) > 1): ?>
        <button onclick="navigateLightbox(-1)" aria-label="Anterior" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.25); color: #fff; font-size: 28px; width: 48px; height: 48px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s ease; z-index: 10;">
            ❮
        </button>
        <button onclick="navigateLightbox(1)" aria-label="Próximo" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.25); color: #fff; font-size: 28px; width: 48px; height: 48px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s ease; z-index: 10;">
            ❯
        </button>
    <?php endif; ?>

    <!-- Main Lightbox Image Container -->
    <div style="max-width: 92vw; max-height: 80vh; display: flex; align-items: center; justify-content: center;">
        <img id="lightboxActiveImg" src="" alt="Visual Book" style="max-width: 100%; max-height: 80vh; object-fit: contain; border-radius: 8px; box-shadow: 0 20px 50px rgba(0,0,0,0.85);">
    </div>

    <!-- Image Counter & Title Info -->
    <div style="margin-top: 16px; text-align: center; color: #94a3b8; font-size: 13px; max-width: 80vw; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
        <span id="lightboxCounter" style="color: #F0D060; font-weight: 700; margin-right: 8px;"></span>
        <span><?= htmlspecialchars($product->title) ?></span>
    </div>
</div>

<style>
/* Visual Book Container em Modo Flex */
.visual-book-flex {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    width: 100%;
    box-sizing: border-box;
}

/* Card base do Visual Book */
.visual-book-card {
    background: #0c0f12;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 10px;
    overflow: hidden;
    cursor: pointer;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
    transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
}

.visual-book-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
    display: block;
}

.visual-book-card .vb-card-overlay {
    position: absolute;
    inset: 0;
    background: rgba(11,15,23,0.55);
    opacity: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: opacity 0.2s ease;
}

.visual-book-card .vb-card-badge {
    background: linear-gradient(135deg, #F0D060 0%, #D4AF37 100%);
    color: #0b0f17;
    font-size: 11px;
    font-weight: 800;
    padding: 6px 14px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.4);
}

.visual-book-card:hover {
    transform: translateY(-4px);
    border-color: rgba(212, 175, 55, 0.6) !important;
    box-shadow: 0 8px 25px rgba(212, 175, 55, 0.18);
}

.visual-book-card:hover img {
    transform: scale(1.05);
}

.visual-book-card:hover .vb-card-overlay {
    opacity: 1 !important;
}

/* DESKTOP (>= 992px): 4 Imagens por Linha */
@media (min-width: 992px) {
    .visual-book-card {
        flex: 0 0 calc(25% - 12px);
        max-width: calc(25% - 12px);
        aspect-ratio: 1 / 1;
    }
}

/* TABLET (601px até 991px): EXATAMENTE 2 IMAGENS POR LINHA */
@media (min-width: 601px) and (max-width: 991px) {
    .visual-book-card {
        flex: 0 0 calc(50% - 8px);
        max-width: calc(50% - 8px);
        aspect-ratio: 1 / 1;
    }
}

/* MOBILE / SMARTPHONE (<= 600px): EXATAMENTE 1 IMAGEM POR LINHA */
@media (max-width: 600px) {
    .visual-book-flex {
        gap: 14px;
    }
    .visual-book-card {
        flex: 0 0 100% !important;
        max-width: 100% !important;
        width: 100% !important;
        aspect-ratio: 4 / 3 !important;
        min-height: 250px;
    }
    .visual-book-card .vb-card-overlay {
        opacity: 1 !important;
        background: linear-gradient(180deg, transparent 55%, rgba(11,15,23,0.75) 100%) !important;
        justify-content: flex-end !important;
        padding-bottom: 14px !important;
    }
    .main-stage-box {
        height: 320px !important;
    }
}

/* Thumbnails da capa */
.product-thumb-item:hover {
    border-color: rgba(212, 175, 55, 0.6) !important;
    transform: scale(1.05);
}
.product-thumb-item.active {
    border-color: #D4AF37 !important;
    box-shadow: 0 0 10px rgba(212, 175, 55, 0.4);
}
</style>

<script>
var productGalleryImages = <?= json_encode($allImages, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
var currentActiveImageIndex = 0;
var isVideoActive = false;

function handleStageClick(event) {
    if (isVideoActive) {
        return;
    }
    openVisualBookLightbox(currentActiveImageIndex);
}

function selectProductVideo(videoUrl, event) {
    if (event) {
        event.stopPropagation();
    }
    isVideoActive = true;
    var mainImg = document.getElementById('main-product-img');
    var mainVideo = document.getElementById('main-product-video');
    var zoomBadge = document.getElementById('main-stage-zoom-badge');
    var playBtn = document.getElementById('btnPlayMainStageVideo');
    var stage = document.getElementById('main-product-stage');

    if (stage) stage.style.cursor = 'default';
    if (mainImg) mainImg.style.display = 'none';
    if (zoomBadge) zoomBadge.style.display = 'none';
    if (playBtn) playBtn.style.display = 'none';
    if (mainVideo) {
        mainVideo.style.display = 'block';
        if (!mainVideo.src || mainVideo.src === '' || mainVideo.src === window.location.href) {
            mainVideo.src = videoUrl;
        }
        mainVideo.play().catch(function() {});
    }

    document.querySelectorAll('.photo-thumb-item').forEach(function(el) {
        el.classList.remove('active');
        el.style.borderColor = 'rgba(255,255,255,0.12)';
    });
    var videoThumb = document.getElementById('thumb-video-item');
    if (videoThumb) {
        videoThumb.classList.add('active');
        videoThumb.style.borderColor = '#c084fc';
        videoThumb.style.boxShadow = '0 0 12px rgba(168,85,247,0.7)';
    }
}

function selectProductImage(index, url) {
    isVideoActive = false;
    currentActiveImageIndex = index;
    var mainImg = document.getElementById('main-product-img');
    var mainVideo = document.getElementById('main-product-video');
    var zoomBadge = document.getElementById('main-stage-zoom-badge');
    var playBtn = document.getElementById('btnPlayMainStageVideo');
    var stage = document.getElementById('main-product-stage');

    if (stage) stage.style.cursor = 'zoom-in';
    if (mainVideo) {
        mainVideo.pause();
        mainVideo.style.display = 'none';
    }
    if (zoomBadge) zoomBadge.style.display = 'flex';
    if (playBtn) playBtn.style.display = 'inline-flex';
    if (mainImg) {
        mainImg.style.display = 'block';
        mainImg.style.opacity = '0.3';
        setTimeout(function() {
            mainImg.src = url;
            mainImg.style.opacity = '1';
        }, 120);
    }

    var videoThumb = document.getElementById('thumb-video-item');
    if (videoThumb) {
        videoThumb.classList.remove('active');
        videoThumb.style.borderColor = 'rgba(168, 85, 247, 0.7)';
        videoThumb.style.boxShadow = 'none';
    }

    document.querySelectorAll('.photo-thumb-item').forEach(function(el, i) {
        if (i === index) {
            el.classList.add('active');
            el.style.borderColor = '#D4AF37';
        } else {
            el.classList.remove('active');
            el.style.borderColor = 'rgba(255,255,255,0.12)';
        }
    });
}

function openVisualBookLightbox(index) {
    if (!productGalleryImages || productGalleryImages.length === 0) return;
    currentActiveImageIndex = (index >= 0 && index < productGalleryImages.length) ? index : 0;
    updateLightbox();
    var lb = document.getElementById('visualBookLightbox');
    if (lb) {
        lb.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeVisualBookLightbox() {
    var lb = document.getElementById('visualBookLightbox');
    if (lb) {
        lb.style.display = 'none';
        document.body.style.overflow = '';
    }
}

function navigateLightbox(step) {
    if (!productGalleryImages || productGalleryImages.length <= 1) return;
    currentActiveImageIndex = (currentActiveImageIndex + step + productGalleryImages.length) % productGalleryImages.length;
    updateLightbox();
}

function updateLightbox() {
    var imgEl = document.getElementById('lightboxActiveImg');
    var counterEl = document.getElementById('lightboxCounter');
    if (imgEl && productGalleryImages[currentActiveImageIndex]) {
        imgEl.src = productGalleryImages[currentActiveImageIndex];
    }
    if (counterEl) {
        counterEl.textContent = 'Foto ' + (currentActiveImageIndex + 1) + ' de ' + productGalleryImages.length;
    }
}

// Controle de Quantidade na página de detalhes do produto
var productUnitPriceBrl = <?= json_encode((float)$product->salePriceBrl) ?>;
var productUnitWeightInGrams = <?= (int)$productWeightGrams ?>;
var productCheckoutBaseUrl = <?= json_encode($checkoutUrl, JSON_UNESCAPED_SLASHES) ?>;
var productIsFreeShipping = <?= json_encode($isFreeShipping) ?>;
var productShippingFee = <?= json_encode($shippingFee) ?>;

function calcProductExpressShipping(qty) {
    var totalWeight = productUnitWeightInGrams * Math.max(1, qty);
    if (totalWeight <= 300) return 19.90;
    if (totalWeight <= 1000) return 29.90;
    if (totalWeight <= 2000) return 39.90;
    var extraKg = Math.ceil((totalWeight - 2000) / 1000);
    return Math.round((39.90 + (extraKg * 10.00)) * 100) / 100;
}

function getProductFormattedWeight(qty) {
    var totalGrams = productUnitWeightInGrams * Math.max(1, qty);
    if (totalGrams >= 1000) {
        var kg = totalGrams / 1000;
        return (totalGrams % 1000 === 0 ? kg.toFixed(0) : kg.toFixed(1)).replace('.', ',') + ' kg';
    }
    return totalGrams + 'g';
}

// Matriz de Variações e SKUs (Padrão SKU Matrix / AliExpress SDK)
var productVariants = <?= json_encode(array_map(fn($v) => $v->toArray(), $product->getVariants()), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
var productVariationAxes = <?= json_encode($product->getVariationAxes(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
var selectedVariationOptions = {};
var activeProductSku = null;

function selectVariantOption(axis, value) {
    if (!axis || !value) return;

    // Impede seleção de opções desativadas ou sem estoque
    var isAllowed = false;
    document.querySelectorAll('.btn-variant-chip').forEach(function(btn) {
        if (btn.getAttribute('data-axis') === axis && btn.getAttribute('data-value') === value) {
            if (btn.disabled || btn.getAttribute('data-in-stock') === '0') {
                isAllowed = false;
            } else {
                isAllowed = true;
            }
        }
    });
    if (!isAllowed) return;

    selectedVariationOptions[axis] = value;

    // Atualiza o rótulo da opção selecionada (sem usar CSS.escape para compatibilidade total)
    document.querySelectorAll('.axis-selected-label').forEach(function(el) {
        if (el.getAttribute('data-axis') === axis) {
            el.textContent = value;
        }
    });

    // Atualiza os estilos visuais dos botões do mesmo eixo (mantendo os desativados/esgotados apagados)
    document.querySelectorAll('.btn-variant-chip').forEach(function(btn) {
        if (btn.getAttribute('data-axis') === axis) {
            var inStock = (btn.getAttribute('data-in-stock') !== '0') && !btn.disabled;
            if (!inStock) {
                btn.style.background = 'rgba(255,255,255,0.01)';
                btn.style.borderColor = 'rgba(255,255,255,0.1)';
                btn.style.color = '#64748b';
                btn.style.boxShadow = 'none';
                return;
            }

            if (btn.getAttribute('data-value') === value) {
                btn.style.background = 'linear-gradient(135deg, rgba(212,175,55,0.3) 0%, rgba(240,208,96,0.2) 100%)';
                btn.style.borderColor = '#D4AF37';
                btn.style.color = '#F0D060';
                btn.style.boxShadow = '0 0 10px rgba(212,175,55,0.35)';
            } else {
                btn.style.background = 'rgba(255,255,255,0.04)';
                btn.style.borderColor = 'rgba(255,255,255,0.14)';
                btn.style.color = '#fff';
                btn.style.boxShadow = 'none';
            }
        }
    });

    // Localiza e aplica a variante correspondente
    findAndApplyMatchingVariant();
}

function findAndApplyMatchingVariant() {
    if (!productVariants || productVariants.length === 0) return;

    var matched = null;
    for (var i = 0; i < productVariants.length; i++) {
        var v = productVariants[i];
        var isMatch = true;
        for (var axis in selectedVariationOptions) {
            if (v.options && v.options[axis] !== selectedVariationOptions[axis]) {
                isMatch = false;
                break;
            }
        }
        if (isMatch) {
            matched = v;
            break;
        }
    }

    var summaryEl = document.getElementById('active-variant-summary');
    var priceDisplayEl = document.getElementById('product-display-price');

    if (matched) {
        activeProductSku = matched.sku_id;

        // Se tiver imagem específica da SKU, atualiza o palco principal
        if (matched.image_url) {
            var mainImg = document.getElementById('main-product-img');
            if (mainImg) {
                mainImg.src = matched.image_url;
            }
        }

        // Se tiver preço diferenciado na variação, atualiza o valor
        if (matched.price_brl && matched.price_brl > 0) {
            productUnitPriceBrl = parseFloat(matched.price_brl);
            if (priceDisplayEl) {
                priceDisplayEl.textContent = 'R$ ' + productUnitPriceBrl.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        }

        // Atualiza o resumo visual da combinação
        if (summaryEl) {
            var optsStr = [];
            for (var ax in matched.options) {
                optsStr.push(matched.options[ax]);
            }
            summaryEl.textContent = optsStr.join(' / ') + ' • ' + (matched.stock > 0 ? (matched.stock + ' em estoque') : 'Pronta Entrega');
            summaryEl.style.color = '#4ade80';
        }
    } else {
        activeProductSku = null;
        if (summaryEl) {
            summaryEl.textContent = 'Selecione todas as opções';
            summaryEl.style.color = '#f59e0b';
        }
    }

    syncProductQuantity();
}

function initProductVariants() {
    if (productVariationAxes && Object.keys(productVariationAxes).length > 0) {
        for (var axis in productVariationAxes) {
            var preferredVal = null;
            // Procura o primeiro chip deste eixo que tem estoque ativo e não está disabled
            var chips = document.querySelectorAll('.btn-variant-chip');
            for (var c = 0; c < chips.length; c++) {
                var chip = chips[c];
                if (chip.getAttribute('data-axis') === axis && !chip.disabled && chip.getAttribute('data-in-stock') === '1') {
                    preferredVal = chip.getAttribute('data-value');
                    break;
                }
            }

            if (preferredVal) {
                selectVariantOption(axis, preferredVal);
            } else {
                var opts = productVariationAxes[axis];
                if (opts && opts.length > 0) {
                    selectVariantOption(axis, opts[0]);
                }
            }
        }
    }
}

function syncProductQuantity() {
    var input = document.getElementById('product-qty-input');
    var buyBtn = document.getElementById('btn-buy-now');
    var subtotalEl = document.getElementById('product-qty-subtotal');
    if (!input) return;

    var qty = parseInt(input.value, 10);
    if (isNaN(qty) || qty < 1) qty = 1;
    if (qty > 50) qty = 50;
    input.value = qty;

    if (buyBtn) {
        var checkoutUrl = productCheckoutBaseUrl + '?qty=' + qty;
        if (activeProductSku) {
            checkoutUrl += '&sku=' + encodeURIComponent(activeProductSku);
        }
        var currentCep = '';
        try {
            var cepField = document.getElementById('shipping-cep-input');
            if (cepField && cepField.value) {
                currentCep = cepField.value.replace(/\D/g, '');
            } else {
                currentCep = localStorage.getItem('rachidd_user_shipping_cep') || '';
            }
        } catch(e) {}
        if (currentCep && currentCep.length === 8) {
            checkoutUrl += '&shipping_zip=' + encodeURIComponent(currentCep);
        }
        buyBtn.href = checkoutUrl;
    }
    if (subtotalEl) {
        if (qty > 1) {
            var total = (productUnitPriceBrl * qty).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            subtotalEl.textContent = qty + ' unid. • Total: R$ ' + total;
            subtotalEl.style.display = 'inline-block';
        } else {
            subtotalEl.style.display = 'none';
        }
    }

    // Atualiza dinamicamente o badge de peso e preço do frete Sedex para a quantidade selecionada
    var weightBadge = document.getElementById('product-shipping-weight-badge');
    var expressBadge = document.getElementById('product-shipping-express-badge');
    if (weightBadge) {
        var formattedWeight = getProductFormattedWeight(qty);
        var totalGrams = productUnitWeightInGrams * qty;
        if (qty > 1) {
            weightBadge.textContent = qty + 'x ' + productUnitWeightInGrams + 'g (' + formattedWeight + ')';
        } else {
            weightBadge.textContent = formattedWeight;
        }
    }
    if (expressBadge) {
        var dynamicExpressPrice = calcProductExpressShipping(qty);
        var totalGrams = productUnitWeightInGrams * qty;
        var extraNote = '';
        if (totalGrams > 2000) {
            var extraKg = Math.ceil((totalGrams - 2000) / 1000);
            extraNote = ' (+' + extraKg + 'kg extra)';
        } else if (totalGrams > 300 && productUnitWeightInGrams <= 300) {
            extraNote = ' (peso acumulado)';
        }
        expressBadge.textContent = '+ R$ ' + dynamicExpressPrice.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + extraNote;
    }
}

function changeProductQuantity(delta) {
    var input = document.getElementById('product-qty-input');
    if (!input) return;
    var current = parseInt(input.value, 10) || 1;
    input.value = Math.max(1, Math.min(50, current + delta));
    syncProductQuantity();
}

// Atalhos de teclado no Lightbox (ESC fecha, Setas navegam)
document.addEventListener('keydown', function(e) {
    var lb = document.getElementById('visualBookLightbox');
    if (lb && lb.style.display === 'flex') {
        if (e.key === 'Escape') closeVisualBookLightbox();
        if (e.key === 'ArrowLeft') navigateLightbox(-1);
        if (e.key === 'ArrowRight') navigateLightbox(1);
    }
});

// Disparo Inteligente de Demanda (Beacon/Keepalive)
var productTrackingData = {
    product_id: <?= json_encode((string)($product->aliexpressId ?: (string)$product->id)) ?>,
    title: <?= json_encode((string)$product->title) ?>,
    category: <?= json_encode((string)$product->category) ?>
};

function trackProductAction(actionType) {
    if (!productTrackingData.product_id) return;
    var base = <?= json_encode($baseUrl) ?>;
    var endpoint = (base ? base.replace(/\/+$/, '') : '') + '/api/drop/track-demand';
    var payload = JSON.stringify({
        product_id: productTrackingData.product_id,
        title: productTrackingData.title,
        category: productTrackingData.category,
        action: actionType,
        timestamp: Math.floor(Date.now() / 1000)
    });

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
}

// Dispara inicialização de variações e evento 'view_details' ao carregar a página de detalhes
document.addEventListener('DOMContentLoaded', function() {
    initProductVariants();
    trackProductAction('view_details');

    // Recupera CEP previamente consultado
    try {
        var cachedCep = localStorage.getItem('rachidd_user_shipping_cep');
        var cepInput = document.getElementById('shipping-cep-input');
        if (cachedCep && cachedCep.length === 8 && cepInput) {
            cepInput.value = cachedCep.substring(0, 5) + '-' + cachedCep.substring(5);
            calculateProductShipping(false);
        }
    } catch(e) {}
});

function formatShippingCep(input) {
    var v = input.value.replace(/\D/g, '').substring(0, 8);
    if (v.length > 5) {
        input.value = v.substring(0, 5) + '-' + v.substring(5);
    } else {
        input.value = v;
    }
}

function calculateProductShipping(shouldFocus) {
    var input = document.getElementById('shipping-cep-input');
    var btn = document.getElementById('btn-calc-shipping');
    var resultBox = document.getElementById('shipping-result-box');
    var locText = document.getElementById('shipping-location-text');
    if (!input || !resultBox || !locText) return;

    var cleanCep = input.value.replace(/\D/g, '');
    if (cleanCep.length !== 8) {
        input.style.borderColor = '#ef4444';
        return;
    }
    input.style.borderColor = 'rgba(255,255,255,0.15)';

    var originalBtnText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '⏳ Calculando...';

    fetch('https://viacep.com.br/ws/' + cleanCep + '/json/')
        .then(function(res) { return res.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.innerHTML = originalBtnText;

            if (data.erro) {
                locText.innerHTML = '❌ CEP não encontrado. Verifique os dígitos informados.';
                locText.style.color = '#f87171';
                resultBox.style.display = 'block';
                return;
            }

            var localStr = (data.bairro ? data.bairro + ' - ' : '') + (data.localidade || '') + '/' + (data.uf || '');
            locText.textContent = '📍 Entrega para: ';
            var strongEl = document.createElement('strong');
            strongEl.textContent = localStr;
            locText.appendChild(strongEl);
            locText.style.color = '#38bdf8';
            resultBox.style.display = 'block';

            try {
                localStorage.setItem('rachidd_user_shipping_cep', cleanCep);
            } catch(e) {}

            syncProductQuantity();
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = originalBtnText;
            if (productIsFreeShipping) {
                locText.innerHTML = '📍 Frete Padrão com Rastreio <strong>GRÁTIS</strong> disponível para todo o Brasil!';
                locText.style.color = '#00d284';
            } else {
                locText.innerHTML = '📍 Frete Padrão com Rastreio por <strong>R$ ' + parseFloat(productShippingFee).toFixed(2).replace('.', ',') + '</strong> para todo o Brasil!';
                locText.style.color = '#38bdf8';
            }
            resultBox.style.display = 'block';
        });
}

function showDropToast(options) {
    var title = options.title || 'Aviso';
    var message = options.message || '';
    var type = options.type || 'warning';
    var duration = options.duration || 7000;

    var container = document.getElementById('domain-drop-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'domain-drop-toast-container';
        container.style.cssText = 'position: fixed; top: 24px; right: 24px; z-index: 9999999; display: flex; flex-direction: column; gap: 12px; pointer-events: none; max-width: 440px; width: calc(100vw - 32px);';
        document.body.appendChild(container);
    }

    var toast = document.createElement('div');
    var isAlert = type === 'error' || type === 'warning';
    var borderColor = isAlert ? '#ef4444' : '#10b981';
    var titleColor = isAlert ? '#fca5a5' : '#6ee7b7';
    var icon = isAlert ? '⚠️' : '✅';

    toast.className = 'drop-toast-item';
    toast.style.cssText = 'pointer-events: auto; background: linear-gradient(135deg, rgba(20, 24, 33, 0.96) 0%, rgba(30, 35, 48, 0.96) 100%); border: 1px solid rgba(255,255,255,0.12); border-left: 4px solid ' + borderColor + '; border-radius: 12px; padding: 16px 18px; box-shadow: 0 16px 36px rgba(0,0,0,0.65), 0 0 20px ' + (isAlert ? 'rgba(239, 68, 68, 0.2)' : 'rgba(16, 185, 129, 0.2)') + '; backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); display: flex; flex-direction: column; gap: 8px; animation: dropToastSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards; transition: all 0.35s ease; font-family: inherit;';

    toast.innerHTML = '<div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">' +
        '<div style="display: flex; align-items: center; gap: 8px;">' +
            '<span style="font-size: 18px; line-height: 1;">' + icon + '</span>' +
            '<strong style="color: ' + titleColor + '; font-size: 14px; font-weight: 700; line-height: 1.3;">' + title + '</strong>' +
        '</div>' +
        '<button type="button" style="background: none; border: none; color: #94a3b8; font-size: 18px; cursor: pointer; padding: 0 4px; line-height: 1; transition: color 0.2s;" onmouseover="this.style.color=\'#fff\'" onmouseout="this.style.color=\'#94a3b8\'" onclick="this.closest(\'.drop-toast-item\').remove();">&times;</button>' +
    '</div>' +
    '<div style="color: #cbd5e1; font-size: 13px; line-height: 1.45;">' + message + '</div>' +
    '<div style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: #94a3b8; margin-top: 4px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 6px;">' +
        '<span>🛡️</span>' +
        '<span>Segurança e Transparência CDC / Procon • Nenhuma cobrança indevida</span>' +
    '</div>';

    container.appendChild(toast);

    setTimeout(function() {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(function() {
            if (toast.parentNode) toast.parentNode.removeChild(toast);
        }, 350);
    }, duration);
}

var _buyCheckInProgress = false;
function handleBuyNowClick(event, btn) {
    if (event) event.preventDefault();
    if (!btn || btn.getAttribute('data-sold-out') === '1' || _buyCheckInProgress) {
        return false;
    }

    <?php if ($product->hasVariants() || !empty($product->attributes['variation_axes'])): ?>
    var missingAxes = [];
    if (typeof productVariationAxes !== 'undefined' && productVariationAxes) {
        for (var axKey in productVariationAxes) {
            if (!selectedVariationOptions[axKey]) {
                missingAxes.push(axKey);
            }
        }
    }
    if (missingAxes.length > 0 || !activeProductSku) {
        var missingMsg = missingAxes.length > 0 ? ('Por favor, selecione ' + missingAxes.join(' e ') + ' antes de continuar para a compra.') : 'Por favor, selecione as opções de cor e tamanho desejadas.';
        if (typeof showDropToast === 'function') {
            showDropToast({
                title: 'Opções Necessárias',
                message: missingMsg,
                type: 'warning',
                duration: 6500
            });
        }
        var varBox = document.getElementById('product-variants-box');
        if (varBox) {
            varBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            varBox.style.boxShadow = '0 0 16px rgba(240, 208, 96, 0.5)';
            setTimeout(function() { varBox.style.boxShadow = ''; }, 2200);
        }
        return false;
    }
    <?php endif; ?>

    _buyCheckInProgress = true;
    var targetHref = btn.href;
    var origHtml = btn.innerHTML;
    var fbBox = document.getElementById('buy-availability-feedback');
    btn.style.opacity = '0.8';
    btn.innerHTML = '<span>⏳ Verificando estoque...</span>';

    var payload = {
        product_id: <?= (int)($product->id ?? 0) ?>,
        slug: <?= json_encode((string)($product->slug ?? ''), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
        sku: activeProductSku || ''
    };

    fetch(<?= json_encode($baseUrl . '/api/drop/check-availability', JSON_UNESCAPED_SLASHES) ?>, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        _buyCheckInProgress = false;
        if (data && data.available === false) {
            btn.setAttribute('data-sold-out', '1');
            btn.style.opacity = '0.55';
            btn.style.cursor = 'not-allowed';
            btn.style.background = '#334155';
            btn.style.color = '#cbd5e1';
            btn.innerHTML = '<span>Estoque Esgotado</span>';

            var soldOutMsg = data.message || 'O estoque deste produto se esgotou recentemente no fornecedor oficial. Para sua total segurança e transparência (em conformidade com o Código de Defesa do Consumidor e Procon), nenhuma cobrança foi realizada no seu cartão ou Pix.';

            if (typeof showDropToast === 'function') {
                showDropToast({
                    title: 'Produto Esgotado / Indisponível',
                    message: soldOutMsg,
                    type: 'warning',
                    duration: 8500
                });
            }

            if (fbBox) {
                fbBox.style.display = 'block';
                fbBox.style.background = 'rgba(239, 68, 68, 0.14)';
                fbBox.style.border = '1px solid rgba(239, 68, 68, 0.45)';
                fbBox.style.color = '#fca5a5';
                fbBox.textContent = '⚠️ ' + soldOutMsg;
            }
            return;
        }

        if (fbBox && data && typeof data.stock === 'number' && data.stock > 0) {
            fbBox.style.display = 'block';
            fbBox.style.background = 'rgba(16, 185, 129, 0.12)';
            fbBox.style.border = '1px solid rgba(16, 185, 129, 0.35)';
            fbBox.style.color = '#6ee7b7';
            fbBox.textContent = '✅ Estoque atual confirmado: ' + data.stock + ' unidade(s) disponíveis.';
        }

        btn.style.opacity = '1';
        btn.innerHTML = origHtml;
        trackProductAction('buy_click');
        window.location.href = targetHref;
    })
    .catch(function() {
        _buyCheckInProgress = false;
        btn.style.opacity = '1';
        btn.innerHTML = origHtml;
        trackProductAction('buy_click');
        window.location.href = targetHref;
    });

    return false;
}

function openSizeGuideModal() {
    var modal = document.getElementById('sizeGuideModal');
    if (modal) modal.style.display = 'flex';
}

function closeSizeGuideModal() {
    var modal = document.getElementById('sizeGuideModal');
    if (modal) modal.style.display = 'none';
}
</script>
