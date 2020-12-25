<?php
session_start();
/********** 手動設定 **********/
$hours_st = '08:00'; //設定開始時間('hh:nn'で指定)
$hours_end = '23:00'; //設定終了時間('hh:nn'で指定)
$hours_margin = 30; //間隔を指定(分)
$tbl_flg = true; //時間を横軸 → true, 縦軸 → falseにする
$master_key = 'special';
/********** ここまで **********/
require_once("fnc/gant1_functions.php");

/*** DB接続 ***/
  //接続パラメーター
  $DBHOST = "localhost";
  $DBPORT = "5432";
  $DBNAME = "reserve_kiki";
  $DBUSER = "postgres";
  $DBPASS = "admin";

  try{
	//DB接続
	$pdo = new PDO("pgsql:host=$DBHOST;port=$DBPORT;dbname=$DBNAME;user=$DBUSER;password=$DBPASS");

          //SQL作成
          $sql = 'select * from "M_Kiki"';
        
          //SQL実行
          // クエリ実行（データを取得）
		  $res = $pdo->query($sql);
		  
} catch (Exception $e) {
	exit('データベース接続に失敗しました。'.$e->getMessage());
}

        // // 「$res」からデータを取り出し、変数「$result」に代入。
        // // 「PDO::FETCH_ASSOC」を指定した場合、カラム名をキーとする連想配列として「$result」に格納される。
        while( $result = $res->fetch( PDO::FETCH_ASSOC ) ){
            $rows[] = $result;
		}
		//取り出した配列から機器名だけをただの配列として抜き出す_樋田
		$chapters = array_column($rows, 'kiki_name');

/*** ページ読込前の設定部分 ***/
//エラー出力する
ini_set( 'display_errors', 1 );
//タイムゾーンセット
date_default_timezone_set('Asia/Tokyo');
//本日を取得
$date = date('Y-m-d'); //YYYY-MM-DDの形

//設定時間を計算して配列化
$hours_baff = new DateTime( $date.' '.$hours_st ); //配列格納用の変数
$hours_end_date = new DateTime( $date.' '.$hours_end ); //終了時間を日付型へ
$hours = array(); //時間を格納する配列
array_push($hours, $hours_baff->format('H:i')); //配列に追加
$hours_baff = $hours_baff->modify ("+{$hours_margin} minutes"); //設定間隔を足す
while ( $hours_baff <= $hours_end_date ) { //終了時間まで繰り返す
	if ( $hours_baff->format('H:i') == '00:00' ){ //終了時間が00:00だったら
		array_push($hours, '24:00'); //24:00で配列に追加
	} else {
		array_push($hours, $hours_baff->format('H:i')); //配列に追加
	}
	$hours_baff = $hours_baff->modify ("+{$hours_margin} minutes"); //設定間隔ずつ足していく
}

//Cookie
$my_name = (string)filter_input(INPUT_COOKIE, 'my_name');
$sect = (string)filter_input(INPUT_COOKIE, 'sect');

//タイムテーブル設定
if ( $tbl_flg == true ) {
	$clm = $hours; //縦軸 → 時間
	$row = $chapters; //横軸 → 設定項目
	$clm_n = count($clm) - 1; //縦の数（時間配列の-1）
	$row_n = count($row); //横の数
} else {
	$clm = $chapters; //縦軸 → 設定項目
	$row = $hours; //横軸 → 時間
	$clm_n = count($clm); //縦の数
	$row_n = count($row) - 1; //横の数（時間配列の-1）
}

//メッセージ用変数
$log1 = '';
$log2 = '';

