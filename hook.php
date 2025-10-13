<?php
if (!defined('GLPI_ROOT')) { die('Direct access not allowed'); }

/** pdf jog ellenőrzés */
function plugin_html2pdf_user_can_use_pdf() : bool {
   if (!Plugin::isPluginActive('pdf')) return false;
   return Session::haveRight('ticket', READ) || Session::haveRight('user', READ);
}

/** Sablon-választós modal + 4 checkbox */
function plugin_html2pdf_print_modal(string $base_url, array $templates, string $button_id) {
   $rand    = mt_rand(100000, 999999);
   $modalId = 'html2pdf_tpl_modal_'.$rand;
   $selId   = 'html2pdf_tpl_select_'.$rand;
   $printId = 'html2pdf_tpl_print_'.$rand;
   $cbH = 'html2pdf_cb_h_'.$rand; $cbF = 'html2pdf_cb_f_'.$rand;
   $cbT = 'html2pdf_cb_t_'.$rand; $cbP = 'html2pdf_cb_p_'.$rand;

   echo "
<div id='{$modalId}' style='display:none; position:fixed; inset:0; z-index:99999; background:rgba(0,0,0,.35);'>
  <div style='position:absolute; left:50%; top:50%; transform:translate(-50%,-50%); width:min(92vw,640px); background:#fff; border-radius:12px; box-shadow:0 10px 40px rgba(0,0,0,.25);'>
    <div style='display:flex; align-items:center; justify-content:space-between; padding:12px 16px; border-bottom:1px solid #e5e7eb; background:#f9fafb;'>
      <div style='font-weight:600'>".__('Munkalap nyomtatása', 'html2pdf')."</div>
      <button type='button' class='vsubmit' onclick=\"document.getElementById('{$modalId}').style.display='none'\">×</button>
    </div>
    <div style='padding:16px;'>
      <label for='{$selId}' style='display:block; font-weight:600; margin-bottom:6px;'>".__('Sablon', 'html2pdf')."</label>
      <select id='{$selId}' style='width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px;'>";
      foreach ($templates as $file => $label) {
         echo "<option value='".Html::cleanInputText($file)."'>".Html::entities_deep($label)."</option>";
      }
   echo " </select>
      <div style='display:grid; grid-template-columns:1fr 1fr; gap:10px 16px; margin-top:14px;'>
        <label style='display:flex; align-items:center; gap:8px;'><input type='checkbox' id='{$cbH}' checked/><span>Fejléc</span></label>
        <label style='display:flex; align-items:center; gap:8px;'><input type='checkbox' id='{$cbF}' checked/><span>Lábléc</span></label>
        <label style='display:flex; align-items:center; gap:8px;'><input type='checkbox' id='{$cbT}' checked/><span>Címsor</span></label>
        <label style='display:flex; align-items:center; gap:8px;'><input type='checkbox' id='{$cbP}' /><span>Privát reakciók</span></label>
      </div>
      <div style='margin-top:16px; display:flex; justify-content:flex-end; gap:8px;'>
        <button id='{$printId}' class='vsubmit'>".__('Nyomtatás', 'html2pdf')."</button>
      </div>
    </div>
  </div>
</div>
<script>
(function(){
  var modalId = <?php echo json_encode($modalId); ?>;
  var baseUrl = <?php echo json_encode($base_url); ?>;
  var selId   = <?php echo json_encode($selId); ?>;
  var printId = <?php echo json_encode($printId); ?>;
  var cbH     = <?php echo json_encode($cbH); ?>;
  var cbF     = <?php echo json_encode($cbF); ?>;
  var cbT     = <?php echo json_encode($cbT); ?>;
  var cbP     = <?php echo json_encode($cbP); ?>;
  var openBtnId = <?php echo json_encode($button_id); ?>;

  var modal = document.getElementById(modalId);

  // ➊ Globális nyitó függvény REGISZTRÁLÁSA (nem kell létező DOM gomb!)
  window.__html2pdfOpeners = window.__html2pdfOpeners || {};
  window.__html2pdfOpeners[openBtnId] = function(){
     if (modal) modal.style.display = 'block';
  };

  // ➋ Ha VAN tényleges DOM gomb ezzel az ID-val, kössük rá
  var open = document.getElementById(openBtnId);
  if (open) {
    open.addEventListener('click', function(e){ e.preventDefault(); window.__html2pdfOpeners[openBtnId](); });
  }

  // ➌ Print gomb: paraméterek összerakása és nyitás új lapon
  var print = document.getElementById(printId);
  if (print) {
    print.addEventListener('click', function(){
       var sel = document.getElementById(selId);
       var h = document.getElementById(cbH), f = document.getElementById(cbF);
       var t = document.getElementById(cbT), p = document.getElementById(cbP);
       var url = baseUrl
         + '&tpl=' + encodeURIComponent(sel && sel.value ? sel.value : '')
         + '&header=' + (h && h.checked ? 1:0)
         + '&footer=' + (f && f.checked ? 1:0)
         + '&title='  + (t && t.checked ? 1:0)
         + '&private='+ (p && p.checked ? 1:0);
       window.open(url, '_blank');
       if (modal) modal.style.display='none';
    });
  }
})();
</script>";
}

