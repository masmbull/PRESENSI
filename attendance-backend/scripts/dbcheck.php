<?php // cek duplicate DB (sementara)
$db = new PDO('sqlite:'.__DIR__.'/../database/database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$q = function (string $sql, array $p = []) use ($db): array {
    $st = $db->prepare($sql);
    $st->execute($p);
    return $st->fetchAll(PDO::FETCH_ASSOC);
};

echo "== CITIES ==\n";
foreach ($q("SELECT id, name FROM cities ORDER BY id") as $r) echo "  {$r['id']}: {$r['name']}\n";

echo "== STORE dup (city_id, lower(trim(name))) ==\n";
foreach ($q("SELECT city_id, lower(trim(name)) n, count(*) c, group_concat(id) ids, group_concat(name) names FROM stores GROUP BY city_id, lower(trim(name)) HAVING count(*)>1") as $r)
  echo "  city{$r['city_id']} '{$r['n']}' x{$r['c']} ids={$r['ids']} names={$r['names']}\n";

echo "== EMP dup (lower(trim(name))) SEMUA ==\n";
foreach ($q("SELECT lower(trim(name)) n, count(*) c, group_concat(id) ids, group_concat(store_id) ss, group_concat(coalesce(face_key,'-')) fk FROM employees GROUP BY lower(trim(name)) HAVING count(*)>1") as $r)
  echo "  '{$r['n']}' x{$r['c']} ids={$r['ids']} stores={$r['ss']} face={$r['fk']}\n";

echo "== EMP dup (store_id, lower(trim(name))) ==\n";
foreach ($q("SELECT ifnull(store_id,-1) s, lower(trim(name)) n, count(*) c, group_concat(id) ids, group_concat(coalesce(face_key,'-')) fk FROM employees GROUP BY ifnull(store_id,-1), lower(trim(name)) HAVING count(*)>1") as $r)
  echo "  store{$r['s']} '{$r['n']}' x{$r['c']} ids={$r['ids']} face={$r['fk']}\n";

echo "== RINGKAS ==\n";
foreach ($q("SELECT 'cities' t, count(*) n FROM cities UNION ALL SELECT 'stores', count(*) FROM stores UNION ALL SELECT 'employees', count(*) FROM employees UNION ALL SELECT 'emp no_face', count(*) FROM employees WHERE face_key IS NULL UNION ALL SELECT 'emp no_store', count(*) FROM employees WHERE store_id IS NULL UNION ALL SELECT 'attendances', count(*) FROM attendances") as $r)
  echo "  {$r['t']}: {$r['n']}\n";