/*** 各種ボタンが押された時の処理 ***/
if ( isset($_POST['calendar']) ){
	/*** カレンダーがクリックされた場合 ***/
	$date = !is_string(key($_POST['calendar'])) ? '' : key($_POST['calendar']);

} elseif ( isset($_POST['register']) ) {
	/*** 登録ボタンがクリックされた場合 ***/
	//フォームに入力された情報を各変数へ格納
	foreach (array('date', 'my_name', 'sect', 'notes', 'time_st', 'time_end', 'cpt_name', 'kwd','user_id','kubun_cd','bunrui_cd') as $v) {
		$$v = (string)filter_input(INPUT_POST, $v);
	}
	
	$time_st = $date . ' ' . $time_st . ':00'; //開始時間（MySQLのDATETIMEフォーマットへ成形）
	$time_end = $date . ' ' . $time_end . ':00'; //終了時間

	//Cookie
	setcookie('my_name', $my_name, time() + 60 * 60 * 24 * 14); //14日間保存
	setcookie('sect', $sect, time() + 60 * 60 * 24 * 14);

	if( $my_name == '' || $sect == '') { //名前か所属が空欄だったら
		$log1 = '<p>備考・削除キー以外は必須項目です。</p>';
	} elseif( $time_st >= $time_end ) { //開始時間 >= 終了時間の場合
		$log1 = '<p>時間設定が不正のため、登録できませんでした。</p>';
	} else { //正常処理
		$sbm_flg = false; //予約済み時間との重複フラグを設定
		$results = $pdo->prepare(
			'SELECT *
			FROM rsv_timetable
			WHERE time_st BETWEEN :date1 AND :date2
			AND cpt_name = :cpt_name'
		);
		
		$results->bindValue(':date1', $date.' 00:00:00', PDO::PARAM_STR);
		$results->bindValue(':date2', $date.' 23:59:59', PDO::PARAM_STR);
		$results->bindValue(':cpt_name', $cpt_name, PDO::PARAM_STR);
		$results->execute();

		if ( $results ) { foreach ( $results as $value ) { //該当のデータ数繰り返す
			$time1 = strtotime( $value['time_st'] ); //該当IDの開始時刻
			$time2 = strtotime( $value['time_end'] ); //該当IDの終了時刻
			if ( $time1 <= strtotime( $time_st ) && strtotime( $time_st ) < $time2 ) {
				$sbm_flg = true; //予約済開始時刻 <= 開始時刻 < 予約済終了時刻 ならフラグを立てる
			}
			if ( $time1 < strtotime( $time_end ) && strtotime( $time_end ) <= $time2 ) {
				$sbm_flg = true; //予約済開始時刻 < 終了時刻 <= 予約済終了時刻 ならフラグを立てる
			}
			if ( strtotime( $time_st ) <= $time1 && $time2 <= strtotime( $time_end ) ) {
				$sbm_flg = true; //開始時刻 <= 予約済開始時刻 & 予約済終了時刻 <= 終了時刻 ならフラグを立てる
			}
		} }
		if( $sbm_flg == true ) { //フラグが立ってたら登録できない
			$log1 = '<p>既に予約されているため、この時間帯では登録できません。</p>';
		} else {
			//登録処理
			$sql = $pdo->prepare(
				'INSERT INTO rsv_timetable
				( name, sect, notes, time_st, time_end, cpt_name, kwd ,kubun_cd ,bunrui_cd ,user_id )
				VALUES ( :name, :sect, :notes, :time_st, :time_end, :cpt_name, :kwd ,:kubun_cd ,:bunrui_cd ,:user_id)'
			);
			$sql->bindValue(':name', $my_name, PDO::PARAM_STR);
			$sql->bindValue(':sect', $sect, PDO::PARAM_STR);
			$sql->bindValue(':notes', $notes, PDO::PARAM_STR);
			$sql->bindValue(':time_st', $time_st, PDO::PARAM_STR);
			$sql->bindValue(':time_end', $time_end, PDO::PARAM_STR);
			$sql->bindValue(':cpt_name', $cpt_name, PDO::PARAM_STR);
			$sql->bindValue(':kwd', $kwd, PDO::PARAM_STR);
			$sql->bindValue(':kubun_cd', '1', PDO::PARAM_STR);
            $sql->bindValue(':bunrui_cd', '1', PDO::PARAM_STR);
            $sql->bindValue(':user_id', $_SESSION['USER_ID'], PDO::PARAM_STR);
			$rsl = $sql->execute(); //実行
			if ( $rsl == false ){
				$log1 = '<p>登録に失敗しました。</p>';
			} else {
				$log1 = '<p>登録しました。</p>';
			}
		}
	}

} elseif( isset($_POST['delete']) ) {
	/*** 削除ボタン（キー無）がクリックされた場合 ***/
	$date = (string)filter_input(INPUT_POST, 'date');
	$id = (int)filter_input(INPUT_POST, 'id');
	$sql = $pdo->prepare( 'DELETE FROM rsv_timetable WHERE id = :id' );
	$sql->bindValue(':id', $id, PDO::PARAM_INT);
	$rsl = $sql->execute(); //実行
	if ( $rsl == false ){
		$log1 = '<p>削除に失敗しました。</p>';
	} else {
		$log1 = '<p>削除しました。</p>';
	}

} elseif ( isset($_POST['kwd_delete']) ) {
	/*** 削除ボタン（キー有）がクリックされた場合 ***/
	$date = (string)filter_input(INPUT_POST, 'date');
	$id = (int)filter_input(INPUT_POST, 'id');
	$log1 .= "<p>削除キーを入力してください。</p>\n";
	$log1 .= '<form action="" method="post">'."\n";
	$log1 .= '<input type="hidden" name="date" value="'.h($date).'" />'."\n";
	$log1 .= '<input type="hidden" name="id" value="'.h($id).'" />'."\n";
	$log1 .= '<input type="text" name="ipt_kwd" size="10" value="" />'."\n";
	$log1 .= '<input type="submit" name="rgs_delete" value="削除">'."\n";
	$log1 .= "</form>\n";

} elseif( isset($_POST['rgs_delete']) ) {
	/*** キー入力後の削除ボタンがクリックされた場合 ***/
	$date = (string)filter_input(INPUT_POST, 'date');
	$id = (int)filter_input(INPUT_POST, 'id');
	$ipt_kwd = (string)filter_input(INPUT_POST, 'ipt_kwd');
	
	$results = $pdo->prepare(	'SELECT kwd FROM rsv_timetable WHERE id = :id' );
	$results->bindValue(':id', $id, PDO::PARAM_INT);
	$results->execute();
	if ( $results ) { foreach ( $results as $value ) {
		$kwd = $value['kwd'];
	}	}

	if ( $ipt_kwd === $kwd || $ipt_kwd === $master_key ) {
		$sql = $pdo->prepare( 'DELETE FROM rsv_timetable WHERE id = :id' );
		$sql->bindValue(':id', $id, PDO::PARAM_INT);
		$rsl = $sql->execute(); //実行
		if ( $rsl == false ){
			$log1 = '<p>削除に失敗しました。</p>';
		} else {
			$log1 = '<p>削除しました。</p>';
		}
	} else {
		$log1 = '<p>キーワードが間違っているため、削除できません。</p>';
	}
}

