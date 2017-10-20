<?php
mb_language("ja");
mb_internal_encoding('EUC-JP');
mb_http_input("auto");
mb_http_output('EUC-JP');
ini_set("default_charset", 'EUC-JP');
define('PHPVER',phpversion());
define( "ABSDIR", dirname( $_SERVER["SCRIPT_FILENAME"] ). DIRECTORY_SEPARATOR);

session_cache_limiter('private_no_expire');
session_start();

if(isset($_POST['id']) xor isset($_SESSION['id'])){
	if($_POST['id']){
		$_SESSION['id']=$_POST['id'];
	}
	require_once('init'.$_SESSION['id'].'.php');

}else{
	require_once('init2.php');
}

//Ajax使用状況（noscript=falseで使用とみなす）
if((isset($_POST['noscript']) && $_POST["noscript"]=="true")
	|| (isset($_SESSION['noscript']) && $_SESSION["noscript"]=="true")
		||!array_key_exists("noscript",$_POST)){

	define("NOSCRIPT",true);

}elseif($_POST["noscript"]=="false"){
	define("NOSCRIPT",false);
}

if(defined("ZM_ROOTOUT") && ZM_ROOTOUT === true){
	define("ZM_LOGFILE",LOGFILEPASS);
}elseif(defined("ZM_ROOTOUT") && ZM_ROOTOUT === false){
	define("ZM_LOGFILE",ABSDIR.ZM_ADMINDIR.LOGFILEPASS);
}

if(isset($_POST)&&count($_POST)>0){

	// add by 2015.09.09 $mode = (isset($_POST['mode'])) ? $_POST['mode'] : null;
	// 未定義エラー(null)処理
	// あるサーバーでのバージョンアップ時のWarring対応（php version 4.3.11→5.5.28）
	$mode = (isset($_POST['mode'])) ? $_POST['mode'] : null;

	if($mode==="Send"){
		zeromailAjax_send($_SESSION);
	}else{

		session_unset();

		//spam killer
		ip_check_destroy();
		ref_check_destroy();
		//送信された値のチェック
		$formitem = value_check($_POST);

		if(NOSCRIPT===true){
			$_SESSION = array_merge($_SESSION,$formitem);
			header('Location: '.CHECKPAGE);

		}elseif( (NOSCRIPT===false && $formitem['Err'] > 1) || (NOSCRIPT===false && $formitem["confirm"]=="true")){
			AjaxConfDisp($formitem);
		}else{
			zeromailAjax_send($formitem);
		}
	}

}else{
	ErrerDisp('不正なアクセスです。');
}


/************************************************************
 *
 * 送信された値のチェック
 *
 ************************************************************/
