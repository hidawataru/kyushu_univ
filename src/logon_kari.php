<?php

/*** DB接続 ***/
  //接続パラメーター
  $DBHOST = "localhost";
  $DBPORT = "5432";
  $DBNAME = "reserve_kiki";
  $DBUSER = "postgres";
  $DBPASS = "admin";

session_start();

//POSTのvalidate
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
  echo '入力された値が不正です。';
  return false;
}
//DB内でPOSTされたメールアドレスを検索
try {
  $pdo = new PDO("pgsql:host=$DBHOST;port=$DBPORT;dbname=$DBNAME;user=$DBUSER;password=$DBPASS");  
  $stmt = $pdo->prepare('select * from "M_User" where mail_address1 = ?');
  $stmt->execute([$_POST['email']]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (\Exception $e) {
  echo $e->getMessage() . PHP_EOL;
}

//emailがDB内に存在しているか確認
if (!isset($row['mail_address1'])) {
  echo 'メールアドレスが間違っています。';
  return false;
}


//パスワード確認後sessionにメールアドレスを渡す
if ($_POST['password'] == $row['password']) {
  session_regenerate_id(true); //session_idを新しく生成し、置き換える
  $_SESSION['EMAIL'] = $row['mail_address1'];
  $_SESSION['USER_ID'] = $row['user_id'];
  $_SESSION['USER_NM'] = $row['user_nm'];
  $_SESSION['AUTHORITY_CD'] = $row['authority_cd'];
  $_SESSION['SECT'] = $row['sect'];

  $msg = 'ログインしました。';
  $link = '<a href="gant1.php"></a>';


  //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
  //実際のURLに変更する必要あり
  //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
  header('Location: http://localhost:2964/index.php');
} else {
  //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
  //実際のURLに変更する必要あり
  //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
  header('Location: http://localhost:2964/singup.php');
  return false;
}

?>
<h1><?php echo $msg; ;?></h1>
echo "<a href='/gant1.php'>予約画面はこちら。</a>"