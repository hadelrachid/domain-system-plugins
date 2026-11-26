<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Services\Assembly\Stations;

use DomainSystem\Plugins\domain_drop\Contracts\Assembly\AssemblyStationInterface;
use DomainSystem\Plugins\domain_drop\Contracts\Assembly\ProductAssemblyContextInterface;

/**
 * Estação 4: Higienização e Validação de Mídia
 *
 * Garante que apenas produtos com fotografias reais e válidas sigam na esteira.
 * Proíbe estritamente a geração de imagens sintéticas ou mascaramento de 404.
 * Documentação de referência: ae-image.pdf
 */
class MediaSanitizationStation implements AssemblyStationInterface
{
    public function getName(): string
    {
        return 'MediaSanitization';
    }

    public function process(ProductAssemblyContextInterface $context): ProductAssemblyContextInterface
    {
        $mainImage = trim((string)$context->get('main_image', ''));
        if ($mainImage === '') {
            $mainImage = trim((string)$context->get('image_url', ''));
        }

        if ($mainImage === '') {
            return $context->abort($this->getName(), 'Produto sem imagem principal autêntica');
        }

        // Validação básica de formato e esquema de URL
        if (!filter_var($mainImage, FILTER_VALIDATE_URL)) {
            return $context->abort($this->getName(), 'URL da imagem principal inválida ou malformada');
        }

        // Rejeita placeholders conhecidos ou imagens sintéticas
        if (str_contains($mainImage, 'default_') && str_contains($mainImage, '.jpg')) {
            return $context->abort($this->getName(), 'Imagem sintética/placeholder rejeitada pelo controle de qualidade');
        }

        // Galeria adicional
        $images = $context->get('images', []);
        $validImages = [$mainImage];

        if (is_array($images)) {
            foreach ($images as $img) {
                $imgUrl = trim((string)$img);
                if ($imgUrl !== '' && $imgUrl !== $mainImage && filter_var($imgUrl, FILTER_VALIDATE_URL)) {
                    $validImages[] = $imgUrl;
                }
            }
        }

        $context->set('main_image', $mainImage);
        $context->set('image_url', $mainImage);
        $context->set('images', array_unique($validImages));

        return $context->recordStationPass($this->getName(), [
            'total_valid_images' => count($validImages),
            'main_image' => $mainImage
        ]);
    }
}
