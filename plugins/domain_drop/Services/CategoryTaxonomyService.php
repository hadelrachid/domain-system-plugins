<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Services;

use DomainSystem\Plugins\domain_drop\Contracts\CategoryRegistryInterface;
use DomainSystem\Plugins\domain_drop\Contracts\CategoryTaxonomyInterface;
use DomainSystem\Plugins\domain_drop\DTO\DepartmentDto;

/**
 * Serviço de Taxonomia Canônica Oficial, Classificação Semântica e Filtros SQL (SOLID / SRP).
 * 
 * Totalmente alinhado às 548 categorias oficiais da AliExpress Open Platform (IOP SDK)
 * para produtos em território nacional brasileiro, sem invenção de termos.
 */
class CategoryTaxonomyService implements CategoryTaxonomyInterface, CategoryRegistryInterface
{
    // Categorias Oficiais alinhadas 1:1 com a AliExpress Open Platform
    public const CAT_TECH         = 'Eletrônicos & Telefonia';          // IDs 44, 509, 7
    public const CAT_MOBILE_TV    = 'Smartphones, TVs & Projetores 4K'; // IDs 509, 44, 7
    public const CAT_SMARTWATCH   = 'Relógios & Smartwatches';          // ID 1511
    public const CAT_HOME         = 'Casa, Iluminação & Eletro';        // IDs 15, 39, 6, 13
    public const CAT_TOOLS        = 'Ferramentas & Utilidades';         // IDs 1420, 30
    public const CAT_BEAUTY       = 'Beleza, Saúde & Maquiagem';        // IDs 66, 660103
    public const CAT_PETS         = 'Pet Shop';                         // ID 100006664 (Casa e jardim)
    public const CAT_AUTO         = 'Peças & Acessórios Automotivos';   // IDs 34, 201355758
    public const CAT_FASHION      = 'Roupas, Bolsas & Calçados';        // IDs 200000345, 200000343, 1524, 322
    public const CAT_FITNESS      = 'Moda Fitness, Leggings & Academia';// IDs 200000785, 200000349, 200000345, 200000780
    public const CAT_LINGERIE     = 'Roupa Íntima & Pijamas';           // ID 200574005 (feminina, masculina, pijamas)
    public const CAT_ADULT        = 'Novidades & Uso Especial';         // ID 200000532

    public function getCanonicalCategories(): array
    {
        return [
            self::CAT_TECH,
            self::CAT_MOBILE_TV,
            self::CAT_SMARTWATCH,
            self::CAT_HOME,
            self::CAT_TOOLS,
            self::CAT_BEAUTY,
            self::CAT_PETS,
            self::CAT_AUTO,
            self::CAT_FASHION,
            self::CAT_FITNESS,
            self::CAT_LINGERIE,
        ];
    }

    /**
     * Retorna a lista das 9 categorias comerciais oficiais de vitrine do DomainDrop.
     * @return array<int, string>
     */
    public static function getCommercialCategories(): array
    {
        return [
            'Eletrônicos & Gadget',
            'Casa Inteligente & Organização',
            'Beleza, Saúde & Bem-Estar',
            'Ferramentas & Construção',
            'Pet Shop & Cuidados Animais',
            'Peças & Acessórios Automotivos',
            'Moda, Roupas & Vestuário',
            'Moda Íntima & Lingerie',
            'Novidades & Uso Especial',
        ];
    }

    /**
     * Retorna os IDs numéricos oficiais correspondentes na árvore de categorias da AliExpress Open Platform.
     * Alinhado a categoria-e-atributos.pdf e dropshiper.pdf.
     *
     * @param string $category
     * @return array<int, int>
     */
    public function getAliExpressCategoryIds(string $category): array
    {
        $canonical = $this->normalizeCategory($category);
        return match ($canonical) {
            self::CAT_TECH       => [44, 509, 7],
            self::CAT_MOBILE_TV  => [509, 44, 7],
            self::CAT_SMARTWATCH => [1511],
            self::CAT_HOME       => [15, 39, 6],
            self::CAT_TOOLS      => [1420, 30],
            self::CAT_BEAUTY     => [66, 660103],
            self::CAT_PETS       => [100006664, 15],
            self::CAT_AUTO       => [34, 201355758],
            self::CAT_FASHION    => [200000345, 200000343, 200000346, 200000347, 200000344, 200000348, 1524, 322],
            self::CAT_FITNESS    => [200000785, 200000349, 200000345, 200000780],
            self::CAT_LINGERIE   => [200574005, 200000851, 200000858, 200000780, 200000778, 200000779, 200000781],
            default              => [44],
        };
    }

