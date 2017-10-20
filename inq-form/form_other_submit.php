<?php require_once('confirm1.php');//confim.phpへのパス。無いと動かない。?>
<?php print('<?xml version="1.0" encoding="EUC-JP"?>'."\n");?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ja" xml:lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta name="keywords" content="" />
<meta name="description" content="" />
<link rel="start" href="../index.htm" title="株式会社メリット" />
<link rel="help" href="../sitemap/index.htm" title="サイトマップ" />
<link rel="stylesheet" type="text/css" href="../css/import.css" media="all" />
<script type="text/javascript" src="../jvs/rollover.js"></script>
<script type="text/javascript" src="../jvs/new_win.js"></script>

<title>お問い合わせフォーム | 株式会社メリット</title>
<!--[if IE 6]><script src="../jvs/DD_belatedPNG.js"></script>
<script>
DD_belatedPNG.fix('img, #gmArea, #contentsArea, #footArea'); 
</script>
<![endif]-->
<link rel="stylesheet" type="text/css" href="meritmail.css" media="all" />
<link rel="stylesheet" type="text/css" href="../css/print.css" media="print" />
</head>
<body><div id="headArea">
<div class="inner">
<div class="logo">

<a href="http://www.toshiba.co.jp/merit/index.htm"><img src="../img/common/head/head_logo.jpg" alt="MERIT" width="83" height="27" /><img src="../img/common/head/head_logo_2.jpg" alt="株式会社メリット" width="148" height="29" class="logo2" /></a>

</div>
<!--.logo-->

<div class="utility">
<ul>
<li class="contact">

<a href="http://www.toshiba.co.jp/merit/inquiry/index.htm">お問い合わせ</a>
</li>
<li class="sitemap">

<a href="http://www.toshiba.co.jp/merit/sitemap/index.htm">サイトマップ</a>

</li>
</ul>

</div>
<!--.utility-->
</div>
<!--.inner-->
</div>
<!--#headArea-->
<div id="gmArea"><div class="inner">
  <ul>
<li>
<a href="http://www.toshiba.co.jp/merit/maintenance/index.htm" class="category1"><span class="noUse">「再整備」医療機器とは</span></a>
</li>
<li>
<a href="http://www.toshiba.co.jp/merit/support/index.htm" class="category2"><span class="noUse">サポート体制</span></a>
</li>
<li>


<a href="http://www.toshiba.co.jp/merit/product/index.htm" class="category3"><span class="noUse">在庫情報</span></a>
</li>
<li>
<a href="http://www.toshiba.co.jp/merit/company/index.htm" class="category4"><span class="noUse">会社情報</span></a>
</li>

</ul>
</div>
<!--.inner-->
</div>
<!--#gmArea-->

<div id="contentsArea">
<div class="inner">
<!-- InstanceBeginEditable name="content" -->
<h1><img src="../inquiry/img/h1_img_form.jpg" alt="お問い合わせフォーム" width="860" height="105" /></h1>
<div class="layout">
<form method="post" action="mail1.php" enctype="multipart/form-data" class="zeromail">
<p class="message"><?php Message();//メッセージ?></p>
<table border="0" cellspacing="0" cellpadding="0" summary="レイアウトテーブル" class="zeromail">
<?php ConfDisp();//確認表示。?>
</table>
<!--<div class="buttanArea">-->
<div class="btnArea">
<br />
<?php Button();//ボタン表示。form内に置くこと。 ?>
</div>
</form>
</div><!--.layout-->

<!-- InstanceEndEditable -->

<div id="pageTop">
<ul>
<li><img src="../img/common/made_for_life.gif" alt="Made for Life" width="62" height="56" /></li>

<li class="top"><a href="#">このページのトップへ</a></li></ul></div>
<!--.pageTop-->
</div>
<!--.inner-->
</div><!--#contentsArea-->

<div id="footArea">
<div class="inner">
<div class="leftArea">
<ul>

<li class="home"><a href="http://www.toshiba.co.jp/merit/index.htm">メリットのトップページへ</a></li>

<li>

<a href="http://www.toshiba.co.jp/merit/privacy/index.htm">個人情報保護方針</a>
</li>
</ul>
</div><!--.leftArea-->
<div class="rightArea">
<div class="ecoStyle"><a href="http://ecostyle.toshiba.co.jp/"><img src="../img/common/foot/eco.gif" width="327" height="32" alt="東芝グループは、持続可能な地球の未来に貢献します。ecoスタイル" /></a></div>
</div><!--.rightArea-->
<address>Copyright 2012 Merit Japan Co.,Ltd. ALL Rights Reserved. </address>
</div>
<!--.inner--></div><!--#footArea-->
</body>
</html>
