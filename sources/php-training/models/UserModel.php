<?php

require_once 'BaseModel.php';

class UserModel extends BaseModel {

    public function findUserById($id) {
        $sql = 'SELECT * FROM users WHERE id = ?';
        $stmt = self::$_connection->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $rows = [];
        if (!empty($result)) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        $stmt->close();
        
        return $rows;
    }

    public function findUser($keyword) {
        $sql = 'SELECT * FROM users WHERE user_name LIKE ? OR user_email LIKE ?';
        $stmt = self::$_connection->prepare($sql);
        $searchTerm = '%' . $keyword . '%';
        $stmt->bind_param('ss', $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $rows = [];
        if (!empty($result)) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        $stmt->close();
        
        return $rows;
    }

    /**
     * Authentication user
     * @param $userName
     * @param $password
     * @return array
     */
    public function auth($userName, $password) {
        $md5Password = md5($password);
        $sql = 'SELECT * FROM users WHERE name = ? AND password = ?';
        $stmt = self::$_connection->prepare($sql);
        $stmt->bind_param('ss', $userName, $md5Password);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $rows = [];
        if (!empty($result)) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        $stmt->close();
        
        return $rows;
    }

    /**
     * Delete user by id
     * @param $id
     * @return mixed
     */
    public function deleteUserById($id) {
        $sql = 'DELETE FROM users WHERE id = ?';
        $stmt = self::$_connection->prepare($sql);
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    /**
     * Update user
     * @param $input
     * @return mixed
     */
    public function updateUser($input) {
        $sql = 'UPDATE users SET name = ?, fullname = ?, email = ?, type = ?, password = ? WHERE id = ?';
        $stmt = self::$_connection->prepare($sql);
        $hashedPassword = md5($input['password']);
        $fullname = $input['fullname'] ?? $input['name'];
        $email = $input['email'] ?? '';
        $type = $input['type'] ?? 'user';
        $stmt->bind_param('sssssi', $input['name'], $fullname, $email, $type, $hashedPassword, $input['id']);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    /**
     * Insert user
     * @param $input
     * @return mixed
     */
    public function insertUser($input) {
        $sql = "INSERT INTO `app_web1`.`users` (`name`, `fullname`, `email`, `type`, `password`) VALUES (?, ?, ?, ?, ?)";
        $stmt = self::$_connection->prepare($sql);
        $hashedPassword = md5($input['password']);
        $fullname = $input['fullname'] ?? $input['name'];
        $email = $input['email'] ?? '';
        $type = $input['type'] ?? 'user';
        $stmt->bind_param('sssss', $input['name'], $fullname, $email, $type, $hashedPassword);
        $result = $stmt->execute();
        $insertId = self::$_connection->insert_id;
        $stmt->close();
        
        return $insertId;
    }

    /**
     * Search users
     * @param array $params
     * @return array
     */
    public function getUsers($params = []) {
        //Keyword
        if (!empty($params['keyword'])) {
            $sql = 'SELECT * FROM users WHERE name LIKE ?';
            $stmt = self::$_connection->prepare($sql);
            $searchTerm = '%' . $params['keyword'] . '%';
            $stmt->bind_param('s', $searchTerm);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $rows = [];
            if (!empty($result)) {
                while ($row = $result->fetch_assoc()) {
                    $rows[] = $row;
                }
            }
            $stmt->close();
            
            return $rows;
        } else {
            $sql = 'SELECT * FROM users';
            $users = $this->select($sql);
        }

        return $users;
    }
}