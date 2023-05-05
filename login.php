<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'GET') 
{
    if (isset($_GET['exit']))
    {
        session_destroy();
        foreach ($_COOKIE as $item => $value)
            setcookie($item, '', 1);
        header('Location: ./login.php');
    }

    if (!empty($_SESSION['login'])) 
    {
        print ('<div>Вы авторизованы как '. $_SESSION['login'] . ', id ' . $_SESSION['id'] . '</div>')
        ?>
        <a href="./login.php?exit=1">Выход</a>
        <a href="./">Главная страница</a>
        <?php
        exit();
    } else {
    ?>

    <form action="./login.php" method="POST">
        <input name="login" placeholder = "Введите ваш логин" required>
        <input name="password" placeholder = "Введите ваш пароль" required>
        <input type="submit" value="Войти">
    </form>
        <a href="./">Главная страница</a>
    <?php
    }
}
else 
{
    $user = 'u54029';
    $pass = '5413631';
    $connection = new PDO('mysql:host=localhost;dbname=u54029', $user, $pass, [PDO::ATTR_PERSISTENT => true]);
    $st = $connection->prepare("SELECT * FROM form1 WHERE id_login = :id_login && id_password = :id_password;");
    $sterror = $st->execute(['id_login' => $_POST['login'], 'id_password' => substr(hash("md5", $_POST['password']), 0, 10)]);
    $result = $st->fetch(PDO::FETCH_ASSOC);
    if (!$result) 
    {
        print ("Пользователя с таким логином и паролем не существует");
        print ('<p><a href="./login.php">Вернуться</a></p>');
        exit();
    }

    $_SESSION['login'] = $_POST['login'];
    $_SESSION['id'] = $result['id'];

    setcookie('name_value', $result['name'], time() + 12 * 30 * 24 * 60 * 60);
    setcookie('email_value', $result['email'], time() + 12 * 30 * 24 * 60 * 60);
    setcookie('dob_value', $result['birthday'], time() + 12 * 30 * 24 * 60 * 60);
    setcookie('radio-1_value', $result['gender'], time() + 12 * 30 * 24 * 60 * 60);
    setcookie('radio-2_value', $result['limbs'], time() + 12 * 30 * 24 * 60 * 60);
    setcookie('life_value', $result['bio'], time() + 12 * 30 * 24 * 60 * 60);
    setcookie('choice_value', $result['contract'], time() + 12 * 30 * 24 * 60 * 60);

    $st = $connection->prepare("SELECT ability FROM powers1 WHERE id IN (SELECT power_id FROM form_power WHERE form_id = ?)");
    $st->execute([$_SESSION['id']]);
    while ($sterror = $st->fetch()){
        $powers[$sterror['ability']] = $sterror['ability'];
    }

    setcookie('powers_value', json_encode($powers), time() + 12 * 30 * 24 * 60 * 60);
    
    header('Location: ./login.php');
}