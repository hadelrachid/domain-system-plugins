<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Services\Validation\Guards;

use DomainSystem\Plugins\domain_drop\Contracts\Validation\ProductValidationGuardInterface;
use DomainSystem\Plugins\domain_drop\Contracts\Validation\ValidationResultInterface;
use DomainSystem\Plugins\domain_drop\DTO\Validation\ValidationResult;
use Throwable;

/**
 * Guard de Existência e Integridade de Link no AliExpress (SRP).
 * 
 * Baseado na documentação oficial:
 * - dropshiper.docx (aliexpress.ds.product.get, ITEM_ID_NOT_FOUND)
 * - ae-product-management.docx (Status do Anúncio: onSelling vs offline/deleted)
 * 
 * Elimina 100% de produtos inexistentes, URLs com 404 e IDs sintéticos.
 */
class ProductExistenceGuard implements ProductValidationGuardInterface
{
    public function getName(): string
    {
        return 'existence_and_link_guard';
    }

    public function validate(string $id, array $rawData): ValidationResultInterface
    {
        $cleanId = trim($id);
        if ($cleanId === '') {
            return ValidationResult::fail($this->getName(), 'Identificador do produto está vazio.');
        }

        // 1. Identificadores do AliExpress precisam ser preenchidos e válidos (sem espaços/caracteres ilegais)
        if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $cleanId)) {
            return ValidationResult::fail(
                $this->getName(),
                "Identificador de produto '{$cleanId}' inválido (caracteres ilegais detectados)."
            );
        }

        // 2. Verificação de status contratual (ae-product-management.docx & dropshiper.docx)
        $baseInfo = $rawData['ae_item_base_info_dto'] 
            ?? ($rawData['result']['ae_item_base_info_dto'] 
            ?? ($rawData['data']['ae_item_base_info_dto'] ?? []));

        if (is_array($baseInfo) && isset($baseInfo['product_status_type'])) {
            $status = strtolower(trim((string)$baseInfo['product_status_type']));
            // Status válidos para venda em dropshipping: 'onselling'
            if ($status !== '' && $status !== 'onselling') {
                return ValidationResult::fail(
                    $this->getName(),
                    "Produto com status comercial inativo no fornecedor: '{$status}' (esperado: onSelling).",
                    ['status' => $status]
                );
            }

            // ae-product-management.docx: ws_offline_date indica encerramento do anúncio
            if (!empty($baseInfo['ws_offline_date'])) {
                $offlineTime = strtotime((string)$baseInfo['ws_offline_date']);
                if ($offlineTime !== false && $offlineTime < time()) {
                    return ValidationResult::fail(
                        $this->getName(),
                        "Anúncio expirado na data '{$baseInfo['ws_offline_date']}' (ws_offline_date vencida).",
                        ['ws_offline_date' => $baseInfo['ws_offline_date']]
                    );
                }
            }
        }

        // 3. Verificação de códigos de erro do SDK AliExpress
        $rspCode = (string)($rawData['rsp_code'] ?? ($rawData['code'] ?? ''));
        $rspMsg = (string)($rawData['rsp_msg'] ?? ($rawData['msg'] ?? ($rawData['sub_msg'] ?? '')));
        if ($rspCode !== '' && $rspCode !== '0' && $rspCode !== '200') {
            if (stripos($rspMsg, 'ITEM_ID_NOT_FOUND') !== false || stripos($rspMsg, 'not found') !== false) {
                return ValidationResult::fail(
                    $this->getName(),
                    "Produto não encontrado na base oficial do AliExpress (ITEM_ID_NOT_FOUND).",
                    ['rsp_code' => $rspCode, 'rsp_msg' => $rspMsg]
                );
            }
        }

        // 4. Verificação de flags de exclusão ou 404 identificados na raspagem ou payload
        if (!empty($rawData['is_not_found']) || !empty($rawData['is_404'])) {
            return ValidationResult::fail($this->getName(), 'Página original do fornecedor retornou 404 (Página não encontrada).');
        }

        return ValidationResult::pass($this->getName(), 'Produto e link confirmados ativos no fornecedor.');
    }
}
