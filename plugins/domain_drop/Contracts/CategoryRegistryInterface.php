<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Contracts;

use DomainSystem\Plugins\domain_drop\DTO\DepartmentDto;

/**
 * Contrato Oficial do Registro de Departamentos e Categorias (SOLID / ISP / DIP).
 *
 * Provê o contrato formal para o ecossistema PHP e a camada JavaScript,
 * garantindo isolamento absoluto de dados entre departamentos (Zero-Contaminação).
 */
interface CategoryRegistryInterface
{
    /**
     * Retorna todos os departamentos oficiais registrados.
     *
     * @return array<string, DepartmentDto> Indexado pelo código canônico (ex: 'auto', 'fashion', etc.)
     */
    public function getAllDepartments(): array;

    /**
     * Busca um departamento pelo seu código canônico único.
     */
    public function getDepartmentByCode(string $code): ?DepartmentDto;

    /**
     * Busca um departamento por qualquer nome comercial, sinônimo ou termo legado.
     */
    public function getDepartmentByName(string $name): ?DepartmentDto;

    /**
     * Retorna a lista de categorias do banco de dados autorizadas para o departamento solicitado.
     *
     * @return array<int, string>
     */
    public function getDatabaseCategoriesFor(string $departmentOrCategory): array;

    /**
     * Retorna o JSON estruturado dos departamentos para injeção limpa no frontend.
     */
    public function toDepartmentJson(): string;
}