function value_check($POST){

	global $formURL,$blocktxt,$blockIP;

	if(FILEPOOL !== true) RemoveFiles(UPLOADPASS);//file delete

	$formitem = array();
	$error = 1;

	//hiddenの必須指定(checkbox,files&etc)
	$SENDS = array_merge($POST,$_FILES);

	if(isset($_POST["require"])){
		$reqnames = explode(",",$POST["require"]);
		foreach($reqnames as $reqname){
			// delete by 2015.09.09 ||$SENDS[$reqname]["error"]==4
			// ファイル添付を許可していないから必要なし
			// あるサーバーでのバージョンアップ時のWarring対応（php version 4.3.11→5.5.28）
			if(!array_key_exists($reqname, $SENDS)){
			//if(!array_key_exists($reqname, $SENDS)||$SENDS[$reqname]["error"]==4){
				 $_SESSION[$reqname]=$formitem[$reqname]=mb_convert_encoding('<p class="error">同意しておりません。この項目は必須です。</p>',"EUC-JP");
				 $error++;
			}
		}
	}

	//hiddenのrep[name]
	if(isset($POST["rep"]) && is_array($POST["rep"])){
		foreach($POST["rep"] as $name => $regs){
			$formitem["reps"][$name]=$regs;
		}
		unset($POST["rep"]);
	}

	foreach($POST as $key => $value){
		$name=explode("_", $key);
		if(is_array($value)){//値が配列
			$value=implode("\n", $value);
		}

		$formitem[$name[0]]=preFilter($value);

		//必須
		if( mb_ereg("_req",$key) && mb_strlen($value) < 1){
				 $formitem[$name[0]]='<p class="error">入力されていません。この項目は必須です。</p>';
				 $error++;
		}

		//データ抽出
		for($i=0; $i<count($name); $i++){
			// 2011.03.08エスケープ処理対応
			$value = htmlentities($value, ENT_QUOTES,"EUC-JP");
			//全角変換
			if( $name[$i] == "jpz" && mb_strlen($value) > 0){
				$value=mb_convert_kana($value, "RNASKH");

			//全カタカナ
			}elseif($name[$i] == "jpk" && mb_strlen($value) > 0){
				$value=mb_convert_kana($value, "RNASKH");
				if (preg_match("/^(\xa5[\xa1-\xf6]|\xa1\xbc)+$/", $value)) {
					$formitem[$name[0]]=$value;
				}else{
					$formitem[$name[0]]=mb_convert_encoding('<p class="error">全角カタカナで入力してください。半角カタカナが含まれていませんか？</p>',"EUC-JP");
					$error++;
				}
			//全ひらがな
			}elseif($name[$i] == "jph" && mb_strlen($value) > 0){
				$value=mb_convert_kana($value, "RNASKH");
				if (preg_match("/^(\xa4[\xa1-\xf3]|\xa1\xbc)+$/", $value)) {
					$formitem[$name[0]]=$value;
				}else{
					$formitem[$name[0]]=mb_convert_encoding('<p class="error">ひらがなで入力してください。</p>',"EUC-JP");
					$formitem[$name[0]].=mb_convert_encoding($value, "EUC-JP");
					$error++;
				}

			//カタカナorひらがな
			}elseif($name[$i] == "jpa" && mb_strlen($value) > 0){
				$value=mb_convert_kana($value, "RNASKH");
				if (preg_match("/^(\xa5[\xa1-\xf6]|\xa4[\xa1-\xf3]|\xa1\xbc)*$/", $value)) {
					$formitem[$name[0]]=$value;
				}else{
					$formitem[$name[0]]=mb_convert_encoding('<p class="error">「ひらがな」または「カタカナ」で入力してください。</p>',"EUC-JP");
					$error++;
				}
			//数字
			}elseif($name[$i] == "num" && mb_strlen($value) > 0){
				$value = mb_convert_kana($value, "n");
				if (preg_match("/^[0-9]*$/", $value)) {
					$formitem[$name[0]]=$value;
				}else{
					$formitem[$name[0]]=mb_convert_encoding('<p class="error">半角数字で入力してください。</p>',"EUC-JP");
					$error++;
				}

			//英字
			}elseif( $name[$i] == "eng" && mb_strlen($value) > 0){
				$value =  mb_convert_kana($value,"as");
				if (!preg_match("/^[a-zA-Z0-9]+$/", $value)) {
					$formitem[$name[0]]=mb_convert_encoding('<p class="error">半角英数で入力してください。</p>',"EUC-JP");
					$error++;
				}else{
					$formitem[$name[0]]=$value;
				}

			//同意
			}elseif( $name[$i] == "chk" && $value != ""){
				if (preg_match("/^[check]*$/", $value)) {
					$formitem[$name[0]]='同意しました。';
				}else{
					$formitem[$name[0]]=mb_convert_encoding('<p class="error">同意しておりません。</p>',"EUC-JP");
					$error;
				}

			//名前
			}elseif( stristr($name[0],"name1")!==false ){
				$value=mb_convert_kana($value, "RNASKH");
				if($name[0] == "name1" && mb_strlen($value) < 1 && NAMECHECK == true){
					$formitem[$name[0]]='<p class="error">入力されていません。この項目は必須です。</p>';
					$error++;
				}else{
					$formitem[$name[0]]=htmlentities($value,ENT_QUOTES, TEXTCODE);
					//edit
					$formitem[$name[0]] = mb_convert_encoding($value, "EUC-JP");
				}

			//ふりがな
			}elseif( stristr($name[0],"name2")!==false ){
				$value=mb_convert_kana($value, "KHV");
				if($name[0] == "name2" && mb_strlen($value) < 1 && NAMECHECK == true){
					$formitem[$name[0]]='<p class="error">入力されていません。この項目は必須です。</p>';
					$error++;
				}else{
					$formitem[$name[0]]=htmlentities($value,ENT_QUOTES, TEXTCODE);
					//edit
					$formitem[$name[0]] = mb_convert_encoding($value, "EUC-JP");
				}

			//会社名(施設名)
			}elseif( stristr($name[0],"company")!==false ){
				$value=mb_convert_kana($value, "RNASKH");
				if($name[0] == "company" && mb_strlen($value) < 1 && NAMECHECK == true){
					$formitem[$name[0]]=mb_convert_encoding('<p class="error">入力されていません。この項目は必須です。</p>', "EUC-JP");
					$error++;
				}else{
					$formitem[$name[0]]=htmlentities($value,ENT_QUOTES, TEXTCODE);
					//edit
					$formitem[$name[0]] = mb_convert_encoding($value, "EUC-JP");
				}

			//製品名
			}elseif( stristr($name[0],"product")!==false ){
				$value=mb_convert_kana($value, "RNASKH");
				if($name[0] == "product" && mb_strlen($value) < 1 && NAMECHECK == true){
					$formitem[$name[0]]=mb_convert_encoding('<p class="error">入力されていません。この項目は必須です。</p>', "EUC-JP");
					$error++;
				}else{
					$formitem[$name[0]]=htmlentities($value,ENT_QUOTES, TEXTCODE);
					//edit
					$formitem[$name[0]] = mb_convert_encoding($value, "EUC-JP");
				}

			//型番
			}elseif( stristr($name[0],"model")!==false ){
				$value=mb_convert_kana($value, "RNASKH");
				if($name[0] == "model" && mb_strlen($value) < 1 && NAMECHECK == true){
					$formitem[$name[0]]=mb_convert_encoding('<p class="error">入力されていません。この項目は必須です。</p>', "EUC-JP");
					$error++;
				}else{
					$formitem[$name[0]]=htmlentities($value,ENT_QUOTES, TEXTCODE);
					//edit
					$formitem[$name[0]] = mb_convert_encoding($value, "EUC-JP");
				}

			//メアド
			}elseif( stristr($name[0],"email")!==false){
				if($name[0] == "email" && mb_strlen($value) < 1 && NAMECHECK == true){
					$formitem[$name[0]]=mb_convert_encoding('<p class="error">入力されていません。この項目は必須です。</p>',"EUC-JP");
					$error++;
				}elseif((mb_strlen($value) < 1 && (REPLY == true || $_POST["reply"] =="true")) || (mb_strlen($value) < 1 &&EMAILCHECK === true )){
					$formitem[$name[0]]="<p class=\"error\">";
					// 2011.03.08エスケープ処理対応
					$formitem[$name[0]] = preFilter($value);
					$formitem[$name[0]].="</p><br /><p class=\"error\">メールアドレスに誤りがあります。お確かめください。</p>";
					$error++;
				}elseif(mb_strlen($value) > 0 && !preg_match("/^([a-z0-9_]|\-|\.|\+)+@(([a-z0-9_]|\-)+\.)+[a-z]{2,6}$/i",$value)){
					$formitem[$name[0]]="<p class=\"error\">";
					// 2011.03.08エスケープ処理対応
					$formitem[$name[0]] = preFilter($value);
					$formitem[$name[0]].="</p><br /><p class=\"error\">メールアドレスに誤りがあります。お確かめください。</p>";
					$error++;
				}

			//URL
			}elseif( stristr($name[0],"url")!==false && mb_strlen($value) > 0){
				if(!preg_match('/^(https|http)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/', $value)) {
					$formitem[$name[0]]=mb_convert_encoding("<p class=\"error\">半角記号が含まれているようです。半角記号の入力はできません。</p>","EUC-JP");
					$error++;
				}

			//電話
			}elseif( stristr($name[0],"tel")!==false && mb_strlen($value) > 0){
				//入力チェック処理 start
				if(strpos($value,"-")===false){
					if (!preg_match("/(^(?<!090|080|070)\d{10}$)|(^(090|080|070)\d{8}$)|(^0120\d{6}$)|(^0080\d{7}$)/", $value)) {
						$formitem[$name[0]]="<p class=\"error\">";
						$formitem[$name[0]]="電話番号の書式に誤りがあります。";
						$formitem[$name[0]].=mb_convert_encoding($value, "EUC-JP");
						$formitem[$name[0]].="</p>";
						$error++;
					}else{
						$formitem[$name[0]]=$value;
					}
				}else{
					if (!preg_match("/(^(?<!090|080|070)(^\d{2,5}?\-\d{1,4}?\-\d{4}$|^[\d\-]{12}$))|(^(090|080|070)(\-\d{4}\-\d{4}|[\\d-]{13})$)|(^0120(\-\d{2,3}\-\d{3,4}|[\d\-]{12})$)|(^0080\-\d{3}\-\d{4})/", $value)) {
						$formitem[$name[0]]="<p class=\"error\">";
						$formitem[$name[0]].=mb_convert_encoding($value, "EUC-JP");
						$formitem[$name[0]].="</p><br /><p class=\"error\">電話番号の書式に誤りがあります。</p>";
						$error++;
					}else{
						$formitem[$name[0]]=$value;
					}
				//入力チェック処理 end
				}

			//備考
			}elseif( stristr($name[0],"message")!==false && mb_strlen($value) > 0){
				$value=mb_convert_kana($value, "RNASKH");
				if(is_array($blocktxt)&&count($blocktxt)>0){//禁止語句チェック
					foreach($blocktxt as $fuck){
						if (strstr($value,$fuck) === false){
							$formitem[$name[0]]=preFilter($value);
							$formitem[$name[0]]=mb_convert_encoding($value, "EUC-JP");
						}else{
							$formitem[$name[0]]="<p class=\"error\">";
							$formitem[$name[0]].=mb_convert_encoding($value, "EUC-JP");
							$formitem[$name[0]].="</p><br /><p class=\"error\">禁止語句が含まれているようです。</p>";
							$error++;
							break;
						}
					}
				}else{
					$formitem[$name[0]] = preFilter($value);
				}
			}//end:preg_match
		}//end:for
	}//end:foreach

	if(isset($_FILES)&&count($_FILES)!=0){
		$error += checkUploadData($_FILES);
	}

	$formitem['Err'] = $error;

	return $formitem;

}



