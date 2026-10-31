<?php
/**
 * Alerta de mensagem do sistema. Espera $message (Message) no escopo; não mostra nada se vazio.
 * A descrição pode conter HTML montado pelo próprio sistema (ex.: link "clique aqui").
 */
if (isset($message) && $message != null && $message->getDesc() != "") {
	$alertStyles = array(
		Message::ERR     => array("k:border-[#f3c2c2] k:bg-[#fdecec] k:text-[#8a1f1f]", "M12 8v5M12 16.5v.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"),
		Message::SUCCESS => array("k:border-[#bfe5cb] k:bg-[#e7f6ec] k:text-[#1a5c30]", "M20 6 9 17l-5-5"),
		Message::WARN    => array("k:border-[#f5d7a8] k:bg-accent-soft k:text-[#7a4300]", "M12 8v5M12 16.5v.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"),
		Message::INFO    => array("k:border-[#c9daf2] k:bg-kade-50 k:text-kade-900", "M12 16v-4M12 8h.01M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20z"),
	);
	$alertStyle = $alertStyles[$message->getType()] ?? $alertStyles[Message::INFO];
?>
<div role="<?=$message->getType() == Message::ERR ? "alert" : "status"?>" class="k:mb-5 k:flex k:items-start k:gap-3 k:rounded-xl k:border k:px-4 k:py-3 k:text-[15px] <?=$alertStyle[0]?>">
	<svg class="k:mt-0.5 k:shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="<?=$alertStyle[1]?>"/></svg>
	<div><?=$message->getDesc()?></div>
</div>
<?php } ?>