    /**
     * Retorna termos de busca otimizados em português para consulta no AliExpress.
     *
     * @param string $category
     * @return array<int, string>
     */
    public function getAliExpressSearchKeywords(string $category): array
    {
        $canonical = $this->normalizeCategory($category);
        return match ($canonical) {
            self::CAT_TECH       => [
                'fone bluetooth tws brasil',
                'carregador turbo tipo c 65w',
                'power bank inducao magnetico',
                'headset gamer microfone rgb',
                'caixa de som bluetooth potente',
                'fone conducao ossea corrida',
                'suporte articulado celular mesa',
                'cabo usb c carregamento rapido',
                'adaptador bluetooth transmissor audio'
            ],
            self::CAT_MOBILE_TV, 'Smartphones, TVs & Projetores 4K' => [
                'mini projetor hy300 4k',
                'projetor magcubic 4k android',
                'tv box android 4k',
                'smartphone poco x6 pro',
                'smartphone xiaomi redmi note',
                'projetor portatil wifi 4k',
                'smart tv box homologada',
                'celular android versao global'
            ],
            self::CAT_SMARTWATCH => [
                'smartwatch prova dagua nfc gps',
                'relogio inteligente monitor cardiaco',
                'smartwatch esportivo corrida fitness',
                'pulseira magnetica smartwatch',
                'smartwatch feminino elegante tela amoled',
                'relogio quartzo masculino executivo luxo',
                'smartwatch chamada bluetooth bateria longa'
            ],
            self::CAT_HOME       => [
                'fita led rgb inteligente tuya wifi',
                'lampada inteligente wifi alexa rgb',
                'sensor presenca inteligente automacao',
                'aspirador de po robo portatil recarregavel',
                'dispensador sabonete automatico sensor',
                'umidificador aromatizador ultrassonico led',
                'fechadura digital biometrica senha',
                'luz noturna sensor movimento sem fio'
            ],
            self::CAT_TOOLS      => [
                'alicate desencapador fios multifuncional',
                'jogo chave de precisao catraca bits',
                'parafusadeira eletrica sem fio recarregavel',
                'trena laser digital medidor distancia',
                'canivete tatico multifuncional sobrevivencia',
                'lanterna tatica led potente militar recarregavel',
                'soldador eletrico portatil precisao ferro'
            ],
            self::CAT_BEAUTY     => [
                'escova secadora alisadora modeladora 3 em 1',
                'paleta de sombras maquiagem profissional',
                'barbeador eletrico masculino aparador pelos',
                'depilador laser luz pulsada ipl indolor',
                'gloss labial efeito plump volume hidratante',
                'massageador facial eletrico gua sha lifting',
                'kit pinceis maquiagem profissional estojo',
                'espelho de maquiagem led recarregavel toque'
            ],
            self::CAT_PETS       => [
                'coleira led luminosa cachorro recarregavel usb',
                'bebedouro fonte automatica pet gato cachorro',
                'escova a vapor pet removedora pelos gatos caes',
                'brinquedo interativo inteligência gato pet',
                'cama nuvem calmante pelucia cachorro pet',
                'cortador de unhas pet com led iluminador',
                'tapete higienico impermeavel lavavel cachorro'
            ],
            self::CAT_AUTO       => [
                'suporte celular veicular carregador inducao magsafe',
                'transmissor bluetooth veicular fm usb aux',
                'aspirador automotivo portatil potente sem fio',
                'compressor de ar portatil eletrico pneus carro',
                'camera veicular dvr gravador painel seguranca',
                'fita led interna veicular sensor som rgb neon',
                'chaveiro veicular couro metal luxo logo'
            ],
            self::CAT_FASHION, 'Moda, Roupas & Vestuário' => [
                'vestido feminino casual verao elegante brasil',
                'camisa social masculina manga longa linho brasil',
                'camiseta basica 100 algodao unissex slim brasil',
                'jaqueta jeans masculina feminina oversize casual',
                'calca jeans feminina masculina skinny stretch brasil',
                'calca jogger streetwear cargo bolsos unissex',
                'jaqueta corta vento impermeavel capuz ciclismo',
                'conjunto moletom masculino feminino agasalho inverno',
                'blusa feminina casual verao elegante brasil',
                'tenis esportivo corrida caminhada respiravel leve',
                'bolsa feminina transversal tiracolo couro vintage',
                'mochila executiva impermeavel antifurto usb notebook'
            ],
            self::CAT_FITNESS, 'Moda Fitness, Leggings & Academia' => [
                'legging suplex feminina',
                'macacao fitness feminino',
                'conjunto academia feminino',
                'macaquinho fitness treino',
                'short suplex academia',
                'top fitness sustentacao',
                'calca legging empina bumbum',
                'body feminino canelado academia'
            ],
            self::CAT_LINGERIE, 'Moda Íntima, Lingeries & Pijamas' => [
                'conjunto lingerie renda floral sutiã calcinha luxo',
                'sutia sem costura sustentacao push up confortavel',
                'pijama feminino cetim seda camisola renda',
                'kit cuecas boxer modal sem costura respiravel',
                'meia calca termica translucida forrada peluciada',
                'kit calcinhas sem costura invisivel algodao',
                'espartilho modelador cinta feminina compressao'
            ],
            default              => ['eletronicos utilidades brasil'],
        };
    }