/************************************************************
 *
 * 添付ファイルのチェック
 *
 ************************************************************/
function checkUploadData($FILES)
{
	$err = 0;

	foreach ($FILES as $name => $array) {

		$filename = $array["name"];

		if(FILETEMP === false){

			$_SESSION[$name] = "<strong class=\"error\">ファイルのアップロードが許可されていません。</strong>";
			$err = 1;

		}elseif ($array["error"] == 0) {

			$tmp_name =$array["tmp_name"];
			$filesize = $array["size"];
			$filetype = $array['type'];
			preg_match("/^.+?((?:\.\w{3})*\.\w{2,4})$/i", $filename, $extension);//拡張子

			if(!check_minetype($filetype, strtolower($extension[1]))){
				$_SESSION[$name] = convert_encode('<strong class="error">'.$label."[".$filename."] のファイル形式が不適切です。</strong>");
				$err = 1;
			}

			if($filesize >= MAXSIZE*1000 ){ //ファイルサイズ
				$_SESSION[$name] = convert_encode('<strong class="error">'.$label."[".$filename."] のファイルサイズ(".($filesize/1000)."kb)が大きすぎます</strong>");
				$err = 1;
			}

			if(!$err){
				if((FILEPOOL === true) || ! preg_match("/^[a-z0-9_\-\.]+?\.\w{2,4}$/i", $filename)){//FILEPOOL=ON | 画像が日本語だったら

					$filename = substr(md5(microtime()), 0, rand(5, 8)).$extension[1];//適当に名前付ける
				}else{
					$filename = strtolower($filename);
				}

				$target = (strpos(IMG_CHECK_TARGET,"_")===0) ? ' target="'.IMG_CHECK_TARGET.'"':' rel="'.IMG_CHECK_TARGET.'"';

				$_SESSION[$name] = convert_encode($filename." (".($filesize/1000)."kb)".' <a href="'.UPLOADPASS.$filename.'"'.$target.'>ファイルの確認</a>');
				$_SESSION["FILES"][] = array('filename'=>$filename, 'type' =>$filetype);
				$_SESSION["FILETEMP"] = true;

				//tmpファイルを移動
				move_uploaded_file($tmp_name,UPLOADPASS.$filename);


			}

		}elseif($array["error"] != 4){

			switch($array["error"]){
				case 1:
					$_SESSION[$name] = convert_encode("<strong class=\"error\">[".$filename."] のファイルサイズが大きすぎます</strong>");
					$err = 1;
				break;

				case 2:
					$_SESSION[$name] = convert_encode("<strong class=\"error\">[".$filename."] のファイルサイズが大きすぎます</strong>");
					$err = 1;
				break;

				case 6:
					$_SESSION[$name] = convert_encode("<strong class=\"error\">テンポラリフォルダがありません</strong>");
					$err = 1;
				break;

				default:
					$_SESSION[$name] = convert_encode("<strong class=\"error\">[".$filename."] はアップロードできませんでした</strong>");
					$err = 1;
			}

		}

	}
	if(isset($err)){
		return $err;
	}

}

