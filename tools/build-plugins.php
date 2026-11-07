<?php

declare(strict_types=1);

/**
 * Domain System Plugins - Build & Package Tool
 *
 * Itera por cada plugin em /plugins, valida o manifesto plugin.json,
 * gera os pacotes ZIP em /dist e atualiza o catálogo marketplace.json.
 *
 * Uso:
 *   php tools/build-plugins.php
 */

$rootDir = dirname(__DIR__);
$pluginsDir = $rootDir . '/plugins';
$distDir = $rootDir . '/dist';
$marketplaceFile = $rootDir . '/marketplace.json';

if (!is_dir($pluginsDir)) {
    fwrite(STDERR, "❌ Erro: Diretório /plugins não encontrado.\n");
    exit(1);
}

if (!is_dir($distDir)) {
    mkdir($distDir, 0755, true);
}

$entries = scandir($pluginsDir) ?: [];
$marketplace = [
    'updated_at' => date('c'),
    'repository' => 'https://github.com/hadelrachid/domain-system-plugins',
    'plugins' => []
];

$successCount = 0;

foreach ($entries as $entry) {
    if ($entry === '.' || $entry === '..') {
        continue;
    }

    $pluginPath = $pluginsDir . '/' . $entry;
    if (!is_dir($pluginPath)) {
        continue;
    }

    $manifestPath = $pluginPath . '/plugin.json';
    if (!is_file($manifestPath)) {
        echo "⚠️  Pulando '{$entry}': plugin.json não encontrado.\n";
        continue;
    }

    $manifest = json_decode((string) file_get_contents($manifestPath), true);
    if (!is_array($manifest) || empty($manifest['name']) || empty($manifest['version'])) {
        echo "⚠️  Pulando '{$entry}': manifesto inválido.\n";
        continue;
    }

    $slug = $entry; // O slug é a pasta do plugin (ex: academy, commerce, seo)
    $name = $manifest['name'];
    $version = $manifest['version'];
    $osVersion = $manifest['os_version'] ?? '2.6';
    $title = $manifest['title'] ?? $name;
    $description = $manifest['description'] ?? '';

    $zipFilename = "{$slug}.zip";
    $zipPath = $distDir . '/' . $zipFilename;

    if (is_file($zipPath)) {
        unlink($zipPath);
    }

    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        fwrite(STDERR, "❌ Falha ao criar ZIP para '{$slug}'.\n");
        continue;
    }

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($pluginPath, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $file) {
        if (!$file->isFile()) {
            continue;
        }

        $filePath = $file->getRealPath();
        $relativeName = substr($filePath, strlen($pluginPath) + 1);
        $relativeName = str_replace('\\', '/', $relativeName);

        // Ignorar arquivos temporários e logs
        if (str_ends_with($relativeName, '.tmp') || str_ends_with($relativeName, '.log') || str_ends_with($relativeName, '.txt')) {
            continue;
        }

        $zip->addFile($filePath, $relativeName);
    }

    $zip->close();

    $sha256 = hash_file('sha256', $zipPath);
    file_put_contents($zipPath . '.sha256', $sha256);

    $marketplace['plugins'][$slug] = [
        'slug' => $slug,
        'name' => $name,
        'title' => $title,
        'version' => $version,
        'os_version' => $osVersion,
        'description' => $description,
        'zip_filename' => $zipFilename,
        'download_url' => "https://github.com/hadelrachid/domain-system-plugins/releases/latest/download/{$zipFilename}",
        'sha256' => $sha256,
        'size_bytes' => filesize($zipPath)
    ];

    echo "✅ Plugin '{$slug}' ({$name}) v{$version} empacotado: {$zipFilename} (" . number_format(filesize($zipPath) / 1024, 1) . " KB)\n";
    $successCount++;
}

file_put_contents($marketplaceFile, json_encode($marketplace, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");

echo "\n📦 Total de plugins empacotados: {$successCount}\n";
echo "📄 Catálogo atualizado em: {$marketplaceFile}\n";
