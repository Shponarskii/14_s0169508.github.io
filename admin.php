<head>
    <title>Задание 6</title>
    <meta name="viewport" content="width=device-width initial-scale=1">
    <style>
        td{
            border: black 1px solid;
        }
        table{
            border-collapse: collapse;
        }
    </style>
</head>
<body>
<?php

function authorize()
{
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="My site"');
    print('<h1>401 Авторизуйтесь!</h1>');
    exit();
}

if (empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW'])) {
    authorize();
}
$user = 'u54029';
$password = '5413631';
$db = new PDO('mysql:host=localhost;dbname=u54029', $user, $password, [PDO::ATTR_PERSISTENT => true]);
$stmt = $db->prepare("SELECT * FROM Admin where login = ? && hash_pass = ?");
$stmt->execute([$_SERVER['PHP_AUTH_USER'], md5($_SERVER['PHP_AUTH_PW'])]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$admin) {
    authorize();
}

if ($_SERVER['REQUEST_METHOD'] == "GET") {
if (!empty($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM form_power WHERE form_id=?");
    $stmtErr = $stmt->execute([$_GET['delete']]);
    $stmt = $db->prepare("DELETE FROM form1 WHERE id=?");
    $stmtErr = $stmt->execute([$_GET['delete']]);
    header('Location: ./admin.php');
}
if (!empty($_GET['change'])) {
$stmt = $db->prepare("SELECT * FROM form1 WHERE id=?");
$stmtErr = $stmt->execute([$_GET['change']]);
$person = $stmt->fetch();

$stmt = $db->prepare("SELECT ability FROM powers1 WHERE id IN (SELECT power_id FROM form_power WHERE form_id = ?)");
$stmt->execute([$_GET['change']]);
$powers = array();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    $powers[$row['ability']] = $row['ability'];
}

$stmt = $db->prepare("SELECT ability FROM powers1");
$stmt->execute();
$abilities = $stmt->fetchAll(PDO::FETCH_ASSOC);
setcookie('changed_uid', $person['id'], time() + 30 * 24 * 60 * 60);
?>
<p>Изменение данных пользователя №<?php print ($person['id']); ?></p>
<form action="" method="POST">
    <label>
        Имя:<br>
        <input name="name"
               placeholder="name" required <?php print('value="' . $person['name'] . '"'); ?>>
    </label><br>

    <label>
        E-mail:<br>
        <input name="email"
               type="email"
               placeholder="email" required <?php print('value="' . $person['email'] . '"'); ?>>
    </label><br>

    <label>
        Дата рождения:
        <input class="form" name="birthday"
               value="<?php print($person['birthday']); ?>"
               type="date"/>
    </label><br/>

    Пол: <br>
    <label><input type="radio"
                  name="gender" value="m" required <?php if ($person['gender'] == 'm') {
            print 'checked';
        } ?>>
        Мужской</label>
    <label><input type="radio"
                  name="gender" value="f"
                  required <?php if ($person['gender'] == 'f') {
            print 'checked';
        } ?>>
        Женский</label><br>

    Количество: <br>
    <label><input type="radio"
                  name="limbs" value="1"
                  required <?php if ($person['limbs'] == '1') {
            print 'checked';
        } ?>>
        1</label>
    <label><input type="radio"
                  name="limbs" value="2"
                  required <?php if ($person['limbs'] == '2') {
            print 'checked';
        } ?>>
        2</label>
    <label><input type="radio"
                  name="limbs" value="3"
                  required <?php if ($person['limbs'] == '3') {
            print 'checked';
        } ?>>
        3</label>
    <label><input type="radio"
                  name="limbs" value="4"
                  required <?php if ($person['limbs'] == '4') {
            print 'checked';
        } ?>>
        4</label><br>

    <label>
        Сверхспособности:
        <br>
        <select name="powers[]" multiple="multiple" required>
            <?php
            foreach ($abilities as $ability){
                $selected = empty($powers[$ability['ability']]) ? '' : 'selected';
                printf('<option value="%s" %s>%s</option>', $ability['ability'], $selected, $ability['ability']);
            }
            ?>
        </select>
    </label><br>

    <label>
        Биография:<br>
        <textarea name="bio"><?php print($person['bio']); ?></textarea>
    </label><br>

    <input type="submit" value="Отправить">
</form>
<?php
exit();
}
print('Вы успешно авторизовались и видите защищенные паролем данные.');

$stmt = $db->prepare("SELECT * FROM form1");
$stmtErr = $stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT ability FROM powers1 WHERE id IN (SELECT power_id FROM form_power WHERE form_id = ?)");


print ('<table>
	<thead>
		<tr>
			<td>ID</td>
			<td>Имя</td>
			<td>Почта</td>
			<td>Дата рождения</td> 
			<td>Пол</td>
			<td>Кол-во конечностей</td>
			<td>Биография</td>
			<td>Контракт</td>
			<td>Логин</td>
			<td>Хеш-пароль</td>
			<td>Способности</td>
			<td>Удалить</td>
			<td>Изменить</td>
		</tr>
	</thead>
	<tbody>');

foreach ($result as $person) {
    print ('<tr>');
    foreach ($person as $key => $value) {
        print('<td>' . $value . '</td>');
    }
    print ('<td>');
    $stmtErr = $stmt->execute([$person['id']]);
    $abilities = $stmt->fetchAll();
    foreach ($abilities as $ability) {
        print $ability['ability'] . " " ;
    }
    print ('</td>');
    print ('<td><a href="./admin.php?delete=' . $person['id'] . '">Удалить</a></td>');
    print ('<td><a href="./admin.php?change=' . $person['id'] . '">Изменить</a></td>');
    print ('</tr>');
}
print ('</tbody>
    </table>');

$stmt = $db->prepare("SELECT COUNT(1), power_id FROM form_power GROUP BY power_id");
$stmt->execute();
$statistics = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT ability FROM powers1 where id = ?");
foreach ($statistics as $statistic) {
    print ('<p>' . $statistic['COUNT(1)'] . ' человек(-а) обладают ');
    $stmtErr = $stmt->execute([$statistic['power_id']]);
    $ability = $stmt->fetch();
    print $ability['ability'] . '</p>';
}
} else {
    $stmt = $db->prepare("UPDATE form1 SET name= :name, email= :email, birthday= :birthday, gender= :gender, limbs= :limbs, bio= :bio where id = :id");
    $stmtErr = $stmt->execute(['id' => $_COOKIE['changed_uid'], 'name' => $_POST['name'], 'email' => $_POST['email'], 'birthday' => $_POST['birthday'], 'gender' => $_POST['gender'], 'limbs' => $_POST['limbs'], 'bio' => $_POST['bio']]);
    setcookie('changed_uid', '', 1);

    $stmt = $db->prepare("DELETE FROM form_power WHERE form_id=?");
    $stmtErr = $stmt->execute([$_COOKIE['changed_uid']]);

    $stmt2 = $db->prepare("INSERT INTO form_power (form_id, power_id) VALUES (:form_id, (SELECT id FROM powers1 WHERE ability=:power))");
    foreach ($_POST['powers'] as $power) {
        $stmt2->bindParam(':form_id', $_COOKIE['changed_uid']);
        $stmt2->bindParam(':power', $power);
        $stmt2->execute();
    }

    header('Location: admin.php');
}