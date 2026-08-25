<?php
if ($viewExtCtrl == null){
	header("HTTP/1.0 404 Not Found");
	exit ();
}
require_once(dirname(dirname(__FILE__))."/shared/class/controller/CustumerRegCtrl.class.php");

$user 	   = User::getLogged();
$userSuper = User::isUserSuper();
$_ufList   = $ctrlCustReg->getListUf();

?>

<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/default_int.css?f=1"/>
<link type="text/css" rel="stylesheet" media="screen" href="../shared/css/data_user.css?f=1"/>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/jquery.mask.min.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/webmask2.min.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/FormFieldException.class.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/ViewInt.class.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/Address.class.js?f=1"></script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="../shared/js/CustumerBean.class.js?f=1"></script>
<script language="javascript" type="text/javascript" charset="UTF-8">$(document).ready(function(){ViewInt.getInstance().initQuery();CustumerBean.getInstance().initBean();});</script>
<div id="div_body_edit_clie" class="div_body">
	<div id="div_inter_esq">
		<?php include_once("menu_int.php");?>
	</div>
	<div id="div_inter_dir">
		<?php include_once("inc_data_user.php"); ?>
	</div>
</div>

