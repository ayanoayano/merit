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

//Ajax���Ѿ�����noscript=false�ǻ��ѤȤߤʤ���
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
	// ̤������顼(null)����
	// ���륵���С��ǤΥС�����󥢥å׻���Warring�б���php version 4.3.11��5.5.28��
	$mode = (isset($_POST['mode'])) ? $_POST['mode'] : null;

	if($mode==="Send"){
		zeromailAjax_send($_SESSION);
	}else{

		session_unset();

		//spam killer
		ip_check_destroy();
		ref_check_destroy();
		//�������줿�ͤΥ����å�
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
	ErrerDisp('�����ʥ��������Ǥ���');
}


/************************************************************
 *
 * �������줿�ͤΥ����å�
 *
 ************************************************************/
function value_check($POST){

	global $formURL,$blocktxt,$blockIP;

	if(FILEPOOL !== true) RemoveFiles(UPLOADPASS);//file delete

	$formitem = array();
	$error = 1;

	//hidden��ɬ�ܻ���(checkbox,files&etc)
	$SENDS = array_merge($POST,$_FILES);

	if(isset($_POST["require"])){
		$reqnames = explode(",",$POST["require"]);
		foreach($reqnames as $reqname){
			// delete by 2015.09.09 ||$SENDS[$reqname]["error"]==4
			// �ե�����ź�դ���Ĥ��Ƥ��ʤ�����ɬ�פʤ�
			// ���륵���С��ǤΥС�����󥢥å׻���Warring�б���php version 4.3.11��5.5.28��
			if(!array_key_exists($reqname, $SENDS)){
			//if(!array_key_exists($reqname, $SENDS)||$SENDS[$reqname]["error"]==4){
				 $_SESSION[$reqname]=$formitem[$reqname]=mb_convert_encoding('<p class="error">Ʊ�դ��Ƥ���ޤ��󡣤��ι��ܤ�ɬ�ܤǤ���</p>',"EUC-JP");
				 $error++;
			}
		}
	}

	//hidden��rep[name]
	if(isset($POST["rep"]) && is_array($POST["rep"])){
		foreach($POST["rep"] as $name => $regs){
			$formitem["reps"][$name]=$regs;
		}
		unset($POST["rep"]);
	}

	foreach($POST as $key => $value){
		$name=explode("_", $key);
		if(is_array($value)){//�ͤ�����
			$value=implode("\n", $value);
		}

		$formitem[$name[0]]=preFilter($value);

		//ɬ��
		if( mb_ereg("_req",$key) && mb_strlen($value) < 1){
				 $formitem[$name[0]]='<p class="error">���Ϥ���Ƥ��ޤ��󡣤��ι��ܤ�ɬ�ܤǤ���</p>';
				 $error++;
		}

		//�ǡ������
		for($i=0; $i<count($name); $i++){
			// 2011.03.08���������׽����б�
			$value = htmlentities($value, ENT_QUOTES,"EUC-JP");
			//�����Ѵ�
			if( $name[$i] == "jpz" && mb_strlen($value) > 0){
				$value=mb_convert_kana($value, "RNASKH");

			//����������
			}elseif($name[$i] == "jpk" && mb_strlen($value) > 0){
				$value=mb_convert_kana($value, "RNASKH");
				if (preg_match("/^(\xa5[\xa1-\xf6]|\xa1\xbc)+$/", $value)) {
					$formitem[$name[0]]=$value;
				}else{
					$formitem[$name[0]]=mb_convert_encoding('<p class="error">���ѥ������ʤ����Ϥ��Ƥ���������Ⱦ�ѥ������ʤ��ޤޤ�Ƥ��ޤ��󤫡�</p>',"EUC-JP");
					$error++;
				}
			//���Ҥ餬��
			}elseif($name[$i] == "jph" && mb_strlen($value) > 0){
				$value=mb_convert_kana($value, "RNASKH");
				if (preg_match("/^(\xa4[\xa1-\xf3]|\xa1\xbc)+$/", $value)) {
					$formitem[$name[0]]=$value;
				}else{
					$formitem[$name[0]]=mb_convert_encoding('<p class="error">�Ҥ餬�ʤ����Ϥ��Ƥ���������</p>',"EUC-JP");
					$formitem[$name[0]].=mb_convert_encoding($value, "EUC-JP");
					$error++;
				}

			//��������or�Ҥ餬��
			}elseif($name[$i] == "jpa" && mb_strlen($value) > 0){
				$value=mb_convert_kana($value, "RNASKH");
				if (preg_match("/^(\xa5[\xa1-\xf6]|\xa4[\xa1-\xf3]|\xa1\xbc)*$/", $value)) {
					$formitem[$name[0]]=$value;
				}else{
					$formitem[$name[0]]=mb_convert_encoding('<p class="error">�֤Ҥ餬�ʡפޤ��ϡ֥������ʡפ����Ϥ��Ƥ���������</p>',"EUC-JP");
					$error++;
				}
			//����
			}elseif($name[$i] == "num" && mb_strlen($value) > 0){
				$value = mb_convert_kana($value, "n");
				if (preg_match("/^[0-9]*$/", $value)) {
					$formitem[$name[0]]=$value;
				}else{
					$formitem[$name[0]]=mb_convert_encoding('<p class="error">Ⱦ�ѿ��������Ϥ��Ƥ���������</p>',"EUC-JP");
					$error++;
				}

			//�ѻ�
			}elseif( $name[$i] == "eng" && mb_strlen($value) > 0){
				$value =  mb_convert_kana($value,"as");
				if (!preg_match("/^[a-zA-Z0-9]+$/", $value)) {
					$formitem[$name[0]]=mb_convert_encoding('<p class="error">Ⱦ�ѱѿ������Ϥ��Ƥ���������</p>',"EUC-JP");
					$error++;
				}else{
					$formitem[$name[0]]=$value;
				}

			//Ʊ��
			}elseif( $name[$i] == "chk" && $value != ""){
				if (preg_match("/^[check]*$/", $value)) {
					$formitem[$name[0]]='Ʊ�դ��ޤ�����';
				}else{
					$formitem[$name[0]]=mb_convert_encoding('<p class="error">Ʊ�դ��Ƥ���ޤ���</p>',"EUC-JP");
					$error;
				}

			//̾��
			}elseif( stristr($name[0],"name1")!==false ){
				$value=mb_convert_kana($value, "RNASKH");
				if($name[0] == "name1" && mb_strlen($value) < 1 && NAMECHECK == true){
					$formitem[$name[0]]='<p class="error">���Ϥ���Ƥ��ޤ��󡣤��ι��ܤ�ɬ�ܤǤ���</p>';
					$error++;
				}else{
					$formitem[$name[0]]=htmlentities($value,ENT_QUOTES, TEXTCODE);
					//edit
					$formitem[$name[0]] = mb_convert_encoding($value, "EUC-JP");
				}

			//�դ꤬��
			}elseif( stristr($name[0],"name2")!==false ){
				$value=mb_convert_kana($value, "KHV");
				if($name[0] == "name2" && mb_strlen($value) < 1 && NAMECHECK == true){
					$formitem[$name[0]]='<p class="error">���Ϥ���Ƥ��ޤ��󡣤��ι��ܤ�ɬ�ܤǤ���</p>';
					$error++;
				}else{
					$formitem[$name[0]]=htmlentities($value,ENT_QUOTES, TEXTCODE);
					//edit
					$formitem[$name[0]] = mb_convert_encoding($value, "EUC-JP");
				}

			//���̾(����̾)
			}elseif( stristr($name[0],"company")!==false ){
				$value=mb_convert_kana($value, "RNASKH");
				if($name[0] == "company" && mb_strlen($value) < 1 && NAMECHECK == true){
					$formitem[$name[0]]=mb_convert_encoding('<p class="error">���Ϥ���Ƥ��ޤ��󡣤��ι��ܤ�ɬ�ܤǤ���</p>', "EUC-JP");
					$error++;
				}else{
					$formitem[$name[0]]=htmlentities($value,ENT_QUOTES, TEXTCODE);
					//edit
					$formitem[$name[0]] = mb_convert_encoding($value, "EUC-JP");
				}

			//����̾
			}elseif( stristr($name[0],"product")!==false ){
				$value=mb_convert_kana($value, "RNASKH");
				if($name[0] == "product" && mb_strlen($value) < 1 && NAMECHECK == true){
					$formitem[$name[0]]=mb_convert_encoding('<p class="error">���Ϥ���Ƥ��ޤ��󡣤��ι��ܤ�ɬ�ܤǤ���</p>', "EUC-JP");
					$error++;
				}else{
					$formitem[$name[0]]=htmlentities($value,ENT_QUOTES, TEXTCODE);
					//edit
					$formitem[$name[0]] = mb_convert_encoding($value, "EUC-JP");
				}

			//����
			}elseif( stristr($name[0],"model")!==false ){
				$value=mb_convert_kana($value, "RNASKH");
				if($name[0] == "model" && mb_strlen($value) < 1 && NAMECHECK == true){
					$formitem[$name[0]]=mb_convert_encoding('<p class="error">���Ϥ���Ƥ��ޤ��󡣤��ι��ܤ�ɬ�ܤǤ���</p>', "EUC-JP");
					$error++;
				}else{
					$formitem[$name[0]]=htmlentities($value,ENT_QUOTES, TEXTCODE);
					//edit
					$formitem[$name[0]] = mb_convert_encoding($value, "EUC-JP");
				}

			//�ᥢ��
			}elseif( stristr($name[0],"email")!==false){
				if($name[0] == "email" && mb_strlen($value) < 1 && NAMECHECK == true){
					$formitem[$name[0]]=mb_convert_encoding('<p class="error">���Ϥ���Ƥ��ޤ��󡣤��ι��ܤ�ɬ�ܤǤ���</p>',"EUC-JP");
					$error++;
				}elseif((mb_strlen($value) < 1 && (REPLY == true || $_POST["reply"] =="true")) || (mb_strlen($value) < 1 &&EMAILCHECK === true )){
					$formitem[$name[0]]="<p class=\"error\">";
					// 2011.03.08���������׽����б�
					$formitem[$name[0]] = preFilter($value);
					$formitem[$name[0]].="</p><br /><p class=\"error\">�᡼�륢�ɥ쥹�˸�꤬����ޤ������Τ��᤯��������</p>";
					$error++;
				}elseif(mb_strlen($value) > 0 && !preg_match("/^([a-z0-9_]|\-|\.|\+)+@(([a-z0-9_]|\-)+\.)+[a-z]{2,6}$/i",$value)){
					$formitem[$name[0]]="<p class=\"error\">";
					// 2011.03.08���������׽����б�
					$formitem[$name[0]] = preFilter($value);
					$formitem[$name[0]].="</p><br /><p class=\"error\">�᡼�륢�ɥ쥹�˸�꤬����ޤ������Τ��᤯��������</p>";
					$error++;
				}

			//URL
			}elseif( stristr($name[0],"url")!==false && mb_strlen($value) > 0){
				if(!preg_match('/^(https|http)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/', $value)) {
					$formitem[$name[0]]=mb_convert_encoding("<p class=\"error\">Ⱦ�ѵ��椬�ޤޤ�Ƥ���褦�Ǥ���Ⱦ�ѵ�������ϤϤǤ��ޤ���</p>","EUC-JP");
					$error++;
				}

			//����
			}elseif( stristr($name[0],"tel")!==false && mb_strlen($value) > 0){
				//���ϥ����å����� start
				if(strpos($value,"-")===false){
					if (!preg_match("/(^(?<!090|080|070)\d{10}$)|(^(090|080|070)\d{8}$)|(^0120\d{6}$)|(^0080\d{7}$)/", $value)) {
						$formitem[$name[0]]="<p class=\"error\">";
						$formitem[$name[0]]="�����ֹ�ν񼰤˸�꤬����ޤ���";
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
						$formitem[$name[0]].="</p><br /><p class=\"error\">�����ֹ�ν񼰤˸�꤬����ޤ���</p>";
						$error++;
					}else{
						$formitem[$name[0]]=$value;
					}
				//���ϥ����å����� end
				}

			//����
			}elseif( stristr($name[0],"message")!==false && mb_strlen($value) > 0){
				$value=mb_convert_kana($value, "RNASKH");
				if(is_array($blocktxt)&&count($blocktxt)>0){//�ػ߸������å�
					foreach($blocktxt as $fuck){
						if (strstr($value,$fuck) === false){
							$formitem[$name[0]]=preFilter($value);
							$formitem[$name[0]]=mb_convert_encoding($value, "EUC-JP");
						}else{
							$formitem[$name[0]]="<p class=\"error\">";
							$formitem[$name[0]].=mb_convert_encoding($value, "EUC-JP");
							$formitem[$name[0]].="</p><br /><p class=\"error\">�ػ߸�礬�ޤޤ�Ƥ���褦�Ǥ���</p>";
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
 * ź�եե�����Υ����å�
 *
 ************************************************************/
function checkUploadData($FILES)
{
	$err = 0;

	foreach ($FILES as $name => $array) {

		$filename = $array["name"];

		if(FILETEMP === false){

			$_SESSION[$name] = "<strong class=\"error\">�ե�����Υ��åץ��ɤ����Ĥ���Ƥ��ޤ���</strong>";
			$err = 1;

		}elseif ($array["error"] == 0) {

			$tmp_name =$array["tmp_name"];
			$filesize = $array["size"];
			$filetype = $array['type'];
			preg_match("/^.+?((?:\.\w{3})*\.\w{2,4})$/i", $filename, $extension);//��ĥ��

			if(!check_minetype($filetype, strtolower($extension[1]))){
				$_SESSION[$name] = convert_encode('<strong class="error">'.$label."[".$filename."] �Υե������������Ŭ�ڤǤ���</strong>");
				$err = 1;
			}

			if($filesize >= MAXSIZE*1000 ){ //�ե����륵����
				$_SESSION[$name] = convert_encode('<strong class="error">'.$label."[".$filename."] �Υե����륵����(".($filesize/1000)."kb)���礭�����ޤ�</strong>");
				$err = 1;
			}

			if(!$err){
				if((FILEPOOL === true) || ! preg_match("/^[a-z0-9_\-\.]+?\.\w{2,4}$/i", $filename)){//FILEPOOL=ON | ���������ܸ���ä���

					$filename = substr(md5(microtime()), 0, rand(5, 8)).$extension[1];//Ŭ����̾���դ���
				}else{
					$filename = strtolower($filename);
				}

				$target = (strpos(IMG_CHECK_TARGET,"_")===0) ? ' target="'.IMG_CHECK_TARGET.'"':' rel="'.IMG_CHECK_TARGET.'"';

				$_SESSION[$name] = convert_encode($filename." (".($filesize/1000)."kb)".' <a href="'.UPLOADPASS.$filename.'"'.$target.'>�ե�����γ�ǧ</a>');
				$_SESSION["FILES"][] = array('filename'=>$filename, 'type' =>$filetype);
				$_SESSION["FILETEMP"] = true;

				//tmp�ե�������ư
				move_uploaded_file($tmp_name,UPLOADPASS.$filename);


			}

		}elseif($array["error"] != 4){

			switch($array["error"]){
				case 1:
					$_SESSION[$name] = convert_encode("<strong class=\"error\">[".$filename."] �Υե����륵�������礭�����ޤ�</strong>");
					$err = 1;
				break;

				case 2:
					$_SESSION[$name] = convert_encode("<strong class=\"error\">[".$filename."] �Υե����륵�������礭�����ޤ�</strong>");
					$err = 1;
				break;

				case 6:
					$_SESSION[$name] = convert_encode("<strong class=\"error\">�ƥ�ݥ��ե����������ޤ���</strong>");
					$err = 1;
				break;

				default:
					$_SESSION[$name] = convert_encode("<strong class=\"error\">[".$filename."] �ϥ��åץ��ɤǤ��ޤ���Ǥ���</strong>");
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
 * Ajax�����ϥ��顼ɽ��
 *
 ************************************************************/
function AjaxConfDisp($formitem){

	global $inputs2,$replymessage;

	if($formitem['Err']>1)
		print '<div id="error" class="zeromail"><p><span class="error">���ϥ��顼�������Ƥ���������</span></p>';
	else
		print '<div class="zeromail confirmed"><p><span class="confirm">�������Ƥ˴ְ㤤��̵����С������ܥ���򲡤��Ƥ���������</span></p>';

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
 * �����ؿ�
 *
 ************************************************************/

function zeromailAjax_send($formitem)
{
	global $inputs2, $endMassage,$replyfoot,$replymessage,$replycomment;

	ip_check_destroy();

	//�桼��������
	if(is_admin()) $csv = '"'.date("Y/m/d H:i:s").'"';

//��ʸ��������
$message=MAILSUBJECT;
$message.="\n������������������������������������������������������������������������";
foreach($inputs2 as $key => $value){

	if(preg_match("/<a href=\"(.+)\"(.*)>?�ե�����γ�ǧ<\/a>/i",$formitem[$key])){
		$formitem[$key] = preg_replace("/\s+<a href=\"(.+)\"(.*)>?�ե�����γ�ǧ<\/a>/i","",$formitem[$key]);
	}

	if(is_admin()) $csv .=',"'.str_replace(array("\n","\r","\r\n"),"",$formitem[$key]).'"';

$message.= <<<EOM
\n��$value
{$formitem[$key]}\n
EOM;
}

// 2015.09.09 delete
// ���륵���С��ǤΥС�����󥢥å׻���Warring�б���php version 4.3.11��5.5.28��

	if(is_admin())
		$csv .= ',"'.$user_ip.'","'.$user_host.'","'.$user_agent.'"'."\n";

	// 2011.03.08���������׽����б�
	//��ʸ����(������¦)
	if(strpos(PHPVER,'5')===false){
		$message = unhtmlentities($message);
	}else{
		$message = html_entity_decode($message,ENT_QUOTES,'EUC-JP');
	}
	$message = str_replace("\r","",str_replace("<br />","",$message));
	$message = mb_convert_encoding($message, "ISO-2022-JP", "EUC-JP");
	$name = isset($formitem["name1"])? $formitem["name1"] : SCRIPT;

	//ź�եե�����ʤ���POOL�����
	if(FILETEMP === false || (FILETEMP === true && FILEPOOL === true) || (FILETEMP === true && $formitem["FILETEMP"] !== true)){

		$mailheader ="From: ".get_mailfrom($name, $formitem['email'])."\n";
		if(BCC != "") $mailheader.="Bcc: ".BCC."\n";
		$mailheader.="X-Mailer: ".SCRIPT."(Version ".VERSION.")\n";

		@mb_send_mail(MAILTO,MAILSUBJECT,$message,$mailheader);

	//ź�եե����뤢���POOL�ʤ���
	}elseif(FILEPOOL === false && FILETEMP === true && $formitem["FILETEMP"]===true){
		$boundary = "zeromail".md5(uniqid(rand()));//�Х�����꡼
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
			$fp = @fopen(ABSDIR.UPLOADPASS.$tmp["filename"], "r"); //�ե�������ɤ߹���
			$contents = @fread($fp, @filesize(ABSDIR.UPLOADPASS.$tmp["filename"]));
			@fclose($fp);
			$encoded = base64_encode($contents); //���󥳡���
			$msg .= "\n\n--".$boundary."\n";
			$msg .= "Content-Type: " . $tmp["type"] . ";\n";
			$msg .= "\tname=\"".$tmp["filename"]."\"\n";
			$msg .= "Content-Transfer-Encoding: base64\n";
			$msg .= "Content-Disposition: attachment;\n";
			$msg .= "\tfilename=\"".$tmp["filename"]."\"\n\n";
			$msg .= $encoded."\n";
		}

		RemoveFiles(ABSDIR.UPLOADPASS);//�ե�������

		$msg .= "--".$boundary."--";

		$subject = base_64_encode(MAILSUBJECT,true);

		@mail(MAILTO,$subject, $msg, $mailheader);
	}

	//�᡼�뼫ư�ֿ�
	// 2015.09.09 delete $formitem['reply'] === "true" || 
	// ���륵���С��ǤΥС�����󥢥å׻���Notice�б���php version 4.3.11��5.5.28��
	if( REPLY === true && $formitem['email'] != "" ){
	//if( ( $formitem['reply'] === "true" || REPLY === true ) && $formitem['email'] != "" ){
		$replyheader ="From: \"".base_64_encode(FROMNAME,true)."\" <".MAILTO.">\r\n";
		if(BCC != "") $replyheader.="Bcc: ".BCC."\r\n";
		$replyheader.="X-Mailer: ".SCRIPT."(Version ".VERSION.")\r\n";

//��ư�ֿ���ʸ��������
$replymessage=$replycomment;
foreach( $inputs2 as $key => $value){
	if(preg_match("/<a href=\"(.+)\"(.*)>?�ե�����γ�ǧ<\/a>/i",$formitem[$key]))
	$formitem[$key] = preg_replace("/<a href=\"(.+)\"(.*)>?�ե�����γ�ǧ<\/a>/i","",$formitem[$key]);
$replymessage.= <<<EOM
\n��$value
{$formitem[$key]}\n
EOM;
}
$replymessage.= <<<EOM
$replyfoot

EOM;
		$pos = strpos(PHPVER,'5');
		//��ư�ֿ���ʸ����(�����͹���)
		if( $pos === false){
			$replymessage = unhtmlentities($replymessage);
		}else{
			// ver 5.
			$replymessage = html_entity_decode($replymessage,ENT_QUOTES,'EUC-JP');
		}
		$replymessage = str_replace("\r","",str_replace("<br />","", $replymessage));

		@mb_send_mail($formitem['email'], REPSUBJECT, $replymessage, $replyheader);
	}

	if(is_admin()) zeromail_data_put_csv($csv);//CSV��¸

	// ���å��������Ǥ���ˤϥ��å���󥯥å����������롣
	// Note: ���å�����������Ǥʤ����å������˲����롣
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
  �������̵�ǽ��¸�ߥ����å�
------------------------------------------------------*/
function is_admin()
{
	return (defined('ZM_ADMIN') && ZM_ADMIN === true);
}

/*-----------------------------------------------------
  csv�˽񤭹���
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
  ISO-2022-JP���󥳡���
------------------------------------------------------*/
function base_64_encode($str,$encode=false)
{
	if($encode)	 $str = mb_convert_encoding($str, "JIS", TEXTCODE);
	return "=?ISO-2022-JP?B?".base64_encode($str). "?=";
}


/*-----------------------------------------------------
  ���пͥե����ޥå�
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
  ź�եե�������
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
  �ե����륿���פΥ����å�
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
  IP�����å�
------------------------------------------------------*/
function ip_check_destroy()
{
	global $blockIP;

	if(array_search($_SERVER['REMOTE_ADDR'],$blockIP)!==false){
		if(NOSCRIPT===true){
			ErrerDisp('������ǧ����Ƥ��ޤ���');
		}else{
			print "<div id=\"error\"><p><span class=\"error\">������ǧ����Ƥ��ޤ���</span></p></div>";
			exit;
		}
	}
}

/*-----------------------------------------------------
  ��ե�������å�
------------------------------------------------------*/
function ref_check_destroy()
{
	global $formURL;

	if(REFCHECK === true && $formURL!="" && $_SERVER["HTTP_REFERER"]!==$formURL){ //��ե��顼�����å�

		if(NOSCRIPT===true){
			ErrerDisp('�������������Ǥ���');
		}else{
			print "<div id=\"error\"><p><span class=\"error\">�������������Ǥ���</span></p></div>";
			exit;
		}
	}
}

/*-----------------------------------------------------
  magic_quotes_gpc=ON�к�
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
  ����å���ä�
------------------------------------------------------*/
function stripslashes_deep($str)
{
	$str = is_array($str) ?
		array_map('stripslashes_deep', $str) :
		stripslashes($str);
	return $str;
}

/*-----------------------------------------------------
  ver4�ѥǥ�����
------------------------------------------------------*/
function unhtmlentities($string)
{
    // ���ͥ���ƥ��ƥ����ִ�
    $string = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $string);
    $string = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $string);
    // ʸ������ƥ��ƥ����ִ�
    $trans_tbl = get_html_translation_table(HTML_ENTITIES);
    $trans_tbl = array_flip($trans_tbl);
    return strtr($string, $trans_tbl);
}
?>