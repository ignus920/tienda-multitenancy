<?php
$content = file_get_contents('C:\Users\GAMING\.gemini\antigravity-ide\brain\d32275a9-9139-4cc7-9068-d9c5c18b952b\.system_generated\steps\741\content.md');

// Buscar bloques JSON en la documentación
preg_match_all('/\{[^{}]*"(?:type|items|category|price|quantity|cost|unitCost)"[^{}]*\}/s', $content, $matches);

echo "📋 BLOQUES JSON O PARÁMETROS ENCONTRADOS EN LA DOCUMENTACIÓN:\n";
echo "==================================================\n";
if (!empty($matches[0])) {
    foreach (array_slice($matches[0], 0, 10) as $idx => $match) {
        echo "Ejemplo " . ($idx + 1) . ":\n";
        echo strip_tags($match) . "\n\n";
    }
} else {
    // Si no encuentra por regex, buscar texto plano relacionado con campos
    echo "No se encontraron estructuras JSON directas por regex. Buscando fragmentos del texto...\n";
    $lines = explode("\n", strip_tags($content));
    $found = 0;
    foreach ($lines as $num => $line) {
        $line = trim($line);
        if (empty($line)) continue;
        if (strpos($line, 'cost') !== false || strpos($line, 'type') !== false || strpos($line, 'adjustment') !== false || strpos($line, 'value') !== false) {
            echo "Línea " . ($num + 1) . ": " . $line . "\n";
            $found++;
            if ($found > 30) break;
        }
    }
}
echo "==================================================\n";
