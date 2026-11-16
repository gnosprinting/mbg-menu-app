<?php
/**
 * Salin data dari SQLite (database/database.sqlite) ke MySQL (koneksi .env).
 * Tabel internal Laravel (migrations, sessions, cache, jobs) dilewati.
 * Jalankan: php database/scripts/copy_sqlite_to_mysql.php
 */
$root = dirname(__DIR__, 2);
$sqlitePath = $root . '/database/database.sqlite';
if (!is_file($sqlitePath)) {
    fwrite(STDERR, "SQLite tidak ditemukan: $sqlitePath\n");
    exit(1);
}

// Baca .env sederhana
$env = [];
foreach (file($root . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
    [$k, $v] = explode('=', $line, 2);
    $env[trim($k)] = trim($v, " \t\n\r\0\x0B\"");
}

$sqlite = new PDO('sqlite:' . $sqlitePath, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$mysql = new PDO(
    sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $env['DB_HOST'] ?? '127.0.0.1', $env['DB_PORT'] ?? '3306', $env['DB_DATABASE'] ?? 'mbg'),
    $env['DB_USERNAME'] ?? 'root', $env['DB_PASSWORD'] ?? '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
$mysql->exec('SET FOREIGN_KEY_CHECKS=0');

$skip = ['migrations', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs', 'sqlite_sequence'];

$tables = $sqlite->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")
    ->fetchAll(PDO::FETCH_COLUMN);

$results = [];
foreach ($tables as $table) {
    if (in_array($table, $skip, true)) continue;

    $cols = array_column($sqlite->query("PRAGMA table_info(\"$table\")")->fetchAll(), 'name');
    $mysqlCols = array_column($mysql->query("SHOW COLUMNS FROM `$table`")->fetchAll(), 'Field');
    $use = array_values(array_intersect($cols, $mysqlCols));
    if (!$use) { $results[$table] = 'TIDAK ADA KOLOM'; continue; }

    $mysql->exec("TRUNCATE TABLE `$table`");
    $select = 'SELECT ' . implode(',', array_map(fn($c) => "\"$c\"", $use)) . " FROM \"$table\"";
    $rows = $sqlite->query($select)->fetchAll();

    if ($rows) {
        $colList = '`' . implode('`,`', $use) . '`';
        $ph = '(' . implode(',', array_fill(0, count($use), '?')) . ')';
        foreach (array_chunk($rows, 400) as $chunk) {
            $sql = "INSERT INTO `$table` ($colList) VALUES " . implode(',', array_fill(0, count($chunk), $ph));
            $vals = [];
            foreach ($chunk as $row) foreach ($use as $c) $vals[] = $row[$c];
            $mysql->prepare($sql)->execute($vals);
        }
    }

    $src = count($rows);
    $dst = (int) $mysql->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    $results[$table] = $src === $dst ? "$dst ok" : "BEDA src=$src dst=$dst";
}

$mysql->exec('SET FOREIGN_KEY_CHECKS=1');

$bad = array_filter($results, fn($v) => !str_ends_with($v, 'ok'));
foreach ($results as $t => $r) printf("%-28s %s\n", $t, $r);
printf("\nTabel: %d · bermasalah: %d\n", count($results), count($bad));
exit($bad ? 1 : 0);
