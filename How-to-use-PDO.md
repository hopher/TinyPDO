
### How to use Pdo

```
#数据库连接
$dbtype = 'mysql';
$host = 'localhost';
$db = 'your db';
$user = 'root';
$psw = 'your pass word';

$dsn = $dbtype . ':host=' . $host . ';' . 'dbname=' . $db;

try {
    $dbh = new PDO($dsn, $user, $psw, array(PDO::ATTR_PERSISTENT => true));
    echo '连接成功<br>';
} catch (Exception $e) {
    die('Connect Failed Message: ' . $e->getMessage());
}

#使用query函数查询
$sql = 'SELECT * FROM user';
$query = $dbh->query($sql);
$query->setFetchMode(PDO::FETCH_ASSOC);    //设置结果集返回格式,此处为关联数组,即不包含index下标
$rs = $query->fetchAll();
var_dump($rs);

#使用exec函数进行INSERT,UPDATE,DELETE,结果返回受影响的行数
$sql = 'INSERT INTO user (`userName`, `userPassword`, `userAge`) SELECT (MAX(userId) + 1), \'123456\', 18 FROM user';    //插入一行用户数据,其中userName使用userId最大值+1
// $rs = $dbh->exec($sql);
// var_dump($rs) . '<br>';
#使用prepareStatement进行CURD
$sql = 'SELECT * FROM user WHERE userId = ?';
$stmt = $dbh->prepare($sql);
$stmt->bindParam(1, $userId);    //绑定第一个参数值
$userId = 1;

$stmt->execute();
$stmt->setFetchMode(PDO::FETCH_ASSOC);
$rs = $stmt->fetchAll();
var_dump($rs);

#使用事务
try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    //设置错误模式,发生错误时抛出异常
    $dbh->beginTransaction();
    $sql1 = 'SELECT bookNum FROM book WHERE bookId = ? FOR UPDATE';    //此处加上行锁,可以对bookNum做一些判断,bookNum>1,才做下一步更新操作
    $sql2 = 'UPDATE book SET bookNum=bookNum-1 WHERE bookId = ?';    //加上行锁后,如果user1在买书,并且user1的买书过程没有结束,user2就不能执行SELECT查询书籍数量的操作,这样就保证了不会出现只有1本书,却两个人同时买的状况
    $stmt1 = $dbh->prepare($sql1);
    $stmt2 = $dbh->prepare($sql2);
    $stmt1->bindParam(1, $userId);
    $stmt2->bindParam(1, $userId);
    $userId = 1;
    $stmt1->execute();
    $stmt2->execute();
    $dbh->commit();
} catch (Exception $e) {
    $dbh->rollBack();
    die('Transaction Error Message: ' . $e->getMessage());
}

```