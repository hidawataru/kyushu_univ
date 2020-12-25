<?php
/*** htmlエスケープ ***/
function h($str) {
	return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/*** カレンダー ***/
function get_rsv_calendar($yyyy, $mm, $date) {
	$thisyear = $yyyy; //年
	$thismonth = $mm; //月
	$unixmonth = mktime(0, 0 , 0, $thismonth, 1, $thisyear); //該当月1日のタイムスタンプ
	$last_day = date('t', $unixmonth); //該当月の日数

	$calendar_output = '<table class="rsv_calendar">
	<caption>' . $thisyear .'年' . $thismonth . '月' . '</caption>
	<thead>
	<tr>';

	$myweek = array("日", "月", "火", "水", "木", "金", "土"); //曜日定義

	foreach ( $myweek as $wd ) {
		$calendar_output .= "\n\t\t<th scope=\"col\" title=\"$wd\">$wd</th>"; //曜日出力
	}

	$calendar_output .= '
	</tr>
	</thead>

	<tbody>
	<form action="" method="post">
	<tr>';

	$pad = date('w', $unixmonth); //該当月1日の曜日番号（日:0～土:6）
	if ( 0 != $pad )
		$calendar_output .= "\n\t\t".'<td colspan="'. $pad .'" class="pad">&nbsp;</td>'."\n\t\t";

	for ( $day = 1; $day <= $last_day; ++$day ) { //1日から最終日まで繰り返し
		if ( isset($newrow) && $newrow )
			$calendar_output .= "\n\t</tr>\n\t<tr>\n\t\t"; //行フラグ$newrowがtrueなら
		$newrow = false; //行フラグリセット

		$sp_date = explode("-", $date); //引数$dateを分割
		if ( $day == $sp_date[2] && $thismonth == $sp_date[1] && $thisyear == $sp_date[0] )
			$calendar_output .= '<td id="current">'; //引数と一致する日にid付加
		elseif ( $day == date('j') && $thismonth == date('m') && $thisyear == date('Y') )
			$calendar_output .= '<td id="today">'; //本日と一致する日にid付加
		else
			$calendar_output .= '<td>';

		$calendar_output .= '<input type="submit" name="calendar['.$thisyear.'-'.str_pad($thismonth,2,"0",STR_PAD_LEFT).'-'.str_pad($day,2,"0",STR_PAD_LEFT).']" value="'.$day.'">';
		$calendar_output .= "</td>\n\t\t";

		if ( 6 == date('w', mktime(0, 0 , 0, $thismonth, $day, $thisyear)) )
			$newrow = true; //最終列なら行フラグを立てる
	}

	$pad = 7 - date('w', mktime(0, 0 , 0, $thismonth, $day, $thisyear));
	if ( $pad != 0 && $pad != 7 )
		$calendar_output .= "\n\t\t".'<td class="pad" colspan="'. $pad .'">&nbsp;</td>'; //余ったtdを埋める

	$calendar_output .= "\n\t</tr>\n\t</form>\n\t</tbody>\n</table>\n";

	echo $calendar_output; //出力
}
?>