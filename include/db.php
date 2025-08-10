<?php
// Classe segura baseada em mysqliDB
class mysqliDB {
    public $CON = false;
    public $CLOSE;
    public $ERROR = false;

    public function __construct($opt = null) {
        global $DBCON;
        $db = ($opt && isset($opt['host'], $opt['user'], $opt['pass'], $opt['name'])) ? $opt : $DBCON;
        $this->CON = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['name']);

        if (!$this->CON) {
            $this->logErrorr('Falha na conexão', 'Conexão');
            exit(json_encode(['status' => false, 'error' => 'dbconn', 'errorcode' => mysqli_connect_error()]));
        }

        $this->CLOSE = true;
    }

    public function query($q) {
        $result = mysqli_query($this->CON, $q);
        if (!$result) {
            $this->logErrorr(mysqli_error($this->CON), $q);
        }
        return $result;
    }

    public function fetch($q) {
        $result = $this->query($q);
        $return = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $return[] = $row;
            }
        }
        return $return;
    }

    public function select($table, $params, $condition = '1') {
        $q = "SELECT $params FROM $table WHERE $condition";
        return $this->fetch($q);
    }

    public function select_order($table, $params, $condition = '1', $order) {
        $q = "SELECT $params FROM $table WHERE $condition ORDER BY $order";
        return $this->fetch($q);
    }

    public function insert_multiple($table, $keys, $rows) {
        $escapedRows = [];
        foreach ($rows as $row) {
            $escaped = array_map([$this, 'clean'], $row);
            $escapedRows[] = '(' . implode(', ', $escaped) . ')';
        }
        $columns = implode(', ', $keys);
        $values = implode(', ', $escapedRows);
        $q = "INSERT INTO $table ($columns) VALUES $values";
        return $this->query($q);
    }

    public function insert($table, $keys, $rows) {
        return $this->insert_multiple($table, $keys, $rows);
    }

    public function insert_single($table, $keys, $val) {
        $escaped = array_map([$this, 'clean'], $val);
        $columns = implode(', ', $keys);
        $values = implode(', ', $escaped);
        $q = "INSERT INTO $table ($columns) VALUES ($values)";
        return $this->query($q);
    }

    public function update($table, $upd, $condition = '1') {
        $q = "UPDATE $table SET $upd WHERE $condition";
        return $this->query($q);
    }

    public function delete($table, $condition = '1') {
        $q = "DELETE FROM $table WHERE $condition";
        return $this->query($q);
    }

    public function param($param, $type = null, $encode = true) {
        $param = isset($_REQUEST[$param]) ? mysqli_real_escape_string($this->CON, $_REQUEST[$param]) : null;
        if ($type === 'i') $param = ($param !== null) ? (int)$param : null;
        elseif ($type === 'f') $param = ($param !== null) ? (float)$param : null;
        return $encode && $param !== null ? htmlspecialchars($param) : $param;
    }

    public function insert_id() {
        return mysqli_insert_id($this->CON);
    }

    public function esc($str) {
        return mysqli_real_escape_string($this->CON, htmlspecialchars($str));
    }

    private function clean($value) {
        return "'" . mysqli_real_escape_string($this->CON, htmlspecialchars($value)) . "'";
    }

    private function logErrorr($message, $query) {
        $log = date('Y-m-d H:i:s') . " | ERRO: $message | QUERY: $query\n";
        file_put_contents(__DIR__ . '/mysqli_errors.log', $log, FILE_APPEND);
    }

    public function closeConnection() {
        if ($this->CON && $this->CLOSE) {
            mysqli_close($this->CON);
        }
    }

    public function __destruct() {
        $this->closeConnection();
    }
}

function db_error($err) {
    return json_encode(['status' => false, 'error' => 'dbconn', 'errorcode' => mysqli_connect_error()]);
}