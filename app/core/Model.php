<?php

class Model
{
    protected $table;
    protected $db;
    protected $fillable = [];

    public function __construct()
    {
        require_once __DIR__ . '/../config/database.php';
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public static function where($column, $value)
    {
        $instance = new static();
        $query = "SELECT * FROM " . $instance->table . " WHERE " . $column . " = ?";
        $stmt = $instance->db->prepare($query);
        $stmt->bind_param("s", $value);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public static function create($data)
    {
        $instance = new static();

        // Fail-closed: reject when no $fillable is defined
        if (empty($instance->fillable)) {
            return [
                'success' => false,
                'message' => 'No $fillable defined on ' . static::class . '. Mass assignment blocked.'
            ];
        }

        // Whitelist columns against $fillable
        $data = array_intersect_key(
            $data,
            array_flip($instance->fillable)
        );

        if (empty($data)) {
            return [
                'success' => false,
                'message' => 'No valid columns to insert.'
            ];
        }

        $columns = array_keys($data);
        $values  = array_values($data);

        // Validate column names contain only safe characters
        foreach ($columns as $col) {
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $col)) {
                return [
                    'success' => false,
                    'message' => 'Invalid column name.'
                ];
            }
        }

        $placeholders = implode(',', array_fill(0, count($columns), '?'));
        $columnNames  = implode(',', array_map(
            fn($c) => "`{$c}`",
            $columns
        ));

        $query = "INSERT INTO " . $instance->table
               . " (" . $columnNames . ") VALUES (" . $placeholders . ")";
        $stmt  = $instance->db->prepare($query);

        $types = str_repeat('s', count($values));
        $stmt->bind_param($types, ...$values);

        if ($stmt->execute()) {
            return [
                'success' => true,
                'id'      => $instance->db->insert_id,
                'message' => 'Data created successfully'
            ];
        }
        $dbError = $stmt->error;
        error_log('[SIAP-MAJU] create() failed on ' . $instance->table . ': ' . $dbError);

        return [
            'success' => false,
            'message' => 'Failed to create data',
            'error'   => (defined('APP_ENV') && APP_ENV === 'production')
                          ? 'Database error' : $dbError,
        ];
    }

    public function all()
    {
        $query = "SELECT * FROM " . $this->table;
        $result = $this->db->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
