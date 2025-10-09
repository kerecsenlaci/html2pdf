<?php
include ('../../../inc/includes.php');
Session::checkRight('user', READ);

$users_id = (int)($_GET['id'] ?? 0);
if ($users_id <= 0) Html::redirect($CFG_GLPI['root_doc']);

$tpl = $_REQUEST['tpl'] ?? null;
$opts = [
   'show_header'     => isset($_REQUEST['header']) ? (bool)$_REQUEST['header'] : true,
   'show_footer'     => isset($_REQUEST['footer']) ? (bool)$_REQUEST['footer'] : true,
   'show_title'      => isset($_REQUEST['title'])  ? (bool)$_REQUEST['title']  : true,
   'include_private' => false
];

PluginHtml2pdfReportGenerator::generateUserWorksheet($users_id, $tpl, $opts);
