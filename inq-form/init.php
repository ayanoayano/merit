<?php
/*------------------------------------------------------------
  mail.php
--------------------------------------------------------------*/

/*//////////* ��������ʱ�¦�Τ��ѹ����뤳�ȡ� *//////////////*/

//ʸ�������ɡ�"UTF-8,EUC-JP,SJIS"��
define('TEXTCODE','EUC-JP');

//�ե�����ǡ�����������᡼�륢�ɥ쥹
define('MAILTO','Merit-jp@ml.toshiba.co.jp');

//���п�̾
define('FROMNAME',"������ҥ��å�");

//���������η�̾
define('MAILSUBJECT','���䤤��碌�ե����फ������������ޤ���');

//��ư�ֿ��η�̾
define('REPSUBJECT','�ֳ�����ҥ��åȡפ��䤤��碌���մ�λ�Τ��Τ餻');

//��ư�ֿ�
//true�ˤ������ϥե�����ǤΥ����å���̵ͭ�ط��ʤ��ֿ�����
//��������˴ط��ʤ��᡼�륢�ɥ쥹��ɬ�����Ϥˤʤ�
//define('REPLY',true);
define('REPLY',true);

//�ᥢ�ɤ�ɬ�ܹ��ܤˤ��뤫�ɤ�����ɬ�ܤˤ������true��
define('EMAILCHECK',true);

//�ֿ��᡼���ź�����å������ʥإå���
$replymessage=<<<EOM
�ڤ��Υ᡼��ϥ����ƥफ��μ�ư�ֿ��Ǥ���

���䤤��碌���꤬�Ȥ��������ޤ�����
�ʲ������Ƥ������������ޤ�����
������������������������������������������������������������������������
EOM;

//�ֿ��᡼���ź�����å������ʤ������ѥإå���
$replycomment=<<<EOM
������ҥ��åȤǤ���
�����Τ���礻�᡼����¤����פ��ޤ�����
���Ƴ�ǧ���衢����Ƥ�Ϣ�������ޤ���
��������ꤤ�������ޤ��� 

EOM;

//�ֿ��᡼���ź�����̾�ʥեå���
$replyfoot= <<<EOM

=====================================================
������ҥ��å�
����324-0036
�����ڸ����ĸ��Բ��о�1376����3
����ǥ�ǥ����륷���ƥॺ����������
��TEL��0287-26-1818
��FAX��0287-29-3690
��URL��http://www.toshiba.co.jp/merit/
===================================================== 
EOM;

//BCC�Ǽ�����꤬ɬ�פʾ��ϥ��ɥ쥹������
define('BCC','');

/*----------------------------------------------------
  �ƥ�ץ졼��
----------------------------------------------------*/
//��ǧ���̡�zeromail.php����Υѥ���
//define('CHECKPAGE','check.php');
define('CHECKPAGE','form_other_submit.php');

//������λ��ɽ������ڡ�����mail.php����Υѥ���
define('SUCCESSPAGE','http://www.toshiba.co.jp/merit/inquiry/form_other_finish.htm');

//���顼��ɽ������ڡ�����mail.php����Υѥ���
//define('ERRORPAGE','error.php');
define('ERRORPAGE','form_other_submit.php');

//Ajax������λ��Υ�å�����
$endMassage = "<div class=\"success exit\"><p class=\"comment\">�������ޤ���</p></div>";

//��ǧ���̤Υե�����ǡ������Ϸ����ʶ���p������
//Table ���ơ��֥�ԡ�th=����̾ td=�͡�
//List �� ����ꥹ�ȡ�dt=����̾ dd=�͡�
define('VIEWSTYLE','Table');

//input��name�Ȥ���̾������
//'name'=>'̾��'�ǣ����åȡ����ֺǸ�ˤ�,��Ĥ��ʤ���
//�����ǽ񤤤��¤ӽ礬����ǧ���̤������᡼����ʸ��ȿ�Ǥ���롣
$inputs = array(
	'chk'=>'Ʊ��',
	'name1'=>'��̾',
	'name2'=>'�դ꤬��',
	'Work'=>'����',
	'company'=>'����̾�����̾',
	'zipcode'=>'͹���ֹ�',
	'pref'=>'��ƻ�ܸ�',
	'address'=>'�Զ�Į¼',
	'tel'=>'�����ֹ�',
	'fax'=>'FAX�ֹ�',
	'email'=>'E-Mail',
	'message'=>'���䤤��碌����'
);