/************************************************************
 *
 * Ajax用入力エラー表示
 *
 ************************************************************/
function AjaxConfDisp($formitem){

	global $inputs2,$replymessage;

	if($formitem['Err']>1)
		print '<div id="error" class="zeromail"><p><span class="error">入力エラーを修正してください。</span></p>';
	else
		print '<div class="zeromail confirmed"><p><span class="confirm">入力内容に間違いが無ければ、送信ボタンを押してください。</span></p>';

		switch(VIEWSTYLE){
			case 'Table':
				echo '<table id="confirm">';
				foreach( $inputs2 as $key => $value){
					$formitem[$key] = zeromail_regtag_replace($formitem, $key);
					echo '<tr><th scope="row" class="label">'.$value.'</th><td class="value">';
					echo $formitem[$key];
					echo '</td></tr>';
				}
				echo '</table>';
			break;

			case'List':
				echo '<dl id="confirm">';
				foreach( $inputs2 as $key => $value){
					$formitem[$key] = zeromail_regtag_replace($formitem, $key);
					echo '<dt class="label">'.$value.'</dt><dd class="value">';
					echo $formitem[$key];
					echo '</dd>';
				}
				echo '</dl>';
			break;

			default:
				echo '<div id="confirm">';
				foreach( $inputs2 as $key => $value){
					$formitem[$key] = zeromail_regtag_replace($formitem, $key);
					echo '<p><em class="label">'.$value.'</em><span class="value">';
					echo $formitem[$key];
					echo '</span></p>';
				}
			echo '</div></div>';
		}
}

