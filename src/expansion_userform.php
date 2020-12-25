<!-- ヘッダ部読込 -->
<?php 
        //メッセージ用変数
        $log1 = '';
        session_start();

        if ( isset($_POST['register']) ){
        //接続パラメーター
        $DBHOST = "localhost";
        $DBPORT = "5432";
        $DBNAME = "reserve_kiki";
        $DBUSER = "postgres";
        $DBPASS = "admin";
        //フォームからの値をそれぞれ変数に代入
        $phone_extension = $_POST['phone_extension'];
        $mail = $_POST['email'];
        $user_email = $_SESSION['EMAIL'];
        try {
            $pdo = new PDO("pgsql:host=$DBHOST;port=$DBPORT;dbname=$DBNAME;user=$DBUSER;password=$DBPASS");

        $results = $pdo->prepare(
            'UPDATE "M_User" SET mail_address2 = :mail , phone_extension = :phone_extension WHERE mail_address1 = :mail_address1'
        );
        $results->bindValue(':mail', $mail, PDO::PARAM_STR);
        $results->bindValue(':phone_extension', $phone_extension, PDO::PARAM_STR);
        $results->bindValue(':mail_address1', $_SESSION['EMAIL'], PDO::PARAM_STR);
        $results->execute();
        //メッセージ用変数
        if ( $results == false ){
            $log1 = '<p>登録に失敗しました。</p>';
        } else {
            $log1 = '<p>登録しました。</p>';
        }
      
        } catch (PDOException $e) {
            $msg = $e->getMessage();
        }

}
        //初期値取得
        //接続パラメーター
        $DBHOST = "localhost";
        $DBPORT = "5432";
        $DBNAME = "reserve_kiki";
        $DBUSER = "postgres";
        $DBPASS = "admin";
        try {
            $pdo = new PDO("pgsql:host=$DBHOST;port=$DBPORT;dbname=$DBNAME;user=$DBUSER;password=$DBPASS");

        $results = $pdo->prepare(
            'SELECT * FROM "M_User" WHERE mail_address1 = :mail_address1'
        );
        $results->bindValue(':mail_address1', $_SESSION['EMAIL'], PDO::PARAM_STR);
        $results->execute();

        $row = $results->fetch(PDO::FETCH_ASSOC);
            $kojin_mail = $row['mail_address2'];
            $naisen = $row['phone_extension'];
            } catch (PDOException $e) {
                $msg = $e->getMessage();
            }


?>

<DOCTYPE html>
    <html>

    <head>
		<link rel="stylesheet" href="css/gant1.css">
        <script type="text/javascript" src="js/gant1.js"></script>
		<link href="product.css" rel="stylesheet">
		<meta name="viewport" content="width=device-width, initial-scale=1">  
        <link rel="stylesheet" href="css/bootstrap.css">
        <script type="text/javascript" src="js/jquery-3.5.1.js"></script>
        <script type="text/javascript" src="js/bootstrap.js"></script>
        <script type="text/javascript" src="js/pd.js"></script>

		<!-- /*左列固定*/ -->
		<style>
			.table-fixed th:first-child, td:first-child {
			position: sticky;  position: -webkit-sticky;
			left: 0;
			background-color: #fff;
			}

		</style>
</head>

<body>
	<?php include('top.php'); ?>
<div id="content">
        <?php
        /*** メッセージ ***/
        if ( $log1 != '') { //処理メッセージがある場合
            $log1 = '<p class="msg">処理メッセージ</p>'."\n".$log1;
        }
        echo '<div id="attention">'."\n";
        if ( $log1 != '' ) { echo $log1."\n"; } //処理メッセージがある場合
        echo "</div>\n";
?>
<div class="container">
	<!-- 登録フォーム -->

		
			
						<h2　class='card-title text-primary'>ユーザー情報登録フォーム</h2>
						<div id="form_box">
							<form action="" name="iptfrm" method="post">
                                <br />
								<br />
								<label>個人用メールアドレス</label>
								<input type="text" name="email" size="30" value="<?php echo $kojin_mail; ?>" />
								<br />
								<label>内線番号      </label>
								<input type="text" name="phone_extension" size="10" value="<?php echo $naisen; ?>" required/>
								<div class='text-center'>
								<input class="btn btn-primary" type="submit" name="register" value="登録" />
								</div>
							</form>
						</div><!-- /#form_box -->




</body>
</html>