/*-----------------------------------------------------
  �ե�����ź�յ�ǽ
------------------------------------------------------*/

//�ե�����ź�յ�ǽ��Ȥ����ɤ�����true�ˤ��ʤ�����������ʤ���
define('FILETEMP',false);

//�ե������ź�դ�������¸���뤫�ɤ�����true����¸��
define('FILEPOOL',false);

//�ե�����κ��祵����(kb)
define("MAXSIZE",1000);

//ź�եե�����ΰ����¸�ǥ��쥯�ȥ��zeromail.php��Ʊ���ؤΥե�������Ѱդ��뤳�ȡ�
define("UPLOADPASS","upfile/");

//������ǧ����Window�������å�
//_string �� target="_string"
//string �� rel="string"
define("IMG_CHECK_TARGET","_blank");

/*--------------------------------------------------------------
�����ѥ��к�����
--------------------------------------------------------------*/

//name��ɬ�ܹ��ܤˤ��뤫�ɤ�����ɬ�ܤˤ������true��
define('NAMECHECK',true);

//Ⱦ�ѱѿ���̾������Ĥ��뤫�ɤ����ʵ��Ĥ������true��
define('ALPHANAME',true);

//��ե��顼�����å����뤫�ɤ���
//true�ˤ������ϲ��������ꤷ���ڡ����ʳ������������֥�å����롣
//���Υե�ѥ�����⤹�뤳�ȡ�
define('REFCHECK',false);

//���ϥե�����Υѥ�
$formURL="";

//��ʸ�Υ�󥯵��ҵ��Ŀ��ʰʲ���
$alink = 1;

//��å�������ζػ߸��
//����ޤ���Ͽ��������Ƚ������Ť��ʤ�Τ����
$blocktxt =array('��','death','porno','sex','pill','fuck','<script','<object');

//�����֥�å�����IP���ɥ쥹
$blockIP = array();


/*///////////////////* END CONFIG *////////////////////*/


/*-----------------------------------------------------
  ���̽����Ѵؿ�
------------------------------------------------------*/

//���顼����
//$err�ᥨ�顼��å���������

function ErrerDisp($err){
	session_unset();
	$_SESSION['Err']=-1;
	$GLOBALS['err']=$err;
	include(ERRORPAGE);
	exit;
}

//��ǧ���̤Υ�å�����
function Message(){

	if($_SESSION['Err']>1){
		$str = '<span class="error">���Υڡ�������ꡢ���ϥ��顼�������Ƥ���������</span>';
	}else if($_SESSION['Err']===-1){
		$str = '<span class="error">'.$GLOBALS['err'].'</span>';
	}else{
		$str ='<span class="confirm">�������Ƥ˴ְ㤤��̵����С������ܥ���򲡤��Ƥ���������</span>';
	}

	//echo convert_encode($str);
	echo $str;

}


//��ǧ���������ܥ����hidden��session_unset�Ͼä��ʤ����ȡ���
function Button(){

	if($_SESSION['Err']>1||$_SESSION['Err']===-1){
		$str = '<noscript><p class="return">�֥饦���Υܥ������äƤ���������</p></noscript><a href="#" class="rollover" onclick="history.back(); return false;"><img src="../inquiry/img/btn_repair.gif" alt="���Ƥ���" /></a>';
		session_unset();
	}else{
		$str = '<a href="#" class="rollover" onclick="history.back(); return false;"><img src="../inquiry/img/btn_repair.gif" alt="���Ƥ���" /></a>';
		$str .= '<input name="mode" type="hidden" id="mode" value="Send" /><input type="image" style="vertical-align:top" src="../inquiry/img/btn_submit.gif" value="submit" alt="����" onmouseover="this.src=\'../inquiry/img/btn_submit_f2.gif\'" onmouseout="this.src=\'../inquiry/img/btn_submit.gif\'" />';
	}
	echo $str;
}

/*---------------------* ����ػ� *-------------------------*/

//���󥳡����Ѵ�
function convert_encode($str){return mb_convert_encoding($str,TEXTCODE,"UTF-8");}

//rep[name]�λ���ˤ���ִ�
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