    public function toCommercialCategory(string $category): string
    {
        $raw = trim($category);
        if ($raw === '') {
            return 'Eletrônicos & Gadget';
        }

        $lower = mb_strtolower($raw, 'UTF-8');
        $clean = preg_replace('/[^\p{L}\p{N}\s&]/u', ' ', $lower) ?? $lower;

        // 0. Moda Fitness, Leggings & Academia (Precedência sobre Moda geral)
        if (str_contains($clean, 'fitness') || str_contains($clean, 'feetness') || str_contains($clean, 'legging') || str_contains($clean, 'academia') || str_contains($clean, 'suplex') || str_contains($clean, 'macaquinho') || str_contains($clean, 'macacao fitness') || str_contains($clean, 'macacão fitness')) {
            return 'Moda Fitness, Leggings & Academia';
        }

        // 0.1 Smartphones, TVs & Projetores 4K (Precedência sobre Eletrônicos gerais)
        if (str_contains($clean, 'smartphone') || str_contains($clean, 'celular') || str_contains($clean, 'projetor') || str_contains($clean, 'tv box') || str_contains($clean, 'smart tv') || str_contains($clean, 'xiaomi') || str_contains($clean, 'poco') || str_contains($clean, 'magcubic') || str_contains($clean, 'hy300')) {
            return 'Smartphones, TVs & Projetores 4K';
        }

        // 1. Moda Íntima e Lingeries
        if (str_contains($clean, 'lingerie') || str_contains($clean, 'intima') || str_contains($clean, 'íntima') || str_contains($clean, 'sutia') || str_contains($clean, 'sutiã') || str_contains($clean, 'calcinha') || str_contains($clean, 'cueca') || str_contains($clean, 'boxer') || str_contains($clean, 'pijama')) {
            return 'Moda Íntima, Lingeries & Pijamas';
        }

        // 2. Massagem & Bem-Estar Corporal
        if (str_contains($clean, 'massageador') || str_contains($clean, 'relaxamento') || str_contains($clean, 'miofascial') || str_contains($clean, 'massagem')) {
            return 'Beleza, Cuidados & Estilo';
        }

        // 3. Moda, Roupas & Vestuário
        if (str_contains($clean, 'roupa') || str_contains($clean, 'vestu') || str_contains($clean, 'calcado') || str_contains($clean, 'calçado') || str_contains($clean, 'casaco') || str_contains($clean, 'jaqueta') || str_contains($clean, 'calca') || str_contains($clean, 'calça') || str_contains($clean, 'camisa') || str_contains($clean, 'camiseta') || str_contains($clean, 'vestido') || str_contains($clean, 'blusa') || str_contains($clean, 'tenis') || str_contains($clean, 'tênis') || str_contains($clean, 'bolsa') || str_contains($clean, 'mochila') || str_contains($clean, 'carteira') || str_contains($clean, 'legging') || str_contains($clean, 'macacao') || str_contains($clean, 'macacão') || str_contains($clean, 'fitness') || str_contains($clean, 'academia') || str_contains($clean, 'moda')) {
            return 'Moda, Roupas & Vestuário';
        }

        // 4. Casa Inteligente & Organização
        if (str_contains($clean, 'casa') || str_contains($clean, 'ilumina') || str_contains($clean, 'eletro') || str_contains($clean, 'organiza') || str_contains($clean, 'decor')) {
            return 'Casa Inteligente & Organização';
        }

        // 5. Beleza, Cuidados & Estilo
        if (str_contains($clean, 'beleza') || str_contains($clean, 'saude') || str_contains($clean, 'saúde') || str_contains($clean, 'maquiagem') || str_contains($clean, 'cuidado') || str_contains($clean, 'estilo') || str_contains($clean, 'cabelo') || str_contains($clean, 'skincare')) {
            return 'Beleza, Cuidados & Estilo';
        }

        // 6. Ferramentas & Utilidades
        if (str_contains($clean, 'ferramenta') || str_contains($clean, 'utilidade') || str_contains($clean, 'alicate') || str_contains($clean, 'parafusadeira')) {
            return 'Ferramentas & Utilidades';
        }

        // 7. Pet Shop & Pets Virais
        if (str_contains($clean, 'pet') || str_contains($clean, 'cachorro') || str_contains($clean, 'gato') || str_contains($clean, 'cao') || str_contains($clean, 'cão')) {
            return 'Pet Shop & Pets Virais';
        }

        // 8. Acessórios para Veículos
        if (str_contains($clean, 'veiculo') || str_contains($clean, 'veículo') || str_contains($clean, 'auto') || str_contains($clean, 'carro') || str_contains($clean, 'moto')) {
            return 'Acessórios para Veículos';
        }

        // 9. Eletrônicos & Gadget
        return 'Eletrônicos & Gadget';
    }

