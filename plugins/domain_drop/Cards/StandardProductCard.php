<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Cards;

/**
 * Especialização de Card Padrão para Utilidades, Ferramentas, Decoração e Casa.
 */
class StandardProductCard extends AbstractProductCard
{
    protected function getCardCssClass(): string
    {
        return 'card-product-standard';
    }
}
