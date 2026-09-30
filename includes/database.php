<?php
/**
 * PDO database layer.
 * Every query in this application goes through these helpers, which always use
 * prepared statements — user data is never concatenated into SQL.
 */

if (!defined('APP_RUNNING')) {
    http_response_code(403);
    exit('Direct access is not allowed.');
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        error_log('DB connection failed: ' . $e->getMessage());
        exit('Database connection failed. Please check includes/config.php.');
    }

    return $pdo;
}

/** Run a prepared statement and return it. */
function q(string $sql, array $params = []): PDOStatement
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/** Fetch all rows. */
function fetch_all(string $sql, array $params = []): array
{
    return q($sql, $params)->fetchAll();
}

/** Fetch a single row or null. */
function fetch_one(string $sql, array $params = []): ?array
{
    $row = q($sql, $params)->fetch();
    return $row === false ? null : $row;
}

/** Fetch a single scalar value. */
function fetch_val(string $sql, array $params = [])
{
    $v = q($sql, $params)->fetchColumn();
    return $v === false ? null : $v;
}

/** Insert a row and return the new id. */
function insert_row(string $table, array $data): int
{
    $cols = array_keys($data);
    $sql  = 'INSERT INTO `' . str_replace('`', '', $table) . '` (`'
        . implode('`,`', array_map(fn ($c) => str_replace('`', '', $c), $cols))
        . '`) VALUES (' . rtrim(str_repeat('?,', count($cols)), ',') . ')';
    q($sql, array_values($data));
    return (int) db()->lastInsertId();
}

/** Update rows matched by $where. */
function update_row(string $table, array $data, string $where, array $whereParams = []): int
{
    $sets = [];
    foreach (array_keys($data) as $c) {
        $sets[] = '`' . str_replace('`', '', $c) . '` = ?';
    }
    $sql = 'UPDATE `' . str_replace('`', '', $table) . '` SET ' . implode(', ', $sets)
        . ' WHERE ' . $where;
    return q($sql, array_merge(array_values($data), $whereParams))->rowCount();
}

/** Delete rows matched by $where (always pass an id placeholder). */
function delete_row(string $table, string $where, array $whereParams = []): int
{
    return q('DELETE FROM `' . str_replace('`', '', $table) . '` WHERE ' . $where, $whereParams)->rowCount();
}