/*** タイムテーブル生成のための下準備をする部分 ***/

foreach ($chapters as $cpt) {
	for ( $i = 0; $i < count($hours); $i++ ) {
		$data_meta[$cpt][$i] = null; //配列を定義しておく（エラー回避）
	}
}

$err_n = 0; //エラー件数カウント用
$data_n = 1; //0はデータ無しにしたいので、1から始める
//指定日付のデータをすべて抽出
$results = $pdo->prepare(
	'SELECT *
	FROM rsv_timetable
	WHERE time_st BETWEEN :date1 AND :date2'
);
$results->bindValue(':date1', $date.' 00:00:00', PDO::PARAM_STR);
$results->bindValue(':date2', $date.' 23:59:59', PDO::PARAM_STR);
$results->execute();
if ( $results ) { foreach ( $results as $value ) { //指定日付のデータ数繰り返す
	$key1 = null; //エラーキャッチ用にnullを入れておく
	$key2 = null;
	
	$time1 = substr($value['time_st'], 11, 5); //該当データの開始日時'00:00'抜出
	$key1 = array_search($time1, $hours); //時間配列内の番号	
	$time2 = substr($value['time_end'], 11, 5); //該当データの終了日時'00:00'抜出
	$key2 = array_search($time2, $hours); //時間配列内の番号
	if ( is_numeric($key1) == false || is_numeric($key2) == false || in_array($value['cpt_name'], $chapters) == false	) {
		$log2 .= '<li>'.h($value['cpt_name']).'('.h($value['name']).','.h($value['sect']).') '.$time1.'～'.$time2."</li>\n"; //エラー内容格納
		$err_n++; //エラー件数カウントアップ
	} else {
		//$data_meta['項目名']['開始時間配列番号']へナンバリングしていく
		$data_meta[$value['cpt_name']][$key1] = $data_n;
		//必要な情報を格納しておく
		$ar_block[$data_n] = $key2 - $key1; //開始時間から終了時間までのブロック数
		$ar_id[$data_n] = $value['id'];
		$ar_name[$data_n] = $value['name'];
		$ar_sect[$data_n] = $value['sect'];
		$ar_notes[$data_n] = $value['notes'];
		$ar_kwd[$data_n] = $value['kwd'];
		$ar_user_id[$data_n] = $value['user_id'];
		$ar_kubun_cd[$data_n] = $value['kubun_cd'];
		$data_n++; //データ数カウントアップ
		
	}
	
} }

