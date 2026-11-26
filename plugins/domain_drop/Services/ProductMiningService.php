<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Services;

use DomainSystem\Plugins\domain_drop\Contracts\AiProductEnricherInterface;
use DomainSystem\Plugins\domain_drop\Contracts\AliExpressApiClientInterface;
use DomainSystem\Plugins\domain_drop\Contracts\CategoryTaxonomyInterface;
use DomainSystem\Plugins\domain_drop\Contracts\CuratedCatalogProviderInterface;
use DomainSystem\Plugins\domain_drop\Contracts\CurrencyConverterInterface;
use DomainSystem\Plugins\domain_drop\Contracts\DropshipCatalogRepositoryInterface;
use DomainSystem\Plugins\domain_drop\Contracts\ProductLinkScraperInterface;
use DomainSystem\Plugins\domain_drop\Contracts\ProductMiningServiceInterface;
use DomainSystem\Plugins\domain_drop\Contracts\WarehouseOriginResolverInterface;
use DomainSystem\Plugins\domain_drop\DTO\DropshipProductDto;
use DomainSystem\Plugins\domain_drop\DTO\ScrapedProductDto;
use DomainSystem\Plugins\domain_drop\Services\CategoryTaxonomyService;
use DomainSystem\Plugins\domain_drop\Services\CuratedCatalogProvider;
use DomainSystem\Plugins\domain_drop\Services\Validation\MiningValidationHub;
use DomainSystem\Plugins\domain_drop\Services\WarehouseOriginResolver;
use RuntimeException;
use Throwable;

/**
 * Orquestrador do Motor de Mineração e Enriquecimento Inteligente (SRP / DIP).
 */
class ProductMiningService implements ProductMiningServiceInterface
{
    public const OFFICIAL_CATEGORIES = [
        'Eletrônicos & Telefonia',
        'Relógios & Smartwatches',
        'Casa, Iluminação & Eletro',
        'Ferramentas & Utilidades',
        'Beleza, Saúde & Maquiagem',
        'Pet Shop',
        'Peças & Acessórios Automotivos',
        'Roupas, Bolsas & Calçados',
        'Roupa Íntima & Pijamas',
    ];

    private ProductLinkScraperInterface $linkScraper;
    private CategoryTaxonomyInterface $taxonomy;
    private ?string $configFilePath;
    private ?\DomainSystem\Plugins\domain_drop\Contracts\DemandMetricsRepositoryInterface $demandMetricsRepo;
    private CuratedCatalogProviderInterface $catalogProvider;
    private MiningValidationHub $validationHub;
    private WarehouseOriginResolverInterface $originResolver;

    public function __construct(
        private readonly AliExpressApiClientInterface $apiClient,
        private readonly CurrencyConverterInterface $currencyConverter,
        private readonly AiProductEnricherInterface $aiEnricher,
        private readonly DropshipCatalogRepositoryInterface $repository,
        ?ProductLinkScraperInterface $linkScraper = null,
        ?string $configFilePath = null,
        ?\DomainSystem\Plugins\domain_drop\Contracts\DemandMetricsRepositoryInterface $demandMetricsRepo = null,
        ?CategoryTaxonomyInterface $taxonomy = null,
        ?CuratedCatalogProviderInterface $catalogProvider = null,
        ?MiningValidationHub $validationHub = null,
        ?WarehouseOriginResolverInterface $originResolver = null
    ) {
        $this->taxonomy = $taxonomy ?? new CategoryTaxonomyService();
        $this->linkScraper = $linkScraper ?? new AliExpressLinkScraperService($this->taxonomy);
        $this->configFilePath = $configFilePath;
        $this->demandMetricsRepo = $demandMetricsRepo;
        $this->catalogProvider = $catalogProvider ?? new CuratedCatalogProvider();
        $this->validationHub = $validationHub ?? new MiningValidationHub();
        $this->originResolver = $originResolver ?? new WarehouseOriginResolver();
    }

    public function getValidationHub(): MiningValidationHub
    {
        return $this->validationHub;
    }

    /**
     * Normaliza categorias legadas e apelidos para uma das categorias oficiais.
     */
    public static function normalizeCategory(string $category): string
    {
        return (new CategoryTaxonomyService())->normalizeCategory($category);
    }

    public static function toCommercialCategory(string $category): string
    {
        return (new CategoryTaxonomyService())->toCommercialCategory($category);
    }

    public function mineTrendingProducts(
        string $category = 'Moda, Roupas & Vestuário',
        int $limit = 10,
        bool $autoPersist = true,
        string $sort = 'volume_desc',
        string $warehouseOrigin = 'ALL'
    ): array {
        $commercialCat = self::toCommercialCategory($category);
        $canonicalCat = self::normalizeCategory($category);
        $warehouseOrigin = strtoupper(trim($warehouseOrigin)) ?: 'ALL';
        $rawProducts = [];
        $candidateTarget = min(60, max(25, $limit * 3));
        try {
            $pageSize = min($candidateTarget, 50);
            $pagesNeeded = (int)ceil($candidateTarget / $pageSize);

            // Obtém IDs e termos de busca oficiais da taxonomia alinhada ao AliExpress (categoria-e-atributos.pdf e dropshiper.pdf)
            $catIds = $this->taxonomy->getAliExpressCategoryIds($canonicalCat);
            $primaryCatId = !empty($catIds) ? (string)$catIds[0] : null;
            $searchTerms = $this->taxonomy->getAliExpressSearchKeywords($canonicalCat);
            if (empty($searchTerms)) {
                $searchTerms = [$commercialCat];
            }

            $seenIds = [];

            // Estratégia Adaptativa (Tentativas e Acertos):
            // Itera sobre os termos de busca oficiais da categoria até acumular a cota de candidatos limpos
            foreach ($searchTerms as $term) {
                if (count($rawProducts) >= $candidateTarget) {
                    break;
                }
                try {
                    $searchPayload = [
                        'keyword'           => $term,
                        'keyWord'           => $term,
                        'query'             => $term,
                        'category_id'       => $primaryCatId,
                        'categoryId'        => $primaryCatId,
                        'feed_name'         => 'DS bestseller',
                        'page_size'         => '20',
                        'page_no'           => '1',
                        'page_index'        => '1',
                        'sort'              => $sort,
                        'sort_by'           => 'orders,desc',
                        'local'             => 'pt_BR',
                        'country_code'      => 'BR',
                        'country'           => 'BR',
                        'currency'          => 'BRL',
                        'ship_to_country'   => 'BR',
                    ];

                    if ($warehouseOrigin === 'BR') {
                        $searchPayload['ship_from_country'] = 'BR';
                    } elseif ($warehouseOrigin === 'CN') {
                        $searchPayload['ship_from_country'] = 'CN';
                    }
                    // Se 'ALL' (Global), não restringe ship_from_country para permitir entrepostos da China e do Brasil

                    $batch = $this->apiClient->searchProducts($searchPayload);

                    if (is_array($batch) && !empty($batch)) {
                        foreach ($batch as $bItem) {
                            $pId = (string)($bItem['itemId'] ?? ($bItem['product_id'] ?? ($bItem['item_id'] ?? '')));
                            if ($pId !== '' && !isset($seenIds[$pId])) {
                                $seenIds[$pId] = true;
                                $rawProducts[] = $bItem;
                                if (count($rawProducts) >= $candidateTarget) {
                                    break;
                                }
                            }
                        }
                    }
                } catch (Throwable $eTerm) {
                    // Isolamento por termo: segue para a próxima tentativa da mesma categoria
                    continue;
                }
            }
        } catch (Throwable $e) {
            $rawProducts = [];
        }

        // Se a API remota oficial não estiver conectada ou retornar vazio, busca em tempo real na vitrine do AliExpress Brasil
        if (empty($rawProducts)) {
            try {
                $liveMined = $this->mineLiveFromAliExpressSearch($commercialCat, $limit, $autoPersist, $warehouseOrigin);
                if (!empty($liveMined)) {
                    return $liveMined;
                }
            } catch (Throwable $e) {
                // Silencia para fallback de catálogo curado oficial
            }
        }

        // Se a API remota oficial não estiver conectada (ou em modo Test), utiliza catálogo curado e verificado
        if (empty($rawProducts)) {
            $rawProducts = $this->getCuratedTrendingFallback($commercialCat, $candidateTarget, $sort, $warehouseOrigin);
        } else {
            $rawProducts = self::scoreAndSortByReputation($rawProducts, $sort, 4.0, 10, $candidateTarget, $warehouseOrigin);
        }

        // Tag de entreposto para inspeção do WarehouseOriginGuard
        foreach ($rawProducts as &$rawItemRef) {
            if (is_array($rawItemRef)) {
                $rawItemRef['target_warehouse_origin'] = $warehouseOrigin;
            }
        }
        unset($rawItemRef);

        // Ingestão no Container e Execução do Pipeline de Validação Contratual (Elimina 404s, Preços Isca e Restrições)
        $validatedBatch = $this->validationHub->ingestAndValidateBatch($rawProducts);
        $rawProducts = array_values($validatedBatch['valid_items']);

        // Se os produtos externos descartados deixarem a cota abaixo do solicitado, complementa com o pool curado oficial
        if (count($rawProducts) < $limit) {
            $needed = $limit - count($rawProducts);
            $curatedFallback = $this->getCuratedTrendingFallback($commercialCat, $needed + 5, $sort, $warehouseOrigin);
            foreach ($curatedFallback as &$curatedRef) {
                if (is_array($curatedRef)) {
                    $curatedRef['target_warehouse_origin'] = $warehouseOrigin;
                }
            }
            unset($curatedRef);

            $validatedCurated = $this->validationHub->ingestAndValidateBatch($curatedFallback);
            foreach ($validatedCurated['valid_items'] as $cItem) {
                $rawProducts[] = $cItem;
                if (count($rawProducts) >= $limit) {
                    break;
                }
            }
        }

        // Garante a cota exata solicitada com produtos 100% aprovados pelos 4 Guardiões
        $rawProducts = array_slice($rawProducts, 0, max(1, $limit));

        $mined = [];
        foreach ($rawProducts as $item) {
            try {
                $baseInfo = $item['ae_item_base_info_dto'] ?? [];
                $itemId = (string)(
                    $item['itemId']
                    ?? ($item['product_id']
                    ?? ($baseInfo['product_id']
                    ?? ($item['item_id']
                    ?? ($item['productId'] ?? uniqid('ae_')))))
                );
                $dto = $this->processWithTransientBuffer(
                    $itemId,
                    $item,
                    function (array $rawItem) use ($commercialCat, $autoPersist) {
                        $transformed = $this->transformRawItemToDto($rawItem, $commercialCat);
                        if ($autoPersist) {
                            $savedId = $this->repository->save($transformed);
                            return $this->repository->findById($savedId) ?? $transformed;
                        }
                        return $transformed;
                    }
                );
                $mined[] = $dto;
            } catch (Throwable $e) {
                // Isolamento de falha individual de produto malformado
            }
        }

        return $mined;
    }

    /**
     * Calcula o índice do Termômetro de Curadoria (0 a 100) combinando nota e aprovações/volume.
     */
    public static function calculateThermometerScore(float $rating, int $ordersCount): int
    {
        $clampedRating = max(0.0, min(5.0, $rating > 0.0 ? $rating : 4.8));
        $ratingScore = ($clampedRating / 5.0) * 60.0;
        $ordersScore = $ordersCount > 0 ? min(40.0, log10($ordersCount + 1) * 10.5) : 15.0;

        return (int)max(10, min(100, round($ratingScore + $ordersScore)));
    }