    public function normalizeCategory(string $category): string
    {
        $raw = trim($category);
        if ($raw === '') {
            return self::CAT_TECH;
        }

        $lower = mb_strtolower($raw, 'UTF-8');
        $clean = preg_replace('/[^\p{L}\p{N}\s&]/u', ' ', $lower) ?? $lower;

        if (str_contains($clean, 'geral')) {
            return self::CAT_TOOLS;
        }

        // 0. Moda Fitness, Leggings & Academia (Precedência sobre Roupas em geral)
        if (str_contains($clean, 'fitness') || str_contains($clean, 'feetness') || str_contains($clean, 'legging') || str_contains($clean, 'academia') || str_contains($clean, 'suplex') || str_contains($clean, 'macaquinho') || str_contains($clean, 'macacao fitness') || str_contains($clean, 'macacão fitness')) {
            return self::CAT_FITNESS;
        }

        // 0.1 Smartphones, TVs & Projetores 4K (Precedência sobre Eletrônicos gerais)
        if (str_contains($clean, 'smartphone') || str_contains($clean, 'celular') || str_contains($clean, 'projetor') || str_contains($clean, 'tv box') || str_contains($clean, 'smart tv') || str_contains($clean, 'xiaomi') || str_contains($clean, 'poco') || str_contains($clean, 'magcubic') || str_contains($clean, 'hy300')) {
            return self::CAT_MOBILE_TV;
        }

        // 1. Roupa Íntima & Pijamas (ID 200574005)
        if (str_contains($clean, 'lingerie') || str_contains($clean, 'sutia') || str_contains($clean, 'sutiã') || str_contains($clean, 'calcinha') || str_contains($clean, 'intima') || str_contains($clean, 'íntima') || str_contains($clean, 'pijama') || str_contains($clean, 'cueca') || str_contains($clean, 'boxer') || str_contains($clean, 'espartilho') || str_contains($clean, 'corset')) {
            return self::CAT_LINGERIE;
        }

        // 2. Massagem & Bem-Estar Corporal
        if (str_contains($clean, 'massageador') || str_contains($clean, 'relaxamento') || str_contains($clean, 'miofascial') || str_contains($clean, 'massagem')) {
            return self::CAT_BEAUTY;
        }

        // 3. Roupas, Bolsas & Calçados (IDs 200000345, 200000343, 1524, 322)
        if (str_contains($clean, 'roupa') || str_contains($clean, 'vestuario') || str_contains($clean, 'vestuário') || str_contains($clean, 'casaco') || str_contains($clean, 'jaqueta') || str_contains($clean, 'mochila') || str_contains($clean, 'bolsa') || str_contains($clean, 'carteira') || str_contains($clean, 'sapato') || str_contains($clean, 'tenis') || str_contains($clean, 'tênis') || str_contains($clean, 'calcado') || str_contains($clean, 'calçado') || str_contains($clean, 'legging') || str_contains($clean, 'macacao') || str_contains($clean, 'macacão') || str_contains($clean, 'fitness') || str_contains($clean, 'academia') || (str_contains($clean, 'moda') && !str_contains($clean, 'intima') && !str_contains($clean, 'íntima'))) {
            return self::CAT_FASHION;
        }

        // 4. Pet Shop (ID 100006664)
        if (str_contains($clean, 'pet') || str_contains($clean, 'cachorro') || str_contains($clean, 'gato') || str_contains($clean, 'cao') || str_contains($clean, 'cão')) {
            return self::CAT_PETS;
        }

        // 5. Peças & Acessórios Automotivos (ID 34)
        if (str_contains($clean, 'veiculo') || str_contains($clean, 'veículo') || str_contains($clean, 'carro') || str_contains($clean, 'automotivo') || str_contains($clean, 'auto') || str_contains($clean, 'moto')) {
            return self::CAT_AUTO;
        }

        // 6. Ferramentas & Utilidades (ID 1420, 30)
        if (str_contains($clean, 'alicate') || str_contains($clean, 'ferramenta') || str_contains($clean, 'crimpador') || str_contains($clean, 'decapador') || str_contains($clean, 'chave de fenda') || str_contains($clean, 'utilidade') || str_contains($clean, 'segurança')) {
            return self::CAT_TOOLS;
        }

        // 7. Relógios & Smartwatches (ID 1511)
        if (str_contains($clean, 'smartwatch') || str_contains($clean, 'relogio') || str_contains($clean, 'relógio') || str_contains($clean, 'pulseira inteligente') || str_contains($clean, 'apple watch')) {
            return self::CAT_SMARTWATCH;
        }

        // 8. Beleza, Saúde & Maquiagem (ID 66, 660103)
        if (str_contains($clean, 'beleza') || str_contains($clean, 'cabelo') || str_contains($clean, 'pele') || str_contains($clean, 'secador') || str_contains($clean, 'escova') || str_contains($clean, 'saude') || str_contains($clean, 'saúde') || str_contains($clean, 'maquiagem') || str_contains($clean, 'batom')) {
            return self::CAT_BEAUTY;
        }

        // 9. Casa, Iluminação & Eletro (ID 15, 39, 6)
        if (str_contains($clean, 'casa') || str_contains($clean, 'led') || str_contains($clean, 'luminaria') || str_contains($clean, 'luminária') || str_contains($clean, 'lampada') || str_contains($clean, 'lâmpada') || str_contains($clean, 'iluminação') || str_contains($clean, 'eletro') || str_contains($clean, 'tuya')) {
            return self::CAT_HOME;
        }

        // 10. Eletrônicos & Telefonia (ID 44, 509, 7)
        return self::CAT_TECH;
    }