?>
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
			/* .table-fixed tr:nth-of-type(odd) th:first-child {
			background-color: #eee;
			}
			.table-fixed tr:nth-of-type(odd) td:first-child {
			background-color: #eee;
			}
			.table-fixed tr:nth-of-type(even) td:first-child {
			background-color: #fff;
			} */
		</style>
</head>
<body>
	<?php include('top.php'); ?>
	<div id="content">

<?php
/*** メッセージ ***/
if ( $log1 != '' ) { //処理メッセージがある場合
	$log1 = '<p class="msg">処理メッセージ</p>'."\n".$log1;
}
if ( $log2 != '' ) { //エラーメッセージがある場合
	$log2 = '<p class="msg">'.$err_n."件の不整合データを表示できませんでした。</p>\n<ul>\n".$log2;
	$log2 .= "</ul>";
}
if ( $log1 != '' || $log2 != '' ) { //どちらかのメッセージがある場合
	echo '<div id="attention">'."\n";
	if ( $log1 != '' ) { echo $log1."\n"; } //処理メッセージがある場合
	if ( $log1 != '' && $log2 != '' ) { echo "<br />\n"; } //両方ある場合は改行も
	if ( $log2 != '' ) { echo $log2."\n"; } //エラーメッセージがある場合
	echo "</div>\n";
}
?>