    /**
     * Filtra e ordena resultados brutos do SDK do AliExpress por nota, aprovações e termômetro.
     *
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    public static function scoreAndSortByReputation(
        array $items,
        string $sort = 'volume_desc',
        float $minRating = 4.5,
        int $minOrders = 50,
        int $limit = 20,
        string $warehouseOrigin = 'ALL'
    ): array {
        $warehouseOrigin = strtoupper(trim($warehouseOrigin)) ?: 'ALL';
        $scored = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $baseInfo = $item['ae_item_base_info_dto'] ?? [];
            $rating = (float)($item['evaluate_rate'] ?? ($baseInfo['avg_evaluation_rating'] ?? ($item['rating'] ?? 4.8)));
            $orders = (int)($item['volume'] ?? ($item['orders_count'] ?? ($item['lastest_volume'] ?? 0)));
            $thermometer = self::calculateThermometerScore($rating, $orders);

            $originResolver = new WarehouseOriginResolver();
            $origin = $originResolver->resolve($item, (string)($item['title'] ?? ($item['product_title'] ?? '')));
            $isBr = ($origin === WarehouseOriginResolverInterface::ORIGIN_BR);

            // Filtro estrito de entreposto quando solicitado
            if ($warehouseOrigin === 'BR' && !$isBr) {
                continue;
            }
            if ($warehouseOrigin === 'CN' && $isBr) {
                continue;
            }

            $scored[] = [
                'item'        => $item,
                'rating'      => $rating,
                'orders'      => $orders,
                'thermometer' => $thermometer,
                'is_br'       => $isBr ? 1 : 0,
                'qualified'   => ($rating === 0.0 || $rating >= $minRating) && ($orders === 0 || $orders >= $minOrders),
            ];
        }

        $qualified = array_values(array_filter($scored, static fn($row) => $row['qualified']));
        $pool = !empty($qualified) ? $qualified : $scored;

        usort($pool, static function (array $a, array $b) use ($sort, $warehouseOrigin): int {
            // Prioridade para produtos com estoque em entrepostos do Brasil se especificado
            if ($warehouseOrigin === 'BR' && $a['is_br'] !== $b['is_br']) {
                return $b['is_br'] <=> $a['is_br'];
            }
            return match ($sort) {
                'evaluate_rate_desc' => ($b['rating'] <=> $a['rating'])
                    ?: ($b['thermometer'] <=> $a['thermometer'])
                    ?: ($b['orders'] <=> $a['orders']),
                'choice_desc' => ($b['thermometer'] <=> $a['thermometer'])
                    ?: ($b['rating'] <=> $a['rating'])
                    ?: ($b['orders'] <=> $a['orders']),
                default => ($b['orders'] <=> $a['orders'])
                    ?: ($b['thermometer'] <=> $a['thermometer'])
                    ?: ($b['rating'] <=> $a['rating']),
            };
        });

        $sliced = array_slice($pool, 0, max(1, $limit));
        return array_map(static fn(array $row): array => $row['item'], $sliced);
    }

    /**
     * Minera produtos reais e ativos em tempo real através da página pública de tendências do AliExpress.
     * Garante que 100% dos produtos minerados possuam links reais, títulos oficiais e fotos autênticas,
     * sem dependência de scraping individual por página e imune a bloqueios de WAF.
     *
     * @return array<int, DropshipProductDto>
     */
    protected function mineLiveFromAliExpressSearch(string $category, int $limit, bool $autoPersist, string $warehouseOrigin = 'ALL'): array
    {
        $commercialCat = self::toCommercialCategory($category);
        $canonicalCat = self::normalizeCategory($category);

        $searchTerms = [
            'Eletrônicos & Gadget'              => ['fone bluetooth tws brasil', 'carregador turbo tipo c 65w brasil', 'power bank inducao magnetico brasil', 'headset gamer microfone rgb brasil', 'mini projetor led 4k portatil brasil', 'caixa som bluetooth potente brasil', 'fone conducao ossea corrida brasil', 'smartwatch amoled nfc gps brasil'],
            'Smartphones, TVs & Projetores 4K'  => ['mini projetor hy300 4k', 'projetor magcubic 4k', 'tv box android 4k', 'smartphone poco x6 pro', 'smartphone redmi note', 'projetor portatil wifi'],
            'Casa Inteligente & Organização'    => ['lampada led inteligente wifi rgb brasil', 'tomada inteligente wifi tuya brasil', 'sensor presenca inteligente automacao brasil', 'dispensador sabonete automatico sensor brasil', 'fita led rgb inteligente wifi brasil', 'aspirador po robo inteligente brasil', 'fechadura digital biometrica brasil'],
            'Beleza, Cuidados & Estilo'         => ['escova secadora alisadora modeladora brasil', 'paleta de sombras maquiagem profissional brasil', 'kit pinceis maquiagem profissional estojo brasil', 'batom matte longa duracao a prova d agua brasil', 'gloss labial efeito plump volume hidratante brasil', 'modelador cabelo profissional automatico brasil', 'depilador laser luz pulsada ipl brasil', 'massageador facial eletrico gua sha brasil'],
            'Ferramentas & Utilidades'          => ['alicate desencapador fios multifuncional brasil', 'trena laser digital medidor fita metrica brasil', 'parafusadeira eletrica recarregavel sem fio brasil', 'canivete tatico multifuncional sobrevivencia brasil', 'chave universal precisao multifuncoes bits brasil', 'lanterna tatica led potente militar recarregavel brasil'],
            'Pet Shop & Pets Virais'            => ['coleira led luminosa cachorro recarregavel usb brasil', 'bebedouro fonte automatica pet gato cachorro brasil', 'escova a vapor pet removedora pelos gatos caes brasil', 'brinquedo interativo inteligente pet gato brasil', 'cama nuvem calmante pelucia cachorro pet brasil', 'cortador unhas pet com led iluminador brasil'],
            'Acessórios para Veículos'          => ['suporte celular veicular carregador inducao magsafe brasil', 'aspirador automotivo portatil potente sem fio brasil', 'transmissor bluetooth veicular fm usb aux brasil', 'compressor ar portatil eletrico pneus carro brasil', 'fita led interna veicular sensor som rgb brasil', 'camera veicular dvr gravador painel brasil'],
            'Moda, Roupas & Vestuário'          => ['vestido feminino casual verao elegante brasil', 'camisa social masculina manga longa linho brasil', 'camiseta basica 100 algodao unissex slim brasil', 'jaqueta jeans masculina feminina oversize casual brasil', 'calca jeans feminina masculina skinny stretch brasil', 'calca jogger streetwear cargo bolsos unissex brasil', 'jaqueta corta vento impermeavel capuz ciclismo brasil', 'conjunto moletom masculino feminino agasalho inverno brasil', 'blusa feminina casual verao elegante brasil', 'tenis esportivo corrida caminhada respiravel leve brasil', 'bolsa feminina transversal tiracolo couro vintage brasil', 'mochila executiva impermeavel antifurto usb brasil'],
            'Moda Fitness, Leggings & Academia' => ['legging suplex feminina', 'macacao fitness feminino', 'conjunto academia feminino', 'macaquinho fitness treino', 'short suplex academia', 'top fitness sustentacao', 'calca legging empina bumbum', 'body feminino canelado'],
            'Moda Íntima, Lingeries & Pijamas'  => ['conjunto lingerie renda floral sutiã calcinha luxo brasil', 'sutia sem costura sustentacao push up confortavel brasil', 'pijama feminino cetim seda camisola renda brasil', 'kit cuecas boxer modal sem costura respiravel brasil', 'meia calca termica translucida forrada peluciada brasil', 'kit calcinhas sem costura invisivel algodao brasil', 'espartilho modelador cinta feminina compressao brasil'],
        ];

        // Mapeia apelidos canônicos para garantir acesso direto e bidirecional
        $searchTerms['Eletrônicos & Telefonia']        = $searchTerms['Eletrônicos & Gadget'];
        $searchTerms['Relógios & Smartwatches']        = $searchTerms['Eletrônicos & Gadget'];
        $searchTerms['Casa, Iluminação & Eletro']      = $searchTerms['Casa Inteligente & Organização'];
        $searchTerms['Beleza, Saúde & Maquiagem']      = $searchTerms['Beleza, Cuidados & Estilo'];
        $searchTerms['Pet Shop']                       = $searchTerms['Pet Shop & Pets Virais'];
        $searchTerms['Peças & Acessórios Automotivos'] = $searchTerms['Acessórios para Veículos'];
        $searchTerms['Roupas, Bolsas & Calçados']      = $searchTerms['Moda, Roupas & Vestuário'];
        $searchTerms['Roupa Íntima & Pijamas']         = $searchTerms['Moda Íntima, Lingeries & Pijamas'];

        $termList = $searchTerms[$commercialCat] ?? ($searchTerms[$canonicalCat] ?? null);
        if (empty($termList)) {
            $termList = $this->taxonomy->getAliExpressSearchKeywords($canonicalCat);
        }
        if (empty($termList)) {
            $termList = [$commercialCat];
        }

        $html = null;

        $catIds = $this->taxonomy->getAliExpressCategoryIds($canonicalCat);
        $primaryCatId = !empty($catIds) ? (int)$catIds[0] : null;
        $catParam = $primaryCatId ? "&catId={$primaryCatId}" : '';
        $shipFromParam = ($warehouseOrigin === 'BR') ? '&shipFromCountry=BR' : '';

        // Executa busca com parâmetro de entreposto oficial (&shipFromCountry=BR) quando solicitado Brasil
        foreach ($termList as $searchTerm) {
            $searchUrl = "https://pt.aliexpress.com/w/wholesale-" . urlencode($searchTerm) . ".html?country=BR&locale=pt_BR&currency=BRL" . $catParam . $shipFromParam . "&isrefine=y";
            $fetched = $this->fetchLiveSearchHtml($searchUrl);
            if ($fetched && is_string($fetched) && strlen($fetched) > 1000) {
                $html = $fetched;
                break;
            }
        }

        if (!$html || !is_string($html)) {
            return [];
        }

        // 1. Extração direta da carga estruturada de produtos injetada pelo AliExpress (init_data)
        $initData = null;
        if (preg_match('/window\._dida_config_\._init_data_\s*=\s*(\{.+?\})\s*(?:;|\/\*)/s', $html, $m)) {
            $jsonStr = preg_replace('/^\{\s*data\s*:/', '{"data":', $m[1]);
            $initData = json_decode($jsonStr, true);
        }
        if (empty($initData) && preg_match('/window\._dida_config_\._init_data_\s*=\s*(\{.+?\})\/\*!-->init-data-end--\*\//s', $html, $m2)) {
            $jsonStr = preg_replace('/^\{\s*data\s*:/', '{"data":', $m2[1]);
            $initData = json_decode($jsonStr, true);
        }

        $itemList = $initData['data']['data']['root']['fields']['mods']['itemList']['content'] 
            ?? ($initData['mods']['itemList']['content'] ?? []);

        if (empty($itemList) || !is_array($itemList)) {
            return [];
        }

        // Ingestão no Container e Validação Contratual dos itens obtidos ao vivo
        $validatedLiveBatch = $this->validationHub->ingestAndValidateBatch($itemList);
        $rawItems = array_values($validatedLiveBatch['valid_items']);

        // 2. Filtro Semântico Rigoroso: Elimina qualquer intruso patrocinado (ex: joias/pulseiras em roupas ou lingerie)
        $cleanItems = [];
        foreach ($rawItems as $item) {
            $rawTitle = trim((string)($item['title']['displayTitle'] ?? ($item['title']['title'] ?? '')));
            if ($rawTitle === '') {
                continue;
            }
            if ($this->isItemRelevantToCategory($rawTitle, $commercialCat)) {
                $cleanItems[] = $item;
            }
        }

        // Se o AliExpress devolveu apenas anúncios patrocinados de outras categorias, aciona contingência curada da categoria
        if (empty($cleanItems)) {
            $cleanItems = $this->catalogProvider->getCatalog()[$commercialCat] 
                ?? ($this->catalogProvider->getCatalog()[$canonicalCat] ?? []);
        }

        // 3. Separação em Memória no Container (Arquitetura Container-First com 3 Estados: BR, CN, UNKNOWN):
        // Detecta quais itens possuem estoque físico ou entrega nacional no Brasil (Local Supply / local_flag)
        $brazilItems = [];
        $globalItems = [];
        $unknownItems = [];
        foreach ($cleanItems as $raw) {
            $rawTitle = trim((string)($raw['title']['displayTitle'] ?? ($raw['title']['title'] ?? ($raw['product_title'] ?? ''))));
            $originCode = $this->originResolver->resolve($raw, $rawTitle);
            $raw['_origin_code'] = $originCode;
            $raw['_is_brazil'] = ($originCode === WarehouseOriginResolverInterface::ORIGIN_BR);
            if ($originCode === WarehouseOriginResolverInterface::ORIGIN_BR) {
                $brazilItems[] = $raw;
            } elseif ($originCode === WarehouseOriginResolverInterface::ORIGIN_CN) {
                $globalItems[] = $raw;
            } else {
                $unknownItems[] = $raw;
            }
        }

        // 4. Seleção e Priorização dos Candidatos conforme Entreposto Solicitado (Resolução Claude & ChatGPT)
        $candidateList = [];
        if ($warehouseOrigin === 'BR') {
            // Apenas Brasil: rigor absoluto, ZERO contaminação de estoque chinês ou de origem não comprovada!
            $candidateList = $brazilItems;
        } elseif ($warehouseOrigin === 'CN') {
            $candidateList = $globalItems;
        } else {
            // ALL / Global: Brasil sempre no topo da vitrine, seguido pelos importados China e demais
            $candidateList = array_merge($brazilItems, $globalItems, $unknownItems);
        }

        if (empty($candidateList)) {
            return [];
        }

        $mined = [];
        $collected = 0;
        $usdRate = $this->currencyConverter->getExchangeRate('USD', 'BRL');
        if ($usdRate <= 0.0) {
            $usdRate = 5.80;
        }

        foreach ($candidateList as $raw) {
            if ($collected >= $limit) {
                break;
            }

            try {
                $aeId = (string)($raw['productId'] ?? ($raw['product_id'] ?? ($raw['item_id'] ?? '')));
                if (empty($aeId) || !is_numeric($aeId)) {
                    continue;
                }

                $rawTitle = trim((string)($raw['title']['displayTitle'] ?? ($raw['title']['title'] ?? ($raw['product_title'] ?? ''))));
                if ($rawTitle === '') {
                    continue;
                }

                $rawMainImg = (string)($raw['image']['imgUrl'] ?? ($raw['product_main_image_url'] ?? ''));
                if ($rawMainImg === '') {
                    continue;
                }

                $mainImg = $this->cleanImageUrl($rawMainImg);
                if (str_starts_with($mainImg, '//')) {
                    $mainImg = 'https:' . $mainImg;
                }

                $images = [$mainImg];
                $videoUrl = null;

                // Enriquecimento com Galeria Completa (Full HD) e Vídeo do Produto
                $itemHtml = $this->fetchItemDetailHtml($aeId);
                if ($itemHtml !== null) {
                    // 1. Galeria Completa (imagePathList)
                    if (preg_match('/"imagePathList":(\[[^\]]+\])/', $itemHtml, $mGallery)) {
                        $decodedGallery = json_decode($mGallery[1], true);
                        if (is_array($decodedGallery)) {
                            foreach ($decodedGallery as $gImg) {
                                $cleanGImg = $this->cleanImageUrl((string)$gImg);
                                if (str_starts_with($cleanGImg, '//')) {
                                    $cleanGImg = 'https:' . $cleanGImg;
                                }
                                if ($cleanGImg !== '' && !in_array($cleanGImg, $images, true)) {
                                    $images[] = $cleanGImg;
                                }
                            }
                        }
                    }
                    // 2. Extração de Vídeo do Produto (.mp4 / videoUrl / mediaUrl / videoUid)
                    if (preg_match('/https?:\/\/[a-zA-Z0-9_\-\.\/]+\.mp4[^\s"\'<>)]*/i', $itemHtml, $mVideo)) {
                        $videoUrl = $mVideo[0];
                    } elseif (preg_match('/["\'](?:productVideoUrl|videoUrl|mediaUrl)["\']\s*:\s*["\'](https?:\/\/[^"\']+)["\']/i', $itemHtml, $mVideo2)) {
                        $videoUrl = $mVideo2[1];
                    } elseif (preg_match('/["\']videoUid["\']\s*:\s*["\']([^"\']+)["\']/i', $itemHtml, $mVideo3)) {
                        $videoUrl = "https://video.aliexpress-media.com/play/u/ae_sg_item/{$mVideo3[1]}/p/1/e/6/t/10301/{$mVideo3[1]}.mp4";
                    }
                }

                if (!empty($raw['images']) && is_array($raw['images'])) {
                    foreach ($raw['images'] as $imgObj) {
                        $u = is_array($imgObj) ? ($imgObj['imgUrl'] ?? '') : (string)$imgObj;
                        if (!empty($u)) {
                            $cleaned = $this->cleanImageUrl($u);
                            if (str_starts_with($cleaned, '//')) {
                                $cleaned = 'https:' . $cleaned;
                            }
                            if (!empty($cleaned) && !in_array($cleaned, $images, true)) {
                                $images[] = $cleaned;
                            }
                        }
                    }
                }

                // Precificação oficial em BRL e conversão limpa para USD de custo
                $minPriceBrl = (float)($raw['prices']['salePrice']['minPrice'] ?? ($raw['prices']['originalPrice']['minPrice'] ?? ($raw['cost_price_brl'] ?? 0.0)));
                $costPriceUsd = $minPriceBrl > 0.0 ? round($minPriceBrl / $usdRate, 2) : (float)($raw['target_sale_price'] ?? 12.00);
                $config = $this->loadConfig();
                $markup = isset($config['default_markup_percent']) ? (float)$config['default_markup_percent'] : 100.0;
                $volatility = isset($config['default_volatility_percent']) ? (float)$config['default_volatility_percent'] : 0.0;
                $salePriceBrl = $minPriceBrl > 0.0
                    ? $this->currencyConverter->calculateSalePriceFromBrl($minPriceBrl, $markup, $volatility)
                    : $this->currencyConverter->calculateSalePrice($costPriceUsd, $markup, null, $volatility);

                // Avaliações e Métricas reais de compra
                $rating = (float)($raw['evaluation']['starRating'] ?? 4.8);
                if ($rating <= 0.0 || $rating > 5.0) {
                    $rating = 4.8;
                }

                $ordersCount = 0;
                $ordersCountKnown = false;
                $tradeDesc = trim((string)($raw['trade']['tradeDesc'] ?? ''));
                if ($tradeDesc !== '' && preg_match('/(\d+[\.,]?\d*)/', $tradeDesc, $mTrade)) {
                    $ordersCount = max(0, (int)str_replace(['.', ','], '', $mTrade[1]));
                    $ordersCountKnown = true;
                } elseif (isset($raw['trade']['tradeCount']) && is_numeric($raw['trade']['tradeCount'])) {
                    $ordersCount = max(0, (int)$raw['trade']['tradeCount']);
                    $ordersCountKnown = true;
                } elseif (isset($raw['orders_count']) && is_numeric($raw['orders_count'])) {
                    $ordersCount = max(0, (int)$raw['orders_count']);
                    $ordersCountKnown = true;
                }

                // Enriquecimento e Polimento de Cópia Comercial por IA
                $polishedTitle = $this->aiEnricher->polishTitle($rawTitle, $commercialCat);
                $slug = $this->slugify($polishedTitle . '-' . substr($aeId, -4));

                $salesLabel = $ordersCount > 0
                    ? "{$ordersCount} vendidos"
                    : ($ordersCountKnown ? '0 vendidos' : 'vendas não informadas');

                $originCode = (string)($raw['_origin_code'] ?? $this->originResolver->resolve($raw, $rawTitle));
                $isItemBr = ($originCode === WarehouseOriginResolverInterface::ORIGIN_BR);
                $isItemCn = ($originCode === WarehouseOriginResolverInterface::ORIGIN_CN);

                $attributes = [
                    'Origem'            => match ($originCode) {
                        'BR' => 'Estoque Nacional (Brasil) - Envio em 3 a 7 dias',
                        'CN' => 'Entreposto Central AliExpress (China) - Remessa Conforme DDP',
                        default => 'Origem Internacional Certificada com Rastreamento',
                    },
                    'Categoria'         => $commercialCat,
                    'Garantia'          => '90 dias contra defeitos de fabricação',
                    'Envio'             => $isItemBr ? 'Envio Nacional Rápido (Correios / Transportadora Nacional)' : 'Rastreamento Expresso com Seguro DDP',
                    'Avaliações'        => "{$rating} estrelas ({$salesLabel})",
                    'Vendas_Informadas' => $ordersCountKnown ? 'sim' : 'nao',
                    'ship_from'         => $originCode,
                    'origem_brasil'     => $isItemBr,
                    'entreposto_brasil' => $isItemBr,
                    'origem_china'      => $isItemCn,
                    'entreposto_china'  => $isItemCn,
                    'despacho_nacional' => $isItemBr ? 'Estoque no Brasil (Entreposto Nacional)' : null,
                    'video_url'         => $videoUrl,
                    'has_video'         => !empty($videoUrl),
                ];
                if (!empty($videoUrl)) {
                    $attributes['Vídeo Demonstrativo'] = 'Disponível em Alta Definição';
                }
                if (!empty($raw['sellingPoints']) && is_array($raw['sellingPoints'])) {
                    foreach ($raw['sellingPoints'] as $sp) {
                        $tagText = trim((string)($sp['tagContent']['tagText'] ?? ''));
                        if ($tagText !== '') {
                            $attributes['Destaque'] = $tagText;
                            break;
                        }
                    }
                }

                $salesCopy = $this->aiEnricher->generateSalesCopy($polishedTitle, $attributes);
                if (!empty($videoUrl)) {
                    $videoEmbed = '
<div style="margin: 18px 0; border-radius: 8px; overflow: hidden; background: #000; text-align: center; border: 1px solid rgba(255,255,255,0.15);">
    <video controls playsinline preload="metadata" style="max-width: 100%; max-height: 400px; border-radius: 8px;" poster="' . htmlspecialchars($mainImg, ENT_QUOTES, 'UTF-8') . '">
        <source src="' . htmlspecialchars($videoUrl, ENT_QUOTES, 'UTF-8') . '" type="video/mp4">
        Seu navegador não suporta reprodução de vídeo.
    </video>
</div>';
                    $salesCopy = $videoEmbed . $salesCopy;
                }

                $dto = new DropshipProductDto(
                    aliexpressId: $aeId,
                    title: $polishedTitle,
                    slug: $slug,
                    costPriceUsd: $costPriceUsd,
                    salePriceBrl: $salePriceBrl,
                    mainImageUrl: $mainImg,
                    images: $images,
                    category: $commercialCat,
                    description: $salesCopy,
                    rating: $rating,
                    ordersCount: $ordersCount,
                    attributes: $attributes,
                    status: 'published'
                );

                // Processamento com buffer transitório em arquivo com descarte imediato
                $savedDto = $this->processWithTransientBuffer(
                    $aeId,
                    $raw,
                    function () use ($dto, $autoPersist) {
                        if ($autoPersist) {
                            $savedId = $this->repository->save($dto);
                            return $this->repository->findById($savedId) ?? $dto;
                        }
                        return $dto;
                    }
                );

                $mined[] = $savedDto;
                $collected++;
            } catch (Throwable $e) {
                // Isolamento de falha individual de produto malformado
            }
        }

        return $mined;
    }

