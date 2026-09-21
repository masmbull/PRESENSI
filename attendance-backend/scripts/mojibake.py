"""Detektor + pemperbaik mojibake double-encoding (UTF-8 dibaca cp1252/latin1).

Kasus di repo ini (absen.blade.php):
  1. 3/4-byte: 'ðŸ§ª' (F0 9F A7 AA dibaca cp1252, disimpan UTF-8) -> U+1F9EA.
  2. 2-byte: 'Â·' (C2 B7 dibaca latin1, disimpan UTF-8) -> U+00B7.

Pakai: python scripts/mojibake.py [apply] <file|dir> [...]
  tanpa 'apply' = dry-run (laporan saja, file tidak diubah).
"""
import os
import sys
import unicodedata

CP1252_EXTRA = {
    0x80: '\u20ac', 0x82: '\u201a', 0x83: '\u0192', 0x84: '\u201e',
    0x85: '\u2026', 0x86: '\u2020', 0x87: '\u2021', 0x88: '\u02c6',
    0x89: '\u2030', 0x8a: '\u0160', 0x8b: '\u2039', 0x8c: '\u0152',
    0x8e: '\u017d', 0x91: '\u2018', 0x92: '\u2019', 0x93: '\u201c',
    0x94: '\u201d', 0x95: '\u2022', 0x96: '\u2013', 0x97: '\u2014',
    0x98: '\u02dc', 0x99: '\u2122', 0x9a: '\u0161', 0x9b: '\u203a',
    0x9c: '\u0153', 0x9e: '\u017e', 0x9f: '\u0178',
}
REV = {v: k for k, v in CP1252_EXTRA.items()}


def to_byte(c):
    o = ord(c)
    if o < 0x80:
        return o
    if o < 0xA0:
        return o  # kontrol = byte mentah 0x80-0x9F
    if c in REV:
        return REV[c]
    if o < 0x100:
        return o
    return None


def try_seq(text, i):
    """Coba baca urutan mojibake di posisi i. Kembalikan (nchar, hasil, hex) / None."""
    b0 = to_byte(text[i])
    if b0 is None:
        return None
    if 0xF0 <= b0 <= 0xF4:
        n = 4
    elif 0xE0 <= b0 <= 0xEF:
        n = 3
    elif 0xC2 <= b0 <= 0xDF:
        n = 2  # contoh: 'Â·'/'Â±'/'Â©' = C2 xx dibaca latin1
    else:
        return None
    bs = [b0]
    for k in range(1, n):
        if i + k >= len(text):
            return None
        b = to_byte(text[i + k])
        if b is None or not (0x80 <= b <= 0xBF):
            return None
        bs.append(b)
    if n == 4 and not (0x90 <= bs[1] <= 0xBF):
        return None
    if n == 2 and bs[0] <= 0xC1:
        return None  # C0/C1 bukan lead UTF-8 valid
    if n == 3:
        if bs[0] == 0xE0 and bs[1] < 0xA0:
            return None
        if bs[0] == 0xED and bs[1] > 0x9F:
            return None
    try:
        s = bytes(bs).decode('utf-8')
    except Exception:
        return None
    if any(ord(c) < 0x20 for c in s):
        return None
    return (n, s, bytes(bs).hex(' ').upper())


def iter_targets(paths):
    for p in paths:
        if os.path.isdir(p):
            for root, _d, files in os.walk(p):
                for f in sorted(files):
                    if f.endswith('.blade.php'):
                        yield os.path.join(root, f)
        else:
            yield p


def main():
    args = sys.argv[1:]
    apply = 'apply' in args
    paths = [a for a in args if a != 'apply'] or ['resources/views']
    total_fix = 0
    files_touched = []
    for path in iter_targets(paths):
        raw = open(path, 'rb').read()
        try:
            text = raw.decode('utf-8-sig')
        except UnicodeDecodeError as e:
            sys.stdout.write('SKIP (bukan utf-8 valid): %s (%s)\n' % (path, e))
            continue
        bom = raw.startswith(b'\xef\xbb\xbf')
        out = []
        i = 0
        fixes = []
        skips = []
        while i < len(text):
            r = try_seq(text, i)
            if r:
                n, s, hx = r
                ln = text.count('\n', 0, i) + 1
                fixes.append((ln, text[i:i + n], s, hx))
                out.append(s)
                i += n
            else:
                b0 = to_byte(text[i])
                if b0 is not None and (0xE0 <= b0 <= 0xF4):
                    ln = text.count('\n', 0, i) + 1
                    skips.append((ln, ascii(text[i:i + 4])))
                out.append(text[i])
                i += 1
        if fixes or skips:
            sys.stdout.write('--- %s: %d kandidat, %d lead-tanpa-pasangan\n'
                             % (path, len(fixes), len(skips)))
            for ln, orig, s, hx in fixes:
                try:
                    nm = unicodedata.name(s)
                except ValueError:
                    nm = '?'
                sys.stdout.write('  L%d %s -> %s\n'
                                 % (ln, ascii(orig), ascii(s + ' [U+%04X %s | %s]'
                                                           % (ord(s[0]), nm, hx))))
            for ln, ctx in skips:
                sys.stdout.write('  L%d SKIP lead-invalid: %s\n' % (ln, ctx))
        if apply and fixes:
            fixed = (b'\xef\xbb\xbf' if bom else b'') + ''.join(out).encode('utf-8')
            open(path, 'wb').write(fixed)
            files_touched.append(path)
            total_fix += len(fixes)
    sys.stdout.write('TOTAL: %d perbaikan di %d file (apply=%s)\n'
                     % (total_fix, len(files_touched), apply))
    if files_touched:
        for p in files_touched:
            sys.stdout.write('  WROTE %s\n' % p)


if __name__ == '__main__':
    main()