    public function classifyFromText(string $title, ?string $rawCategory = null): string
    {
        $t = mb_strtolower(trim($title), 'UTF-8');

        // 0. Moda Fitness, Leggings & Academia (Precedência alta sobre Moda geral)
        if (preg_match('/\b(legging|leggings|suplex|macaquinho fitness|macacão fitness|macacao fitness|conjunto academia|top fitness|calça suplex|calca suplex|bermuda fitness|empina bumbum|short suplex|fitness|feetness|academia)\b/iu', $t)) {
            return self::CAT_FITNESS;
        }

        // 0.1 Smartphones, TVs & Projetores 4K (Precedência sobre Eletrônicos gerais)
        if (preg_match('/\b(smartphone|smartphones|celular|celulares|projetor|projetores|magcubic|hy300|tv box|smart tv|poco x6|redmi note|xiaomi)\b/iu', $t)) {
            return self::CAT_MOBILE_TV;
        }

        // 1. Roupa Íntima & Pijamas (Precedência alta sobre Moda geral para evitar falso-positivo)
        if (preg_match('/\b(lingerie|sutiã|sutia|calcinha|calcinhas|bojo|push up|push-up|guipir|bralette|camisola|baby doll|pijama|pijamas|renda sexy|natalina sexy|meia-calça|meias-calça|meia calça|meias calça|meia calca|meia-calca|meia arrastão|meia arrastao|cueca|cuecas|boxer|slip|corpete|espartilho|espartilhos|corset|cinta modeladora|peças íntimas|pecas intimas|roupa íntima)\b/iu', $t)) {
            return self::CAT_LINGERIE;
        }

        // 2. Massagem, Fisioterapia & Bem-Estar Corporal
        if (preg_match('/\b(massageador|massageadora|relaxamento muscular|miofascial|pistola de massagem|almofada massageadora)\b/iu', $t)) {
            return self::CAT_BEAUTY;
        }

        // 2.5 Joias, Pulseiras, Colares, Anéis & Semijoias
        if (preg_match('/\b(pulseira|pulseiras|bracelete|braceletes|colar|colares|gargantilha|corrente banhada|brinco|brincos|anel|anéis|aneis|aliança|alianças|alianca|aliancas|pingente|pingentes|tornozeleira|tornozeleiras|berloque|berloques|joia|joias|jóia|jóias|bijuteria|bijuterias|semijoia|semijoias|semi-joia|ouro 18k|prata 925)\b/iu', $t)) {
            return self::CAT_BEAUTY;
        }

        // 3. Roupas, Bolsas & Calçados (Moda em Geral - Inclui Macacão, Vestidos, Tecidos e Calçados)
        if (preg_match('/\b(macacão|macacao|macacões|macacoes|macaquinho|macaquinhos|playsuit|playsuits|romper|rompers|cropped|top|body|bodies|biquíni|biquini|maiô|maio|tricô|trico|poliamida|viscose|elastano|linho|algodão|algodao|tecido|moda urbana|mulheres|mulher|feminino|feminina|masculino|masculina|unissex|casaco|casacos|jaqueta|jaquetas|corta-vento|corta vento|ciclismo|corrida secagem|impermeável masculino|capuz capa|moletom|calça|calca|camisa|camiseta|vestido|saia|bermuda|roupa|roupas|vestuário|vestuario|mochila|mochilas|bolsa|bolsas|carteira|carteiras|pochete|tênis|tenis|sapato|sapatos|sandália|sandalia|chinelo|chinelos|bota|botas|mocassim|calçado|calçados|calcado|calcados|meia|meias|invisível|invisivel|chaveiro|chaveiros|cinto|cintos|boné|bone|óculos|oculos|terno|blazer|cardigan|pullover|suéter|sueter)\b/iu', $t)) {
            return self::CAT_FASHION;
        }

        // 4. Pet Shop
        if (preg_match('/\b(pet|pets|cachorro|cachorros|cão|cães|gato|gatos|coleira|trela|fita led noite segurança|fibra ótica recarregável cães)\b/iu', $t)) {
            return self::CAT_PETS;
        }

        // 5. Peças & Acessórios Automotivos (Restrito estritamente a veículos)
        if (preg_match('/\b(veicular|veículo|veiculo|carro|automotivo|ventilação de ar|suporte celular carro|suporte automotivo|telemóvel com ventosa|suporte magnético 360|peças automotivas|painel do carro|para-brisa|parabrisa|volante carro|palheta carro|pneu carro)\b/iu', $t)) {
            return self::CAT_AUTO;
        }

        // 6. Ferramentas & Utilidades
        if (preg_match('/\b(alicate|alicates|crimpador|decapador|desencapar fios|chaves de fenda|chave de fenda|catraca|ferramentas|ferramenta|chaves de precisão|bits)\b/iu', $t)) {
            return self::CAT_TOOLS;
        }

        // 7. Relógios & Smartwatches
        if (preg_match('/\b(smartwatch|smartwatches|relógio inteligente|relogio inteligente|relógio|relogio|apple watch|amazfit|aurafit|pulseira homem de ferro)\b/iu', $t)) {
            return self::CAT_SMARTWATCH;
        }

        // 8. Beleza, Saúde & Maquiagem
        if (preg_match('/\b(secador|escova secadora|escova alisadora|alisador|modelador de cabelo|cabelo|curling|barbeador|barba|depilador|maquiagem|facial|pele|batom|batons|gloss|rímel|rimel|delineador|base líquida|base liquida|corretivo|pó compacto|po compacto|paleta de sombras|paleta sombras|blush|pincel|pincéis|pinceis|esponja maquiagem|lip tint)\b/iu', $t)) {
            return self::CAT_BEAUTY;
        }

        // 9. Casa, Iluminação & Eletro (Casa Inteligente, Banheiro, Prateleiras, Cozinha, Organização)
        if (preg_match('/\b(prateleira|prateleiras|suporte parede|suporte de parede|suporte para parede|adesivo parede|adesivos para parede|alto adesivo|adesivo para parede|toalheiro|porta toalha|saboneteira|dispenser|banheiro|chuveiro|ducha|torneira|ralo|estante|nicho|organizador|cozinha|escorredor|louça|panela|frigideira|utensílio|utensilio|mesa|cadeira|sofá|sofa|almofada|cortina|tapete|fita de led|fita led|rgbic|tuya|luminária|luminaria|lâmpada|lampada|barra de luz|monitor luzes|iluminação|teto|armário|quarto|aspirador|eletrodoméstico|eletrodomestico|liquidificador portátil)\b/iu', $t)) {
            return self::CAT_HOME;
        }

        // 10. Se houver categoria bruta informada da API oficial, normaliza diretamente
        if ($rawCategory !== null && trim($rawCategory) !== '' && trim($rawCategory) !== 'Geral') {
            return $this->normalizeCategory($rawCategory);
        }

        return self::CAT_TECH;
    }

