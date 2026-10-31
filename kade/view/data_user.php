<?php
if ($viewExtCtrl == null){
	header("HTTP/1.0 404 Not Found");
	exit ();
}
require_once(dirname(dirname(__FILE__))."/shared/class/controller/CustumerRegCtrl.class.php");

$user      = $ctrlCustReg->getLoggedUserFresh();
$userSuper = User::isUserSuper();
$_ufList   = $ctrlCustReg->getListUf();

$pageTitle = "Meus dados";
$pageDesc  = "Mantenha seus dados de contato e endereço atualizados.";
?>
<script type="text/javascript" charset="UTF-8" src="../shared/js/jquery.mask.min.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/webmask2.min.js?v=<?=@filemtime(dirname(dirname(__FILE__))."/shared/js/webmask2.min.js")?>"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/FormFieldException.class.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/Address.class.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/CustumerBean.class.js?v=<?=@filemtime(dirname(dirname(__FILE__))."/shared/js/CustumerBean.class.js")?>"></script>
<script type="text/javascript" charset="UTF-8">$(document).ready(function(){CustumerBean.getInstance().initBean();});</script>
<?php include(dirname(__FILE__)."/partials/int_open.php"); ?>
	<?php include(dirname(__FILE__)."/inc_data_user.php"); ?>
<?php include(dirname(__FILE__)."/partials/int_close.php"); ?>