/************************************************************
 *
 * 送信関数
 *
 ************************************************************/

function zeromailAjax_send($formitem)
{
	global $inputs2, $endMassage,$replyfoot,$replymessage,$replycomment;

	ip_check_destroy();

	//ユーザー情報
	if(is_admin()) $csv = '"'.date("Y/m/d H:i:s").'"';

//本文スタイル
$message=MAILSUBJECT;
$message.="\n────────────────────────────────────";
foreach($inputs2 as $key => $value){

	if(preg_match("/<a href=\"(.+)\"(.*)>?ファイルの確認<\/a>/i",$formitem[$key])){
		$formitem[$key] = preg_replace("/\s+<a href=\"(.+)\"(.*)>?ファイルの確認<\/a>/i","",$formitem[$key]);
	}

	if(is_admin()) $csv .=',"'.str_replace(array("\n","\r","\r\n"),"",$formitem[$key]).'"';

$message.= <<<EOM
\n■$value
{$formitem[$key]}\n
EOM;
}

// 2015.09.09 delete
// あるサーバーでのバージョンアップ時のWarring対応（php version 4.3.11→5.5.28）

	if(is_admin())
		$csv .= ',"'.$user_ip.'","'.$user_host.'","'.$user_agent.'"'."\n";

	// 2011.03.08エスケープ処理対応
	//本文整形(管理者側)
	if(strpos(PHPVER,'5')===false){
		$message = unhtmlentities($message);
	}else{
		$message = html_entity_decode($message,ENT_QUOTES,'EUC-JP');
	}
	$message = str_replace("\r","",str_replace("<br />","",$message));
	$message = mb_convert_encoding($message, "ISO-2022-JP", "EUC-JP");
	$name = isset($formitem["name1"])? $formitem["name1"] : SCRIPT;

	//添付ファイルなし（POOLあり）
	if(FILETEMP === false || (FILETEMP === true && FILEPOOL === true) || (FILETEMP === true && $formitem["FILETEMP"] !== true)){

		$mailheader ="From: ".get_mailfrom($name, $formitem['email'])."\n";
		if(BCC != "") $mailheader.="Bcc: ".BCC."\n";
		$mailheader.="X-Mailer: ".SCRIPT."(Version ".VERSION.")\n";

		@mb_send_mail(MAILTO,MAILSUBJECT,$message,$mailheader);

	//添付ファイルあり（POOLなし）
	}elseif(FILEPOOL === false && FILETEMP === true && $formitem["FILETEMP"]===true){
		$boundary = "zeromail".md5(uniqid(rand()));//バウンダリー
		$mailfrom2 = get_mailfrom($name, $formitem['email']);

		$mailheader = "From: ".$mailfrom2."\n";
		if(BCC != "") $mailheader.="Bcc: ".BCC."\n";
		$mailheader .= "X-Mailer: ".SCRIPT."(Version ".VERSION.")\n";
		$mailheader .= "MIME-version: 1.0\n";
		$mailheader .= "Content-Type: multipart/mixed;";
		$mailheader .= " boundary=".$boundary."\n";

		$msg = "--".$boundary."\n";
        $msg .= "Content-type: text/plain; charset=ISO-2022-JP\n";
		$msg .= "Content-transfer-encoding: 7bit\n\n";
		$msg .= mb_convert_encoding($message, "JIS", TEXTCODE);

		foreach($formitem["FILES"] as $i => $tmp){
			$fp = @fopen(ABSDIR.UPLOADPASS.$tmp["filename"], "r"); //ファイルの読み込み
			$contents = @fread($fp, @filesize(ABSDIR.UPLOADPASS.$tmp["filename"]));
			@fclose($fp);
			$encoded = base64_encode($contents); //エンコード
			$msg .= "\n\n--".$boundary."\n";
			$msg .= "Content-Type: " . $tmp["type"] . ";\n";
			$msg .= "\tname=\"".$tmp["filename"]."\"\n";
			$msg .= "Content-Transfer-Encoding: base64\n";
			$msg .= "Content-Disposition: attachment;\n";
			$msg .= "\tfilename=\"".$tmp["filename"]."\"\n\n";
			$msg .= $encoded."\n";
		}

		RemoveFiles(ABSDIR.UPLOADPASS);//ファイル削除

		$msg .= "--".$boundary."--";

		$subject = base_64_encode(MAILSUBJECT,true);

		@mail(MAILTO,$subject, $msg, $mailheader);
	}

	//メール自動返信
	// 2015.09.09 delete $formitem['reply'] === "true" || 
	// あるサーバーでのバージョンアップ時のNotice対応（php version 4.3.11→5.5.28）
	if( REPLY === true && $formitem['email'] != "" ){
	//if( ( $formitem['reply'] === "true" || REPLY === true ) && $formitem['email'] != "" ){
		$replyheader ="From: \"".base_64_encode(FROMNAME,true)."\" <".MAILTO.">\r\n";
		if(BCC != "") $replyheader.="Bcc: ".BCC."\r\n";
		$replyheader.="X-Mailer: ".SCRIPT."(Version ".VERSION.")\r\n";

//自動返信本文スタイル
$replymessage=$replycomment;
foreach( $inputs2 as $key => $value){
	if(preg_match("/<a href=\"(.+)\"(.*)>?ファイルの確認<\/a>/i",$formitem[$key]))
	$formitem[$key] = preg_replace("/<a href=\"(.+)\"(.*)>?ファイルの確認<\/a>/i","",$formitem[$key]);
$replymessage.= <<<EOM
\n■$value
{$formitem[$key]}\n
EOM;
}
$replymessage.= <<<EOM
$replyfoot

EOM;
		$pos = strpos(PHPVER,'5');
		//自動返信本文整形(お客様控え)
		if( $pos === false){
			$replymessage = unhtmlentities($replymessage);
		}else{
			// ver 5.
			$replymessage = html_entity_decode($replymessage,ENT_QUOTES,'EUC-JP');
		}
		$replymessage = str_replace("\r","",str_replace("<br />","", $replymessage));

		@mb_send_mail($formitem['email'], REPSUBJECT, $replymessage, $replyheader);
	}

	if(is_admin()) zeromail_data_put_csv($csv);//CSV保存

	// セッションを切断するにはセッションクッキーも削除する。
	// Note: セッション情報だけでなくセッションを破壊する。
	if (ini_get("session.use_cookies")) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$params["path"], $params["domain"],
			$params["secure"], $params["httponly"]
		);
	}
	session_destroy();

	if(NOSCRIPT === false){

		print $endMassage;

	}else{

		header('Location: '.SUCCESSPAGE);

	}

}


