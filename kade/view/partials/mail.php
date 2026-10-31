<?php
/**
 * Layout dos e-mails. Tabelas e estilos inline porque é o que os clientes de
 * e-mail entendem (sem Tailwind, sem CSS externo).
 * Variáveis: $mailTitle, $mailGreeting, $mailText, $mailButton, $mailUrl,
 * $mailLogin (opcional), $mailNotes (parágrafos em HTML, já escapados), $mailBaseUrl.
 */
if (!isset($mailTitle)){
	header("HTTP/1.0 404 Not Found");
	exit ();
}
$e = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, "UTF-8"); };
$font = "font-family:Helvetica,Arial,sans-serif;";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?=$e($mailTitle)?></title>
</head>
<body style="margin:0;padding:0;background:#f6f7f9;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f6f7f9;">
<tr>
<td align="center" style="padding:32px 16px;">
	<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:560px;">
		<tr>
			<td style="padding:0 4px 20px 4px;<?=$font?>font-size:20px;font-weight:bold;color:#0b3b6f;">
				KADE <span style="font-weight:normal;color:#5b6576;">Caminhões</span>
			</td>
		</tr>
		<tr>
			<td style="background:#ffffff;border:1px solid #e3e7ee;border-radius:16px;padding:32px;<?=$font?>font-size:15px;line-height:1.6;color:#3d4757;">
				<h1 style="margin:0 0 16px 0;<?=$font?>font-size:22px;line-height:1.3;color:#0b2545;"><?=$e($mailTitle)?></h1>
				<p style="margin:0 0 12px 0;"><?=$e($mailGreeting)?></p>
				<p style="margin:0 0 24px 0;"><?=$e($mailText)?></p>
				<table role="presentation" cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td style="border-radius:10px;background:#ff8a00;">
							<a href="<?=$e($mailUrl)?>" style="display:inline-block;padding:14px 26px;border-radius:10px;<?=$font?>font-size:15px;font-weight:bold;color:#0b2545;text-decoration:none;"><?=$e($mailButton)?></a>
						</td>
					</tr>
				</table>
				<p style="margin:24px 0 0 0;font-size:13px;color:#5b6576;">
					Se o botão não funcionar, copie e cole este endereço no navegador:<br>
					<a href="<?=$e($mailUrl)?>" style="color:#0b3b6f;word-break:break-all;"><?=$e($mailUrl)?></a>
				</p>
				<?php if (!empty($mailLogin)) { ?>
				<p style="margin:20px 0 0 0;padding:12px 16px;border-radius:10px;background:#f6f7f9;">
					Seu login: <strong style="color:#0f1b2d;"><?=$e($mailLogin)?></strong>
				</p>
				<?php } ?>
			</td>
		</tr>
		<tr>
			<td style="padding:20px 8px 0 8px;<?=$font?>font-size:13px;line-height:1.6;color:#5b6576;">
				<?php foreach ($mailNotes ?? array() as $note) { ?>
				<p style="margin:0 0 12px 0;"><?=$note?></p>
				<?php } ?>
				<p style="margin:0;">Atenciosamente,<br>Equipe KADE Caminhões · <a href="<?=$e($mailBaseUrl)?>" style="color:#0b3b6f;"><?=$e(preg_replace('#^https?://#', '', $mailBaseUrl))?></a></p>
			</td>
		</tr>
	</table>
</td>
</tr>
</table>
</body>
</html>
