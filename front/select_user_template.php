<?php
// plugins/html2pdf/front/select_user_template.php
include ('../../../inc/includes.php');

Session::checkRight('user', READ);

$users_id = (int)($_GET['id'] ?? 0);
if ($users_id <= 0) {
   Html::redirect($CFG_GLPI['root_doc']);
}

// itt állítsd be a felhasználói sablonjaidat
$templates = [
   'user_munkalap.html.twig' => 'Felhasználói összefoglaló',
   // 'user_munkalap_reszletes.html.twig' => 'Részletes (példa)',
];

$title = __('Munkalap nyomtatása', 'html2pdf');

// GLPI fejléc (kicsi, menüvel), ha „üres” nézet kell, használhatod a Html::nullHeader-t is
Html::header($title, $_SERVER['PHP_SELF'], 'tools', 'plugins', 'html2pdf');

echo "<div class='card' style='max-width:720px; margin:20px auto;'>
        <div class='card-header'><h3 class='card-title' style='margin:0;'>{$title}</h3></div>
        <div class='card-body'>";

// az űrlap közvetlenül a generátor végpontodra küld (GET), és ÚJ lapon nyit
$action = Plugin::getWebDir('html2pdf')."/front/generate_user_report.php";
echo "<form method='get' action='{$action}' target='_blank' id='html2pdf-user-form'>";

// kötelező: a user id
echo Html::hidden('id', ['value' => $users_id]);

// sablon választó
echo "<div class='mb-3'>
        <label class='form-label' for='tpl'>".__('Sablon', 'html2pdf')."</label>
        <select class='form-select' name='tpl' id='tpl' required>";
foreach ($templates as $file => $label) {
   echo "<option value='".Html::cleanInputText($file)."'>".Html::entities_deep($label)."</option>";
}
echo "  </select>
      </div>";

// 4 jelölőnégyzet (alap beállításokkal)
$checks = [
   'header'  => ['Fejléc', true],
   'footer'  => ['Lábléc', true],
   'title'   => ['Címsor', true],
   'private' => ['Privát megjegyzések', false],
];

echo "<div class='row'>";
foreach ($checks as $name => [$label, $def]) {
   $checked = $def ? "checked='checked'" : '';
   echo "<div class='col-6'>
           <label class='form-check'>
             <input class='form-check-input' type='checkbox' name='{$name}' value='1' {$checked}>
             <span class='form-check-label'>{$label}</span>
           </label>
         </div>";
}
echo "</div>";

// gombok
echo "  <div class='mt-3' style='display:flex; gap:8px;'>
           <button type='submit' class='btn btn-primary'>
             <i class='ti ti-printer' style='margin-right:6px;'></i>".__('Nyomtatás', 'html2pdf')."
           </button>
           <a href='".Html::cleanInputText(Toolbox::getItemTypeFormURL('User').'?id='.$users_id)."' class='btn btn-secondary'>".__('Vissza', 'html2pdf')."</a>
         </div>";

echo "</form>";
echo "  </div>
      </div>";

Html::footer();