/*-----------------------------------------------------
  管理画面機能の存在チェック
------------------------------------------------------*/
function is_admin()
{
	return (defined('ZM_ADMIN') && ZM_ADMIN === true);
}

/*-----------------------------------------------------
  csvに書き込み
------------------------------------------------------*/
function zeromail_data_put_csv($csv)
{

	$filename = ZM_LOGFILE;

	if (is_writable($filename)) {
		$data = @file_get_contents($filename);
		$data = str_replace("<?php exit;/*",'',$data);
		$data = ltrim($data);
	}else{
		touch($filename);
	}

	if(filesize($filename) == "0"){
		$empty = true;
	}

	$fp = fopen($filename, "w");
	fwrite($fp, mb_convert_encoding("<?php exit;/*\n".$csv,"EUC-JP", TEXTCODE));

	if(isset($data) && is_numeric(ZM_ADMIN_LOGMAX)){
		$line = explode("\n",$data);
		for($i=0; $i < ZM_ADMIN_LOGMAX; $i++){
			fwrite($fp, mb_convert_encoding($line[$i]."\n","EUC-JP", TEXTCODE));
		}


	}elseif(isset($data) && !is_numeric(ZM_ADMIN_LOGMAX)){
		fwrite($fp, mb_convert_encoding($data,"EUC-JP", TEXTCODE));
	}

	if($empty===true || count($line)>ZM_ADMIN_LOGMAX) fwrite($fp,mb_convert_encoding("*/?>","EUC-JP", TEXTCODE));

	fclose($fp);

}

