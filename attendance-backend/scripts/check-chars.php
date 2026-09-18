<?php // check-chars.php — hitung char non-ASCII & sisa signature mojibake
foreach (array_slice($argv, 1) as $f) {
    $t = file_get_contents($f);
    preg_match_all('/[\x{80}-\x{10FFFF}]/u', $t, $m);
    $counts = [];
    foreach (array_unique($m[0]) as $ch) {
        $code = mb_ord($ch, 'UTF-8');
        // signature mojibake: leading byte CP1252 sbg char
        if (in_array($code, [0xC2, 0xC3, 0xE2, 0xF0], true)) {
            echo "  !! MOJIBAKE CHAR U+" . strtoupper(dechex($code)) . " di $f\n";
        }
        $counts[sprintf('U+%04X', $code)] = ($counts[sprintf('U+%04X', $code)] ?? 0) + 1;
    }
    ksort($counts);
    echo "$f: " . strlen($t) . " byte, " . count($counts) . " char unik non-ASCII:\n";
    foreach ($counts as $c => $n) echo "   $c x$n\n";
}