<div class="container">
	<!-- 登録フォーム -->
	<div class="row">
		<div class="col-md-5">
			<br />
			<button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapseContent01" aria-expanded="false" aria-controls="collapseContent01"> 新規予約登録</button>
			<div class="collapse" id="collapseContent01">
				<div class='card bg-light' style="max-width: 25rem;">
					<div class='card-body ml-1 pb-0'>
						<h2　class='card-title text-primary'>新規予約登録フォーム</h2>
						<div id="form_box">
							<form action="" name="iptfrm" method="post">
								<input type="hidden" name="date" value="<?php echo h($date); ?>" />
								<br />
								<label>名前</label>
								<input type="text" name="my_name" size="10" value="<?php echo h($my_name); ?>" />
								<br />
								<label>所属</label>
								<input type="text" name="sect" size="10" value="<?php echo h($sect); ?>" />
								<br />
								<label>備考</label>
								<input type="text" name="notes" size="10" value="" />
								<br />
								<label>開始時間</label>
								<select name="time_st" onChange="autoPlus(this)">
								<?php
								for ( $i=0; $i<count($hours)-1; $i++ ) {
									echo '	<option value="'.$hours[$i].'">'.$hours[$i].'</option>'."\n";
								}
								?>
								</select>
								<br />
								<label>終了時間</label>
								<select name="time_end">
								<?php
								for ( $i=1; $i<count($hours); $i++ ) {
									echo '	<option value="'.$hours[$i].'">'.$hours[$i].'</option>'."\n";
								}
								?>
								</select>
								<br />
								<label>予約機器</label>
								<select name="cpt_name">
								<?php
								foreach ($chapters as $value) {
									echo '<option value="'.$value.'">'.$value.'</option>';
								}
								?>
								</select>
								<br />
								<label>削除キー</label>
								<input type="text" name="kwd" size="10" value="" />
								<br />
								<div class='text-center'>
								<input class="btn btn-primary" type="submit" name="register" value="登録" />
								</div>
							</form>
						</div><!-- /#form_box -->
					</div><!-- /#Card Body -->
				</div><!-- /#Card -->
			</div><!-- /#collapseContent01 -->
		</div><!-- /#col -->

		<!-- カレンダー部分 -->
		<div class="col">
			<div id="calendar_box">
				<h4>予約日カレンダー</h4>
				<?php
				$get_y = date('Y'); //本日の年
				$get_m = date('n'); //本日の月
				$i = 0;
				while ( $i < 2 ) { //今月から3つ出したかったら
					get_rsv_calendar($get_y, $get_m, $date); //カレンダー出力
					$get_m++; //月+1
					if ( $get_m > 12 ) { //12月を超えたら
						$get_m = 1; //1月へ
						$get_y++; //年+1
					}
					$i++;
				}
				?>
			</div><!-- /#calendar_box -->
		</div><!-- /#col -->
	</div><!-- /#row -->
</div><!-- /#container -->

<!-- タイムテーブル -->
<div class="container-fluid">

<?php $sp_date = explode("-", $date); ?>
	<h3><?php printf('%s年%s月%s日', $sp_date[0], $sp_date[1], $sp_date[2]); ?></h3>

