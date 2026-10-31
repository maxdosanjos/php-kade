<?php
/**
 * Funções de apoio das views novas. Incluir com require_once.
 */
if (!function_exists("k_fmt_doc")) {
	/**
	 * Formata CPF (000.000.000-00) ou CNPJ (00.000.000/0000-00); outros valores voltam como estão.
	 * Usado em campos travados, que não passam pela máscara do JS.
	 */
	function k_fmt_doc($doc) {
		$d = preg_replace('/\D/', '', (string) $doc);
		if (strlen($d) == 11)
			return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $d);
		if (strlen($d) == 14)
			return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $d);
		return (string) $doc;
	}
}
