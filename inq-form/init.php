<?php
/*------------------------------------------------------------
  mail.php
--------------------------------------------------------------*/

/*//////////* 基本設定（右側のみ変更すること） *//////////////*/

//文字コード（"UTF-8,EUC-JP,SJIS"）
define('TEXTCODE','EUC-JP');

//フォームデータを受け取るメールアドレス
define('MAILTO','Merit-jp@ml.toshiba.co.jp');

//差出人名
define('FROMNAME',"株式会社メリット");

//受け取る時の件名
define('MAILSUBJECT','お問い合わせフォームから送信がありました');

//自動返信の件名
define('REPSUBJECT','「株式会社メリット」お問い合わせ受付完了のお知らせ');

//自動返信
//trueにした場合はフォームでのチェックの有無関係なく返信する
//下記設定に関係なくメールアドレスが必須入力になる
//define('REPLY',true);
define('REPLY',true);

//メアドを必須項目にするかどうか（必須にする場合はtrue）
define('EMAILCHECK',true);

//返信メールに添えるメッセージ（ヘッダ）
$replymessage=<<<EOM
【このメールはシステムからの自動返信です】

お問い合わせありがとうございました。
以下の内容で送信いたしました。
────────────────────────────────────
EOM;

//返信メールに添えるメッセージ（お客様用ヘッダ）
$replycomment=<<<EOM
株式会社メリットです。
下記のお問合せメールをお預かり致しました。
内容確認次第、改めてご連絡いたします。
よろしくお願いいたします。 

EOM;

//返信メールに添える署名（フッタ）
$replyfoot= <<<EOM

=====================================================
株式会社メリット
　〒324-0036
　栃木県大田原市下石上1376番地3
（東芝メディカルシステムズ株式会社内）
　TEL：0287-26-1818
　FAX：0287-29-3690
　URL：http://www.toshiba.co.jp/merit/
===================================================== 
EOM;

//BCCで受け取りが必要な場合はアドレスを設定
define('BCC','');

/*----------------------------------------------------
  テンプレート
----------------------------------------------------*/
//確認画面（zeromail.phpからのパス）
//define('CHECKPAGE','check.php');
define('CHECKPAGE','form_other_submit.php');

//送信完了で表示するページ（mail.phpからのパス）
define('SUCCESSPAGE','http://www.toshiba.co.jp/merit/inquiry/form_other_finish.htm');

//エラーで表示するページ（mail.phpからのパス）
//define('ERRORPAGE','error.php');
define('ERRORPAGE','form_other_submit.php');

//Ajax送信完了後のメッセージ
$endMassage = "<div class=\"success exit\"><p class=\"comment\">送信しました</p></div>";

//確認画面のフォームデータ出力形式（空はpタグ）
//Table →テーブル行（th=項目名 td=値）
//List → 定義リスト（dt=項目名 dd=値）
define('VIEWSTYLE','Table');

//inputのnameとその名称設定
//'name'=>'名称'で１セット。一番最後には,をつけない。
//ここで書いた並び順が、確認画面と送信メール本文に反映される。
$inputs = array(
	'chk'=>'同意',
	'name1'=>'氏名',
	'name2'=>'ふりがな',
	'Work'=>'職業',
	'company'=>'施設名／会社名',
	'zipcode'=>'郵便番号',
	'pref'=>'都道府県',
	'address'=>'市区町村',
	'tel'=>'電話番号',
	'fax'=>'FAX番号',
	'email'=>'E-Mail',
	'message'=>'お問い合わせ内容'
);

/*-----------------------------------------------------
  ファイル添付機能
------------------------------------------------------*/

//ファイル添付機能を使うかどうか（trueにしなければ送信しない）
define('FILETEMP',false);

//ファイルを添付せずに保存するかどうか（trueで保存）
define('FILEPOOL',false);

//ファイルの最大サイズ(kb)
define("MAXSIZE",1000);

//添付ファイルの一時保存ディレクトリ（zeromail.phpと同階層のフォルダを用意すること）
define("UPLOADPASS","upfile/");

//画像確認時のWindowターゲット
//_string → target="_string"
//string → rel="string"
define("IMG_CHECK_TARGET","_blank");

