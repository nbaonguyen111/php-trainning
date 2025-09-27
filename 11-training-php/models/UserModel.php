<?php

require_once 'BaseModel.php';

class UserModel extends BaseModel {

    public function findUserById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        return $this->prepareAndFetch($sql, 'i', [$id]);
    }

    public function findUser($keyword) {
        $kw = "%$keyword%";
        $sql = "SELECT * FROM users WHERE user_name LIKE ? OR user_email LIKE ?";
        return $this->prepareAndFetch($sql, 'ss', [$kw, $kw]);
    }

    public function auth($userName, $password) {
        $sql = "SELECT * FROM users WHERE name = ?";
        $users = $this->prepareAndFetch($sql, 's', [$userName]);
        if (!empty($users) && password_verify($password, $users[0]['password'])) {
            return $users;
        }
        return [];
    }

    public function deleteUserById($id) {
        $stmt = self::$_connection->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    }

    public function updateUser($input) {
        $hashed = password_hash($input['password'], PASSWORD_DEFAULT);
        $stmt = self::$_connection->prepare("UPDATE users SET name = ?, password = ? WHERE id = ?");
        $stmt->bind_param('ssi', $input['name'], $hashed, $input['id']);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    }

    public function insertUser($input) {
        $hashed = password_hash($input['password'], PASSWORD_DEFAULT);
        $stmt = self::$_connection->prepare("INSERT INTO users (name, password) VALUES (?, ?)");
        $stmt->bind_param('ss', $input['name'], $hashed);
        $stmt->execute();
        $insertId = $stmt->insert_id;
        $stmt->close();
        return $insertId;
    }

    public function getUsers($params = []) {
        if (!empty($params['keyword'])) {
            $kw = "%".$params['keyword']."%";
            $sql = "SELECT id, name, fullname, type FROM users WHERE name LIKE ? OR fullname LIKE ?";
            return $this->prepareAndFetch($sql, 'ss', [$kw, $kw]);
        } else {
            return $this->select("SELECT id, name, fullname, type FROM users");
        }
    }
}

    