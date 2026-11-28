<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\DTO;

/**
 * DTO Imutável de Departamento / Categoria Canônica (Clean Architecture / Contract).
 *
 * Estabelece o contrato de dados estrito entre o backend PHP e o frontend JavaScript,
 * eliminando vazamento de dados, colisões de substring e acoplamento desordenado.
 */
class DepartmentDto
{
    /**
     * @param string $code Identificador canônico único em kebab-case (ex: 'auto', 'fashion', 'lingerie')
     * @param string $name Nome comercial oficial para exibição (ex: 'Peças & Acessórios Automotivos')
     * @param string $icon Emoji representativo do departamento (ex: '🚗')
     * @param array<int, int> $aliExpressCategoryIds IDs oficiais na árvore do AliExpress (ex: [34, 201355758])
     * @param array<int, string> $databaseCategories Lista exata e fechada de strings de categoria no banco de dados
     * @param string $description Breve descrição do departamento
     */
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly string $icon,
        public readonly array $aliExpressCategoryIds,
        public readonly array $databaseCategories,
        public readonly string $description = ''
    ) {
    }

    /**
     * Serializa o contrato para consumo em APIs REST e injeção no JavaScript.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'code'               => $this->code,
            'name'               => $this->name,
            'icon'               => $this->icon,
            'ali_ids'            => $this->aliExpressCategoryIds,
            'db_categories'      => $this->databaseCategories,
            'description'        => $this->description,
        ];
    }
}
