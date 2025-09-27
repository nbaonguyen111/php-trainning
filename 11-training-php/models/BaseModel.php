<?php
require_once 'configs/database.php';

abstract class BaseModel {
    protected static $_connection;

    public function __construct() {
        if (!isset(self::$_connection)) {
            self::$_connection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);
            if (self::$_connection->connect_errno) {
                die("Connect failed: " . self::$_connection->connect_error);
            }
            self::$_connection->set_charset("utf8mb4");
        }
    }

    protected function query($sql) {
        $result = self::$_connection->query($sql);
        return $result;
    }

    protected function select($sql) {
        $result = $this->query($sql);
        $rows = [];
        if ($result instanceof \mysqli_result) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            $result->free();
        }
        return $rows;
    }

    protected function delete($sql) { return $this->query($sql); }
    protected function update($sql) { return $this->query($sql); }
    protected function insert($sql) { return $this->query($sql); }

    // helper để chạy prepared statement SELECT
    protected function prepareAndFetch($sql, $types = '', $params = []) {
        $stmt = self::$_connection->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        $stmt->close();
        return $rows;
    }
}