    /**
     * Retorna todos os 11 departamentos oficiais com suas categorias de banco,
     * IDs do AliExpress e metadados estruturados (Contrato CategoryRegistryInterface).
     *
     * @return array<string, DepartmentDto>
     */
    public function getAllDepartments(): array
    {
        return [
            'auto' => new DepartmentDto(
                code: 'auto',
                name: 'Peças & Acessórios Automotivos',
                icon: '🚗',
                aliExpressCategoryIds: [34, 201355758],
                databaseCategories: [
                    'Peças & Acessórios Automotivos',
                    'Peças e acessórios automotivos',
                    'Acessórios para Veículos',
                    'Acessórios Auto'
                ],
                description: 'Acessórios veiculares, suportes e peças automotivas.'
            ),
            'fashion' => new DepartmentDto(
                code: 'fashion',
                name: 'Roupas, Bolsas & Calçados',
                icon: '👗',
                aliExpressCategoryIds: [200000345, 200000343, 200000346, 200000347, 200000344, 200000348, 1524, 322],
                databaseCategories: [
                    'Roupas, Bolsas & Calçados',
                    'Moda, Roupas & Vestuário',
                    'Roupas femininas',
                    'Roupas masculinas',
                    'Bolsas e malas',
                    'Sapatos',
                    'Calçados',
                    'Vestuário',
                    'Moda'
                ],
                description: 'Vestuário feminino e masculino, casacos, bolsas e calçados.'
            ),
            'lingerie' => new DepartmentDto(
                code: 'lingerie',
                name: 'Roupa Íntima & Pijamas',
                icon: '👙',
                aliExpressCategoryIds: [200574005, 200000851, 200000858, 200000780, 200000778, 200000779, 200000781],
                databaseCategories: [
                    'Roupa Íntima & Pijamas',
                    'Moda Íntima, Lingeries & Pijamas',
                    'Roupa íntima e pijamas',
                    'Lingerie',
                    'Lingeries',
                    'Moda Íntima',
                    'Pijamas'
                ],
                description: 'Lingeries, pijamas, sutiãs, calcinhas, meias-calça e cuecas.'
            ),
            'fitness' => new DepartmentDto(
                code: 'fitness',
                name: 'Moda Fitness, Leggings & Academia',
                icon: '🏃‍♀️',
                aliExpressCategoryIds: [200000785, 200000349, 200000345, 200000780],
                databaseCategories: [
                    'Moda Fitness, Leggings & Academia',
                    'Moda Fitness',
                    'Fitness',
                    'Academia'
                ],
                description: 'Tops de sustentação, calças legging suplex e roupas de academia.'
            ),
            'tech' => new DepartmentDto(
                code: 'tech',
                name: 'Eletrônicos & Telefonia',
                icon: '📱',
                aliExpressCategoryIds: [44, 509, 7],
                databaseCategories: [
                    'Eletrônicos & Telefonia',
                    'Eletrônicos & Gadget',
                    'Eletrônicos de consumo',
                    'Eletrônicos',
                    'Áudio',
                    'Audio',
                    'Fones Bluetooth TWS'
                ],
                description: 'Fones sem fio, caixas de som, carregadores e acessórios tech.'
            ),
            'mobile_tv' => new DepartmentDto(
                code: 'mobile_tv',
                name: 'Smartphones, TVs & Projetores 4K',
                icon: '📺',
                aliExpressCategoryIds: [509, 44, 7],
                databaseCategories: [
                    'Smartphones, TVs & Projetores 4K',
                    'Smartphones',
                    'Celulares',
                    'Telefones e telecomunicações'
                ],
                description: 'Celulares Xiaomi/POCO, mini projetores 4K e TV boxes.'
            ),
            'smartwatch' => new DepartmentDto(
                code: 'smartwatch',
                name: 'Relógios & Smartwatches',
                icon: '⌚',
                aliExpressCategoryIds: [1511],
                databaseCategories: [
                    'Relógios & Smartwatches',
                    'Relógios',
                    'Smartwatches & Áudio',
                    'Smartwatches'
                ],
                description: 'Smartwatches esportivos, pulseiras inteligentes e relógios.'
            ),
            'home' => new DepartmentDto(
                code: 'home',
                name: 'Casa, Iluminação & Eletro',
                icon: '🏠',
                aliExpressCategoryIds: [15, 39, 6, 13],
                databaseCategories: [
                    'Casa, Iluminação & Eletro',
                    'Casa Inteligente & Organização',
                    'Casa e iluminação',
                    'Casa e jardim'
                ],
                description: 'Iluminação inteligente, fitas LED, organizadores e itens de casa.'
            ),
            'tools' => new DepartmentDto(
                code: 'tools',
                name: 'Ferramentas & Utilidades',
                icon: '🛠️',
                aliExpressCategoryIds: [1420, 30],
                databaseCategories: [
                    'Ferramentas & Utilidades',
                    'Ferramentas',
                    'Segurança e proteção',
                    'Acessórios Virais & Ferramentas'
                ],
                description: 'Alicates, kits de chave de fenda, crimpadores e ferramentas de precisão.'
            ),
            'beauty' => new DepartmentDto(
                code: 'beauty',
                name: 'Beleza, Saúde & Maquiagem',
                icon: '✨',
                aliExpressCategoryIds: [66, 660103],
                databaseCategories: [
                    'Beleza, Saúde & Maquiagem',
                    'Beleza, Cuidados & Estilo',
                    'Beleza e saúde',
                    'Saúde & Cuidados',
                    'Maquiagem'
                ],
                description: 'Secadores, escovas alisadoras, maquiagens e cuidados pessoais.'
            ),
            'pets' => new DepartmentDto(
                code: 'pets',
                name: 'Pet Shop',
                icon: '🐾',
                aliExpressCategoryIds: [100006664],
                databaseCategories: [
                    'Pet Shop',
                    'Pet Shop & Cuidados Animais',
                    'Pet Shop & Pets Virais'
                ],
                description: 'Acessórios, coleiras, brinquedos e caminhas para pets.'
            ),
        ];
    }