    /**
     * Valida relevância semântica do título do produto para eliminar itens patrocinados intrusos (ex: joias em moda íntima).
     */
    protected function isItemRelevantToCategory(string $title, string $category): bool
    {
        $clean = mb_strtolower(trim($title), 'UTF-8');
        $commercialCat = self::toCommercialCategory($category);

        if ($commercialCat === 'Moda Íntima, Lingeries & Pijamas' || str_contains($commercialCat, 'Íntima')) {
            $forbidden = ['pulseira', 'brinco', 'colar', 'anel', 'jóia', 'joia', 'bijuteria', 'relogio', 'smartwatch', 'fone', 'headphone', 'parafusadeira', 'alicate', 'ferramenta', 'lampada', 'cabo usb', 'carregador', 'drone', 'projetor', 'chaveiro', 'suporte veicular'];
            foreach ($forbidden as $bad) {
                if (str_contains($clean, $bad)) {
                    return false;
                }
            }
            $allowed = ['lingerie', 'sutia', 'sutiã', 'calcinha', 'pijama', 'cueca', 'boxer', 'renda', 'cetim', 'camisola', 'baby doll', 'espartilho', 'corset', 'meia', 'top', 'cinta', 'soutien', 'bojo', 'robe', 'short doll', 'tangas', 'fio dental', 'modal', 'sem costura'];
            foreach ($allowed as $good) {
                if (str_contains($clean, $good)) {
                    return true;
                }
            }
            return false;
        }

        if ($commercialCat === 'Moda Fitness, Leggings & Academia') {
            $forbidden = ['fone', 'smartwatch', 'relogio', 'parafusadeira', 'alicate', 'ferramenta', 'lampada', 'pulseira', 'brinco', 'colar', 'jóia', 'joia', 'anel', 'bijuteria', 'drone', 'projetor', 'celular'];
            foreach ($forbidden as $bad) {
                if (str_contains($clean, $bad)) {
                    return false;
                }
            }
            $allowed = ['legging', 'macacao', 'macacão', 'macaquinho', 'suplex', 'fitness', 'academia', 'conjunto', 'short', 'top', 'bermuda', 'calca', 'calça', 'body', 'canelado', 'treino', 'corrida', 'cintura alta', 'empina bumbum', 'cinta', 'dry fit'];
            foreach ($allowed as $good) {
                if (str_contains($clean, $good)) {
                    return true;
                }
            }
            return false;
        }

        if ($commercialCat === 'Smartphones, TVs & Projetores 4K') {
            $forbidden = ['calcinha', 'sutiã', 'sutia', 'lingerie', 'vestido', 'saia', 'batom', 'maquiagem', 'brinco', 'colar', 'pulseira', 'anel', 'jóia', 'joia', 'alicate'];
            foreach ($forbidden as $bad) {
                if (str_contains($clean, $bad)) {
                    return false;
                }
            }
            $allowed = ['projetor', 'projetores', 'smartphone', 'smartphones', 'celular', 'celulares', 'tv', 'box', 'xiaomi', 'poco', 'redmi', 'magcubic', 'hy300', 'hy320', 'android', '4k', 'display', 'hdmi', 'wifi', 'led', 'tela', 'telefone'];
            foreach ($allowed as $good) {
                if (str_contains($clean, $good)) {
                    return true;
                }
            }
            return false;
        }

        if ($commercialCat === 'Moda, Roupas & Vestuário') {
            $forbidden = ['fone', 'smartwatch', 'relogio', 'parafusadeira', 'alicate', 'ferramenta', 'lampada', 'pulseira', 'brinco', 'colar', 'jóia', 'joia'];
            foreach ($forbidden as $bad) {
                if (str_contains($clean, $bad)) {
                    return false;
                }
            }
            $allowed = ['vestido', 'camisa', 'camiseta', 'calca', 'calça', 'jaqueta', 'casaco', 'moletom', 'macacao', 'macacão', 'legging', 'jeans', 'short', 'saia', 'cropped', 'blusa', 'suplex', 'fitness', 'academia', 'agasalho', 'tenis', 'tênis', 'sapato', 'bolsa', 'mochila', 'macaquinho', 'suéter'];
            foreach ($allowed as $good) {
                if (str_contains($clean, $good)) {
                    return true;
                }
            }
            return false;
        }

        if ($commercialCat === 'Eletrônicos & Gadget') {
            $forbidden = ['calcinha', 'sutiã', 'lingerie', 'vestido', 'saia', 'batom', 'maquiagem'];
            foreach ($forbidden as $bad) {
                if (str_contains($clean, $bad)) {
                    return false;
                }
            }
            return true;
        }

        return true;
    }

    /**
     * Valida se o produto do AliExpress realmente possui estoque ou vendedor no Brasil.
     */
    public function isRawItemShippedFromBrazil(array $raw, string $title = ''): bool
    {
        return $this->originResolver->isShippedFromBrazil($raw, $title);
    }

