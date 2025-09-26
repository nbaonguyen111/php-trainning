<?php

require_once 'BaseModel.php';

class UserModel extends BaseModel {

    public function findUserById($id) {
        $sql = 'SELECT * FROM users WHERE id = '.$id;
        $user = $this->select($sql);

        return $user;
    }

    public function findUser($keyword) {
        $sql = 'SELECT * FROM users WHERE user_name LIKE %'.$keyword.'%'. ' OR user_email LIKE %'.$keyword.'%';
        $user = $this->select($sql);

        return $user;
    }

    /**
     * Authentication user
     * @param $userName
     * @param $password
     * @return array
     */
    public function auth($userName, $password) {
        $stmt = self::$_connection->prepare("SELECT id, name, password FROM users WHERE name = ? LIMIT 1");
        $stmt->bind_param("s", $userName);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
    
        if ($user && password_verify($password, $user['password'])) {
            return [$user]; // tương thích với code hiện tại
        }
        return null;
    }
    


    /**
     * Delete user by id
     * @param $id
     * @return mixed
     */
    public function deleteUserById($id) {
        $sql = 'DELETE FROM users WHERE id = '.$id;
        return $this->delete($sql);

    }

    /**
     * Update user
     * @param $input
     * @return mixed
     */
    public function updateUser($input) {
        $sql = 'UPDATE users SET 
                 name = "' . mysqli_real_escape_string(self::$_connection, $input['name']) .'", 
                 password="'. md5($input['password']) .'"
                WHERE id = ' . $input['id'];

        $user = $this->update($sql);

        return $user;
    }

    /**
     * Insert user
     * @param $input
     * @return mixed
     */
    public function insertUser($input) {
        $passwordHash = password_hash($input['password'], PASSWORD_DEFAULT);
        $stmt = self::$_connection->prepare(
            "INSERT INTO `app_web1`.`users` (`name`, `password`) VALUES (?, ?)"
        );
        $stmt->bind_param("ss", $input['name'], $passwordHash);
        $stmt->execute();
        $userId = $stmt->insert_id;
        $stmt->close();
    
        return $userId; 
    }
    
    /**
     * Search users
     * @param array $params
     * @return array
     */
    public function getUsers($params = []) {
        $users = [];
    
        if (!empty($params['keyword'])) {
            // Thêm wildcard trước khi bind để an toàn với LIKE
            $keyword = '%' . $params['keyword'] . '%';
    
            // Prepare statement (không dùng multi_query)
            $stmt = self::$_connection->prepare("SELECT id, name, fullname, type FROM users WHERE name LIKE ?");
            if ($stmt === false) {
                error_log('Prepare failed in getUsers: ' . self::$_connection->error);
                return $users;
            }
    
            $stmt->bind_param("s", $keyword);
            $stmt->execute();
    
            $result = $stmt->get_result();
            if ($result) {
                $users = $result->fetch_all(MYSQLI_ASSOC);
                $result->free();
            }
    
            $stmt->close();
        } else {
            $sql = 'SELECT id, name, fullname, type FROM users';
            $users = $this->select($sql);
        }
    
        return $users;
    }
    
    // public function getTest($params = []) {
    //     //Keyword
    //     if (!empty($params['keyword'])) {
    //         $sql = 'SELECT * FROM users WHERE name LIKE "%' . $params['keyword'] .'%"';

    //         //Keep this line to use Sql Injection
    //         //Don't change
    //         //Example keyword: abcef%";TRUNCATE banks;##
    //         $users = self::$_connection->multi_query($sql);

    //         //Get data
    //         $users = $this->query($sql);
    //     } else {
    //         $sql = 'SELECT * FROM thu_nghiem';
    //         $users = $this->select($sql);
    //     }

    //     return $users;
    // }
}