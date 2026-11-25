<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Services\Validation\Guards;

use DomainSystem\Plugins\domain_drop\Contracts\Validation\ProductValidationGuardInterface;
use DomainSystem\Plugins\domain_drop\Contracts\Validation\ValidationResultInterface;
use DomainSystem\Plugins\domain_drop\DTO\Validation\ValidationResult;

/**
 * Guard de Logística Regional e Elegibilidade para o Brasil (SRP).
 * 
 * Baseado na documentação oficial:
 * - dropshiper.docx (Erro: "Item is not allowed to this country", ship_to_country = BR)
 * - ae-freight-shipment.docx & aliexpress-direct-logistic.docx (Remessa Conforme e Envio Local)
 * 
 * Garante que produtos sem rota de entrega para o Brasil sejam descartados antes do catálogo.
 */
class RegionalLogisticsGuard implements ProductValidationGuardInterface
{
    public function getName(): string
    {
        return 'regional_logistics_guard';
    }

    public function validate(string $id, array $rawData): ValidationResultInterface
    {
        // 1. Mensagem explícita de bloqueio de país documentada em dropshiper.docx
        $rspMsg = (string)($rawData['rsp_msg'] ?? ($rawData['msg'] ?? ($rawData['sub_msg'] ?? '')));
        if (stripos($rspMsg, 'not allowed to this country') !== false) {
            return ValidationResult::fail(
                $this->getName(),
                'Produto possui venda proibida ou indisponível para o Brasil pelo fornecedor (Item is not allowed to this country).',
                ['reason' => $rspMsg]
            );
        }

        // 2. Verificação de restrições expressas de destino
        $shipTo = strtoupper((string)($rawData['ship_to_country'] ?? ($rawData['country_code'] ?? ($rawData['country'] ?? 'BR'))));
        if ($shipTo !== '' && $shipTo !== 'BR' && $shipTo !== 'GLOBAL' && $shipTo !== 'ALL') {
            return ValidationResult::fail(
                $this->getName(),
                "Produto configurado exclusivamente para destino '{$shipTo}', incompatível com o Brasil.",
                ['ship_to' => $shipTo]
            );
        }

        // 3. Verificação de frete / entrega disponível
        if (isset($rawData['has_shipping_to_destination']) && $rawData['has_shipping_to_destination'] === false) {
            return ValidationResult::fail(
                $this->getName(),
                'Nenhum método de envio viável disponível para o Brasil.',
                ['destination' => 'BR']
            );
        }

        // 4. Se houver informações de frete, verifica se o custo de frete é proibitivo
        $freightAmount = (float)($rawData['freight_amount'] ?? ($rawData['shipping_fee'] ?? 0.0));
        if ($freightAmount > 3000.0) {
            return ValidationResult::fail(
                $this->getName(),
                "Custo de frete abusivo detectado (R$ {$freightAmount}), incompatível com operação viável de dropshipping.",
                ['freight_amount' => $freightAmount]
            );
        }

        return ValidationResult::pass($this->getName(), 'Produto elegível para envio prioritário com rastreamento para o Brasil.');
    }
}
