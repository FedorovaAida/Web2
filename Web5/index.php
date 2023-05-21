<?php

header('Content-Type: text/html; charset=UTF-8');

$user = 'u52878';
$pass = '2527519';
$db = new PDO('mysql:host=localhost;dbname=u52878', $user, $pass, array(PDO::ATTR_PERSISTENT => true));

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  $messages = array();
  if (!empty($_COOKIE['save'])) {
    setcookie('save', '', 100000);
    $messages['allok'] = '<div class="good">Спасибо, результаты сохранены</div>';
    if (!empty($_COOKIE['password'])) {
      $messages['login'] = sprintf('<div class="login">Логин: <strong>%s</strong><br>
        Пароль: <strong>%s</strong><br>Войдите в аккаунт с этими данными,<br>чтобы изменить введёные значения формы</div>',
        strip_tags($_COOKIE['login']),
        strip_tags($_COOKIE['password']));
    }
    setcookie('login', '', 100000);
    setcookie('password', '', 100000);
  }

  $errors = array();
  $errors['name1'] = !empty($_COOKIE['name_error1']);
  $errors['name2'] = !empty($_COOKIE['name_error2']);
  $errors['email1'] = !empty($_COOKIE['email_error1']);
  $errors['email2'] = !empty($_COOKIE['email_error2']);
  $errors['year1'] = !empty($_COOKIE['year_error1']);
  $errors['year2'] = !empty($_COOKIE['year_error2']);
  $errors['gender1'] = !empty($_COOKIE['gender_error1']);
  $errors['gender2'] = !empty($_COOKIE['gender_error2']);
  $errors['limbs1'] = !empty($_COOKIE['limbs_error1']);
  $errors['limbs2'] = !empty($_COOKIE['limbs_error2']);
  $errors['abilities1'] = !empty($_COOKIE['abilities_error1']);
  $errors['abilities2'] = !empty($_COOKIE['abilities_error2']);
  $errors['biography1'] = !empty($_COOKIE['biography_error1']);
  $errors['biography2'] = !empty($_COOKIE['biography_error2']);
  $errors['checkboxContract'] = !empty($_COOKIE['checkboxContract_error']);

  if ($errors['name1']) {
    setcookie('name_error1', '', 100000);
    $messages['name1'] = '<p class="msg">Заполните имя</p>';
  }
  if ($errors['name2']) {
    setcookie('name_error2', '', 100000);
    $messages['name2'] = '<p class="msg">Корректно* заполните имя</p>';
  }
  if ($errors['email1']) {
    setcookie('email_error1', '', 100000);
    $messages['email1'] = '<p class="msg">Заполните email</p>';
  } else if ($errors['email2']) {
    setcookie('email_error2', '', 100000);
    $messages['email2'] = '<p class="msg">Корректно* заполните email</p>';
  }
  if ($errors['year1']) {
    setcookie('year_error1', '', 100000);
    $messages['year1'] = '<p class="msg">Неправильный формат ввода года</p>';
  } else if ($errors['year2']) {
    setcookie('year_error2', '', 100000);
    $messages['year2'] = '<p class="msg">Вам должно быть 18 лет</p>';
  }
  if ($errors['gender1']) {
    setcookie('gender_error1', '', 100000);
    $messages['gender1'] = '<p class="msg">Выберите пол</p>';
  }
  if ($errors['gender2']) {
    setcookie('gender_error2', '', 100000);
    $messages['gender2'] = '<p class="msg">Выбран неизвестный пол</p>';
  }
  if ($errors['limbs1']) {
    setcookie('limbs_error1', '', 100000);
    $messages['limbs1'] = '<p class="msg">Выберите кол-во конечностей</p>';
  }
  if ($errors['limbs2']) {
    setcookie('limbs_error2', '', 100000);
    $messages['limbs2'] = '<p class="msg">Выбрана неизвестное кол-во конечностей</p>';
  }
  if ($errors['abilities1']) {
    setcookie('abilities_error1', '', 100000);
    $messages['abilities1'] = '<p class="msg">Выберите хотя бы одну <br> сверхспособность</p>';
  } else if ($errors['abilities2']) {
    setcookie('abilities_error2', '', 100000);
    $messages['abilities2'] = '<p class="msg">Выбрана неизвестная <br> сверхспособность</p>';
  }
  if ($errors['biography1']) {
    setcookie('biography_error1', '', 100000);
    $messages['biography1'] = '<p class="msg">Расскажи о себе что-нибудь</p>';
  } else if ($errors['biography2']) {
    setcookie('biography_error2', '', 100000);
    $messages['biography2'] = '<p class="msg">Недопустимый формат ввода <br> биографии</p>';
  }
  if ($errors['checkboxContract']) {
    setcookie('checkboxContract_error', '', 100000);
    $messages['checkboxContract'] = '<p class="msg">Ознакомьтесь с контрактом</p>';
  }

  $values = array();
  $values['name'] = empty($_COOKIE['name_value']) ? '' : htmlspecialchars(strip_tags($_COOKIE['name_value']));
  $values['email'] = empty($_COOKIE['email_value']) ? '' : htmlspecialchars(strip_tags($_COOKIE['email_value']));
  $values['year'] = empty($_COOKIE['year_value']) ? '' : htmlspecialchars(strip_tags($_COOKIE['year_value']));
  $values['gender'] = empty($_COOKIE['gender_value']) ? '' : htmlspecialchars(strip_tags($_COOKIE['gender_value']));
  $values['limbs'] = empty($_COOKIE['limbs_value']) ? '' : htmlspecialchars(strip_tags($_COOKIE['limbs_value']));
  $values['abilities'] = empty($_COOKIE['abilities_value']) ? '' : strip_tags($_COOKIE['abilities_value']);
  $values['biography'] = empty($_COOKIE['biography_value']) ? '' : htmlspecialchars(strip_tags($_COOKIE['biography_value']));
  $values['checkboxContract'] = empty($_COOKIE['checkboxContract_value']) ? '' : htmlspecialchars(strip_tags($_COOKIE['checkboxContract_value']));

  if (!empty($_COOKIE[session_name()]) && session_start() && !empty($_SESSION['login'])) {
    if (count(array_filter($errors)) === 0) {
      $_SESSION['token'] = bin2hex(random_bytes(32));
      $login = $_SESSION['login'];
      try {
        $stmt = $db->prepare("SELECT application_id FROM users WHERE login = ?");
        $stmt->execute([$login]);
        $app_id = $stmt->fetchColumn();

        $stmt = $db->prepare("SELECT name, email, year, gender, limbs, biography FROM application WHERE application_id = ?");
        $stmt->execute([$app_id]);
        $dates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $db->prepare("SELECT superpower_id FROM abilities WHERE application_id = ?");
        $stmt->execute([$app_id]);
        $abilities = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        if (!empty($dates[0]['name'])) {
          $values['name'] = htmlspecialchars(strip_tags($dates[0]['name']));
        }
        if (!empty($dates[0]['email'])) {
          $values['email'] = htmlspecialchars(strip_tags($dates[0]['email']));
        }
        if (!empty($dates[0]['year'])) {
          $values['year'] = htmlspecialchars(strip_tags($dates[0]['year']));
        }
        if (!empty($dates[0]['gender'])) {
          $values['gender'] = htmlspecialchars(strip_tags($dates[0]['gender']));
        }
        if (!empty($dates[0]['limbs'])) {
          $values['limbs'] = htmlspecialchars(strip_tags($dates[0]['limbs']));
        }
        if (!empty($abilities)) {
          $values['abilities'] =  serialize($abilities);
        }
        if (!empty($dates[0]['biography'])) {
          $values['biography'] = htmlspecialchars(strip_tags($dates[0]['biography']));
        }

      } catch (PDOException $e) {
          print('Error : ' . $e->getMessage());
          exit();
      }
    }
    printf('<div id="header"><p>Вход с логином %s; uid: %d</p><a href=logout.php>Выйти</a></div>', $_SESSION['login'], $_SESSION['uid']);
  }
  include('form.php');
} else {
  $errors = FALSE;

  $name = $_POST['name'];
  $email = $_POST['email'];
  $year = $_POST['year'];
  $gender = $_POST['gender'];
  $limbs = $_POST['limbs'];
  if(isset($_POST["abilities"])) {
    $abilities = $_POST["abilities"];
    $filtred_abilities = array_filter($abilities,
    function($value) {
      return($value == 1 || $value == 2 || $value == 3);
    }
    );
  }
  $biography = $_POST['biography'];
  $checkboxContract = isset($_POST['checkboxContract']);

  if (empty($name)) {
    setcookie('name_error1', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  } else if (!preg_match('/^[\p{Cyrillic}\p{L}\d\s.,()]+$/u', $name)) {
    setcookie('name_error2', '1', time() + 24 * 60 * 60);
    setcookie('name_value', $name, time() + 30 * 24 * 60 * 60);
    $errors = TRUE;
  } else {
    setcookie('name_value', $name, time() + 30 * 24 * 60 * 60);
  }

  if (empty($email)) {
    setcookie('email_error1', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setcookie('email_error2', '1', time() + 24 * 60 * 60);
    setcookie('email_value', $email, time() + 30 * 24 * 60 * 60);
    $errors = TRUE;
  } else {
    setcookie('email_value', $email, time() + 30 * 24 * 60 * 60);
  }

  if (!is_numeric($year)) {
    setcookie('year_error1', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  } else if ((2023 - $year) < 14) {
    setcookie('year_error2', '1', time() + 24 * 60 * 60);
    setcookie('year_value', $year, time() + 30 * 24 * 60 * 60);
    $errors = TRUE;
  } else {
    setcookie('year_value', $year, time() + 30 * 24 * 60 * 60);
  }

  if (empty($gender)) {
    setcookie('gender_error1', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  } else if ($gender != 'male' && $gender != 'female') {
    setcookie('gender_error2', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  } else {
    setcookie('gender_value', $gender, time() + 30 * 24 * 60 * 60);
  }

  if (empty($limbs)) {
    setcookie('limbs_error1', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  } else if ($limbs != '2' && $limbs != '4' && $limbs != '8') {
    setcookie('limbs_error2', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  } else {
    setcookie('limbs_value', $limbs, time() + 30 * 24 * 60 * 60);
  }

  if (empty($abilities)) {
    setcookie('abilities_error1', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  } else if (count($filtred_abilities) != count($abilities)) {
    setcookie('abilities_error2', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  } else {
    setcookie('abilities_value', serialize($abilities), time() + 30 * 24 * 60 * 60);
  }

  if (empty($biography)) {
    setcookie('biography_error1', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  } else if (!preg_match('/^[\p{Cyrillic}\p{L}\d\s.,()]+$/u', $biography)) {
    setcookie('biography_error2', '1', time() + 24 * 60 * 60);
    setcookie('biography_value', $biography, time() + 30 * 24 * 60 * 60);
    $errors = TRUE;
  } else {
    setcookie('biography_value', $biography, time() + 30 * 24 * 60 * 60);
  }

  if ($checkboxContract == '') {
    setcookie('checkboxContract_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  } else {
    setcookie('checkboxContract_value', $checkboxContract, time() + 30 * 24 * 60 * 60);
  }

  if ($errors) {
    header('Location: index.php');
    exit();
  } else {
    setcookie('name_error1', '', 100000);
    setcookie('name_error2', '', 100000);
    setcookie('email_error1', '', 100000);
    setcookie('email_error2', '', 100000);
    setcookie('year_error1', '', 100000);
    setcookie('year_error2', '', 100000);
    setcookie('gender_error1', '', 100000);
    setcookie('gender_error2', '', 100000);
    setcookie('limbs_error1', '', 100000);
    setcookie('limbs_error2', '', 100000);
    setcookie('abilities_error1', '', 100000);
    setcookie('abilities_error2', '', 100000);
    setcookie('biography_error1', '', 100000);
    setcookie('biography_error2', '', 100000);
    setcookie('checkboxContract_error', '', 100000);
  }

  if (!empty($_COOKIE[session_name()]) && session_start() && !empty($_SESSION['login'])) {
    if (!empty($_POST['token']) && hash_equals($_POST['token'], $_SESSION['token'])) {
      $login = $_SESSION['login'];
      try {
        $stmt = $db->prepare("SELECT application_id FROM users WHERE login = ?");
        $stmt->execute([$login]);
        $app_id = $stmt->fetchColumn();

        $stmt = $db->prepare("UPDATE application SET name = ?, email = ?, year = ?, gender = ?, limbs = ?, biography = ?
          WHERE application_id = ?");
        $stmt->execute([$name, $email, $year, $gender, $limbs, $biography, $app_id]);

        $stmt = $db->prepare("SELECT superpower_id FROM abilities WHERE application_id = ?");
        $stmt->execute([$app_id]);
        $abil = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        if (array_diff($abil, $abilities)) {
          $stmt = $db->prepare("DELETE FROM abilities WHERE application_id = ?");
          $stmt->execute([$app_id]);

          $stmt = $db->prepare("INSERT INTO abilities (application_id, superpower_id) VALUES (?, ?)");
          foreach ($abilities as $superpower_id) {
            $stmt->execute([$app_id, $superpower_id]);
          }
        }

      } catch (PDOException $e) {
          print('Error : ' . $e->getMessage());
          exit();
      }
    } else {
      die('Ошибка CSRF: недопустимый токен');
    }
  }
  else {
    $login = 'user' . rand(1, 1000);
    $password = rand(1, 100);
    setcookie('login', $login);
    setcookie('password', $password);
    try {
      $stmt = $db->prepare("INSERT INTO application (name, email, year, gender, limbs, biography) VALUES (?, ?, ?, ?, ?, ?)");
      $stmt->execute([$name, $email, $year, $gender, $limbs, $biography]);
      $application_id = $db->lastInsertId();
      $stmt = $db->prepare("INSERT INTO abilities (application_id, superpower_id) VALUES (?, ?)");
      foreach ($abilities as $superpower_id) {
        $stmt->execute([$application_id, $superpower_id]);
      }
      $stmt = $db->prepare("INSERT INTO users (application_id, login, password) VALUES (?, ?, ?)");
      $stmt->execute([$application_id, $login, md5($password)]);
    } catch (PDOException $e) {
      print('Error : ' . $e->getMessage());
      exit();
    }
  }

  setcookie('save', '1');
  header('Location: ./');
}
