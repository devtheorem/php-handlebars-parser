<?php

/**
 * Parser benchmark script. Default iterations: 1000.
 *
 * Usage: php -d opcache.enable_cli=1 -d opcache.jit=tracing tests/benchmark.php
 */

use DevTheorem\HandlebarsParser\ParserFactory;

require __DIR__ . '/../vendor/autoload.php';

$iterations = (int) ($argv[1] ?? 1000);
$filename = __DIR__ . "/largeTemplate.hbs";

// A large, complex template exercising as many syntax features as possible.
$template = file_get_contents($filename);
if ($template === false) {
    exit("Failed to open $filename");
}

$parser = (new ParserFactory())->create();

// Warm up: give the JIT a chance to compile hot paths before we measure.
for ($i = 0; $i < 50; $i++) {
    $parser->parse($template);
}

$start = hrtime(true);

for ($i = 0; $i < $iterations; $i++) {
    $parser->parse($template);
}

$elapsed = (hrtime(true) - $start) / 1e9;
$perParse = $elapsed / $iterations * 1000;
$templateBytes = strlen($template);

printf(
    "Parsed %d times in %.3f s  |  %.3f ms/parse  |  %.1f KB template\n",
    $iterations,
    $elapsed,
    $perParse,
    $templateBytes / 1024,
);
