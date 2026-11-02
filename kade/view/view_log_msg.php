<?php
if ($viewExtCtrl == null){
	header("HTTP/1.0 404 Not Found");
	exit ();
}
require_once(dirname(dirname(__FILE__))."/shared/class/controller/MsgLogQueryCtrl.class.php");

$userSuper = User::isUserSuper();
if (!$userSuper){
	header("HTTP/1.0 404 Not Found");
	exit ();
}
$ctrlMsgLogQry->onSearchArray();
$_msgLogs   = $ctrlMsgLogQry->getMsgLogs() ?? array();
$length     = count($_msgLogs);
$pagination = $ctrlMsgLogQry->getPagination();
$message    = $ctrlMsgLogQry->getMessage();

// Tudo aqui vem de SMS, WhatsApp e Telegram (rotas públicas): sempre escapar
$h = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, "UTF-8"); };
$typeLog = $_POST["type_log"] ?? "";
$channel = $_POST["channel"] ?? "";
$_channels = Messenger::channels();
// Resultado: sucesso, erro ou "outras" (instruções, lista de códigos, número confirmado no Telegram)
$_results = array(
	Message::SUCCESS => array("Sucesso", "UTILIZED"),
	Message::ERR     => array("Erro", "CANCEL"),
	Message::INFO    => array("Outras", ""),
);

