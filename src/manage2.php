<DOCTYPE html>
    <html>
    
    <head lang = "ja">
        <title>予約システム_管理者</title>
        <meta charset = "utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">  
        <link rel="stylesheet" href="css/bootstrap.css">
        <script type="text/javascript" src="js/jquery-3.5.1.js"></script>
        <script type="text/javascript" src="js/bootstrap.js"></script>
        <script type="text/javascript" src="js/jquery-3.3.1.slim.min.js"></script>
        <script type="text/javascript" src="js/bootstrap.min.js"></script>
        <script type="text/javascript" src="js/pd.js"></script>
    	<!-- jqueryDatePicker利用 -->
        <link rel="stylesheet" href="css/jquery-ui.min.css">
        <script type="text/javascript" src="js/jquery-ui.min.js"></script>
        <script type="text/javascript" src="js/jquery.ui.datepicker-ja.min.js"></script>
        <script type="text/javascript" src="js/pd.js"></script>

        <link href="product.css" rel="stylesheet">
        <link href="../example.css" rel="stylesheet">
        
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

          //タイムゾーンの設定(しとかないと時刻がズレる)
        ini_set('date.timezone', 'Asia/Tokyo');

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

        <!-- ヘッダ部読込 -->
        <?php include('top.php'); ?>

        <div class="container">
            <h4>機器一覧</h4>

            <div class="table-responsive">
            <table class="table table-hover table-bordered">
              <thead class="table-primary">
                <tr>
                  <th scope="col">ID</th>
                  <th scope="col">機器名</th>
                  <th scope="col">告知内容１</th>
                  <th scope="col">告知内容２</th>
                  <th scope="col">操作</th>
                </tr>
              </thead>
              <tbody>
              <?php
              foreach($rows as $row){
            ?>
                <tr>
                  <th scope="row"><?php echo $row['ID']; ?></th>
                  <td><?php echo $row['kiki_name']; ?></td>
                  <td><?php echo $row['msg']; ?></td>
                  <td><?php echo $row['msg2']; ?></td>
                  <td>
                    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#testModal"
                        data-msg="<?php echo $row['msg']; ?>" 
                        data-msg2="<?php echo $row['msg2']; ?>" 
                        data-cpt="<?php echo $row['kiki_name']; ?>" 
                        >
                    編集</button>
                  </td>
                </tr>
             <?php
                }
             ?>
              </tbody>
            </table>
            </div>

            <!-- ボタン・リンククリック後に表示される画面の内容 -->
            <div class="modal fade" id="testModal" tabindex="-1" role="dialog" aria-labelledby="basicModal" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">機器マスタ編集</h4>
                        </div>
                        <div class="modal-body">
                            <h5><span class="label label-primary">機器名称</span></h5>
                            <input type="text" class="form-control" id="recipient-kikiname">
                            <h5><span class="label label-primary">告知メッセージ１</span></h5>
                            <input type="text" class="form-control" id="recipient-name1">
                            <h5><span class="label label-primary">告知メッセージ２</span></h5>
                            <input type="text" class="form-control" id="recipient-name2">
                            <label>データを更新しますか？</label>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">閉じる</button>
                            <button type="button" class="btn btn-success">更新</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ボタン・リンククリック後のイベント内容 -->
            <script>
                $('#testModal').on('show.bs.modal', function (event) {
                    // ボタンを取得
                    var button = $(event.relatedTarget);
                    // data-***の部分を取得
                    var sampledata = button.data('msg1');
                    var sampledata2 = button.data('cpt');
                    var sampledata3 = button.data('msg2');
                    var modal = $(this);
                    // モーダルに取得したパラメータを表示
                    // 以下ではh5のモーダルタイトルのクラス名を取得している
                    modal.find('.modal-body input#recipient1-name').val(sampledata);
                    modal.find('.modal-body input#recipient-kikiname').val(sampledata2);
                    modal.find('.modal-body input#recipient2-name').val(sampledata3);
                })
            </script> 

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