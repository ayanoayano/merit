<?php
session_start();
if(isset($_SESSION['id'])){
	$fn ='init'.$_SESSION['id'].'.php';
	require_once($fn);
}else{
	require_once('init.php');
}

if(SID) ErrerDisp('Cookieを有効にして下さい');
if(!$_SESSION) ErrerDisp('送信データがありません。');

function ConfDisp(){
	global $inputs;
	switch(VIEWSTYLE){
	case 'Table':
		foreach( $inputs as $key => $value){
			$_SESSION[$key] = zeromail_regtag_replace($_SESSION, $key);
			$str = '<tr><th scope="row">'.$value.'</th><td>';
			$str .= unhtmlentities($_SESSION[$key]);
			$str .= '</td></tr>';
			echo $str;
		}
	break;

	case'List':
		foreach( $inputs as $key => $value){
			$_SESSION[$key] = zeromail_regtag_replace($_SESSION, $key);
			echo convert_encode('<dt>'.$value.'</dt><dd>');
			echo $_SESSION[$key];
			echo convert_encode('</dd>');
		}
	break;

	default:
		foreach( $inputs as $key => $value){
			$_SESSION[$key] = zeromail_regtag_replace($_SESSION, $key);
			$str = '<tr><th scope="row">'.$value.'</th><td>';
			$str .= unhtmlentities($_SESSION[$key]);
			$str .= '</td></tr>';
			echo $str;
		}
	}
}

function unhtmlentities($string)
{
	// 数値エンティティの置換
	$string = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $string);
	$string = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $string);
	// 文字エンティティの置換
	$trans_tbl = get_html_translation_table(HTML_SPECIALCHARS);
	$trans_tbl = array_flip($trans_tbl);
	return strtr($string, $trans_tbl);
}
?>