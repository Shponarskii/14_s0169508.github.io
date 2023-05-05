<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $messages = array();
    if (!empty($_COOKIE['save'])) {
        setcookie('save', '');
        $messages['success'] = 'Спасибо, результаты сохранены.';
        if (!empty($_COOKIE['login']))
        {
            $messages['success'] = sprintf(' Вы можете <a href="login.php">войти</a> с логином <strong>%s</strong> и паролем <strong>%s</strong> для изменения данных.',
                    strip_tags($_COOKIE['login']), strip_tags($_COOKIE['password']));
            setcookie('login', '', 1);
            setcookie('password', '', 1);
        }
    }

    $errors = array();
    $errors['name'] = !empty($_COOKIE['name_error']);
    $errors['life'] = !empty($_COOKIE['life_error']);


    if ($errors['name']) {
        $messages['name'] = 'Заполните имя, используя только латинские буквы<br>';
    }

    if ($errors['life']) {
        $messages['life'] = 'Заполните биографию, используя только латинские буквы<br>';
    }

    $values = array();
    $values['name'] = empty($_COOKIE['name_value']) ? '' : $_COOKIE['name_value'];
    $values['email'] = empty($_COOKIE['email_value']) ? '' : $_COOKIE['email_value'];
    $values['dob'] = empty($_COOKIE['dob_value']) ? '' : $_COOKIE['dob_value'];
    $values['radio-1'] = empty($_COOKIE['radio-1_value']) ? '' : $_COOKIE['radio-1_value'];
    $values['radio-2'] = empty($_COOKIE['radio-2_value']) ? '' : $_COOKIE['radio-2_value'];
    $values['life'] = empty($_COOKIE['life_value']) ? '' : $_COOKIE['life_value'];
    $values['choice'] = empty($_COOKIE['choice_value']) ? '' : $_COOKIE['choice_value'];

    $values['powers'] = [];
    $powersCookie = array();
    if (!empty($_COOKIE['powers_value'])) {
        $powersCookie = (array)json_decode($_COOKIE['powers_value']);
        foreach ($powersCookie as $power) {
            $values['powers'][$power] = $power;
        }
    }
    include('form.php');
    exit();
} else {

    $name = $_POST['name'];
    $email = $_POST['email'];
    $gender = $_POST['radio-1'];
    $limbs = $_POST['radio-2'];
    $dob = $_POST['dob'];
    $bio = $_POST['life'];
    $powers = $_POST['powers'];
    $contract = $_POST['choice'];

    $errors = FALSE;

    if (!preg_match("/^[a-zA-z]*$/", $name)) {
        setcookie('name_error', '1');
        $errors = TRUE;
    } else {
        setcookie('name_value', $name, time() + 12 * 30 * 24 * 60 * 60);
        setcookie('name_error', '');
    }

    if (!preg_match("/^([a-zA-Z' ]+)$/", $bio)) {
        setcookie('life_error', '1');
        $errors = TRUE;
    } else {
        setcookie('life_value', $bio, time() + 12 * 30 * 24 * 60 * 60);
        setcookie('life_error', '');
    }

    if (!empty($_COOKIE[session_name()]) && !empty($_SESSION['login'])) {
        printf('Вход с логином %s, id %d', $_SESSION['login'], $_SESSION['id']);
    }

    setcookie('email_value', $email, time() + 12 * 30 * 24 *60 * 60);
    setcookie('dob_value', $dob, time() + 12 * 30 * 24 * 60 * 60);
    setcookie('radio-1_value', $gender, time() + 12 * 30 * 24 * 60 * 60);
    setcookie('radio-2_value', $limbs, time() + 12 * 30 * 24 * 60 * 60);
    setcookie('powers_value', json_encode($powers), time() + 12 * 30 * 24 * 60 * 60);
    setcookie('choice_value', $contract, time() + 12 * 30 * 24 * 60 * 60);

    if ($errors) {
        header('Location: index.php');
        exit();
    } else {
        setcookie('name_error', '');
        setcookie('life_error', '');
    }

    $usr = 'u54029';
    $password = '5413631';
    $connection = new PDO("mysql:host=localhost;dbname=u54029", $usr, $password, array(PDO::ATTR_PERSISTENT => true));

    if (!empty($_COOKIE[session_name()]) && session_start() && !empty($_SESSION['login'])) {
        $st = $connection->prepare("UPDATE form1 SET name= :name, email= :email, birthday= :birthday, gender= :gender, limbs= :limbs, bio= :bio, contract= :contract where id = :id");
        $sterror = $st->execute(['id' => $_SESSION['id'], 'name' => $name,'email' => $email , 'birthday' => $dob, 'gender' => $gender, 'limbs' => $limbs, 'bio' => $bio, 'contract' => $contract]);
        $st = $connection->prepare("DELETE FROM form_power WHERE form_id=:form_id;");
        $sterror = $st->execute(['form_id' => $_SESSION['id']]);
        foreach ($powers as $power) {
            $ex3 = $connection->prepare("INSERT INTO form_power (form_id, power_id) VALUES (:form_id, (SELECT id FROM powers1 WHERE ability=:power))");
            $ex3->bindParam(':form_id', $_SESSION['id']);
            $ex3->bindParam(':power', $power);
            $ex3->execute();
        }
    } else {
        try {
            $id = uniqid();
            $login = strval(rand(10000000,99999999));
            $pass = substr(md5($id), 5, 10);
            $hash = substr(hash("md5", $pass), 0, 10);

            $query1 = "INSERT INTO form1 (name, email, birthday, gender, limbs, bio, contract, id_login, id_password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $ex1 = $connection->prepare($query1);
            $ex1->execute(array($name, $email, $dob, $gender, $limbs, $bio, $contract, $login, $hash));
            $id_user = $connection->lastInsertId();

            $_SESSION['login'] = $login;
            $_SESSION['id'] = intval($id_user);
            setcookie('login', $login, time() + 12 * 30 * 24 * 60 * 60);
            setcookie('password', $pass, time() + 12 * 30 * 24 * 60 * 60);
    
            $query3 = "INSERT INTO form_power (form_id, power_id) VALUES (:form_id, (SELECT id FROM powers1 WHERE ability=:power))";
            $ex3 = $connection->prepare($query3);
    
            foreach ($powers as $power) {
                $ex3->bindParam(':form_id', $_SESSION['id']);
                $ex3->bindParam(':power', $power);
                $ex3->execute();
            }
    
        } catch (PDOException $e) {
            print('Database error : ' . $e->getMessage());
            exit();
        }
    }

    setcookie('save', '1', time() + 12 * 30 * 24 * 60 * 60);
}
header('Location: ?save=1');