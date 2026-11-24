<?php

declare(strict_types=1);

/**
 * Domain System Plugins - Sync Tool
 *
 * Sincroniza o código-fonte de um ou todos os plugins de agregação
 * a partir do repositório principal (domain-system) para este repositório,
 * e em seguida executa o empacotamento oficial (build-plugins.php).
 *
 * Uso:
 *   php tools/sync-plugin.php domain_drop
 *   php tools/sync-plugin.php all
 */

$pluginsRepoDir = dirname(__DIR__);
$corePluginsDir = dirname($pluginsRepoDir) . '/domain-system/src/Plugins';

if (!is_dir($corePluginsDir)) {
    fwrite(STDERR, "❌ Erro: Diretório fonte não encontrado em '{$corePluginsDir}'.\n");
    exit(1);
}

$target = $argv[1] ?? null;
if (!$target) {
    echo "Uso:\n";
    echo "  php tools/sync-plugin.php <plugin_slug>\n";
    echo "  php tools/sync-plugin.php all\n\n";
    echo "Exemplo: php tools/sync-plugin.php domain_drop\n";
    exit(1);
}

$pluginsToSync = [];
if (strtolower($target) === 'all') {
    $entries = scandir($pluginsRepoDir . '/plugins') ?: [];
    foreach ($entries as $e) {
        if ($e !== '.' && $e !== '..' && is_dir($pluginsRepoDir . '/plugins/' . $e)) {
            $pluginsToSync[] = $e;
        }
    }
} else {
    $pluginsToSync[] = trim($target);
}

function recursiveCopy(string $src, string $dst): void
{
    $dir = opendir($src);
    if (!is_dir($dst)) {
        mkdir($dst, 0755, true);
    }

    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        // Ignora arquivos temporários, logs e caches locais
        if (str_ends_with($file, '.tmp') || str_ends_with($file, '.log') || str_ends_with($file, '.lock') || $file === '.DS_Store' || $file === 'Thumbs.db') {
            continue;
        }

        $srcPath = $src . '/' . $file;
        $dstPath = $dst . '/' . $file;

        if (is_dir($srcPath)) {
            recursiveCopy($srcPath, $dstPath);
        } else {
            copy($srcPath, $dstPath);
        }
    }

    closedir($dir);
}

function recursiveDelete(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    $files = array_diff(scandir($dir) ?: [], ['.', '..']);
    foreach ($files as $file) {
        $p = $dir . '/' . $file;
        is_dir($p) ? recursiveDelete($p) : unlink($p);
    }
    rmdir($dir);
}

foreach ($pluginsToSync as $plugin) {
    $srcFolder = $corePluginsDir . '/' . $plugin;
    $dstFolder = $pluginsRepoDir . '/plugins/' . $plugin;

    if (!is_dir($srcFolder)) {
        fwrite(STDERR, "⚠️  Plugin '{$plugin}' não encontrado no core em '{$srcFolder}'. Pulando.\n");
        continue;
    }

    echo "🔄 Sincronizando '{$plugin}' do Core...\n";

    // Limpa a pasta destino para garantir remoção de arquivos obsoletos
    if (is_dir($dstFolder)) {
        recursiveDelete($dstFolder);
    }

    recursiveCopy($srcFolder, $dstFolder);

    $manifest = $dstFolder . '/plugin.json';
    $version = 'Desconhecida';
    if (is_file($manifest)) {
        $json = json_decode((string)file_get_contents($manifest), true);
        $version = $json['version'] ?? 'Desconhecida';
    }

    echo "✅ '{$plugin}' sincronizado com sucesso! (Versão: v{$version})\n";
}

echo "\n📦 Executando empacotamento oficial (build-plugins.php)...\n";
require __DIR__ . '/build-plugins.php';
