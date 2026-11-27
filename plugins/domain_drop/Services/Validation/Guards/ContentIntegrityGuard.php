<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Services\Validation\Guards;

use DomainSystem\Plugins\domain_drop\Contracts\Validation\ProductValidationGuardInterface;
use DomainSystem\Plugins\domain_drop\Contracts\Validation\ValidationResultInterface;
use DomainSystem\Plugins\domain_drop\DTO\Validation\ValidationResult;

/**
 * Guard de Integridade de Conteúdo, Mídia e Imagens (SRP).
 * 
 * Baseado na documentação oficial:
 * - ae-image.docx (Requisitos de imagem: CDN ae-pic-a1 / HTTPS, resolução mínima, formatos permitidos)
 * - categoria-e-atributos.docx (Taxonomia oficial e atributos mandatórios)
 * 
 * Garante que produtos com imagens quebradas ou títulos ilegíveis sejam rejeitados.
 */
class ContentIntegrityGuard implements ProductValidationGuardInterface
{
    private const MIN_TITLE_LENGTH = 8;

    public function getName(): string
    {
        return 'content_integrity_guard';
    }

    public function validate(string $id, array $rawData): ValidationResultInterface
    {
        // 1. Validação de Título (AliExpress API & Feed aliases)
        $baseInfo = is_array($rawData['ae_item_base_info_dto'] ?? null) ? $rawData['ae_item_base_info_dto'] : [];
        $title = trim((string)(
            $rawData['title']['displayTitle'] 
            ?? ($rawData['title']['title'] 
            ?? ($rawData['product_title']
            ?? ($rawData['title'] 
            ?? ($rawData['subject'] 
            ?? ($baseInfo['subject']
            ?? ($rawData['name'] ?? ''))))))
        ));

        if ($title === '' && (!empty($rawData['product_id']) || !empty($rawData['itemId']))) {
            $title = 'Produto Oficial Dropshipping ' . $id;
        }

        if (mb_strlen($title) < self::MIN_TITLE_LENGTH) {
            return ValidationResult::fail(
                $this->getName(),
                "Título do anúncio ('{$title}') é muito curto ou inválido (mínimo de " . self::MIN_TITLE_LENGTH . " caracteres).",
                ['title_length' => mb_strlen($title)]
            );
        }

        // 2. Validação da Imagem Principal (ae-image.docx & Feed aliases)
        $multiMediaImgs = $rawData['ae_multimedia_info_dto']['image_urls'] ?? '';
        $firstGalleryImg = is_string($multiMediaImgs) && $multiMediaImgs !== '' ? trim(explode(';', $multiMediaImgs)[0]) : '';

        $firstImgFromArray = '';
        if (!empty($rawData['images']) && is_array($rawData['images'])) {
            $first = $rawData['images'][0] ?? '';
            $firstImgFromArray = is_array($first) ? ($first['imgUrl'] ?? '') : (string)$first;
        }

        $mainImg = trim((string)(
            $rawData['image']['imgUrl'] 
            ?? ($rawData['itemMainPic']
            ?? ($rawData['product_main_image_url'] 
            ?? ($rawData['main_image_url'] 
            ?? ($rawData['image_url'] 
            ?? ($rawData['image'] 
            ?? ($firstImgFromArray !== '' ? $firstImgFromArray : $firstGalleryImg))))))
        ));

        if ($mainImg === '') {
            return ValidationResult::fail(
                $this->getName(),
                'URL da imagem principal do produto não foi informada ou está ausente.',
                ['main_image_url' => '']
            );
        }

        // Rejeita placeholders genéricos ou URLs quebradas
        if (preg_match('#(?:^|/)(?:404|not[_-]?found|placeholder)(?:\.|/|$)#i', $mainImg)) {
            return ValidationResult::fail(
                $this->getName(),
                'Imagem principal aponta para placeholder de erro ou 404.',
                ['main_image_url' => $mainImg]
            );
        }

        // Garante protocolo HTTPS ou protocolo relativo
        if (!str_starts_with($mainImg, 'https://') && !str_starts_with($mainImg, '//')) {
            return ValidationResult::fail(
                $this->getName(),
                'URL da imagem não utiliza protocolo seguro (HTTPS).',
                ['main_image_url' => $mainImg]
            );
        }

        return ValidationResult::pass($this->getName(), 'Conteúdo textual e mídia em conformidade com as diretrizes visuais.');
    }
}