<div class="table-responsive">
	<div id="timetable_box">


	<?php
	
	for ( $i = 0; $i < $clm_n; $i++ ) {
		$span_n[$i] = 0; //rowspan結合数を格納する配列にゼロを入れておく
	}
	//ここから $timetable_output へ table の記述を入れていく
	$timetable_output = '<table id="timetable" class="table table-fixed">'."\n<thead>\n<tr>\n".'<th id="origin">時間</th>'."\n";
	for ( $i = 0; $i < $clm_n; $i++ ) {
		$timetable_output .= '<th class="cts">'.$clm[$i]."</th>\n"; //横軸見出し
	}
	$timetable_output .= "</tr>\n</thead>\n<tbody>\n";
	for ( $i = 0; $i < $row_n; $i++ ) { //縦軸の数繰り返す
		$timetable_output .= "<tr><td><a href='gant2-1.php?kikiname=".$row[$i]."'>".$row[$i].'</a></td>'; //縦軸見出し
		for ( $j = 0; $j < $clm_n; $j++ ) { //横軸の数繰り返す
			if ( $tbl_flg == false && $span_n[$j] > 0 ) { //時間軸が縦の場合の繰り上げ処理
				$span_n[$j]--; //rowspan結合の数だけtd出力をスルー
			} else { //通常時
				$block = '';
				$data_n = 0; //ゼロはデータ無し
				if ( $tbl_flg == true ) { //時間軸が横なら
					$data_n = $data_meta[$row[$i]][$j];
				} else { //時間軸が縦なら
					$data_n = $data_meta[$clm[$j]][$i];
				}
				if ( $data_n == 0 ) { //データが無いとき
					$timetable_output .= '<td>&nbsp;</td>'; //空白を入れる
				} else { //データが有るとき
					if ( $ar_block[$data_n] > 1 ) { //ブロックが2つ以上
						if ($tbl_flg == true) { //時間軸が横だったら
							if ($ar_user_id[$data_n] == $_SESSION['USER_ID']&&$ar_kubun_cd[$data_n] == 1) {//自分の予約データ
                                //$block = ' colspan="'.$ar_block[$data_n].'"'; //横方向へ結合
                                $block = ' colspan="'.$ar_block[$data_n].'" style="background-color: #FF4500" '; //赤色に変えて横方向へ結合

                            }elseif ($ar_kubun_cd[$data_n] == 2) {//修理の場合
                                $block = ' colspan="'.$ar_block[$data_n].'" style="background-color: #228B22" '; //緑色に変えて横方向へ結合


                            }elseif ($ar_kubun_cd[$data_n] == 3) {//その他の場合
                                    $block = ' colspan="'.$ar_block[$data_n].'" style="background-color: #FFD700" '; //黄色を変えて横方向へ結合
                                
                            
                            }elseif ($ar_user_id[$data_n] <> $_SESSION['USER_ID']&&$ar_kubun_cd[$data_n] == 1) {//自分の予約データ以外
                                //$block = ' colspan="'.$ar_block[$data_n].'"'; //横方向へ結合
                                $block = ' colspan="'.$ar_block[$data_n].'" style="background-color: #00BFFF" '; //青色に変えて横方向へ結合
                            }
							$j = $j + $ar_block[$data_n] - 1; //colspan結合ぶん横軸数を繰り上げ
							
						} else { //時間軸が縦だったら
							$block = ' rowspan="'.$ar_block[$data_n].'"'; //縦方向へ結合
							$span_n[$j] = $ar_block[$data_n] - 1; //rowspan結合数を格納→冒頭で繰り上げ処理
						}
					} elseif ( $ar_block[$data_n] = 1 ) { //ブロックが1つ
                        if ($ar_user_id[$data_n] == $_SESSION['USER_ID']&&$ar_kubun_cd[$data_n] == 1) {//自分が予約したデータ
                            $block = ' style="background-color: #FF4500" '; //赤色出力
                        }elseif ($ar_kubun_cd[$data_n] == 2) {//修理の場合
                            $block = ' style="background-color: #228B22" '; //緑色出力
                        }elseif ($ar_kubun_cd[$data_n] == 3) {//その他の場合
                            $block = ' style="background-color: #FFD700" '; //黄色出力
                        }elseif ($ar_user_id[$data_n] <> $_SESSION['USER_ID']&&$ar_kubun_cd[$data_n] == 1) {//自分の予約データ以外
                            $block = ' style="background-color: #00BFFF" '; //青色出力
                        }
                    }
					
					$cts = h($ar_name[$data_n]).'（'.h($ar_sect[$data_n]).'）<br />'.h($ar_notes[$data_n]); //htmlエスケープしながら中身成形
					if ( $ar_kwd[$data_n] === '' ) { //削除キー無
						//onsubmitでJavaScriptを呼び出す
						$dlt = '<form action="" method="post" onsubmit="return dltChk()"><input type="hidden" name="date" value="'.$date.'" /><input type="hidden" name="id" value="'.$ar_id[$data_n].'" /><input type="submit" name="delete" value="×"></form>';
					} else { //削除キー有
						//カギ画像付加
						$dlt = '<form action="" method="post"><input type="hidden" name="date" value="'.$date.'" /><input type="hidden" name="id" value="'.$ar_id[$data_n].'" /><input type="submit" name="kwd_delete" value="×"></form><img src="key.gif" width="18" height="18" />';
					}
					$timetable_output .= '<td class="exist"'.$block.'>'.$cts.$dlt.'</td>'; //tdの中に出力
				}
			}
		} //横軸for
		$timetable_output .= "</tr>\n";
	} //縦軸for
	$timetable_output .= "</tbody>\n</table>\n";
	echo $timetable_output; //出力
	?>
</div>	
</div>	
</div><!-- /#timetable_box -->




</div><!-- #content -->


</body>
</html>