    public function getDepartmentByCode(string $code): ?DepartmentDto
    {
        $code = strtolower(trim($code));
        $departments = $this->getAllDepartments();
        return $departments[$code] ?? null;
    }

    public function getDepartmentByName(string $name): ?DepartmentDto
    {
        $clean = mb_strtolower(trim($name), 'UTF-8');
        if ($clean === '') {
            return null;
        }

        $departments = $this->getAllDepartments();

        // 1. Match exato pelo código ou nome oficial
        foreach ($departments as $code => $dto) {
            if ($code === $clean || mb_strtolower($dto->name, 'UTF-8') === $clean) {
                return $dto;
            }
        }

        // 2. Match pelas categorias de banco mapeadas
        foreach ($departments as $dto) {
            foreach ($dto->databaseCategories as $dbCat) {
                if (mb_strtolower($dbCat, 'UTF-8') === $clean) {
                    return $dto;
                }
            }
        }

        // 3. Mapeamentos inequívocos de alta precisão (sem vazamento)
        if (str_contains($clean, 'automotiv') || str_contains($clean, 'veicular') || str_contains($clean, 'veículo') || str_contains($clean, 'veiculo') || (str_contains($clean, 'peças') && str_contains($clean, 'acessórios') && !str_contains($clean, 'ferramenta'))) {
            return $departments['auto'];
        }
        if (str_contains($clean, 'lingerie') || str_contains($clean, 'íntima') || str_contains($clean, 'intima') || str_contains($clean, 'pijama')) {
            return $departments['lingerie'];
        }
        if (str_contains($clean, 'fitness') || str_contains($clean, 'legging') || str_contains($clean, 'academia')) {
            return $departments['fitness'];
        }
        if (str_contains($clean, 'roupa') || str_contains($clean, 'vestuário') || str_contains($clean, 'vestuario') || str_contains($clean, 'moda') || str_contains($clean, 'bolsa') || str_contains($clean, 'calçado') || str_contains($clean, 'calcado') || str_contains($clean, 'sapato')) {
            return $departments['fashion'];
        }
        if (str_contains($clean, 'celular') || str_contains($clean, 'smartphone') || str_contains($clean, 'projetor') || str_contains($clean, 'tv box')) {
            return $departments['mobile_tv'];
        }
        if (str_contains($clean, 'relogio') || str_contains($clean, 'relógio') || str_contains($clean, 'smartwatch')) {
            return $departments['smartwatch'];
        }
        if (str_contains($clean, 'ferramenta') || str_contains($clean, 'alicate') || str_contains($clean, 'crimpador')) {
            return $departments['tools'];
        }
        if (str_contains($clean, 'pet') || str_contains($clean, 'cachorro') || str_contains($clean, 'gato')) {
            return $departments['pets'];
        }
        if (str_contains($clean, 'beleza') || str_contains($clean, 'maquiagem') || str_contains($clean, 'saúde') || str_contains($clean, 'saude')) {
            return $departments['beauty'];
        }
        if (str_contains($clean, 'casa') || str_contains($clean, 'iluminação') || str_contains($clean, 'iluminacao') || str_contains($clean, 'eletro')) {
            return $departments['home'];
        }
        if (str_contains($clean, 'eletron') || str_contains($clean, 'telefonia') || str_contains($clean, 'áudio') || str_contains($clean, 'audio')) {
            return $departments['tech'];
        }

        return null;
    }

    public function getDatabaseCategoriesFor(string $departmentOrCategory): array
    {
        $dept = $this->getDepartmentByCode($departmentOrCategory) ?? $this->getDepartmentByName($departmentOrCategory);
        if ($dept !== null) {
            return $dept->databaseCategories;
        }

        return [$departmentOrCategory];
    }