/*-----------------------------------------------------
  ISO-2022-JPエンコード
------------------------------------------------------*/
function base_64_encode($str,$encode=false)
{
	if($encode)	 $str = mb_convert_encoding($str, "JIS", TEXTCODE);
	return "=?ISO-2022-JP?B?".base64_encode($str). "?=";
}


/*-----------------------------------------------------
  差出人フォーマット
------------------------------------------------------*/
function get_mailfrom($name, $email)
{

	$name = base_64_encode($name, true);

	if(!$email){
		return '"'.$name.'" <'.SCRIPT.'@Ver'.VERSION.'>';
	}else {
		return '"'.$name.'" <'.$email.'>';
	}

}

/*-----------------------------------------------------
  添付ファイル削除
------------------------------------------------------*/
function RemoveFiles($dir)
{
    if(!$dh = @opendir($dir)) return;
    while (false !== ($obj = readdir($dh))) {
        if($obj=='.' || $obj=='..') continue;
        @unlink($dir.'/'.$obj);
    }
    closedir($dh);
}

/*-----------------------------------------------------
  ファイルタイプのチェック
------------------------------------------------------*/
function check_minetype($filetype, $extension)
{
	$minetype = array("image/jpeg","image/pjpeg","image/x-png","image/png","image/gif","image/bmp","application/pdf","application/octet-stream","application/x-shockwave-flash","text/plain","application/x-zip","application/zip","application/x-zip-compressed","application/x-lha-compressed","application/mspowerpoint","application/x-compress","application/x-excel","application/excel","application/vnd.ms-excel","application/vnd.ms-powerpoint","application/x-msexcel","application/x-gzip");
	$ext  = array(".gif",".png",".jpg",".bmp",".pdf",".swf",".txt",".xls",".doc",".ppt",".zip",".lzh",".tar.gz");

	if(array_search($filetype,$minetype)===false){
		return false;
	}elseif(array_search($extension,$ext)===false){
		return false;
	}else{
		return true;
	}
}