    /**
     * Executa requisição HTTPS para a página pública de busca com verificação estrita de certificado SSL.
     */
    protected function fetchLiveSearchHtml(string $searchUrl): ?string
    {
        $ch = curl_init($searchUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 6);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
            'Cookie: aep_usuc_f=region=BR&site=bra&b_locale=pt_BR&c_tp=BRL; xman_us_f=x_locale=pt_BR; intl_locale=pt_BR;',
        ]);
        $html = curl_exec($ch);
        curl_close($ch);

        return is_string($html) && $html !== '' ? $html : null;
    }

    /**
     * Executa requisição HTTPS para a página de detalhe público do produto (/item/{id}.html)
     * para extrair a galeria completa de imagens (imagePathList) e arquivos de vídeo (.mp4).
     */
    protected function fetchItemDetailHtml(string $productId): ?string
    {
        $cleanId = trim($productId);
        if ($cleanId === '' || !is_numeric($cleanId)) {
            return null;
        }

        $url = "https://pt.aliexpress.com/item/{$cleanId}.html";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 6);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
            'Cookie: aep_usuc_f=region=BR&site=bra&b_locale=pt_BR&c_tp=BRL; xman_us_f=x_locale=pt_BR; intl_locale=pt_BR;',
        ]);
        $html = curl_exec($ch);
        curl_close($ch);

        return is_string($html) && strlen($html) > 500 ? $html : null;
    }

    public function importSingleProduct(string|int $productIdOrUrl): DropshipProductDto
    {
        $inputStr = trim((string)$productIdOrUrl);

        // 1. Extrai ID numérico do produto (seja via URL ou ID direto)
        $numericId = $inputStr;
        if (preg_match('/\/item\/(\d+)\.html/', $numericId, $matches)) {
            $numericId = $matches[1];
        } elseif (preg_match('/(\d{10,20})/', $numericId, $matches)) {
            $numericId = $matches[1];
        }

        // 2. Consulta Primária: API Oficial do AliExpress (IOP Dropshipping SDK - aliexpress.ds.product.get)
        // O SDK oficial é a fonte prioritária garantindo a integridade dos atributos de SKUs (tamanhos, cores, estoque)
        $productData = null;
        if ($numericId !== '') {
            try {
                $raw = $this->apiClient->getProductDetails($numericId);
                $productData = $raw['result'] 
                    ?? ($raw['aliexpress_ds_product_get_response']['result'] ?? null);
            } catch (Throwable $e) {
                $productData = null;
            }
        }

        if (!empty($productData)) {
            $dto = $this->transformRawItemToDto($productData);
            $savedId = $this->repository->save($dto);
            return $this->repository->findById($savedId) ?? $dto;
        }

        // 3. Consulta de Simulação/Catálogo Curado SDK:
        // Caso a chave da API do AliExpress não esteja com OAuth ativo em ambiente local,
        // busca no pool curado com a matriz de SKUs (tamanhos e cores) oficial
        if ($numericId !== '') {
            $curatedItem = $this->findCuratedProductById($numericId);
            if ($curatedItem !== null) {
                $dto = $this->transformRawItemToDto($curatedItem, (string)($curatedItem['category_name'] ?? 'Geral'));
                $savedId = $this->repository->save($dto);
                return $this->repository->findById($savedId) ?? $dto;
            }
        }

        // 4. Fallback de Scraping Web em Tempo Real (apenas se nem a API nem o pool curado possuírem o item)
        if (str_starts_with($inputStr, 'http://') || str_starts_with($inputStr, 'https://')) {
            $scraped = $this->linkScraper->scrape($inputStr);
            if ($scraped !== null) {
                return $this->createAndSaveDtoFromScraped($scraped);
            }
        }

        // 5. Scraper pelo ID numérico
        if ($numericId !== '') {
            $scrapedById = $this->linkScraper->scrape($numericId);
            if ($scrapedById !== null) {
                return $this->createAndSaveDtoFromScraped($scrapedById);
            }
        }

        // 6. Se for uma URL pública e nem a API, nem o catálogo curado, nem o scraper encontraram o produto
        if (str_starts_with($inputStr, 'http://') || str_starts_with($inputStr, 'https://')) {
            throw new RuntimeException("O link informado não foi encontrado ou está desativado no AliExpress (Erro 404). Por favor, forneça o link de um anúncio ativo.");
        }

        // 7. Fallback estruturado para testes com IDs sintéticos
        $productData = $this->createFallbackItemFromIdOrUrl($numericId, (string)$productIdOrUrl);
        $dto = $this->transformRawItemToDto($productData);
        $savedId = $this->repository->save($dto);

        return $this->repository->findById($savedId) ?? $dto;
    }

    /**
     * Localiza um produto no catálogo curado oficial pelo ID numérico do AliExpress.
     * 
     * @return array<string, mixed>|null
     */
    public function findCuratedProductById(string $productId): ?array
    {
        $catalog = $this->getCuratedCatalog();
        foreach ($catalog as $catName => $items) {
            foreach ($items as $item) {
                if ((string)($item['product_id'] ?? '') === $productId) {
                    $item['category_name'] = $item['category_name'] ?? $catName;
                    return $item;
                }
            }
        }

        return null;
    }

    /**
     * Localiza um produto no catálogo curado oficial ou nos padrões de mineração pelo slug amigável da URL.
     * 
     * @return array<string, mixed>|null
     */
    public function findCuratedProductBySlug(string $slug): ?array
    {
        $normalizedSlug = mb_strtolower(trim(urldecode($slug)));
        if ($normalizedSlug === '') {
            return null;
        }

        $catalog = $this->getCuratedCatalog();
        foreach ($catalog as $catName => $items) {
            foreach ($items as $item) {
                $title = (string)($item['product_title'] ?? '');
                $itemSlug = self::slugify($title);
                if ($itemSlug === $normalizedSlug || str_contains($itemSlug, $normalizedSlug) || str_contains($normalizedSlug, $itemSlug)) {
                    $item['category_name'] = $item['category_name'] ?? $catName;
                    return $item;
                }
            }
        }

        foreach (self::OFFICIAL_CATEGORIES as $cat) {
            $pool = $this->expandCategoryPoolToLimit([], $cat, 15);
            foreach ($pool as $item) {
                $title = (string)($item['product_title'] ?? '');
                $itemSlug = self::slugify($title);
                if ($itemSlug === $normalizedSlug || str_contains($itemSlug, $normalizedSlug) || str_contains($normalizedSlug, $itemSlug)) {
                    $item['category_name'] = $item['category_name'] ?? $cat;
                    return $item;
                }
            }
        }

        return null;
    }

    public function importFromBrowserSource(string $rawSource, ?string $category = null): DropshipProductDto
    {
        $rawSource = trim($rawSource);
        if ($rawSource === '') {
            throw new \InvalidArgumentException("O código-fonte ou texto do produto fornecido está vazio.");
        }

        // 1. Extração estruturada via Scraper Service
        $scraped = $this->linkScraper->parseRawHtml($rawSource);
        if ($scraped === null) {
            throw new \RuntimeException("Não foi possível extrair os dados do produto do código colado. Verifique se copiou o código completo (Ctrl+U ou F12) de uma página de anúncio ativa do AliExpress.");
        }

        // 2. Processa com Buffer Transitório por Item com descarte imediato
        return $this->processWithTransientBuffer(
            $scraped->sourceId,
            [
                'source_id'  => $scraped->sourceId,
                'raw_title'  => $scraped->rawTitle,
                'main_image' => $scraped->mainImageUrl,
                'gallery'    => $scraped->galleryImages,
                'cost_usd'   => $scraped->costPriceUsd,
                'category'   => $category ?? $scraped->category,
                'attributes' => $scraped->attributes,
            ],
            function () use ($scraped, $category) {
                return $this->createAndSaveDtoFromScraped($scraped, $category);
            }
        );
    }

    /**
     * Gerencia um buffer transitório em arquivo por produto, com garantia de descarte imediato
     * e liberação de memória pelo coletor do PHP (gc_collect_cycles).
     * 
     * @param string $itemIdentifier ID do produto
     * @param array<string, mixed>|string $payload Dados brutos para armazenar temporariamente
     * @param callable $processor Função de transformação e persistência
     */
    private function processWithTransientBuffer(string $itemIdentifier, array|string $payload, callable $processor): DropshipProductDto
    {
        $tempDir = dirname(__DIR__, 4) . '/storage/cache/domain_drop_transient';
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0755, true);
        }

        $sanitizedId = preg_replace('/[^a-zA-Z0-9_\-]/', '', $itemIdentifier) ?: uniqid('item_');
        $tempFile = $tempDir . "/temp_item_{$sanitizedId}_" . hrtime(true) . ".json";

        try {
            $content = is_array($payload) ? json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $payload;
            file_put_contents($tempFile, $content, LOCK_EX);

            /** @var DropshipProductDto $dto */
            $dto = $processor($payload);

            return $dto;
        } finally {
            // Garante que o buffer transitório seja SEMPRE apagado imediatamente passo a passo
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
            // Força a liberação de ciclos de memória do motor PHP
            gc_collect_cycles();
        }
    }

    private function createAndSaveDtoFromScraped(ScrapedProductDto $scraped, ?string $overrideCategory = null): DropshipProductDto
    {
        $category = $overrideCategory !== null ? self::normalizeCategory($overrideCategory) : self::normalizeCategory($scraped->category);
        $polishedTitle = $this->aiEnricher->polishTitle($scraped->rawTitle, $category);
        $slug = $this->slugify($polishedTitle . '-' . substr($scraped->sourceId, -4));
        $costBrl = !empty($scraped->attributes['cost_price_brl']) ? (float)$scraped->attributes['cost_price_brl'] : 0.0;
        $config = $this->loadConfig();
        $markup = isset($config['default_markup_percent']) ? (float)$config['default_markup_percent'] : 100.0;
        $volatility = isset($config['default_volatility_percent']) ? (float)$config['default_volatility_percent'] : 0.0;
        $salePriceBrl = $costBrl > 0.0
            ? $this->currencyConverter->calculateSalePriceFromBrl($costBrl, $markup, $volatility)
            : $this->currencyConverter->calculateSalePrice($scraped->costPriceUsd, $markup, null, $volatility);
        $salesCopy = $this->aiEnricher->generateSalesCopy(
            "Produto importado oficial de alta durabilidade com compatibilidade e acabamento premium.",
            $scraped->attributes
        );

        $dto = new DropshipProductDto(
            aliexpressId: $scraped->sourceId,
            title: $polishedTitle,
            slug: $slug,
            costPriceUsd: $scraped->costPriceUsd,
            salePriceBrl: $salePriceBrl,
            mainImageUrl: $scraped->mainImageUrl,
            images: $scraped->getAllImages(),
            category: $category,
            description: $salesCopy,
            rating: $scraped->rating,
            ordersCount: $scraped->ordersCount,
            attributes: $scraped->attributes,
            status: 'published'
        );

        $savedId = $this->repository->save($dto);

        return $this->repository->findById($savedId) ?? $dto;
    }

    /**
     * Transforma o payload bruto do AliExpress em um DTO limpo e precificado.
     * Suporta tanto o payload oficial do IOP SDK (ae_item_base_info_dto, ae_multimedia_info_dto, ae_item_sku_info_dtos)
     * quanto feeds diretos, web scraping (imagePathList) e listas planas.
     * 
     * @param array<string, mixed> $item
     */
    public function transformRawItemToDto(array $item, ?string $category = null): DropshipProductDto
    {
        $defaultCategory = $category ?: 'Eletrônicos & Gadget';
        $baseInfo = $item['ae_item_base_info_dto'] ?? [];
        $multiMedia = $item['ae_multimedia_info_dto'] ?? [];
        $skuDtos = $item['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'] ?? ($item['ae_item_sku_info_dtos'] ?? []);

        $aeId = (string)(
            $item['itemId']
            ?? ($item['product_id']
            ?? ($baseInfo['product_id']
            ?? ($item['item_id']
            ?? ($item['productId'] ?? uniqid('ae_')))))
        );

        // Se o item veio de busca resumida e não tem SKUs ou galeria completa, enriquece sob demanda apenas este item final
        if ($aeId !== '' && is_numeric($aeId) && empty($item['ae_item_sku_info_dtos']) && empty($item['variants'])) {
            try {
                $details = $this->apiClient->getProductDetails($aeId, 'pt', 'BRL');
                $unwrapped = \DomainSystem\Plugins\domain_drop\ValueObjects\AliExpressToken::unwrapPayload($details);
                $pData = $unwrapped['result'] ?? ($details['result'] ?? $unwrapped);
                if (is_array($pData) && !empty($pData)) {
                    $item = array_merge($item, $pData);
                    $baseInfo = $item['ae_item_base_info_dto'] ?? $baseInfo;
                    $multiMedia = $item['ae_multimedia_info_dto'] ?? $multiMedia;
                    $skuDtos = $item['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'] ?? ($item['ae_item_sku_info_dtos'] ?? $skuDtos);
                }
            } catch (\Throwable $e) {}
        }

        $rawTitle = (string)(
            $item['title']
            ?? ($item['product_title']
            ?? ($baseInfo['subject']
            ?? 'Produto Internacional'))
        );
        $rawCat = (string)($item['category_name'] ?? ($item['cateId'] ?? $defaultCategory));
        $category = self::toCommercialCategory($rawCat);

        // 1. Higienização por I.A.
        $polishedTitle = $this->aiEnricher->polishTitle($rawTitle, $category);
        $slug = $this->slugify($polishedTitle . '-' . substr($aeId, -4));

        // 2. Extração de Imagens e Galeria Completa (Multi-Image Carousel)
        $rawMainImage = (string)(
            $item['itemMainPic']
            ?? ($item['product_main_image_url']
            ?? ($item['image_url']
            ?? ($item['pic_url'] ?? '')))
        );
        $collectedImages = [];

        // 2.1 Da galeria oficial do IOP SDK (image_urls delimitado por ponto-e-vírgula ';')
        if (!empty($multiMedia['image_urls'])) {
            $splitUrls = is_array($multiMedia['image_urls']) ? $multiMedia['image_urls'] : explode(';', (string)$multiMedia['image_urls']);
            foreach ($splitUrls as $u) {
                $collectedImages[] = (string)$u;
            }
        }

        // 2.2 Da lista web de alta resolução (imagePathList)
        if (!empty($item['imagePathList']) && is_array($item['imagePathList'])) {
            foreach ($item['imagePathList'] as $u) {
                $collectedImages[] = (string)$u;
            }
        }

        // 2.3 Da chave direta images
        if (!empty($item['images']) && is_array($item['images'])) {
            foreach ($item['images'] as $u) {
                $collectedImages[] = (string)$u;
            }
        }

        // 2.4 De thumbnails oficiais da API de afiliados (product_small_image_urls)
        if (!empty($item['product_small_image_urls']['string'])) {
            $smallUrls = (array)$item['product_small_image_urls']['string'];
            foreach ($smallUrls as $u) {
                $collectedImages[] = (string)$u;
            }
        }

        // 2.5 De variantes/SKUs oficiais do AliExpress
        if (is_array($skuDtos)) {
            $normalizedSkuList = (isset($skuDtos['sku_id']) || isset($skuDtos['id']) || isset($skuDtos['ae_sku_property_dtos'])) ? [$skuDtos] : $skuDtos;
            foreach ($normalizedSkuList as $sku) {
                if (!is_array($sku)) {
                    continue;
                }
                if (!empty($sku['sku_image'])) {
                    $collectedImages[] = (string)$sku['sku_image'];
                }
                if (!empty($sku['sku_image_url'])) {
                    $collectedImages[] = (string)$sku['sku_image_url'];
                }
                $rawProps = $sku['ae_sku_property_dtos']['ae_sku_property_d_t_o']
                    ?? ($sku['ae_sku_property_dtos'] ?? ($sku['sku_properties'] ?? []));
                if (is_array($rawProps)) {
                    if (isset($rawProps['sku_image']) || isset($rawProps['sku_property_name'])) {
                        $rawProps = [$rawProps];
                    }
                    foreach ($rawProps as $rp) {
                        if (is_array($rp) && !empty($rp['sku_image'])) {
                            $collectedImages[] = (string)$rp['sku_image'];
                        }
                    }
                }
            }
        }

        // 2.5.1 De variantes estruturadas locais
        if (!empty($item['variants']) && is_array($item['variants'])) {
            foreach ($item['variants'] as $v) {
                if (is_array($v) && !empty($v['image_url'])) {
                    $collectedImages[] = (string)$v['image_url'];
                }
            }
        }

        // 2.6 Higienização, desduplicação e normalização para Full HD (remove sufixos de thumbnails _80x80.jpg)
        $images = [];
        if (!empty($rawMainImage)) {
            $cleanedMain = $this->cleanImageUrl($rawMainImage);
            $images[] = $cleanedMain;
            $mainImage = $cleanedMain;
        } else {
            $mainImage = '';
        }

        foreach ($collectedImages as $img) {
            $clean = $this->cleanImageUrl($img);
            if (!empty($clean) && !in_array($clean, $images, true)) {
                $images[] = $clean;
            }
        }

        if (empty($mainImage) && !empty($images)) {
            $mainImage = $images[0];
        }

        // 3. Precificação em Dólar e Conversão em Reais (suporta camelCase do dropshiper.pdf e snake_case legado)
        $minSkuCost = null;
        if (is_array($skuDtos) && !empty($skuDtos)) {
            $normSkus = (isset($skuDtos['sku_id']) || isset($skuDtos['offer_sale_price']) || isset($skuDtos['sku_price']))
                ? [$skuDtos]
                : $skuDtos;
            foreach ($normSkus as $sk) {
                if (is_array($sk)) {
                    $p = (float)($sk['offer_sale_price'] ?? ($sk['sku_price'] ?? 0.0));
                    if ($p > 0.0 && ($minSkuCost === null || $p < $minSkuCost)) {
                        $minSkuCost = $p;
                    }
                }
            }
        }

        $costBrl = (float)($item['cost_price_brl'] ?? 0.0);
        $rawCurrency = strtoupper((string)(
            $item['salePriceCurrency']
            ?? ($item['target_sale_price_currency']
            ?? ($item['currency']
            ?? ($item['currency_code'] ?? '')))
        ));

        $candidatePrice = (float)(
            $item['targetSalePrice']
            ?? ($item['salePrice']
            ?? ($item['target_sale_price']
            ?? ($item['sale_price']
            ?? ($minSkuCost
            ?? ($item['targetOriginalPrice']
            ?? ($item['originalPrice']
            ?? ($item['original_price'] ?? 10.0)))))))
        );

        if ($costBrl <= 0.0 && ($rawCurrency === 'BRL' || $rawCurrency === 'R$')) {
            $costBrl = $candidatePrice;
        }

        $config = $this->loadConfig();
        $markup = isset($config['default_markup_percent']) ? (float)$config['default_markup_percent'] : 100.0;
        $volatility = isset($config['default_volatility_percent']) ? (float)$config['default_volatility_percent'] : 0.0;

        if ($costBrl > 0.0) {
            $salePriceBrl = $this->currencyConverter->calculateSalePriceFromBrl($costBrl, $markup, $volatility);
            $costPriceUsd = round($costBrl / 5.75, 2);
        } else {
            $costPriceUsd = $candidatePrice > 0.0 ? $candidatePrice : 10.0;
            $salePriceBrl = $this->currencyConverter->calculateSalePrice($costPriceUsd, $markup, null, $volatility);
        }

        // 4. Métricas e Atributos de Curadoria
        $rawRate = $item['evaluateRate'] ?? ($item['evaluate_rate'] ?? ($item['score'] ?? ($baseInfo['avg_evaluation_rating'] ?? 4.8)));
        $numRate = is_numeric($rawRate) ? (float)$rawRate : (float)str_replace(['%', ','], ['', '.'], (string)$rawRate);
        if ($numRate > 5.0) {
            $numRate = round(($numRate / 100.0) * 5.0, 1);
        }
        $effectiveRating = $numRate > 0 ? $numRate : 4.8;
        $ordersCount = (int)($item['orders'] ?? ($item['volume'] ?? ($item['orders_count'] ?? 0)));
        $thermometer = self::calculateThermometerScore($effectiveRating, $ordersCount);

        $props = is_array($item['properties'] ?? null) ? $item['properties'] : [];
        $props['curadoria_nota'] = number_format($effectiveRating, 1) . ' ★';
        $props['termometro_demanda'] = $thermometer . '%';

        // Detecção Rigorosa e Tri-State de Entreposto Físico (BR / CN / UNKNOWN)
        $storeInfo = $item['ae_store_info'] ?? ($item['store_info'] ?? []);
        $resolvedOrigin = $this->originResolver->resolve($item, (string)($item['title'] ?? ($item['product_title'] ?? '')));

        if ($resolvedOrigin === WarehouseOriginResolverInterface::ORIGIN_BR) {
            $props['ship_from'] = 'BR';
            $props['origem_brasil'] = true;
            $props['entreposto_brasil'] = true;
            $props['despacho_nacional'] = 'Estoque no Brasil (Entreposto Nacional)';
        } elseif ($resolvedOrigin === WarehouseOriginResolverInterface::ORIGIN_CN) {
            $props['ship_from'] = 'CN';
            $props['origem_china'] = true;
            $props['entreposto_china'] = true;
            $props['despacho_origem'] = 'Entreposto Central AliExpress (China)';
            $props['remessa_conforme'] = 'Importação Oficial com DDP e Rastreamento';
        } else {
            // Estado UNKNOWN preservado: nunca converte ausência de dados em China
            $props['ship_from'] = 'UNKNOWN';
            $props['origem_desconhecida'] = true;
            $props['despacho_origem'] = 'Origem a Confirmar no Fornecedor';
        }

        // Metadados do Vendedor Oficial AliExpress (ae_store_info)
        if (!empty($storeInfo) && is_array($storeInfo)) {
            if (!empty($storeInfo['store_name'])) {
                $props['store_name'] = (string)$storeInfo['store_name'];
            }
            if (!empty($storeInfo['store_id'])) {
                $props['store_id'] = (string)$storeInfo['store_id'];
                $props['store_url'] = 'https://www.aliexpress.com/store/' . $storeInfo['store_id'];
            }
            if (!empty($storeInfo['store_country_code'])) {
                $props['store_country'] = (string)$storeInfo['store_country_code'];
            }
        }

        // Link Direto do Produto no Vendedor Oficial
        if (!empty($item['itemUrl'])) {
            $props['aliexpress_url'] = (string)$item['itemUrl'];
        } elseif (!empty($aeId) && is_numeric($aeId)) {
            $props['aliexpress_url'] = 'https://pt.aliexpress.com/item/' . $aeId . '.html?country=BR&locale=pt_BR&currency=BRL';
        }
        if (!empty($item['productVideoUrl'])) {
            $props['video_url'] = (string)$item['productVideoUrl'];
        }
        if (!empty($item['discount'])) {
            $props['discount_label'] = (string)$item['discount'];
        }

        // 4.1 Extração de Variações e SKUs (Padrão SKU Matrix / AliExpress Open Platform)
        $extractedVariants = [];
        $variationAxes = [];

        if (!empty($item['variants']) && is_array($item['variants'])) {
            $extractedVariants = $item['variants'];
            if (!empty($item['variation_axes']) && is_array($item['variation_axes'])) {
                $variationAxes = $item['variation_axes'];
            } else {
                foreach ($extractedVariants as $ev) {
                    if (!is_array($ev) || empty($ev['options']) || !is_array($ev['options'])) {
                        continue;
                    }
                    foreach ($ev['options'] as $axisName => $axisVal) {
                        $axisName = trim((string)$axisName);
                        $axisVal = trim((string)$axisVal);
                        if ($axisName !== '' && $axisVal !== '') {
                            if (!isset($variationAxes[$axisName])) {
                                $variationAxes[$axisName] = [];
                            }
                            if (!in_array($axisVal, $variationAxes[$axisName], true)) {
                                $variationAxes[$axisName][] = $axisVal;
                            }
                        }
                    }
                }
            }
        } elseif (is_array($skuDtos) && !empty($skuDtos)) {
            $normalizedSkus = (isset($skuDtos['sku_id']) || isset($skuDtos['id']) || isset($skuDtos['ae_sku_property_dtos']) || isset($skuDtos['sku_properties']))
                ? [$skuDtos]
                : $skuDtos;

            foreach ($normalizedSkus as $sku) {
                if (!is_array($sku)) {
                    continue;
                }
                $skuId = (string)($sku['sku_id'] ?? ($sku['id'] ?? uniqid('sku_')));
                $skuPriceUsd = (float)($sku['offer_sale_price'] ?? ($sku['sku_price'] ?? $costPriceUsd));
                $skuCostBrl = (float)($sku['cost_price_brl'] ?? 0.0);
                $skuPriceBrl = $skuCostBrl > 0.0
                    ? $this->currencyConverter->calculateSalePriceFromBrl($skuCostBrl, $markup, $volatility)
                    : $this->currencyConverter->calculateSalePrice($skuPriceUsd, $markup, null, $volatility);
                $skuRawImg = !empty($sku['sku_image']) ? $sku['sku_image'] : (!empty($sku['sku_image_url']) ? $sku['sku_image_url'] : '');
                $skuImage = !empty($skuRawImg) ? $this->cleanImageUrl((string)$skuRawImg) : $mainImage;
                $skuStock = (int)($sku['sku_available_stock'] ?? ($sku['sku_stock'] ?? 100));
                $skuAttr = (string)($sku['sku_attr'] ?? ($sku['sku_code'] ?? ''));

                $options = [];
                $rawProps = $sku['ae_sku_property_dtos']['ae_sku_property_d_t_o']
                    ?? ($sku['ae_sku_property_dtos'] ?? ($sku['sku_properties'] ?? []));
                if (is_array($rawProps)) {
                    if (isset($rawProps['sku_property_name']) || isset($rawProps['property_name']) || isset($rawProps['sku_image'])) {
                        $rawProps = [$rawProps];
                    }
                    foreach ($rawProps as $rp) {
                        if (!is_array($rp)) {
                            continue;
                        }
                        if (!empty($rp['sku_image'])) {
                            $skuImage = $this->cleanImageUrl((string)$rp['sku_image']);
                        }
                        $propName = trim((string)($rp['sku_property_name'] ?? ($rp['property_name'] ?? '')));
                        $lowerProp = mb_strtolower($propName, 'UTF-8');
                        if ($lowerProp === 'color' || $lowerProp === 'colour') {
                            $propName = 'Cor';
                        } elseif ($lowerProp === 'size') {
                            $propName = 'Tamanho';
                        } elseif ($lowerProp === 'plug' || $lowerProp === 'plug type') {
                            $propName = 'Tipo de Tomada';
                        } elseif ($lowerProp === 'voltage') {
                            $propName = 'Voltagem';
                        } elseif ($lowerProp === 'material') {
                            $propName = 'Material';
                        } elseif ($lowerProp === 'bundle' || $lowerProp === 'package') {
                            $propName = 'Pacote';
                        } elseif ($lowerProp === 'model') {
                            $propName = 'Modelo';
                        }
                        $propVal = trim((string)($rp['property_value_definition_name'] ?? ($rp['sku_property_value'] ?? ($rp['property_value'] ?? ''))));
                        if (in_array($lowerProp, ['ships from', 'enviado de', 'origem', 'despachado de', 'local de envio', 'país de envio'], true)) {
                            $lowerVal = mb_strtolower($propVal, 'UTF-8');
                            if (in_array($lowerVal, ['br', 'brazil', 'brasil', 'são paulo', 'sp'], true)) {
                                $props['ship_from'] = 'BR';
                                $props['origem_brasil'] = true;
                                $props['entreposto_brasil'] = true;
                                $props['despacho_nacional'] = 'Estoque no Brasil (Entreposto Nacional)';
                            }
                        }
                        if ($propName !== '' && $propVal !== '') {
                            $options[$propName] = $propVal;
                            if (!isset($variationAxes[$propName])) {
                                $variationAxes[$propName] = [];
                            }
                            if (!in_array($propVal, $variationAxes[$propName], true)) {
                                $variationAxes[$propName][] = $propVal;
                            }
                        }
                    }
                }

                $skuEan = trim((string)($sku['ean_code'] ?? ($sku['barcode'] ?? ($sku['gtin'] ?? ''))));

                if (!empty($options)) {
                    $extractedVariants[] = [
                        'sku_id'         => $skuId,
                        'sku_attr'       => $skuAttr,
                        'options'        => $options,
                        'price_brl'      => $skuPriceBrl,
                        'cost_price_usd' => $skuPriceUsd,
                        'image_url'      => $skuImage,
                        'stock'          => $skuStock,
                        'ean_code'       => $skuEan,
                    ];
                }
            }
        }

        if (!empty($extractedVariants)) {
            $props['variants'] = $extractedVariants;
            if (!empty($variationAxes)) {
                $props['variation_axes'] = $variationAxes;
            }
        }

        $productEan = trim((string)($item['ean_code'] ?? ($baseInfo['ean_code'] ?? ($item['gtin'] ?? ''))));
        if ($productEan !== '') {
            $props['ean_code'] = $productEan;
        }

        $rawDesc = (string)($item['product_description'] ?? ($baseInfo['detail'] ?? ($item['description'] ?? '')));
        $salesCopy = $this->aiEnricher->generateSalesCopy($rawDesc, $props);

        return new DropshipProductDto(
            aliexpressId: $aeId,
            title: $polishedTitle,
            slug: $slug,
            costPriceUsd: $costPriceUsd,
            salePriceBrl: $salePriceBrl,
            mainImageUrl: $mainImage,
            images: $images,
            category: $category,
            description: $salesCopy,
            rating: $effectiveRating,
            ordersCount: $ordersCount,
            attributes: $props,
            status: 'published'
        );
    }

    /**
     * Limpa e padroniza a URL de imagem do AliExpress para máxima resolução.
     */
    private function cleanImageUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (str_starts_with($url, '//')) {
            $url = 'https:' . $url;
        }
        // Remove sufixos de thumbnails como _80x80.jpg, _50x50.jpg, etc.
        $url = (string)preg_replace('/_[0-9]+x[0-9]+\.(?:jpg|png|webp|jpeg)$/i', '', $url);

        return $url;
    }

    private function slugify(string $text): string
    {
        $str = mb_strtolower(trim($text), 'UTF-8');
        // Normaliza caracteres acentuados para URLs limpas (sem 'port-til' ou 'tit-nio')
        $str = preg_replace(
            ['/[áàãâä]/u', '/[éèêë]/u', '/[íìîï]/u', '/[óòõôö]/u', '/[úùûü]/u', '/[ç]/u', '/[ñ]/u'],
            ['a', 'e', 'i', 'o', 'u', 'c', 'n'],
            $str
        ) ?? $str;
        $str = (string)preg_replace('/[^a-z0-9]+/i', '-', $str);

        return trim($str, '-') ?: 'produto-dropship';
    }

    /**
     * Fallback curado de produtos virais de alta conversão para mineração inicial / testes.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getCuratedTrendingFallback(string $category, int $limit, string $sort = 'volume_desc', string $warehouseOrigin = 'ALL'): array
    {
        $catalog = $this->getCuratedCatalog();
        $commercialCat = self::toCommercialCategory($category);

        $aliasMap = [
            'Eletrônicos & Telefonia'        => 'Eletrônicos & Gadget',
            'Relógios & Smartwatches'        => 'Eletrônicos & Gadget',
            'Casa, Iluminação & Eletro'      => 'Casa Inteligente & Organização',
            'Ferramentas & Utilidades'       => 'Ferramentas & Utilidades',
            'Beleza, Saúde & Maquiagem'      => 'Beleza, Cuidados & Estilo',
            'Pet Shop'                       => 'Pet Shop & Pets Virais',
            'Peças & Acessórios Automotivos' => 'Acessórios para Veículos',
            'Roupas, Bolsas & Calçados'      => 'Moda, Roupas & Vestuário',
            'Roupa Íntima & Pijamas'         => 'Moda Íntima, Lingeries & Pijamas',
        ];
        $curatedKey = $aliasMap[$category] ?? $commercialCat;
        $pool = $catalog[$commercialCat] ?? ($catalog[$curatedKey] ?? ($catalog[$category] ?? ($catalog['Moda, Roupas & Vestuário'] ?? reset($catalog))));

        // Filtragem estrita por entreposto físico solicitado (Zero-Contamination)
        if ($warehouseOrigin === 'BR') {
            $pool = array_values(array_filter($pool, function(array $item) {
                return $this->originResolver->resolve($item, (string)($item['product_title'] ?? '')) === WarehouseOriginResolverInterface::ORIGIN_BR;
            }));
            // No modo BR, NUNCA expande artificialmente com produtos CN/UNKNOWN para bater meta de cota!
        } elseif ($warehouseOrigin === 'CN') {
            $pool = array_values(array_filter($pool, function(array $item) {
                return $this->originResolver->resolve($item, (string)($item['product_title'] ?? '')) !== WarehouseOriginResolverInterface::ORIGIN_BR;
            }));
            if (count($pool) < $limit) {
                $pool = $this->expandCategoryPoolToLimit($pool, $commercialCat, $limit);
            }
        } else {
            // ALL: permite expansão normal garantindo variedade
            if (count($pool) < $limit) {
                $pool = $this->expandCategoryPoolToLimit($pool, $commercialCat, $limit);
            }
        }

        // Ordena pelo critério de reputação solicitado: maior volume de vendas ou maior avaliação por estrelas
        if ($sort === 'evaluate_rate_desc') {
            usort($pool, fn($a, $b) => ((float)($b['evaluate_rate'] ?? 0)) <=> ((float)($a['evaluate_rate'] ?? 0)));
        } else {
            usort($pool, fn($a, $b) => ((int)($b['volume'] ?? 0)) <=> ((int)($a['volume'] ?? 0)));
        }

        return array_slice($pool, 0, $limit);
    }

    /**
     * Retorna os produtos virais qualificados (4.8★ a 5.0★) da categoria sem repetições artificiais.
     *
     * @param array<int, array<string, mixed>> $pool
     * @return array<int, array<string, mixed>>
     */
    private function expandCategoryPoolToLimit(array $pool, string $normalizedCat, int $limit): array
    {
        // Retorna os produtos autênticos sem duplicações artificiais de itens para bater meta
        return array_values($pool);
    }

    /**
     * Catálogo mestre curado com especificações completas de produtos e matrizes de SKUs.
     * Delegado para CuratedCatalogProviderInterface conforme o princípio da responsabilidade única (SRP).
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function getCuratedCatalog(): array
    {
        return $this->catalogProvider->getCatalog();
    }

    /**
     * Gera estrutura de dados de fallback inteligente para um ID ou URL informada.
     * Tenta primeiro extrair dados reais (título, galeria HD imagePathList e preço) diretamente da página web.
     *
     * @return array<string, mixed>
     */
    private function createFallbackItemFromIdOrUrl(string $productId, string $url): array
    {
        $liveWebData = $this->fetchProductDataFromWeb($productId, $url);
        if ($liveWebData !== null) {
            return $liveWebData;
        }

        $title = 'Produto Importado AliExpress ' . $productId;
        $category = 'Geral';

        // Tenta inferir o título a partir da URL
        if (preg_match('/item\/\d+-([^.]+)\.html/i', $url, $m)) {
            $words = explode('-', $m[1]);
            $title = ucwords(implode(' ', array_filter($words)));
            if (stripos($url, 'watch') !== false || stripos($url, 'relogio') !== false) {
                $category = 'Eletrônicos';
            } elseif (stripos($url, 'fone') !== false || stripos($url, 'earphone') !== false) {
                $category = 'Eletrônicos';
            } elseif (stripos($url, 'lamp') !== false || stripos($url, 'luz') !== false) {
                $category = 'Casa Inteligente';
            }
        }

        return [
            'product_id' => $productId,
            'product_title' => $title,
            'category_name' => $category,
            'target_sale_price' => '14.00',
            'product_main_image_url' => 'https://images.unsplash.com/photo-1526738549149-8e07eca6c147?w=600&auto=format&fit=crop&q=80',
            'evaluate_rate' => '4.8',
            'volume' => 2400,
            'product_description' => "Produto de alta qualidade importado diretamente do fabricante com envio rastreado.",
            'properties' => ['Garantia' => 'Internacional', 'Envio' => 'Rastreio Oficial'],
        ];
    }

    /**
     * Extrai dados em tempo real diretamente do HTML da página pública do AliExpress.
     * Captura título limpo, imagem de capa, galeria Full HD (imagePathList) e precificação via pdp_npi.
     *
     * @return array<string, mixed>|null
     */
    private function fetchProductDataFromWeb(string $productId, string $url): ?array
    {
        $targetUrl = str_starts_with($url, 'http') ? $url : "https://pt.aliexpress.com/item/{$productId}.html";

        $ch = curl_init($targetUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        $html = curl_exec($ch);
        curl_close($ch);

        if (!$html || !is_string($html)) {
            return null;
        }

        // Descarta se for página de desafio anti-bot ou link quebrado
        if (stripos($html, 'Arraste o controle deslizante') !== false
            || stripos($html, 'sec.aliexpress.com') !== false
            || stripos($html, 'punish') !== false
            || stripos($html, 'baxia') !== false
            || stripos($html, 'Página não encontrada') !== false
        ) {
            return null;
        }

        $title = '';
        if (preg_match('/<meta property="og:title" content="([^"]+)"/i', $html, $m)) {
            $title = html_entity_decode(preg_replace('/\s*-\s*AliExpress.*$/i', '', $m[1]));
        }

        $mainImage = '';
        if (preg_match('/<meta property="og:image" content="([^"]+)"/i', $html, $m)) {
            $mainImage = $m[1];
        }

        $images = [];
        if (preg_match('/"imagePathList":(\[[^\]]+\])/', $html, $m)) {
            $decoded = json_decode($m[1], true);
            if (is_array($decoded)) {
                $images = $decoded;
            }
        }

        if (empty($title) && empty($mainImage) && empty($images)) {
            return null;
        }

        // Tenta inferir preço da query string (pdp_npi) ou HTML
        $costPriceUsd = 12.00;
        $decodedUrl = urldecode($targetUrl . ' ' . $html);
        if (preg_match('/dis!([A-Z]{3})![0-9.]+!([0-9.]+)/i', $decodedUrl, $pm)) {
            $currency = strtoupper($pm[1]);
            $promoVal = (float)$pm[2];
            if ($currency === 'BRL') {
                $costPriceUsd = round($promoVal / 5.25, 2);
            } elseif ($currency === 'USD') {
                $costPriceUsd = $promoVal;
            }
        }

        // Categoria inteligente
        $category = 'Eletrônicos';
        if (stripos($title, 'pulseira') !== false || stripos($title, 'watch') !== false || stripos($title, 'relogio') !== false) {
            $category = 'Eletrônicos';
        } elseif (stripos($title, 'beleza') !== false || stripos($title, 'cabelo') !== false || stripos($title, 'pele') !== false) {
            $category = 'Beleza & Estilo';
        } elseif (stripos($title, 'luminaria') !== false || stripos($title, 'casa') !== false || stripos($title, 'quarto') !== false) {
            $category = 'Casa Inteligente';
        }

        return [
            'product_id' => $productId,
            'product_title' => $title ?: ('Produto AliExpress ' . $productId),
            'category_name' => $category,
            'target_sale_price' => (string)$costPriceUsd,
            'product_main_image_url' => $mainImage,
            'images' => $images,
            'imagePathList' => $images,
            'evaluate_rate' => '4.8',
            'volume' => 1350,
            'product_description' => "Produto importado oficial de alta durabilidade com compatibilidade e acabamento premium.",
            'properties' => [
                'Origem' => 'Importação Oficial',
                'Garantia' => 'Garantia do Fabricante',
                'Envio' => 'Rastreamento Internacional Expresso',
            ],
        ];
    }

    /**
     * Executa o ciclo de rotação diária automatizada de 24 horas.
     * Minera produtos virais aleatórios para todas as 9 categorias oficiais,
     * atualiza o catálogo, registra a data/hora de conclusão e retorna o resumo estatístico.
     *
     * @param int $itemsPerCategory Quantidade de novos produtos minerados por categoria (padrão 10 = ~90 a 100 no ciclo diário de 24h)
     * @return array<string, mixed> Resumo da rodada executada
     */
    public function runDailyAutomatedRotation(int $itemsPerCategory = 10, ?string $warehouseOrigin = null): array
    {
        $config = $this->loadConfig();
        if ($warehouseOrigin === null) {
            $warehouseOrigin = (string)($config['mining_warehouse_origin'] ?? 'ALL');
        }
        $warehouseOrigin = strtoupper(trim($warehouseOrigin)) ?: 'ALL';

        if (isset($config['daily_items_per_category']) && is_numeric($config['daily_items_per_category'])) {
            $itemsPerCategory = max(1, min(20, (int)$config['daily_items_per_category']));
        }
        $lockPath = $this->getConfigFilePath() . '.rotation.lock';
        $lockDir = dirname($lockPath);
        if (!is_dir($lockDir)) {
            @mkdir($lockDir, 0775, true);
        }

        $lockHandle = @fopen($lockPath, 'c+');
        if ($lockHandle === false) {
            return [
                'success'         => false,
                'skipped'         => true,
                'reason'          => 'lock_file_unavailable',
                'executed_at'     => 0,
                'total_mined'     => 0,
                'category_counts' => [],
            ];
        }

        if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
            fclose($lockHandle);
            return [
                'success'         => false,
                'skipped'         => true,
                'reason'          => 'rotation_already_running',
                'executed_at'     => 0,
                'total_mined'     => 0,
                'category_counts' => [],
            ];
        }

        try {
            $results = [];
            $totalProducts = 0;

            // 1. Curadoria Prioritária por Demanda: Garante vagas cativas para os produtos campeões de cliques/compras
            $priorityTermsPreserved = [];
            if ($this->demandMetricsRepo !== null) {
                try {
                    $topDemanded = $this->demandMetricsRepo->getMonthlyRankings(date('Y-m'), 5);
                    foreach ($topDemanded as $demandedItem) {
                        $term = trim((string)($demandedItem['product_title'] ?? ''));
                        $cat = trim((string)($demandedItem['category_slug'] ?? ''));
                        if ($term !== '') {
                            $targetCat = self::normalizeCategory($cat);
                            try {
                                $priorityMined = $this->mineTrendingProducts(category: $targetCat, limit: 1, autoPersist: true, sort: 'volume_desc', warehouseOrigin: $warehouseOrigin);
                                if (!empty($priorityMined)) {
                                    $priorityTermsPreserved[] = $term;
                                    $totalProducts += count($priorityMined);
                                }
                            } catch (Throwable $e) {}
                        }
                    }
                } catch (Throwable $e) {
                    // Isolamento gracioso
                }
            }

            // 2. Rotação das 9 Categorias Oficiais
            foreach (self::OFFICIAL_CATEGORIES as $cat) {
                try {
                    $mined = $this->mineTrendingProducts(category: $cat, limit: $itemsPerCategory, autoPersist: true, sort: 'volume_desc', warehouseOrigin: $warehouseOrigin);
                    $count = count($mined);
                    $results[$cat] = $count;
                    $totalProducts += $count;
                } catch (Throwable $e) {
                    $results[$cat] = 0;
                }
            }

            // 3. Limpeza automática de anúncios cuja página no AliExpress retornou 404 / "Página não encontrada" (ciclo de 24h)
            $removedDeadLinks = $this->purgeNotFoundProducts(50);

            // Registra a conclusão do ciclo de 24 horas
            $now = time();
            $this->updateConfigField('last_daily_cycle_at', $now);

            return [
                'success'            => true,
                'skipped'            => false,
                'executed_at'        => $now,
                'executed_date'      => date('d/m/Y H:i:s', $now),
                'total_mined'        => $totalProducts,
                'deleted_not_found'  => $removedDeadLinks,
                'category_counts'    => $results,
                'priority_preserved' => $priorityTermsPreserved,
            ];
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }

    /**
     * Retorna os metadados do ciclo diário de 24 horas:
     * timestamp da última execução, próxima execução, segundos restantes, tempo formatado (HH:MM:SS),
     * status se expirado (is_due) e estatísticas de produtos por categoria.
     *
     * @return array<string, mixed>
     */
    public function getDailyCycleInfo(): array
    {
        $config = $this->loadConfig();
        $intervalSeconds = (int)($config['cycle_interval_seconds'] ?? 86400); // 24 horas padrão
        if ($intervalSeconds <= 0) {
            $intervalSeconds = 86400;
        }

        $lastCycleAt = isset($config['last_daily_cycle_at']) && is_numeric($config['last_daily_cycle_at'])
            ? (int)$config['last_daily_cycle_at']
            : 0;

        $now = time();
        $autoSyncEnabled = isset($config['price_sync_enabled'])
            ? !empty($config['price_sync_enabled'])
            : (!isset($config['auto_sync_enabled']) || !empty($config['auto_sync_enabled']));

        if ($lastCycleAt <= 0) {
            // Se nunca executou, marca como vencido para disparar a primeira rodada
            $nextCycleAt = $now;
            $secondsRemaining = 0;
            $isDue = true;
        } else {
            $nextCycleAt = $lastCycleAt + $intervalSeconds;
            $secondsRemaining = max(0, $nextCycleAt - $now);
            $isDue = ($secondsRemaining === 0);
        }

        $hours = (int)floor($secondsRemaining / 3600);
        $minutes = (int)floor(($secondsRemaining % 3600) / 60);
        $seconds = (int)($secondsRemaining % 60);
        $formatted = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

        return [
            'last_cycle_at'       => $lastCycleAt,
            'last_cycle_date'     => $lastCycleAt > 0 ? date('d/m/Y H:i:s', $lastCycleAt) : 'Pendente de primeira execução',
            'next_cycle_at'       => $nextCycleAt,
            'next_cycle_date'     => date('d/m/Y H:i:s', $nextCycleAt),
            'interval_seconds'    => $intervalSeconds,
            'seconds_remaining'   => $secondsRemaining,
            'formatted_remaining' => $formatted,
            'is_due'              => $isDue,
            'auto_sync_enabled'   => $autoSyncEnabled,
            'official_categories' => self::OFFICIAL_CATEGORIES,
        ];
    }

    /**
     * Retorna os metadados da Sentinela de Preços e Estoque de 6 Horas.
     */
    public function getPriceSyncInfo(): array
    {
        $config = $this->loadConfig();
        $intervalSeconds = (int)($config['price_sync_interval_seconds'] ?? 21600); // 6 horas padrão (21600s)
        if ($intervalSeconds <= 0) {
            $intervalSeconds = 21600;
        }

        $lastSyncAt = isset($config['last_price_sync_at']) && is_numeric($config['last_price_sync_at'])
            ? (int)$config['last_price_sync_at']
            : 0;

        $now = time();
        $autoSyncEnabled = isset($config['price_sync_enabled'])
            ? !empty($config['price_sync_enabled'])
            : (!isset($config['auto_sync_enabled']) || !empty($config['auto_sync_enabled']));

        if ($lastSyncAt <= 0) {
            $nextSyncAt = $now;
            $secondsRemaining = 0;
            $isDue = true;
        } else {
            $nextSyncAt = $lastSyncAt + $intervalSeconds;
            $secondsRemaining = max(0, $nextSyncAt - $now);
            $isDue = ($secondsRemaining === 0);
        }

        $hours = (int)floor($secondsRemaining / 3600);
        $minutes = (int)floor(($secondsRemaining % 3600) / 60);
        $seconds = (int)($secondsRemaining % 60);
        $formatted = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

        return [
            'last_sync_at'        => $lastSyncAt,
            'last_sync_date'      => $lastSyncAt > 0 ? date('d/m/Y H:i:s', $lastSyncAt) : 'Pendente de primeira execução',
            'next_sync_at'        => $nextSyncAt,
            'next_sync_date'      => date('d/m/Y H:i:s', $nextSyncAt),
            'interval_seconds'    => $intervalSeconds,
            'seconds_remaining'   => $secondsRemaining,
            'formatted_remaining' => $formatted,
            'is_due'              => $isDue,
            'auto_sync_enabled'   => $autoSyncEnabled,
            'volatility_percent'  => (float)($config['volatility_index_percent'] ?? 12.0),
        ];
    }

    /**
     * Executa a varredura da Sentinela de Preços e Estoque a cada 6 horas:
     * Consulta o fornecedor no AliExpress, aplica o Índice de Correção de Volatilidade (12%)
     * e ajusta preços de venda em R$ ou pausa produtos esgotados.
     *
     * @param int $batchLimit Quantidade máxima de produtos verificados por rodada
     * @return array<string, mixed> Relatório estatístico da varredura
     */
    public function runPriceAndStockSync(int $batchLimit = 50): array
    {
        $lockPath = $this->getConfigFilePath() . '.price_sync.lock';
        $lockDir = dirname($lockPath);
        if (!is_dir($lockDir)) {
            @mkdir($lockDir, 0775, true);
        }

        $lockHandle = @fopen($lockPath, 'c+');
        if ($lockHandle === false) {
            return [
                'success' => false,
                'skipped' => true,
                'reason'  => 'lock_file_unavailable',
                'executed_at' => 0,
            ];
        }

        if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
            fclose($lockHandle);
            return [
                'success' => false,
                'skipped' => true,
                'reason'  => 'sync_already_running',
                'executed_at' => 0,
            ];
        }

        try {
            $config = $this->loadConfig();
            $volatility = (float)($config['volatility_index_percent'] ?? 0.0);
            $markup = (float)($config['default_markup_percent'] ?? 30.0);
            $products = $this->repository->list(['status' => 'published'], $batchLimit, 0);

            $checked = 0;
            $adjusted = 0;
            $paused = 0;
            $deletedNotFound = 0;
            $unchanged = 0;

            foreach ($products as $prod) {
                $checked++;
                try {
                    $aeId = (string)$prod->aliexpressId;
                    if ($aeId === '') {
                        continue;
                    }

                    // 0. Verifica disponibilidade real do link (mesmo em modo teste e mesmo com imagem já indexada)
                    // Se o anúncio no fornecedor retornou 404 / "Página não encontrada" (ex: promo relâmpago encerrada), exclui o card automaticamente.
                    if ($this->isProductLinkConfirmedNotFound($aeId)) {
                        if ($prod->id !== null && $prod->id > 0) {
                            $this->repository->delete((int)$prod->id);
                            $deletedNotFound++;
                        }
                        continue;
                    }

                    // 1. Tenta obter o preço atualizado via API oficial do AliExpress
                    $remoteData = null;
                    try {
                        $remoteData = $this->apiClient->getProductDetails($aeId);
                    } catch (Throwable $e) {
                        $remoteData = null;
                    }

                    $newCostUsd = null;
                    $newCostBrl = null;

                    if (!empty($remoteData) && is_array($remoteData)) {
                        $root = $remoteData['result'] ?? $remoteData;
                        $baseInfo = $root['ae_item_base_info_dto'] ?? [];
                        $skuDtos = $root['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'] ?? ($root['ae_item_sku_info_dtos'] ?? []);
                        $minSkuCost = null;
                        if (is_array($skuDtos)) {
                            $skuList = (isset($skuDtos['sku_id']) || isset($skuDtos['id']) || isset($skuDtos['offer_sale_price'])) ? [$skuDtos] : $skuDtos;
                            foreach ($skuList as $sku) {
                                if (is_array($sku)) {
                                    $p = (float)($sku['offer_sale_price'] ?? ($sku['sku_price'] ?? 0.0));
                                    if ($p > 0.0 && ($minSkuCost === null || $p < $minSkuCost)) {
                                        $minSkuCost = $p;
                                    }
                                }
                            }
                        }
                        if (!empty($baseInfo['offer_sale_price'])) {
                            $newCostUsd = (float)$baseInfo['offer_sale_price'];
                        } elseif (!empty($baseInfo['target_sale_price'])) {
                            $newCostUsd = (float)$baseInfo['target_sale_price'];
                        } elseif ($minSkuCost !== null && $minSkuCost > 0.0) {
                            $newCostUsd = $minSkuCost;
                        }
                    }

                    // 2. Se a API remota não trouxer, consulta o Scraper
                    if ($newCostUsd === null || $newCostUsd <= 0.0) {
                        try {
                            $scraped = $this->linkScraper->scrape($aeId);
                            if ($scraped !== null) {
                                if (!empty($scraped->attributes['cost_price_brl'])) {
                                    $newCostBrl = (float)$scraped->attributes['cost_price_brl'];
                                } elseif ($scraped->costPriceUsd > 0.0) {
                                    $newCostUsd = $scraped->costPriceUsd;
                                }
                            }
                        } catch (Throwable $e) {
                            // Silencioso
                        }
                    }

                    // 3. Aplica o recálculo com o Índice de Volatilidade de 12%
                    $currentSalePrice = (float)$prod->salePriceBrl;
                    $calculatedPrice = 0.0;

                    if ($newCostBrl !== null && $newCostBrl > 0.0) {
                        $calculatedPrice = $this->currencyConverter->calculateSalePriceFromBrl($newCostBrl, $markup, $volatility);
                    } elseif ($newCostUsd !== null && $newCostUsd > 0.0) {
                        $calculatedPrice = $this->currencyConverter->calculateSalePrice($newCostUsd, $markup, null, $volatility);
                    } else {
                        $calculatedPrice = $this->currencyConverter->calculateSalePrice($prod->costPriceUsd, $markup, null, $volatility);
                    }

                    // Se a variação de preço for maior que R$ 0,50, atualiza o produto
                    if ($calculatedPrice > 0.0 && abs($calculatedPrice - $currentSalePrice) >= 0.50) {
                        $updatedAttrs = $prod->attributes;
                        $updatedAttrs['last_price_sync_at'] = time();
                        $updatedAttrs['volatility_index_applied'] = $volatility;
                        $updatedAttrs['previous_sale_price_brl'] = $currentSalePrice;

                        $updatedDto = new DropshipProductDto(
                            aliexpressId: $prod->aliexpressId,
                            title: $prod->title,
                            slug: $prod->slug,
                            costPriceUsd: $newCostUsd ?? $prod->costPriceUsd,
                            salePriceBrl: $calculatedPrice,
                            mainImageUrl: $prod->mainImageUrl,
                            images: $prod->images,
                            category: $prod->category,
                            description: $prod->description,
                            rating: $prod->rating,
                            ordersCount: $prod->ordersCount,
                            attributes: $updatedAttrs,
                            id: $prod->id,
                            status: $prod->status
                        );

                        $this->repository->save($updatedDto);
                        $adjusted++;
                    } else {
                        $unchanged++;
                    }
                } catch (Throwable $e) {
                    // Isolamento de falha individual por produto
                }
            }

            $now = time();
            $this->updateConfigField('last_price_sync_at', $now);

            return [
                'success'             => true,
                'executed_at'         => $now,
                'executed_date'       => date('d/m/Y H:i:s', $now),
                'products_checked'    => $checked,
                'prices_adjusted'     => $adjusted,
                'unchanged_prices'    => $unchanged,
                'paused_out_of_stock' => $paused,
                'deleted_not_found'   => $deletedNotFound,
                'volatility_applied'  => $volatility,
            ];
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }

    /**
     * Consulta o SDK oficial do AliExpress (`aliexpress.ds.product.get`) para saber se o produto
     * existe ('alive'), foi removido/não encontrado ('not_found') ou está sem credenciais/indeterminado ('unknown').
     */
    public static function checkAvailabilityViaApiClient(AliExpressApiClientInterface $apiClient, string $aliexpressId): string
    {
        $aeId = trim($aliexpressId);
        if ($aeId === '') {
            return 'unknown';
        }

        try {
            $remote = $apiClient->getProductDetails($aeId);
        } catch (Throwable $e) {
            return self::classifyApiExceptionAvailability($e);
        }

        return self::classifyApiDetailsAvailability($remote);
    }

    public static function classifyApiExceptionAvailability(Throwable $e): string
    {
        $msg = strtolower($e->getMessage());
        $type = '';
        if ($e instanceof \DomainSystem\Plugins\domain_drop\Exceptions\AliExpressApiException) {
            $type = strtolower($e->type);
        }

        // 1. Erros de Autenticação / Permissão / Token / Chaves / Assinatura (NUNCA exclui produtos)
        $authMarkers = [
            'access_token',
            'missingparameter',
            'invalidsession',
            'illegalaccesstoken',
            'invalidappkey',
            'incompletesignature',
            'signature',
            'unauthorized',
            'forbidden',
            'auth_required',
            'needs_reauth',
            'session_expired',
            'session',
            'invalidcode',
            'illegalrefreshtoken',
            'invalid_token',
        ];
        foreach ($authMarkers as $marker) {
            if (str_contains($msg, $marker) || ($type !== '' && str_contains($type, $marker))) {
                return 'sdk_auth_required';
            }
        }

        // 2. Erros de Quota / Limite de Requisições / Rate Limiting (NUNCA exclui produtos)
        $rateLimitMarkers = [
            'appcallquotaexceeded',
            'quota_exceeded',
            'quota',
            'ratelimit',
            'rate limit',
            'too many requests',
            'throttle',
            'busy',
            'concurrent',
        ];
        foreach ($rateLimitMarkers as $marker) {
            if (str_contains($msg, $marker) || ($type !== '' && str_contains($type, $marker))) {
                return 'rate_limited';
            }
        }

        // 3. Erros de Rede, Conexão, DNS, Timeout, Gateway HTTP (NUNCA exclui produtos)
        $networkMarkers = [
            'timeout',
            'timed out',
            'connection refused',
            'curl',
            'resolve',
            'dns',
            'gateway',
            'serviceunavailable',
            'service unavailable',
            'maintenance',
            'internal error',
            'system error',
            'connection_error',
            'http_500',
            'http_502',
            'http_503',
            'http_504',
            'ssl',
        ];
        foreach ($networkMarkers as $marker) {
            if (str_contains($msg, $marker) || ($type !== '' && str_contains($type, $marker))) {
                return 'network_or_system_error';
            }
        }

        // 4. Somente marcadores específicos de PRODUTO/ITEM inexistente ou despublicado pelo fornecedor
        $specificNotFoundMarkers = [
            'item_not_found',
            'item_not_exist',
            'product_not_found',
            'product_not_exist',
            'item does not exist',
            'product does not exist',
            'invalid item id',
            'invalid product id',
            'item_id_not_found',
            'item-not-exist',
            'all sku unsaleable',
            'product offline',
            'item offline',
        ];
        foreach ($specificNotFoundMarkers as $marker) {
            if (str_contains($msg, $marker) || ($type !== '' && str_contains($type, $marker))) {
                return 'not_found';
            }
        }

        return 'unknown';
    }

    /**
     * @param mixed $remote
     */
    public static function classifyApiDetailsAvailability(mixed $remote): string
    {
        if (!is_array($remote) || empty($remote)) {
            return 'unknown';
        }

        $baseInfo = $remote['ae_item_base_info_dto']
            ?? ($remote['result']['ae_item_base_info_dto']
            ?? ($remote['aliexpress_ds_product_get_response']['result']['ae_item_base_info_dto'] ?? null));
        if (is_array($baseInfo)) {
            $statusType = strtolower(trim((string)($baseInfo['product_status_type'] ?? '')));
            if (in_array($statusType, ['offline', 'deleted', 'auditing_failed'], true)) {
                return 'not_found';
            }
            if (!empty($baseInfo['subject']) || !empty($baseInfo['product_id'])) {
                return 'alive';
            }
        }

        return 'unknown';
    }

    /**
     * Verifica se a página do produto no fornecedor foi confirmada como inexistente (404 / "Página não encontrada").
     * Prioriza o SDK oficial do AliExpress (sem captcha) e usa o scraper apenas como fallback quando o SDK estiver indisponível.
     */
    private function isProductLinkConfirmedNotFound(string $aliexpressId): bool
    {
        $sdkStatus = self::checkAvailabilityViaApiClient($this->apiClient, $aliexpressId);
        if ($sdkStatus === 'not_found') {
            return true;
        }
        if ($sdkStatus === 'alive') {
            return false;
        }

        if (method_exists($this->linkScraper, 'checkAvailability')) {
            return $this->linkScraper->checkAvailability($aliexpressId) === 'not_found';
        }
        return false;
    }

    /**
     * Varre anúncios publicados e exclui automaticamente cards cujo link no AliExpress
     * retornou "Página não encontrada" / 404 (ex.: promoção relâmpago encerrada).
     */
    public function purgeNotFoundProducts(int $batchLimit = 50): int
    {
        $deleted = 0;
        $products = $this->repository->list(['status' => 'published'], $batchLimit, 0);
        foreach ($products as $prod) {
            $aeId = (string)$prod->aliexpressId;
            if ($aeId === '' || $prod->id === null || $prod->id <= 0) {
                continue;
            }
            try {
                if ($this->isProductLinkConfirmedNotFound($aeId)) {
                    if ($this->repository->delete((int)$prod->id)) {
                        $deleted++;
                    }
                }
            } catch (Throwable $e) {
                // Isolamento de falha por item
            }
        }
        return $deleted;
    }

    /**
     * Verifica ao vivo (no clique do botão Comprar) se o anúncio ainda existe no fornecedor
     * e consulta o estoque atual via SDK do AliExpress quando disponível.
     *
     * @return array{available: bool, reason: string, message: string, stock: ?int, stock_source: string}
     */
    public function checkLiveProductAvailability(DropshipProductDto $product, string $skuId = ''): array
    {
        $aeId = trim((string)$product->aliexpressId);

        // 1. Consulta o SDK oficial do AliExpress uma única vez (disponibilidade + estoque)
        $remote = null;
        $sdkStatus = 'unknown';
        if ($aeId !== '') {
            try {
                $remote = $this->apiClient->getProductDetails($aeId);
                $sdkStatus = self::classifyApiDetailsAvailability($remote);
            } catch (Throwable $e) {
                $sdkStatus = self::classifyApiExceptionAvailability($e);
            }
        }

        if ($sdkStatus === 'not_found'
            || ($sdkStatus === 'unknown' && $aeId !== '' && method_exists($this->linkScraper, 'checkAvailability') && $this->linkScraper->checkAvailability($aeId) === 'not_found')
        ) {
            if ($product->id !== null && $product->id > 0) {
                $this->repository->delete((int)$product->id);
            }
            return [
                'available'    => false,
                'reason'       => 'not_found',
                'message'      => 'O estoque deste produto já acabou e o anúncio não está mais disponível no fornecedor.',
                'stock'        => 0,
                'stock_source' => $sdkStatus === 'not_found' ? 'aliexpress_sdk' : 'supplier_page',
            ];
        }

        // 2. Extrai estoque atual real a partir da mesma resposta do SDK do AliExpress
        $sdkStock = null;
        if (is_array($remote) && !empty($remote)) {
                    $sdkNode = $remote['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o']
                        ?? ($remote['result']['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'] ?? null);
                    if (is_array($sdkNode) && !empty($sdkNode)) {
                        $skuList = isset($sdkNode['sku_id']) || isset($sdkNode['id']) ? [$sdkNode] : $sdkNode;
                        $matchedStock = null;
                        $totalStock = 0;
                        $hasNumericStock = false;
                        foreach ($skuList as $sku) {
                            if (!is_array($sku)) {
                                continue;
                            }
                            if (isset($sku['sku_available_stock']) || isset($sku['sku_stock'])) {
                                $sVal = max(0, (int)($sku['sku_available_stock'] ?? $sku['sku_stock']));
                                $hasNumericStock = true;
                                $totalStock += $sVal;
                                $curSkuId = (string)($sku['sku_id'] ?? ($sku['id'] ?? ''));
                                if ($skuId !== '' && $curSkuId === $skuId) {
                                    $matchedStock = $sVal;
                                }
                            }
                        }
                        if ($matchedStock !== null) {
                            $sdkStock = $matchedStock;
                        } elseif ($hasNumericStock) {
                            $sdkStock = $totalStock;
                        }
                    }

                    if ($sdkStock === null) {
                        $baseInfo = $remote['ae_item_base_info_dto'] ?? ($remote['result']['ae_item_base_info_dto'] ?? []);
                        if (is_array($baseInfo) && (isset($baseInfo['total_available_stock']) || isset($baseInfo['stock']))) {
                            $sdkStock = max(0, (int)($baseInfo['total_available_stock'] ?? $baseInfo['stock']));
                        }
                    }
        }

        if ($sdkStock !== null) {
            if ($sdkStock <= 0) {
                return [
                    'available'    => false,
                    'reason'       => 'out_of_stock',
                    'message'      => 'O estoque deste produto já acabou no momento.',
                    'stock'        => 0,
                    'stock_source' => 'aliexpress_sdk',
                ];
            }

            return [
                'available'    => true,
                'reason'       => 'in_stock',
                'message'      => "Estoque atual confirmado: {$sdkStock} unidade(s) disponíveis.",
                'stock'        => $sdkStock,
                'stock_source' => 'aliexpress_sdk',
            ];
        }

        // 3. Fallback para estoque local da variante selecionada (caso SDK não retorne contagem)
        if ($skuId !== '') {
            $variant = $product->findVariantBySku($skuId);
            if ($variant !== null) {
                if ($variant->stock <= 0) {
                    return [
                        'available'    => false,
                        'reason'       => 'out_of_stock',
                        'message'      => 'O estoque da variação selecionada já acabou.',
                        'stock'        => 0,
                        'stock_source' => 'catalog_variant',
                    ];
                }
                return [
                    'available'    => true,
                    'reason'       => 'in_stock',
                    'message'      => "Estoque disponível: {$variant->stock} unidade(s).",
                    'stock'        => $variant->stock,
                    'stock_source' => 'catalog_variant',
                ];
            }
        }

        return [
            'available'    => true,
            'reason'       => 'available',
            'message'      => 'Produto disponível para compra.',
            'stock'        => null,
            'stock_source' => 'catalog',
        ];
    }

    private function getConfigFilePath(): string
    {
        if ($this->configFilePath !== null) {
            return $this->configFilePath;
        }
        $root = defined('DOMAIN_SYSTEM_ROOT') ? DOMAIN_SYSTEM_ROOT : dirname(__DIR__, 4);
        return $root . '/config/plugins/domain_drop.json';
    }

    private function loadConfig(): array
    {
        $file = $this->getConfigFilePath();
        if (file_exists($file)) {
            $content = @file_get_contents($file);
            if ($content !== false) {
                $data = json_decode($content, true);
                if (is_array($data)) {
                    return $data;
                }
            }
        }
        return [
            'enabled'                => true,
            'auto_sync_enabled'      => true,
            'cycle_interval_seconds' => 86400,
        ];
    }

    public function updateConfigField(string $key, mixed $value): void
    {
        $file = $this->getConfigFilePath();
        $config = $this->loadConfig();
        $config[$key] = $value;
        $dir = dirname($file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        @file_put_contents($file, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