    public function toDepartmentJson(): string
    {
        $list = array_values(array_map(fn(DepartmentDto $d) => $d->toArray(), $this->getAllDepartments()));
        return json_encode($list, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';
    }

    /**
     * Constrói cláusula SQL estrita para filtragem na vitrine (Zero-Contaminação entre departamentos).
     *
     * Elimina cláusulas frouxas de 'OR title LIKE' que antes vazavam itens de vestuário
     * para peças automotivas ou ferramentas. Utiliza placeholders parametrizados.
     */
    public function buildSqlFilter(string $requestedCategory, array &$params): string
    {
        $raw = trim($requestedCategory);
        if ($raw === '') {
            return '1=1';
        }

        $dept = $this->getDepartmentByCode($raw) ?? $this->getDepartmentByName($raw);
        if ($dept !== null) {
            $allowed = $dept->databaseCategories;
            $clauses = [];
            foreach ($allowed as $catName) {
                $clauses[] = "(category = ? OR LOWER(category) = ?)";
                $params[] = $catName;
                $params[] = mb_strtolower(trim($catName), 'UTF-8');
            }
            $clauseStr = implode(' OR ', $clauses);
            return "(({$clauseStr}) AND LOWER(category) NOT IN ('geral', 'default', ''))";
        }

        // Fallback parametrizado estrito por categoria exata
        $params[] = $raw;
        $params[] = mb_strtolower($raw, 'UTF-8');
        return "((category = ? OR LOWER(category) = ?) AND LOWER(category) NOT IN ('geral', 'default', ''))";
    }

    /**
     * Retorna a lista de categorias irmãs e sinônimos pertencentes à mesma família/nicho semântico.
     * Garante Zero-Contaminação entre departamentos distintos (ex: Moda jamais mistura com Eletrônicos ou Pet).
     *
     * @return array<int, string>
     */
    public function getFamilyCategories(string $category, string $title = ''): array
    {
        $combined = trim($category . ' ' . $title);
        $clean = mb_strtolower(preg_replace('/[^\p{L}\p{N}\s&]/u', ' ', $combined) ?? $combined, 'UTF-8');

        // 1. Família MODA / VESTUÁRIO / CALÇADOS / FITNESS / LINGERIE / TOPS
        $fashionTerms = [
            'moda', 'roupa', 'vestuário', 'vestuario', 'vestido', 'calça', 'calca', 'camisa',
            'camiseta', 'short', 'lingerie', 'fitness', 'legging', 'biquini', 'maiô', 'calçado',
            'calcado', 'sapato', 'tênis', 'tenis', 'sandália', 'sandalia', 'cueca', 'sutiã',
            'sutia', 'jaqueta', 'casaco', 'bolsa', 'feminina', 'feminino', 'masculina', 'masculino',
            'intima', 'íntima', 'top', 'tops', 'academia', 'suplex', 'macaquinho', 'cropped',
            'regata', 'body', 'saia', 'bermuda'
        ];
        foreach ($fashionTerms as $term) {
            if (str_contains($clean, $term)) {
                return [
                    'Moda, Roupas & Vestuário',
                    'Moda Íntima & Lingerie',
                    'Moda Fitness, Leggings & Academia',
                    'Roupas, Bolsas & Calçados',
                    'Roupa Íntima & Pijamas',
                    'Moda Feminina',
                    'Moda Masculina',
                    'Vestuário',
                    'Calçados',
                    'Roupas femininas',
                    'Roupas masculinas',
                    'Fitness',
                    'Academia',
                ];
            }
        }

        // 2. Família ELETRÔNICOS / SMARTPHONES / SMARTWATCH / GADGETS / ÁUDIO / TV
        $techTerms = [
            'eletrônico', 'eletronico', 'celular', 'smartphone', 'smartwatch', 'relógio',
            'relogio', 'fone', 'bluetooth', 'tws', 'headphone', 'projetor', 'tv', 'tv box',
            'carregador', 'cabo', 'gamer', 'computador', 'teclado', 'mouse', 'áudio', 'audio',
            'telefonia', 'gadget', 'drone', 'câmera', 'camera'
        ];
        foreach ($techTerms as $term) {
            if (str_contains($clean, $term)) {
                return [
                    'Eletrônicos & Gadget',
                    'Eletrônicos & Telefonia',
                    'Smartphones, TVs & Projetores 4K',
                    'Relógios & Smartwatches',
                    'Telefones e telecomunicações',
                    'Áudio',
                    'Fones Bluetooth TWS',
                ];
            }
        }

        // 3. Família PET SHOP / CUIDADOS ANIMAIS
        $petTerms = ['pet', 'cão', 'cao', 'gato', 'cachorro', 'animal', 'coleira', 'aquário', 'aquario', 'ração', 'racao'];
        foreach ($petTerms as $term) {
            if (str_contains($clean, $term)) {
                return [
                    'Pet Shop & Cuidados Animais',
                    'Pet Shop',
                    'Animais de Estimação',
                ];
            }
        }

        // 4. Família PEÇAS & ACESSÓRIOS AUTOMOTIVOS
        $autoTerms = ['automotivo', 'carro', 'moto', 'veículo', 'veiculo', 'pneu', 'farol', 'retrovisor', 'auto'];
        foreach ($autoTerms as $term) {
            if (str_contains($clean, $term)) {
                return [
                    'Peças & Acessórios Automotivos',
                    'Automóveis e Motocicletas',
                    'Acessórios para Veículos',
                ];
            }
        }

        // 5. Família BELEZA, SAÚDE & BEM-ESTAR
        $beautyTerms = ['beleza', 'saúde', 'saude', 'maquiagem', 'batom', 'cabelo', 'secador', 'depilador', 'pele', 'skin', 'massagem', 'perfume'];
        foreach ($beautyTerms as $term) {
            if (str_contains($clean, $term)) {
                return [
                    'Beleza, Saúde & Bem-Estar',
                    'Beleza, Saúde & Maquiagem',
                    'Beleza e saúde',
                ];
            }
        }

        // 6. Família CASA, ILUMINAÇÃO & CONSTRUÇÃO
        $homeTerms = ['casa', 'iluminação', 'iluminacao', 'cozinha', 'ferramenta', 'furadeira', 'decoração', 'decoracao', 'organização', 'organizacao', 'construção', 'construcao'];
        foreach ($homeTerms as $term) {
            if (str_contains($clean, $term)) {
                return [
                    'Casa Inteligente & Organização',
                    'Casa, Iluminação & Eletro',
                    'Ferramentas & Construção',
                    'Ferramentas & Utilidades',
                    'Casa e jardim',
                ];
            }
        }

        return [$category];
    }
}
