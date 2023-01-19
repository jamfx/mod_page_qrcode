<?php
defined('_JEXEC') or die('Restricted access');

$ausrichten = $params->get('ausrichten');
$groesse = $params->get('groesse');
$link = $params->get('link');
$farbe = $params->get('farbe');
$bgfarbe = $params->get('bgfarbe');
$margin = $params->get('margin');
$currenturl = JURI::current();

?>
<div style="text-align:<?php echo $ausrichten; ?>">
<img src="https://api.qrserver.com/v1/create-qr-code/?data=<?php echo $currenturl; ?>&amp;size=<?php echo $groesse; ?>&amp;color=<?php echo $farbe; ?>&amp;bgcolor=<?php echo $bgfarbe; ?>&amp;margin=<?php echo $margin; ?>" alt="QR-Code dieser Seite" title="QR-Code dieser Seite" />
<?php if ($link == "1") {
echo '<br /><a href="https://www.nik-o-mat.de" target="_blank" title="www.nik-o-mat.de" alt="www.nik-o-mat.de" style="font-size: 9px;">www.nik-o-mat.de</a>';
}; ?>
</div>
