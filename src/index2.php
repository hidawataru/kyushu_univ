<DOCTYPE html>
    <html>
    
    <head lang = "ja">
        <title>Reserve_Test</title>
        <meta charset = "utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">  
        <link rel="stylesheet" href="css/bootstrap.css">
        <script type="text/javascript" src="js/jquery-3.5.1.js"></script>
        <script type="text/javascript" src="js/bootstrap.js"></script>
        <script type="text/javascript" src="js/pd.js"></script>
    	<!-- jqueryDatePicker利用 -->
        <link rel="stylesheet" href="css/jquery-ui.min.css">
        <script type="text/javascript" src="js/jquery-ui.min.js"></script>
        <script type="text/javascript" src="js/jquery.ui.datepicker-ja.min.js"></script>
        <script type="text/javascript" src="js/pd.js"></script>

        <link href="product.css" rel="stylesheet">
        <link href="../example.css" rel="stylesheet">

        <style media="screen">
            .skyblue{
                background: skyblue;
                height: 100px;
                text-align: center;
                line-height: 100px;
            }
            .pink{
                background: pink;
                height: 100px;
                text-align: center;
                line-height: 100px;
            }
        </style>
        <script>
            function MyFunction1() {
                var txt = document.getElementById("get_para");
                alert(txt.innerHTML);
                }
        </script> 
    </head>
    
    <body>
    <!-- PostGreSQLへの接続 -->
       <?php
          //接続パラメーター
        $DBHOST = "localhost";
        $DBPORT = "5432";
        $DBNAME = "reserve_kiki";
        $DBUSER = "postgres";
        $DBPASS = "admin";
        
        try{
          //DB接続
          $dbh = new PDO("pgsql:host=$DBHOST;port=$DBPORT;dbname=$DBNAME;user=$DBUSER;password=$DBPASS");
                //   print('<br>'."接続成功".'<br>');
        
          //SQL作成
          $sql = 'select * from "M_Kiki"';
        
          //SQL実行
          // クエリ実行（データを取得）
          $res = $dbh->query($sql);

        }catch(PDOException $e){
          print("接続失敗".'<br>');
          print($e.'<br>');
          die();
        }

        // // 「$res」からデータを取り出し、変数「$result」に代入。
        // // 「PDO::FETCH_ASSOC」を指定した場合、カラム名をキーとする連想配列として「$result」に格納される。
        while( $result = $res->fetch( PDO::FETCH_ASSOC ) ){
            $rows[] = $result;
        }
        
        //データベースへの接続を閉じる
        $dbh = null;
        $today = date("Y/m/d");
    ?>

        <nav class="site-header sticky-top py-1">
        <div class="container d-flex flex-column flex-md-row justify-content-between">
            <a class="py-2" href="#">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="d-block mx-auto"><circle cx="12" cy="12" r="10"></circle><line x1="14.31" y1="8" x2="20.05" y2="17.94"></line><line x1="9.69" y1="8" x2="21.17" y2="8"></line><line x1="7.38" y1="12" x2="13.12" y2="2.06"></line><line x1="9.69" y1="16" x2="3.95" y2="6.06"></line><line x1="14.31" y1="16" x2="2.83" y2="16"></line><line x1="16.62" y1="12" x2="10.88" y2="21.94"></line></svg>
            </a>
            <h4 class="text-light">九州大学　教育・研究支援センター</h1>
            <!-- <a class="py-2 d-none d-md-inline-block" href="#">Tour</a> -->
            <a class="py-2 d-none d-md-inline-block" href="index2.php">予約</a>
            <!-- <a class="py-2 d-none d-md-inline-block" href="#">Product</a> -->
            <a class="py-2 d-none d-md-inline-block" href="#">管理者</a>
        </div>
        </nav>

        <div class="container">
            <h4>機器予約一覧</h4>
            <p id="get_para">予約表示したい日付を選択して入力して下さい！</p>
            <input id="calendar" type="text" value="<?php echo $today; ?>" />
            <a href="#" class="btn btn-primary">更新</a>

            <div class="table-responsive">
            <table class="table table-hover table-bordered">
              <thead class="table-primary">
                <tr>
                  <th scope="col">#</th>
                  <th scope="col">機器名</th>
                  <th scope="col">0:00</th>
                  <th scope="col">1:00</th>
                  <th scope="col">2:00</th>
                  <th scope="col">3:00</th>
                  <th scope="col">4:00</th>
                  <th scope="col">5:00</th>
                  <th scope="col">6:00</th>
                  <th scope="col">7:00</th>
                  <th scope="col">8:00</th>
                  <th scope="col">9:00</th>
                  <th scope="col">10:00</th>
                  <th scope="col">11:00</th>
                  <th scope="col">12:00</th>
                  <th scope="col">13:00</th>
                  <th scope="col">14:00</th>
                  <th scope="col">15:00</th>
                  <th scope="col">16:00</th>
                  <th scope="col">17:00</th>
                  <th scope="col">18:00</th>
                  <th scope="col">19:00</th>
                  <th scope="col">20:00</th>
                  <th scope="col">21:00</th>
                  <th scope="col">22:00</th>
                  <th scope="col">23:00</th>
                </tr>
              </thead>
              <tbody>
              <?php
              foreach($rows as $row){
            ?>
                <tr>
                  <th scope="row"><?php echo $row['ID']; ?></th>
                  <td><?php echo $row['kiki_name']; ?></td>
                  <td colspan="24"></td>
                </tr>
             <?php
                }
             ?>
              </tbody>
            </table>
            </div>

        </div>
        
        <footer class="container py-5">
            <div class="row">
                <div class="col-12 col-md">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="d-block mb-2"><circle cx="12" cy="12" r="10"></circle><line x1="14.31" y1="8" x2="20.05" y2="17.94"></line><line x1="9.69" y1="8" x2="21.17" y2="8"></line><line x1="7.38" y1="12" x2="13.12" y2="2.06"></line><line x1="9.69" y1="16" x2="3.95" y2="6.06"></line><line x1="14.31" y1="16" x2="2.83" y2="16"></line><line x1="16.62" y1="12" x2="10.88" y2="21.94"></line></svg>
                <small class="d-block mb-3 text-muted">&copy; 2020- Pro Device Inc</small>
                </div>
                <div class="col-6 col-md">
                <!-- <h5>Features</h5> -->
                <h5>センター特徴</h5>
                <ul class="list-unstyled text-small">
                    <!-- <li><a class="text-muted" href="#">Cool stuff</a></li> -->
                    <li><a class="text-muted" href="#">スタッフ</a></li>
                    <!-- <li><a class="text-muted" href="#">Random feature</a></li> -->
                    <li><a class="text-muted" href="#">サポート内容</a></li>
                    <!-- <li><a class="text-muted" href="#">Stuff for developers</a></li> -->
                    <li><a class="text-muted" href="#">研究者向け</a></li>
                    <!-- <li><a class="text-muted" href="#">Another one</a></li> -->
                    <li><a class="text-muted" href="#">そのほか</a></li>
                </ul>
                </div>
                <div class="col-6 col-md">
                <!-- <h5>Resources</h5> -->
                <h5>機器リソース</h5>
                <ul class="list-unstyled text-small">
                    <!-- <li><a class="text-muted" href="#">Resource</a></li> -->
                    <li><a class="text-muted" href="#">リソース一覧</a></li>
                </ul>
                </div>
                <div class="col-6 col-md">
                <!-- <h5>About</h5> -->
                <h5>このサイトについて</h5>
                <ul class="list-unstyled text-small">
                    <!-- <li><a class="text-muted" href="#">Privacy</a></li> -->
                    <li><a class="text-muted" href="#">プライバシー</a></li>
                    <!-- <li><a class="text-muted" href="#">Terms</a></li> -->
                    <li><a class="text-muted" href="#">利用規約</a></li>
                </ul>
                </div>
            </div>
        </footer>


    </body>
    
    </html>