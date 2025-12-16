<?php

class Model
{
    protected $db;
    protected $table;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all()
    {
        return $this->db->fetchAll("SELECT * FROM {$this->table}");
    }

    public function find($id)
    {
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
    }

    public function findBy($column, $value)
    {
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE $column = ?", [$value]);
    }

    public function where($conditions = [], $orderBy = null, $limit = null)
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $column => $value) {
                $where[] = "$column = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }

        if ($limit) {
            $sql .= " LIMIT $limit";
        }

        return $this->db->fetchAll($sql, $params);
    }

    public function create($data)
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($data), '?');

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ")
                VALUES (" . implode(', ', $placeholders) . ")";

        $this->db->query($sql, array_values($data));
        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $set = [];
        $params = [];

        foreach ($data as $column => $value) {
            $set[] = "$column = ?";
            $params[] = $value;
        }

        $params[] = $id;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . " WHERE id = ?";

        return $this->db->query($sql, $params);
    }

    public function delete($id)
    {
        return $this->db->query("DELETE FROM {$this->table} WHERE id = ?", [$id]);
    }

    public function count($conditions = [])
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $column => $value) {
                $where[] = "$column = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $result = $this->db->fetch($sql, $params);
        return $result['count'] ?? 0;
    }
}
