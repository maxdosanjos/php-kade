<?php
// /sitemap.xml (reescrito para cá no .htaccess). Os endereços usam a URL pública do
// site (APP_URL no .env), então saem com https quando o site usa https; sem APP_URL,
// usa o endereço desta requisição.
require_once(dirname(__FILE__)."/shared/class/util/Properties.class.php");

$base = rtrim((string) Properties::get("app.url"), "/");
if ($base === "") {
	$https = (($_SERVER["HTTPS"] ?? "off") !== "off" && ($_SERVER["HTTPS"] ?? "") !== "")
		|| strtolower($_SERVER["HTTP_X_FORWARDED_PROTO"] ?? "") === "https";
	$host = preg_replace('/[^A-Za-z0-9.:-]/', "", (string) ($_SERVER["HTTP_HOST"] ?? "localhost"));
	$base = ($https ? "https" : "http") . "://" . $host;
}

// caminho => array(changefreq, priority)
$_pages = array(
	"/"               => array("hourly", "1.0"),
	"/quem_somos/"    => array("monthly", "0.8"),
	"/cad_veiculo/"   => array("daily", null),
	"/cad_assinante/" => array("daily", null),
	"/busca_veiculo/" => array("always", null),
	"/terms_use/"     => array("yearly", "0.3"),
);

header("Content-Type: application/xml; charset=UTF-8");
echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($_pages as $path => $page) { ?>
  <url>
    <loc><?=htmlspecialchars($base . $path, ENT_XML1, "UTF-8")?></loc>
    <changefreq><?=$page[0]?></changefreq>
<?php if ($page[1] !== null) { ?>
    <priority><?=$page[1]?></priority>
<?php } ?>
  </url>
<?php } ?>
</urlset>