/*-----------------------------------------------------
  IPチェック
------------------------------------------------------*/
function ip_check_destroy()
{
	global $blockIP;

	if(array_search($_SERVER['REMOTE_ADDR'],$blockIP)!==false){
		if(NOSCRIPT===true){
			ErrerDisp('送信が認められていません。');
		}else{
			print "<div id=\"error\"><p><span class=\"error\">送信が認められていません。</span></p></div>";
			exit;
		}
	}
}

/*-----------------------------------------------------
  リファラチェック
------------------------------------------------------*/
function ref_check_destroy()
{
	global $formURL;

	if(REFCHECK === true && $formURL!="" && $_SERVER["HTTP_REFERER"]!==$formURL){ //リファラーチェック

		if(NOSCRIPT===true){
			ErrerDisp('不正な送信元です。');
		}else{
			print "<div id=\"error\"><p><span class=\"error\">不正な送信元です。</span></p></div>";
			exit;
		}
	}
}

/*-----------------------------------------------------
  magic_quotes_gpc=ON対策
------------------------------------------------------*/
function preFilter($str)
{
	if (ini_get('magic_quotes_gpc')){
		$str = stripslashes_deep(nl2br(htmlentities($str, ENT_QUOTES,TEXTCODE)));
	}else{
		$str = nl2br(htmlentities($str, ENT_QUOTES,TEXTCODE));
	}
	return $str;
}

/*-----------------------------------------------------
  スラッシュ消す
------------------------------------------------------*/
function stripslashes_deep($str)
{
	$str = is_array($str) ?
		array_map('stripslashes_deep', $str) :
		stripslashes($str);
	return $str;
}

/*-----------------------------------------------------
  ver4用デコード
------------------------------------------------------*/
function unhtmlentities($string)
{
    // 数値エンティティの置換
    $string = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $string);
    $string = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $string);
    // 文字エンティティの置換
    $trans_tbl = get_html_translation_table(HTML_ENTITIES);
    $trans_tbl = array_flip($trans_tbl);
    return strtr($string, $trans_tbl);
}
?>