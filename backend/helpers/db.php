<?php
/**
 * Database Helper Functions
 */
require_once __DIR__ . '/../config/database.php';

function dbQuery($sql, $params = []) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("DB Error: " . $e->getMessage() . " | SQL: " . $sql);
        if (function_exists('errorResponse')) {
            errorResponse('Database error occurred', 500);
        }
        throw $e;
    }
}

function dbFetchAll($sql, $params = []) {
    return dbQuery($sql, $params)->fetchAll();
}

function dbFetchOne($sql, $params = []) {
    return dbQuery($sql, $params)->fetch();
}

function dbInsert($table, $data) {
    $pdo = getDBConnection();
    $columns = implode(', ', array_keys($data));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_values($data));
    return $pdo->lastInsertId();
}

function dbUpdate($table, $data, $where, $whereParams = []) {
    $pdo = getDBConnection();
    $setParts = [];
    $values = [];
    foreach ($data as $key => $value) {
        $setParts[] = "{$key} = ?";
        $values[] = $value;
    }
    $sql = "UPDATE {$table} SET " . implode(', ', $setParts) . " WHERE {$where}";
    $values = array_merge($values, $whereParams);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($values);
    return $stmt->rowCount();
}

function dbDelete($table, $where, $params = []) {
    return dbQuery("DELETE FROM {$table} WHERE {$where}", $params)->rowCount();
}

function dbCount($table, $where = '1=1', $params = []) {
    $r = dbFetchOne("SELECT COUNT(*) as cnt FROM {$table} WHERE {$where}", $params);
    return (int)$r['cnt'];
}

function logActivity($userId, $action, $entityType, $entityId = null, $details = null) {
    dbInsert('activity_log', [
        'user_id' => $userId,
        'action' => $action,
        'entity_type' => $entityType,
        'entity_id' => $entityId,
        'details' => $details ? json_encode($details) : null
    ]);
}
