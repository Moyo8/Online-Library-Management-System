<?php
namespace App\Core;

use PDO;
use PDOStatement;

/**
 * Base Model class
 */
class Model {
    protected $db;
    protected $cache;
    protected $lastStatement;

    public function __construct() {
        $this->db = \Database::getInstance();
        $this->cache = new \App\Cache();
        $this->lastStatement = null;
    }

    /**
     * Prepare and execute a query
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @return PDOStatement
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                throw new \Exception('Failed to prepare statement: ' . $sql);
            }
            if (!$stmt->execute($params)) {
                throw new \Exception('Failed to execute statement: ' . $sql);
            }
            $this->lastStatement = $stmt;
            return $stmt;
        } catch (\PDOException $e) {
            throw new \Exception('Database query error: ' . $e->getMessage() . ' | Query: ' . $sql);
        }
    }

    /**
     * Fetch all results with optional caching
     * @param string $sql SQL query
     * @param array $params Parameters
     * @param int $ttl Time to live in seconds for caching (null to disable cache)
     * @return array Results
     */
    public function findAll($sql, $params = [], $ttl = null) {
        // Generate cache key from SQL and params
        if ($ttl !== null) {
            $cacheKey = md5($sql . serialize($params));
            $cachedResult = $this->cache->get($cacheKey);
            if ($cachedResult !== null) {
                return $cachedResult;
            }
        }

        $stmt = $this->query($sql, $params);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Cache result if TTL specified
        if ($ttl !== null && !empty($result)) {
            $cacheKey = md5($sql . serialize($params));
            $this->cache->set($cacheKey, $result, $ttl);
        }

        return $result;
    }

    /**
     * Fetch single result with optional caching
     * @param string $sql SQL query
     * @param array $params Parameters
     * @param int $ttl Time to live in seconds for caching (null to disable cache)
     * @return mixed Single result or false
     */
    public function find($sql, $params = [], $ttl = null) {
        // Generate cache key from SQL and params
        if ($ttl !== null) {
            $cacheKey = md5($sql . serialize($params));
            $cachedResult = $this->cache->get($cacheKey);
            if ($cachedResult !== null) {
                return $cachedResult;
            }
        }

        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Cache result if TTL specified
        if ($ttl !== null && $result !== false) {
            $cacheKey = md5($sql . serialize($params));
            $this->cache->set($cacheKey, $result, $ttl);
        }

        return $result;
    }

    /**
     * Get last inserted ID
     * @return int Last insert ID
     */
    public function lastInsertId() {
        return $this->db->lastInsertId();
    }

    /**
     * Get number of affected rows
     * @return int Affected rows
     */
    public function rowCount() {
        if ($this->lastStatement instanceof PDOStatement) {
            return $this->lastStatement->rowCount();
        }
        return 0;
    }

    /**
     * Paginate a query
     * @param string $sql SQL query (without LIMIT clause)
     * @param array $params Parameters for prepared statement
     * @param int $page Page number (1-based)
     * @param int $perPage Items per page
     * @return array Associative array with keys: data, total, page, per_page, total_pages
     */
    public function paginate($sql, $params = [], $page = 1, $perPage = 10) {
        // Ensure page is at least 1
        $page = max(1, (int)$page);
        $perPage = max(1, (int)$perPage);

        // Get total count
        $countSql = preg_replace('/^SELECT\s+(?:DISTINCT\s+)?(.*)\s+FROM\s+/i', 'SELECT COUNT(*) AS total FROM ', $sql);
        // Remove ORDER BY clause from count query if present (it's not needed for count and can cause issues)
        $countSql = preg_replace('/\s+ORDER\s+BY\s+.*$/', '', $countSql);
        $totalStmt = $this->query($countSql, $params);
        $totalRow = $totalStmt->fetch(PDO::FETCH_NUM);
        $total = (int)$totalRow[0];

        // Calculate total pages
        $totalPages = (int)ceil($total / $perPage);

        // Ensure page is within bounds
        $page = min($page, $totalPages > 0 ? $totalPages : 1);

        // Calculate offset
        $offset = ($page - 1) * $perPage;

        // Add LIMIT and OFFSET to the original query
        $paginatedSql = $sql . " LIMIT ? OFFSET ?";
        $paginatedParams = array_merge($params, [$perPage, $offset]);

        // Get paginated data
        $data = $this->findAll($paginatedSql, $paginatedParams);

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages
        ];
    }

    /**
     * Clear cache
     * @param string|null $pattern Optional pattern to match cache keys
     */
    public function clearCache($pattern = null) {
        if ($pattern === null) {
            $this->cache->clear();
        } else {
            // For more advanced cache clearing, this would need to be implemented
            // based on the cache backend being used
        }
    }
}
?>