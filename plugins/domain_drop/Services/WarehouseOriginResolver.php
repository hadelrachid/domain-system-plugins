<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Services;

use DomainSystem\Plugins\domain_drop\Contracts\WarehouseOriginResolverInterface;

/**
 * Resolvedor e Classificador de Procedência Logística e Entrepostos (SRP / DIP).
 * 
 * Atende às diretrizes das auditorias técnicas:
 * - Elimina a confusão entre país cadastral da loja (store_country_code) e país físico do estoque.
 * - Detecta as tags oficiais de Local Supply da Alibaba (ex: 'local_flag' em sellingPoints e log_point).
 * - Suporta 3 estados oficiais: BR, CN e UNKNOWN (sem converter ausência de dados em China).
 */
class WarehouseOriginResolver implements WarehouseOriginResolverInterface
{
    public function resolve(array $rawItem, ?string $title = null): string
    {
        // 1. Tags explícitas de armazém previamente auditadas
        if (!empty($rawItem['origem_brasil']) || !empty($rawItem['entreposto_brasil'])) {
            return self::ORIGIN_BR;
        }

        // 2. Campo de expedição física (ship_from / send_goods_country_code / delivery.shippingFrom)
        $shipFrom = strtoupper(trim((string)(
            $rawItem['ship_from']
            ?? $rawItem['send_goods_country_code']
            ?? ($rawItem['delivery']['shippingFrom'] ?? '')
        )));

        if (in_array($shipFrom, ['BR', 'BRAZIL', 'BRASIL'], true)) {
            return self::ORIGIN_BR;
        }

        // 3. Inspeção de tags oficiais do AliExpress (Local Supply / ASF / local_flag)
        if (!empty($rawItem['sellingPoints']) && is_array($rawItem['sellingPoints'])) {
            foreach ($rawItem['sellingPoints'] as $sp) {
                if (!is_array($sp)) {
                    continue;
                }
                $source = strtolower(trim((string)($sp['source'] ?? '')));
                $tagId = strtolower(trim((string)($sp['sellingPointTagId'] ?? '')));
                if ($source === 'local_flag' || $tagId === 'local_flag') {
                    return self::ORIGIN_BR;
                }

                $tagText = mb_strtolower(trim((string)($sp['tagContent']['tagText'] ?? '')), 'UTF-8');
                if ($tagText !== '') {
                    if (str_contains($tagText, 'brasil') 
                        || str_contains($tagText, 'nacional') 
                        || str_contains($tagText, 'estoque no brasil') 
                        || str_contains($tagText, 'enviado do brasil') 
                        || str_contains($tagText, '3 a 7 dias')
                    ) {
                        return self::ORIGIN_BR;
                    }
                }
            }
        }

        // 4. Verificação de log_point de rastreio de interface
        $logPoint = is_array($rawItem['log_point'] ?? null)
            ? (string)json_encode($rawItem['log_point'])
            : (string)($rawItem['log_point'] ?? '');
        if ($logPoint !== '' && str_contains($logPoint, 'local_flag')) {
            return self::ORIGIN_BR;
        }

        // 5. Verificação de tagList estruturada
        if (!empty($rawItem['tagList']) && is_array($rawItem['tagList'])) {
            foreach ($rawItem['tagList'] as $t) {
                $tagStr = is_string($t) ? $t : (string)($t['tag'] ?? '');
                if (stripos($tagStr, 'LOCAL_DELIVERY') !== false 
                    || stripos($tagStr, 'DESPACHO_BRASIL') !== false
                    || stripos($tagStr, 'ENTREGA_LOCAL_BR') !== false
                ) {
                    return self::ORIGIN_BR;
                }
            }
        }

        // 6. Verificação no título (se fornecido)
        $rawTitleCandidate = $title;
        if (empty($rawTitleCandidate)) {
            if (isset($rawItem['title'])) {
                if (is_array($rawItem['title'])) {
                    $rawTitleCandidate = (string)($rawItem['title']['displayTitle'] ?? ($rawItem['title']['title'] ?? ''));
                } else {
                    $rawTitleCandidate = (string)$rawItem['title'];
                }
            } elseif (isset($rawItem['product_title'])) {
                $rawTitleCandidate = (string)$rawItem['product_title'];
            }
        }
        $cleanTitle = mb_strtolower(trim((string)$rawTitleCandidate), 'UTF-8');
        if ($cleanTitle !== '') {
            if (str_contains($cleanTitle, 'estoque brasil')
                || str_contains($cleanTitle, 'estoque no brasil')
                || str_contains($cleanTitle, 'envio do brasil')
                || str_contains($cleanTitle, 'pronta entrega')
                || str_contains($cleanTitle, 'nacional brasil')
                || str_contains($cleanTitle, 'armazem brasil')
                || str_contains($cleanTitle, 'armazém brasil')
            ) {
                return self::ORIGIN_BR;
            }
        }

        // 7. Confirmação de Entreposto Central China (Cross-Border)
        if (in_array($shipFrom, ['CN', 'CHINA'], true) 
            || !empty($rawItem['origem_china']) 
            || !empty($rawItem['remessa_conforme'])
            || stripos($logPoint, 'cross_border') !== false
            || stripos($logPoint, 'choice') !== false
        ) {
            return self::ORIGIN_CN;
        }

        // 8. Se a expedição for de outro país documentado (ex: US, ES, FR)
        if ($shipFrom !== '' && strlen($shipFrom) === 2 && $shipFrom !== 'BR' && $shipFrom !== 'CN') {
            return $shipFrom;
        }

        return self::ORIGIN_UNKNOWN;
    }

    public function isShippedFromBrazil(array $rawItem, ?string $title = null): bool
    {
        return $this->resolve($rawItem, $title) === self::ORIGIN_BR;
    }
}
