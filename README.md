# phpSQLite3
Simple PHP Driver for SQLite3

> Install - Cài đặt:
* Require or include file **phpSQLite3.php**. And use Class phpSQLite3. **/** Chèn tệp **phpSQLite3.php**. Và sử dụng Class phpSQLite3.
```php
require_once 'phpSQLite3.php';

$dir_sqlite3 = $_SERVER['DOCUMENT_ROOT'];
$sqlite = new phpSQLite3();
```

> Example / Ví dụ:
```php
require_once 'phpSQLite3.php';

$dir_sqlite3 = $_SERVER['DOCUMENT_ROOT'];
$sqlite = new phpSQLite3();

echo $sqlite->create_table_with_column('chatbox', [
    'name' => 'TEXT',
    'message' => 'TEXT',
    'time' => 'TEXT'
]);
echo '<br/>';

if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
    $name = $_POST['name'];
    $content = $_POST['message'];
    $time = date('U');
    // thêm dữ liệu vào bảng chatbox
    echo $sqlite->insert_rows_table('chatbox', [
        'name' => $name,
        'message' => $content,
        'time' => $time
    ]);
}

// trả về số hàng của bảng chatbox
echo '<br/>' . $sqlite->get_table_count('chatbox');
?>
<form method="post">
    <input type="text" name="name" placeholder="name">
    <br />
    <textarea name="message" placeholder="message"></textarea>
    <br />
    <input type="submit" value="Send">
</form>
<?php
echo '<br/>';

foreach ($sqlite->get_list_column('chatbox') as $column) {
    echo $column . '<br/>';
    $column_explode = explode(' (', $column);
    $column_name = $column_explode[0];
    // kiểm tra xem cột có tôn tại hay không
    if ($sqlite->column_exists('chatbox', $column_name)) {
        echo '- Column `' . $column_name . '` exists<br/>';
    } else {
        echo '- Column `' . $column_name . '` does not exist<br/>';
    }
}
// lấy danh sách dữ liệu của bảng chatbox
foreach ($sqlite->select_table_data('chatbox') as $row) {
    echo '[ '.$row['id'] . ' ] ' . $row['name'] . ': ' . $row['message'] . '<br/>';
}
```
