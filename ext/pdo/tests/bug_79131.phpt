--TEST--
PDO Common: Bug #79131 (PDO does not throw an exception when parameter values are missing)
--SKIPIF--
<?php
if (!extension_loaded('pdo')) die('skip');
$dir = getenv('REDIR_TEST_DIR');
if (false == $dir) die('skip no driver');
require_once $dir . 'pdo_test.inc';
PDOTest::skip();

$db = PDOTest::factory();
if (@$db->getAttribute(PDO::ATTR_EMULATE_PREPARES) && !@$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false)) die('skip driver cannot use prepared statements');
?>
--FILE--
<?php
if (getenv('REDIR_TEST_DIR') === false) putenv('REDIR_TEST_DIR='.__DIR__ . '/../../pdo/tests/');
require_once getenv('REDIR_TEST_DIR') . 'pdo_test.inc';

$db = PDOTest::factory();
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $db->prepare('SELECT ? a, ? b');

$set = [
    ['a', 'b'],
    [0 => 'a', 1 => 'b'],
    [0 => 'a', 2 => 'b'], /* Note the array keys */
];

foreach ($set as $params) {
    try {
        var_dump($stmt->execute($params), $stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (Throwable $error) {
        echo $error->getMessage() . "\n";
    }
}

?>
--EXPECT--
bool(true)
array(1) {
  [0]=>
  array(2) {
    ["a"]=>
    string(1) "a"
    ["b"]=>
    string(1) "b"
  }
}
bool(true)
array(1) {
  [0]=>
  array(2) {
    ["a"]=>
    string(1) "a"
    ["b"]=>
    string(1) "b"
  }
}
SQLSTATE[HY000]: General error: 25 bind or column index out of range
