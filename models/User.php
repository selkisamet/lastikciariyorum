<?php

require_once __DIR__ . '/../core/Model.php';

class User extends Model
{
    protected $table = 'users';

    public function findByEmail($email)
    {
        return $this->findBy('email', $email);
    }

    public function register($data)
    {
        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        return $this->create($data);
    }

    public function verify($email, $password)
    {
        $user = $this->findByEmail($email);

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            return false;
        }

        if (!$user['is_active']) {
            return false;
        }

        return $user;
    }

    public function createResetToken($email)
    {
        $user = $this->findByEmail($email);

        if (!$user) {
            return false;
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->update($user['id'], [
            'reset_token' => $token,
            'reset_token_expires' => $expires
        ]);

        return $token;
    }

    public function verifyResetToken($token)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE reset_token = ?
                AND reset_token_expires > NOW()";

        return $this->db->fetch($sql, [$token]);
    }

    public function resetPassword($token, $newPassword)
    {
        $user = $this->verifyResetToken($token);

        if (!$user) {
            return false;
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $this->update($user['id'], [
            'password' => $hashedPassword,
            'reset_token' => null,
            'reset_token_expires' => null
        ]);

        return true;
    }

    public function updatePassword($userId, $newPassword)
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($userId, ['password' => $hashedPassword]);
    }

    public function getAllWithCompany()
    {
        $sql = "SELECT u.*, c.name as company_name, c.id as company_id
                FROM {$this->table} u
                LEFT JOIN companies c ON u.id = c.user_id
                ORDER BY u.created_at DESC";

        return $this->db->fetchAll($sql);
    }

    public function getTotalCount()
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $result = $this->db->fetch($sql);
        return $result['total'] ?? 0;
    }

    public function getActiveCount()
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE is_active = 1";
        $result = $this->db->fetch($sql);
        return $result['total'] ?? 0;
    }
}