$pageTitle = "Log de mensagens";
$pageDesc  = "Mensagens recebidas pelo cadastro de veículos via SMS, WhatsApp e Telegram.";
?>
<script type="text/javascript" charset="UTF-8" src="../shared/js/jquery.mask.min.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/webmask2.min.js?v=<?=@filemtime(dirname(dirname(__FILE__))."/shared/js/webmask2.min.js")?>"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/Pagination.class.js?f=1"></script>
<script type="text/javascript" charset="UTF-8" src="../shared/js/MsgLogBean.class.js?v=<?=@filemtime(dirname(dirname(__FILE__))."/shared/js/MsgLogBean.class.js")?>"></script>
<script type="text/javascript" charset="UTF-8">$(document).ready(function(){MsgLogBean.getInstance().initQuery();});</script>
<?php if ($message != null && $message->getDesc() != "") { ?>
	<script type="text/javascript" charset="UTF-8">$(document).ready(function(){$("#span_loc_msg").notify(<?=json_encode($message->getDesc())?>, <?=json_encode($message->getType())?>);});</script>
<?php } ?>
<?php include(dirname(__FILE__)."/partials/int_open.php"); ?>

	<form id="msglog_query_open" name="msglog_query_open" action="<?=$h($_SERVER["REQUEST_URI"])?>" method="post" class="k:flex k:flex-col k:gap-5 k:rounded-2xl k:border k:border-line k:bg-white k:p-5 k:md:p-6">
		<div class="k:grid k:grid-cols-1 k:gap-4 k:sm:grid-cols-2 k:lg:grid-cols-4">
			<label class="k-label" for="msglog_dt_init">Período: de
				<input type="text" id="msglog_dt_init" name="msglog_dt_init" maxlength="10" class="k-field mask.date input_clear" value="<?=$ctrlMsgLogQry->getDtHrInit()->format("d.m.Y")?>"/>
			</label>
			<label class="k-label" for="msglog_dt_end">até
				<input type="text" id="msglog_dt_end" name="msglog_dt_end" maxlength="10" class="k-field mask.date input_clear" value="<?=$ctrlMsgLogQry->getDtHrEnd()->format("d.m.Y")?>"/>
			</label>
			<label class="k-label" for="type_log" id="type_log_label">Resultado
				<select id="type_log" name="type_log" class="k-field">
					<option value="">Todos</option>
					<?php foreach ($_results as $value => $result) { ?>
					<option value="<?=$value?>" <?=$typeLog === $value ? "selected" : ""?>><?=$result[0]?></option>
					<?php } ?>
				</select>
			</label>
			<label class="k-label" for="channel">Canal
				<select id="channel" name="channel" class="k-field">
					<option value="">Todos</option>
					<?php foreach ($_channels as $value => $name) { ?>
					<option value="<?=$value?>" <?=$channel === $value ? "selected" : ""?>><?=$name?></option>
					<?php } ?>
				</select>
			</label>
		</div>
		<div id="buttonBar" class="k:flex k:flex-wrap k:items-center k:justify-end k:gap-2.5">
			<span id="span_loc_msg" class="k:mr-auto"></span>
			<button type="button" id="button_reset" class="k-btn">Limpar</button>
			<button type="submit" id="button_search" class="k-btn k-btn-primary">Buscar</button>
		</div>
		<input type="hidden" id="reg_per_pag" name="reg_per_pag" value="<?=$pagination->getEndLimit();?>"/>
		<input type="hidden" id="pag" name="pag" value="<?=$pagination->getPagCurrent();?>"/>
		<input type="hidden" id="order_column" name="order_column" value="<?=$h($ctrlMsgLogQry->getOrderColumn());?>"/>
		<input type="hidden" id="order_type" name="order_type" value="<?=$h($ctrlMsgLogQry->getOrderType());?>"/>
		<input type="hidden" id="msg_type" name="msg_type" value=""/>
		<input type="hidden" id="msg_txt" name="msg_txt" value=""/>
	</form>

	<section id="div_result" class="k:overflow-hidden k:rounded-2xl k:border k:border-line k:bg-white" aria-label="Mensagens">
		<div class="k:border-b k:border-line k:px-5 k:py-4 k:text-[15px]">
			<strong class="k:font-semibold"><?=(int) $pagination->getTotalReg()?> <?=$pagination->getTotalReg() == 1 ? "mensagem" : "mensagens"?></strong>
			<?php if ($pagination->getTotalReg() > 0) { ?><span class="k:text-muted"> · exibindo <?=$pagination->showDetailsPag();?></span><?php } ?>
		</div>
		<div class="k-focus k:overflow-x-auto" tabindex="0" role="region" aria-label="Tabela (role para o lado se precisar)">
			<table id="table_query_open" class="k-table">
				<thead>
					<tr>
						<th>Recebida em</th>
						<th>Canal</th>
						<th>Telefone</th>
						<th>Mensagem</th>
						<th>Resultado</th>
						<th>Detalhe</th>
						<th>IP</th>
					</tr>
				</thead>
				<tbody class="tbody_query">
					<?php if ($length == 0) { ?>
					<tr><td colspan="7" class="k:py-10 k:text-center k:text-muted">Nenhuma mensagem no período.</td></tr>
					<?php } ?>
					<?php foreach ($_msgLogs as $msgLogList) {
						$dtHr = new DateTimeCustom($msgLogList->date_hr ?? "now");
						// O log pode ser JSON ([{"message": ...}]) ou texto puro
						$json = json_decode((string) $msgLogList->log);
						$detail = (is_array($json) && isset($json[0]->message) && $json[0]->message != "")
							? html_entity_decode($json[0]->message, ENT_QUOTES, "UTF-8")
							: (string) $msgLogList->log;
						$result = $_results[(string) $msgLogList->type_log] ?? $_results[Message::ERR]; ?>
					<tr>
						<td class="k:whitespace-nowrap k:text-ink-soft"><?=$dtHr->format("d/m/Y H:i")?></td>
						<td class="k:whitespace-nowrap"><?=$h($_channels[$msgLogList->channel] ?? $msgLogList->channel)?></td>
						<td class="k:whitespace-nowrap"><?=$h($msgLogList->from)?></td>
						<td class="k:font-mono k:text-[13px]"><?=$h($msgLogList->body)?></td>
						<td><span class="k-badge <?=$result[1]?>"><?=$result[0]?></span></td>
						<td class="k:text-ink-soft"><?=$h($detail)?></td>
						<td class="k:whitespace-nowrap k:text-ink-soft" title="URL requisitada: <?=$h($msgLogList->url_request)?>"><?=$h($msgLogList->ip_trat)?></td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
		<?php include(dirname(__FILE__)."/partials/pagination.php"); ?>
	</section>

<?php include(dirname(__FILE__)."/partials/int_close.php"); ?>
