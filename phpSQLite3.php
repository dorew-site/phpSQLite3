<?php

/**
 * Sử dụng SQLite3
 * Viết bởi: valedrat
 */

class phpSQLite3
{

    public function __construct()
    {
        global $dir_site_custom;
        $path_sqlite = $dir_site_custom.'/database.sqlite';
        if (!file_exists($path_sqlite)) {
            file_put_contents($path_sqlite, '');
        }
        // kết nối với csdl sqlite
        $this->db = new SQLite3($path_sqlite);
        // dung lượng của path_sqlite
        $this->db_size = filesize($path_sqlite);
        // font uft8
        $this->db->query('PRAGMA encoding = "UTF-8"');
    }

    public function getName()
    {
        return 'phpSQLite3';
    }

    /**_____ FUNCTION TRUY -> XUẤT _____**/

   public function data_count($sql){
        return $this->db->querySingle($sql);
    }
    public function data_assoc($sql) {
        $result = $this->db->query($sql);
        return $result->fetchArray(SQLITE3_ASSOC);
    }

    public function data_while($sql) {
        $result = $this->db->query($sql);
             while($row = $result->fetchArray(SQLITE3_ASSOC)) {
                  $data[]=$row;
             }
     return $data;
    }

    public function data_exec($sql) {
           return $this->db->exec($sql);
    }
    public function data_query($sql) {
        return $this->db->query($sql);
    }

    /*
    -----------------------------------------------------------------
    Action with tables in database
    -----------------------------------------------------------------
    */

    /* --- QUERY_COMMAND_TABLE --- */

