<?php // fix-mojibake.php — balikin double-encoding UTF-8→CP1252 pada file teks.
// Usage: php fix-mojibake.php file1 [file2 ...]   (backup .bak-moji dibuat otomatis)
// Logika: file berisi mojibake = byte UTF-8 asli yang sempat di-decode sbg CP1252
// lalu di-encode ulang jadi UTF-8. Fix = tiap run non-ASCII di-decode balik
// char→byte CP1252 (termasuk C1 identity) → hasil harus valid UTF-8.

function cp1252Map(): array
{
    $specials = [
        0x80 => "\u{20AC}", 0x82 => "\u{201A}", 0x83 => "\u{192}", 0x84 => "\u{201E}",
        0x85 => "\u{2026}", 0x86 => "\u{2020}", 0x87 => "\u{2021}", 0x88 => "\u{2C6}",
        0x89 => "\u{2030}", 0x8A => "\u{160}", 0x8B => "\u{2039}", 0x8C => "\u{152}",
        0x8E => "\u{17D}", 0x91 => "\u{2018}", 0x92 => "\u{2019}", 0x93 => "\u{201C}",
        0x94 => "\u{201D}", 0x95 => "\u{2022}", 0x96 => "\u{2013}", 0x97 => "\u{2014}",
        0x98 => "\u{2DC}", 0x99 => "\u{2122}", 0x9A => "\u{161}", 0x9B => "\u{203A}",
        0x9C => "\u{153}", 0x9E => "\u{17E}", 0x9F => "\u{178}",
    ];
    $map = [];
    for ($i = 0x80; $i <= 0x9F; $i++) $map[mb_chr($i, 'UTF-8')] = chr($i); // C1 identity
    foreach ($specials as $b => $ch) $map[$ch] = chr($b);
    for ($i = 0xA0; $i <= 0xFF; $i++) $map[mb_chr($i, 'UTF-8')] = chr($i);
    return $map;
}

$map = cp1252Map();
foreach (array_slice($argv, 1) as $file) {
    if (!is_file($file)) { echo "$file: SKIP (bukan file)\n"; continue; }
    $raw = file_get_contents($file);
    $t = $raw;
    if (str_starts_with($t, "\xEF\xBB\xBF")) $t = substr($t, 3);
    if (!preg_match('/[\x{0100}-\x{FFFF}]/u', $t)) { echo "$file: clean\n"; continue; }

    // proses per run non-ASCII; revert run yang hasilnya bukan UTF-8 valid
    $out = '';
    $fixed = $kept = 0;
    foreach (preg_split('/([\x{80}-\x{FFFF}]+)/u', $t, -1, PREG_SPLIT_DELIM_CAPTURE) as $seg) {
        if ($seg === '') continue;
        if (!preg_match('/[\x{80}-\x{FFFF}]/u', $seg)) { $out .= $seg; continue; }
        $bytes = strtr($seg, $map);
        if (preg_match('//u', $bytes) && !str_contains(mb_convert_encoding($bytes, 'UTF-8', 'UTF-8') ?: "\u{FFFD}", "\u{FFFD}")) {
            $out .= $bytes; $fixed++;
        } else { $out .= $seg; $kept++; } // sudah bener / gak bisa dibalik → biarkan
    }
    if ($out !== $raw) {
        copy($file, $file . '.bak-moji');
        file_put_contents($file, $out);
        echo "$file: FIXED (run dibalik=$fixed, dibiarkan=$kept, " . strlen($raw) . " → " . strlen($out) . " byte)\n";
    } else {
        echo "$file: no change (run dibalik=$fixed, dibiarkan=$kept)\n";
    }
}