/** Űrlapok előtt: Ticket-hez és (JS-injektálással) User-hez is gomb */
function plugin_html2pdf_pre_item_form(array $params) {
   if (!isset($params['item']) || !($params['item'] instanceof CommonDBTM)) return;

   // TICKET
   if ($params['item'] instanceof Ticket) {
      if (!plugin_html2pdf_user_can_use_pdf()) return;
      $id = (int)$params['item']->getID(); if ($id<=0) return;

      $base = Plugin::getWebDir('html2pdf')."/front/generate_report.php?id=".$id;
      $templates = ['ticket_munkalap.html.twig'=>'Alap munkalap'];
      $openId = 'html2pdf_open_ticket_'.mt_rand(100000,999999);

      echo "<tr class='tab_bg_1 center'><td colspan='4' style='padding:10px 0;'>
              <a class='vsubmit' href='#' id='{$openId}'>".__('Munkalap nyomtatása (html2pdf)', 'html2pdf')."</a>
            </td></tr>";
      plugin_html2pdf_print_modal($base, $templates, $openId);
      return;
   }

   // USER – itt CSAK a modált rakjuk le rejtve
   if ($params['item'] instanceof User) {
      if (!plugin_html2pdf_user_can_use_pdf()) return;
      $id = (int)$params['item']->getID(); if ($id<=0) return;

      $base = Plugin::getWebDir('html2pdf')."/front/generate_user_report.php?id=".$id;
      $templates = ['user_munkalap.html.twig'=>'Felhasználói összefoglaló'];
      $openId = 'html2pdf_open_user_'.mt_rand(100000,999999);
      plugin_html2pdf_print_modal($base, $templates, $openId);

      // átadjuk az opener ID-t a post hooknak
      echo "<script>window.__html2pdf_open_user_id=".json_encode($openId).";</script>";
      return;
   }
}

/** Űrlapok után: itt biztosan kész a DOM → beszúrjuk a User gombot a „Kukába” elé */
function plugin_html2pdf_post_item_form(array $params) {
   if (!isset($params['item']) || !($params['item'] instanceof User)) return;
   if (!plugin_html2pdf_user_can_use_pdf()) return;

   echo Html::scriptBlock("
  (function(){
    var OPEN_ID = window.__html2pdf_open_user_id || '';
    function place(){
      var delBtn = document.querySelector('#main-form button[name=\"delete\"]');
      if (!delBtn || !delBtn.parentNode) return false;
      if (document.getElementById('html2pdf_injected_user_btn')) return true;

      var btn=document.createElement('button');
      btn.type='button';
      btn.id='html2pdf_injected_user_btn';
      btn.className='btn btn-primary me-2';
      btn.innerHTML='<i class=\"ti ti-printer\" style=\"margin-right:6px\"></i> " . addslashes(__('Munkalap nyomtatása (html2pdf)', 'html2pdf')) . "';
      btn.addEventListener('click', function(){
         if (window.__html2pdfOpeners && typeof window.__html2pdfOpeners[OPEN_ID] === 'function') {
            window.__html2pdfOpeners[OPEN_ID]();
         }
      });
      delBtn.parentNode.insertBefore(btn, delBtn);
      return true;
    }
    if (place()) return;
    var tries=0, iv=setInterval(function(){ if(place()||++tries>40) clearInterval(iv); },250);
  })();
  ");
}

/** Massive actions – regisztráció */
function plugin_html2pdf_MassiveActions($type) {
   if ($type==='User' && plugin_html2pdf_user_can_use_pdf()) {
      return ['PluginHtml2pdfUserPrint' => __('Munkalap nyomtatása (html2pdf)', 'html2pdf')];
   }
   return [];
}

function plugin_html2pdf_MassiveActionsDisplay($options=[]) {
   if (($options['itemtype']??'')!=='User' || ($options['action']??'')!=='PluginHtml2pdfUserPrint') return false;

   $templates = ['user_munkalap.html.twig'=>'Felhasználói összefoglaló'];
   echo "<div class='center' style='padding:8px 0;'><table class='tab_cadre' style='margin:auto;min-width:520px;'>";
   echo "<tr class='tab_bg_1'><td style='text-align:right;padding:6px 10px;width:30%;font-weight:600'>".__('Sablon','html2pdf')."</td><td style='text-align:left;padding:6px 10px;'><select name='tpl' style='min-width:320px;'>";
   foreach ($templates as $f=>$l) echo "<option value='".Html::cleanInputText($f)."'>".Html::entities_deep($l)."</option>";
   echo "</select></td></tr>";
   $checks = [
      'header'=>['Fejléc',1],'footer'=>['Lábléc',1],'title'=>['Címsor',1],'private'=>['Privát megjegyzések',0]
   ];
   foreach ($checks as $name=>$meta) {
      $chk=$meta[1]?"checked='checked'":'';
      echo "<tr class='tab_bg_1'><td style='text-align:right;padding:6px 10px;font-weight:600'>{$meta[0]}</td>
            <td style='text-align:left;padding:6px 10px;'><input type='checkbox' name='{$name}' value='1' {$chk}></td></tr>";
   }
   echo "</table></div>";
   return true;
}

function plugin_html2pdf_MassiveActionsProcess($data) {
   if (($data['itemtype']??'')!=='User' || ($data['action']??'')!=='PluginHtml2pdfUserPrint') return;

   $ids = $data['ids'] ?? [];
   if (!is_array($ids) || !count($ids)) {
      Session::addMessageAfterRedirect(__('Nincs kiválasztott felhasználó.', 'html2pdf'), true, WARNING);
      return;
   }
   $users_id = (int)reset($ids);
   $tpl     = $data['tpl'] ?? 'user_munkalap.html.twig';
   $header  = !empty($data['header'])?1:0;
   $footer  = !empty($data['footer'])?1:0;
   $title   = !empty($data['title'])?1:0;
   $private = !empty($data['private'])?1:0;

   $url = Plugin::getWebDir('html2pdf')."/front/generate_user_report.php"
        . "?id={$users_id}&tpl=".rawurlencode($tpl)
        . "&header={$header}&footer={$footer}&title={$title}&private={$private}";
   Html::redirect($url);
}