    function query_select_table($table_name = null, $column = null, $other_sql = null)
    {
        if (!$table_name) {
            return 'There is not table_name in query_select_table()';
        } else {
            if (!$this->table_exists($table_name)) {
                return 'Table `' . $table_name . '` does not exist';
            } else {
                if (!$column) {
                    $sql = "SELECT * FROM $table_name";
                } else {
                    $sql = "SELECT $column FROM $table_name";
                }
                if ($other_sql) {
                    $sql .= " $other_sql";
                }
                $result = $this->db->query($sql);
                $data = [];
                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                    $data[] = $row;
                }
                return $data;
            }
        }
    }

    function query_update_table($table_name = null, $array_row = null, $other_sql = null)
    {
        if (!$table_name) {
            return 'There is not table_name in query_update_table()';
        } else {
            if (!$this->table_exists($table_name)) {
                return 'Table `' . $table_name . '` does not exist';
            } else {
                if (is_array($array_row)) {
                    $sql = "UPDATE $table_name SET ";
                    foreach ($array_row as $key => $value) {
                        $sql .= "`$key` = '$value',";
                    }
                    $sql = rtrim($sql, ',');
                    if ($other_sql) {
                        $sql .= " $other_sql";
                    }
                    $this->db->query($sql);
                    return 'Rows in table `' . $table_name . '` updated';
                } else {
                    return 'There is not array_row in query_update_table()';
                }
            }
        }
    }

    /* --- QUERY AND PROCESS DATA IN TABLE --- */

    public function get_size_db()
    {
        $size_db = $this->db_size ? $this->db_size : 0;
        return $size_db;
    }

    function table_exists($table_name)
    {
        // kiểm tra xem table có tồn tại hay không
        $sql = "SELECT name FROM sqlite_master WHERE type='table' AND name='$table_name'";
        $result = $this->db->query($sql);
        if ($result->fetchArray()) {
            return true;
        } else {
            return false;
        }
    }

    /* --- MANIPULATING TABLES --- */

    /* TABLE */

    public function get_table_count($table_name = null)
    {
        if (!$table_name) {
            return 'There is not table_name in get_table_count()';
        } else {
            if (!$this->table_exists($table_name)) {
                return 'Table `' . $table_name . '` does not exist';
            } else {
                $sql = "SELECT COUNT(*) FROM $table_name";
                $result = $this->db->query($sql);
                $row = $result->fetchArray();
                return $row[0] ? $row[0] : 0;
            }
        }
    }

    public function create_table_with_column($table_name = null, $columns = null)
    {
        /**
         * Tạo bảng với các trường, columns có input là json, id là trường chính
         * Ví dụ: {% set columns = {"name": "TEXT", "msg": "TEXT", "time": "INTEGER"} %}
         */
        if (!$table_name || !$columns) {
            return 'There is not table_name or columns in create_table_with_column()';
        } else {
            if (!$this->table_exists($table_name)) {
                $sql = "CREATE TABLE $table_name (";
                $sql .= 'id INTEGER PRIMARY KEY AUTOINCREMENT,';
                foreach ($columns as $key => $value) {
                    $sql .= $key . ' ' . $value . ',';
                }
                $sql = substr($sql, 0, -1);
                $sql .= ')';
                $this->db->exec($sql);
                return 'Table `' . $table_name . '` created with your columns';
            } else {
                return 'Table `' . $table_name . '` already exists';
            }
        }
    }

    public function create_table($table_name = null)
    {
        if (!$table_name) {
            return 'There is not table_name in create_table()';
        } else {
            if ($this->table_exists($table_name)) {
                return 'Table `' . $table_name . '` already exists';
            } else {
                $column = ['time' => 'INTEGER'];
                return $this->create_table_with_column($table_name, $column);
            }
        }
    }

    public function drop_table($table_name = null)
    {
        if (!$table_name) {
            return 'There is not table_name in drop_table()';
        } else {
            if ($this->table_exists($table_name)) {
                $sql = "DROP TABLE IF EXISTS $table_name";
                $this->db->query($sql);
                return 'Table `' . $table_name . '` dropped';
            } else {
                return 'Table `' . $table_name . '` does not exist';
            }
        }
    }

    public function rename_table($current_table = null, $new_table = null)
    {
        if (!$current_table || !$new_table) {
            return 'There is not table_name in rename_table()';
        } else {
            if ($this->table_exists($current_table)) {
                $sql = "ALTER TABLE $current_table RENAME TO $new_table";
                $this->db->query($sql);
                return 'Table `' . $current_table . '` renamed to `' . $new_table . '`';
            } else {
                return 'Table `' . $current_table . '` does not exist';
            }
        }
    }

    /* COLUMN */

    public function get_list_column($table_name = null)
    {
        if (!$table_name) {
            return 'There is not table_name in get_list_column()';
        } else {
            if ($this->table_exists($table_name)) {
                $sql = "PRAGMA table_info($table_name)";
                $result = $this->db->query($sql);
                $columns = [];
                while ($row = $result->fetchArray()) {
                    $columns[] = $row['name'] . ' (' . $row['type'] . ')';
                }
                return $columns;
            } else {
                return 'Table `' . $table_name . '` does not exist';
            }
        }
    }

    public function column_exists($table_name = null, $column_name = null)
    {
        if (!$table_name || !$column_name) {
            return 'There is not table_name or column_name in column_exists()';
        } else {
            if ($this->table_exists($table_name)) {
                $sql = "PRAGMA table_info($table_name)";
                $result = $this->db->query($sql);
                while ($row = $result->fetchArray()) {
                    if ($row['name'] == $column_name) {
                        return true;
                    }
                }
                return false;
            } else {
                return 'Table `' . $table_name . '` does not exist';
            }
        }
    }

    public function create_columns_table($table = null, $columns = null)
    {
        /**
         * Tạo các trường cho bảng, columns có input là json
         * Ví dụ: {% set columns = {"name": "TEXT", "msg": "TEXT", "time": "INTEGER"} %}
         */
        if (!$table || !$columns) {
            return 'There is not table_name or columns in create_columns_table()';
        } else {
            if ($this->table_exists($table)) {
                $sql = "ALTER TABLE $table ADD COLUMN ";
                foreach ($columns as $key => $value) {
                    $sql .= "$key $value, ";
                }
                $sql = substr($sql, 0, -2);
                $this->db->query($sql);
                return 'Columns added to table `' . $table . '`';
            } else {
                return 'Table `' . $table . '` does not exist';
            }
        }
    }

    public function create_column_table($table_name = null, $column_name = null, $column_type = null)
    {
        if (!$table_name || !$column_name || !$column_type) {
            return 'There is not table_name or column_name or column_type in create_column_table()';
        } else {
            if ($this->table_exists($table_name)) {
                $column = [$column_name => $column_type];
                return $this->create_columns_table($table_name, $column);
            } else {
                return 'Table `' . $table_name . '` does not exist';
            }
        }
    }

    public function drop_column_table($table_name = null, $column_name = null)
    {
        if (!$table_name || !$column_name) {
            return 'There is not table_name or column_name in drop_column_table()';
        } else {
            if ($this->table_exists($table_name)) {
                $sql = "ALTER TABLE $table_name DROP COLUMN $column_name";
                $this->db->query($sql);
                return 'Column `' . $column_name . '` dropped from table `' . $table_name . '`';
            } else {
                return 'Table `' . $table_name . '` does not exist';
            }
        }
    }

    public function edit_type_column_table($table_name = null, $column_name = null, $current_type = null, $new_type = null)
    {
        /**
         * Sửa kiểu dữ liệu của trường trong bảng
         */
        if (!$table_name || !$column_name || !$current_type || !$new_type) {
            return 'There is not table_name or column_name or current_type or new_type in edit_type_column_table()';
        } else {
            if ($this->table_exists($table_name)) {
                if ($this->column_exists($table_name, $column_name)) {
                    $sql = "ALTER TABLE $table_name CHANGE $column_name $column_name $new_type";
                    $this->db->query($sql);
                    return 'Column `' . $column_name . '` changed type from `' . $current_type . '` to `' . $new_type . '` in table `' . $table_name . '`';
                } else {
                    return 'Column `' . $column_name . '` does not exist in table `' . $table_name . '`';
                }
            } else {
                return 'Table `' . $table_name . '` does not exist';
            }
        }
    }

    /* --- ROW --- */

    public function insert_rows_table($table_name = null, $rows = null)
    {
        /**
         * Thêm dữ liệu vào bảng, rows có input là json
         * Ví dụ: {% set rows = {"name": "valedrat", "msg": "hello world!", "time": "123456789"} %}
         */
        if (!$table_name || !$rows) {
            return 'There is not table_name or rows in insert_rows_table()';
        } else {
            if ($this->table_exists($table_name)) {
                $error = null;
                $sql = "INSERT INTO $table_name (";
                foreach ($rows as $key => $value) {
                    // kiểm tra xem cột key có tồn tại hay không
                    if ($this->column_exists($table_name, $key)) {
                        $sql .= "$key, ";
                    } else {
                        $error[] = $key;
                    }
                }
                if ($error) {
                    $notice = 'The mentioned data columns do not exist, those are: ';
                    foreach ($error as $key => $value) {
                        $value = $this->db->escapeString($value);
                        $notice .= $value;
                        if ($key < count($error) - 1) {
                            $notice .= ', ';
                        } else {
                            $notice .= '.';
                        }
                    }
                    return $notice;
                } else {
                    $sql = substr($sql, 0, -2);
                    $sql .= ") VALUES (";
                    foreach ($rows as $key => $value) {
                        $sql .= "'$value', ";
                    }
                    $sql = substr($sql, 0, -2);
                    $sql .= ")";
                    $this->db->query($sql);
                    return 'Rows added to table `' . $table_name . '`';
                }
            } else {
                return 'Table `' . $table_name . '` does not exist';
            }
        }
    }

    public function insert_row_table($table_name = null, $column_name = null, $column_value = null)
    {
        if (!$table_name || !$column_name || !$column_value) {
            return 'There is not table_name or column_name or column_value in insert_row_table()';
        } else {
            if (!$this->table_exists($table_name)) {
                return 'Table `' . $table_name . '` does not exist';
            } else {
                $array = [$column_name => $column_value];
                return $this->insert_rows_table($table_name, $array);
            }
        }
    }

    public function update_rows_table($table_name = null, $columns = null, $where = null)
    {
        /**
         * Cập nhật dữ liệu vào bảng, columns và where có input là json
         * Ví dụ:
         * {% set columns = {"name": "valedrat", "msg": "hello", "time": "123456789"} %}
         * {% set where = {"id": "1"} %}
         */
        if (!$table_name || !$columns || !$where) {
            return 'There is not table_name or columns or where in update_rows_table()';
        } else {
            if ($this->table_exists($table_name)) {
                $sql = "UPDATE `$table_name` SET ";
                foreach ($columns as $key => $value) {
                    $value = $this->db->escapeString($value);
                    $sql .= "$key = '$value', ";
                }
                $sql = substr($sql, 0, -2);
                $sql .= " WHERE ";
                foreach ($where as $key => $value) {
                    $sql .= "$key = '$value' AND ";
                }
                $sql = substr($sql, 0, -5);
                $this->db->query($sql);
                return 'Row updated to table `' . $table_name . '`';
            } else {
                return 'Table `' . $table_name . '` does not exist';
            }
        }
    }

    public function update_row_table($table_name = null, $column_name = null, $column_value = null, $where_column_name = null, $where_column_value = null)
    {
        if (!$table_name || !$column_name || !$column_value || !$where_column_name || !$where_column_value) {
            return 'There is not table_name or column_name or column_value or where_column_name or where_column_value in update_row_table()';
        } else {
            if (!$this->table_exists($table_name)) {
                return 'Table `' . $table_name . '` does not exist';
            } else {
                $array = [$column_name => $column_value];
                $where = [$where_column_name => $where_column_value];
                return $this->update_rows_table($table_name, $array, $where);
            }
        }
    }

    public function delete_rows_table($table_name = null, $where = null)
    {
        /**
         * Xóa dữ liệu của bảng, where có input là json
         * Ví dụ: {% set where = {"id": "1"} %}
         */
        if (!$table_name || !$where) {
            return 'There is not table_name or where in delete_rows_table()';
        } else {
            if ($this->table_exists($table_name)) {
                $sql = "DELETE FROM `$table_name` WHERE ";
                foreach ($where as $key => $value) {
                    $sql .= "$key = '$value' AND ";
                }
                $sql = substr($sql, 0, -5);
                $this->db->query($sql);
                return 'Row deleted from table `' . $table_name . '`';
            } else {
                return 'Table `' . $table_name . '` does not exist';
            }
        }
    }

    public function delete_row_table($table_name = null, $column_name = null, $column_value = null)
    {
        if (!$table_name || !$column_name || !$column_value) {
            return 'There is not table_name or column_name or column_value in delete_row_table()';
        } else {
            if (!$this->table_exists($table_name)) {
                return 'Table `' . $table_name . '` does not exist';
            } else {
                $array = [$column_name => $column_value];
                return $this->delete_rows_table($table_name, $array);
            }
        }
    }

    /* --- COLUMN AND ROW --- */

    public function select_table_row_data_by_where($table_name =  null, $where = null)
    {
        /**
         * Lấy dữ liệu của 1 dòng trong bảng, where có input là json
         * Ví dụ: {% set where = {"id": "1"} %}
         */
        if (!$table_name || !$where) {
            return 'There is not table_name or where in select_row_data_by_where()';
        } else {
            if ($this->table_exists($table_name)) {
                $sql = "SELECT * FROM `$table_name` WHERE ";
                foreach ($where as $key => $value) {
                    $sql .= "$key = '$value' AND ";
                }
                $sql = substr($sql, 0, -5);
                $result = $this->db->query($sql);
                return $result->fetchArray(SQLITE3_ASSOC);
            } else {
                return 'Table `' . $table_name . '` does not exist';
            }
        }
    }

    public function select_table_row_data($table_name =  null, $column_name = null, $column_value = null)
    {
        if (!$table_name || !$column_name || !$column_value) {
            return 'There is not table_name or column_name or column_value in select_table_row_data()';
        } else {
            if ($this->table_exists($table_name)) {
                $where = [$column_name => $column_value];
                return $this->select_table_row_data_by_where($table_name, $where);
            } else {
                return 'Table `' . $table_name . '` does not exist';
            }
        }
    }

    public function select_table_data($table_name = null, $order = null, $sort = null)
    {
        if (!$order) $order = 'id';
        if (!$sort) $sort = 'ASC';
        if (!$table_name) {
            return 'There is not table_name in select_table_data()';
        } else {
            if ($this->table_exists($table_name)) {
                $sql = "SELECT * FROM `$table_name` ORDER BY `$order` $sort";
                $result = $this->db->query($sql);
                $data = [];
                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                    $data[] = $row;
                }
                return $data;
            } else {
                return 'Table `' . $table_name . '` does not exist';
            }
        }
    }

    public function select_table_where_array_data($table_name = null, $where = null, $order = null, $sort = null)
    {
        /**
         * Lấy danh sách dữ liệu của bảng, where có input là json
         * Ví dụ: {% set where = {"id": "1"} %}
         */
        if (!$order) $order = 'id';
        if (!$sort) $sort = 'ASC';
        if (!$table_name) {
            return 'There is not table_name in select_table_where_array_data()';
        } else {
            if ($this->table_exists($table_name)) {
                $sql = "SELECT * FROM `$table_name` WHERE ";
                foreach ($where as $key => $value) {
                    $sql .= "$key = '$value' AND ";
                }
                $sql = substr($sql, 0, -5);
                $sql .= " ORDER BY `$order` $sort";
                $result = $this->db->query($sql);
                $data = [];
                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                    $data[] = $row;
                }
                return $data;
            } else {
                return 'Table `' . $table_name . '` does not exist';
            }
        }
    }

    public function select_table_where_data($table_name = null, $where_column_name = null, $where_column_value = null, $order = null, $sort = null)
    {
        if (!$order) $order = 'id';
        if (!$sort) $sort = 'ASC';
        if (!$table_name) {
            return 'There is not table_name in select_table_where_data()';
        } else {
            if ($this->table_exists($table_name)) {
                $where = [$where_column_name => $where_column_value];
                return $this->select_table_where_array_data($table_name, $where, $order, $sort);
            } else {
                return 'Table `' . $table_name . '` does not exist';
            }
        }
    }
}