/*--------------------------------------------------------------
　スパム対策設定
--------------------------------------------------------------*/

//nameを必須項目にするかどうか（必須にする場合はtrue）
define('NAMECHECK',true);

//半角英数の名前を許可するかどうか（許可する場合はtrue）
define('ALPHANAME',true);

//リファラーチェックするかどうか
//trueにした場合は下記で設定したページ以外からの送信をブロックする。
//下のフルパス設定もすること。
define('REFCHECK',false);

//入力フォームのパス
$formURL="";

//本文のリンク記述許可数（以下）
$alink = 1;

//メッセージ欄の禁止語句
//あんまり登録しすぎると処理が重くなるので注意
$blocktxt =array('死','death','porno','sex','pill','fuck','<script','<object');

//送信ブロックするIPアドレス
$blockIP = array();


/*///////////////////* END CONFIG *////////////////////*/


/*-----------------------------------------------------
  画面出力用関数
------------------------------------------------------*/

//エラー画面
//$err＝エラーメッセージ出力

function ErrerDisp($err){
	session_unset();
	$_SESSION['Err']=-1;
	$GLOBALS['err']=$err;
	include(ERRORPAGE);
	exit;
}

//確認画面のメッセージ
function Message(){

	if($_SESSION['Err']>1){
		$str = '<span class="error">前のページに戻り、入力エラーを修正してください。</span>';
	}else if($_SESSION['Err']===-1){
		$str = '<span class="error">'.$GLOBALS['err'].'</span>';
	}else{
		$str ='<span class="confirm">入力内容に間違いが無ければ、送信ボタンを押してください。</span>';
	}

	//echo convert_encode($str);
	echo $str;

}


//確認画面送信ボタン（hiddenとsession_unsetは消さないこと。）
function Button(){

	if($_SESSION['Err']>1||$_SESSION['Err']===-1){
		$str = '<noscript><p class="return">ブラウザのボタンで戻ってください。</p></noscript><a href="#" class="rollover" onclick="history.back(); return false;"><img src="../inquiry/img/btn_repair.gif" alt="内容を修正" /></a>';
		session_unset();
	}else{
		$str = '<a href="#" class="rollover" onclick="history.back(); return false;"><img src="../inquiry/img/btn_repair.gif" alt="内容を修正" /></a>';
		$str .= '<input name="mode" type="hidden" id="mode" value="Send" /><input type="image" style="vertical-align:top" src="../inquiry/img/btn_submit.gif" value="submit" alt="送信" onmouseover="this.src=\'../inquiry/img/btn_submit_f2.gif\'" onmouseout="this.src=\'../inquiry/img/btn_submit.gif\'" />';
	}
	echo $str;
}

/*---------------------* 削除禁止 *-------------------------*/

//エンコード変換
function convert_encode($str){return mb_convert_encoding($str,TEXTCODE,"UTF-8");}

//rep[name]の指定による置換
function zeromail_regtag_replace($formitem, $key){
	if(isset($formitem["reps"]) && array_key_exists($key,$formitem["reps"])!==false) {

		preg_match_all("/\{(?:([^\{\}\:]+)\:{1})*([\w\d\-]+)(?:\:{1}([^\{\}\:]+))*\}/", $formitem["reps"][$key], $match);

		$str = $formitem["reps"][$key];

		foreach($match[0] as $i => $tag){
			if(!empty($formitem[$match[2][$i]]))
				$str = str_replace($tag, $match[1][$i].$formitem[$match[2][$i]].$match[3][$i], $str);
			else
				$str = str_replace($tag, "", $str);
		}

		return $str;

	}else{

		return $formitem[$key];
	}
}

define("SCRIPT","meritmail");
define("VERSION","");

function zm_copyright($print=false)
{
	$code = '<p class="wtn_copyright"><a href="http://'.strtolower(SCRIPT).'.webtecnote.com/" title="'.SCRIPT.' Home" rel="bookmark">- '.SCRIPT.' -</a></p>';

	if($print === false)
		return $code;
	else
		print $code;

}
/*----------------------------------------------------------*/
?>