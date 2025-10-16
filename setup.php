<?php
use Glpi\Plugin\Hooks;

function plugin_init_html2pdf() {
   global $PLUGIN_HOOKS;

   // Űrlap hookok
   $PLUGIN_HOOKS['pre_item_form']['html2pdf']  = 'plugin_html2pdf_pre_item_form';
   $PLUGIN_HOOKS['post_item_form']['html2pdf'] = 'plugin_html2pdf_post_item_form';

   // Massive actions a Felhasználók listához
   $PLUGIN_HOOKS['use_massive_action']['html2pdf']         = 1;
   $PLUGIN_HOOKS['massiveactions']['html2pdf']             = 'plugin_html2pdf_MassiveActions';
   $PLUGIN_HOOKS['massiveactionsdisplay']['html2pdf']      = 'plugin_html2pdf_MassiveActionsDisplay';
   $PLUGIN_HOOKS['massiveactionsprocess']['html2pdf']      = 'plugin_html2pdf_MassiveActionsProcess';
}

function plugin_version_html2pdf() {
   return [
      'name'         => 'HTML → PDF bridge',
      'version'      => '1.2.0',
      'author'       => 'kerecsenlaci',
      'requirements' => ['glpi' => ['min' => '11.0', 'max' => '11.99']],
   ];
}

function plugin_html2pdf_install() : bool {
   $pdf_inc = Plugin::getPhpDir('pdf') . '/inc';
   $source  = GLPI_ROOT . '/plugins/html2pdf/install/api.class.php';
   $target  = $pdf_inc . '/api.class.php';

   if (!is_dir($pdf_inc)) {
      Session::addMessageAfterRedirect(__('A "pdf" bővítmény nincs telepítve. Telepítsd előbb, majd telepítsd újra a html2pdf-et.', 'html2pdf'), true, ERROR);
      return false;
   }
   if (!@copy($source, $target)) {
      Session::addMessageAfterRedirect(__('Nem sikerült bemásolni az api.class.php fájlt a pdf pluginba.', 'html2pdf'), true, ERROR);
      return false;
   }
   $marker = "<?php /* installed-by: html2pdf */ ?>\n";
   $data   = @file_get_contents($target);
   if ($data !== false && strpos($data, 'installed-by: html2pdf') === false) {
      $data = $marker . $data;
      file_put_contents($target, $data);
   }
   return true;
}

function plugin_html2pdf_uninstall() : bool {
  $pdf_inc = Plugin::getPhpDir('pdf') . '/inc';
   $target  = $pdf_inc . '/api.class.php';
   if (is_file($target)) {
      $data = @file_get_contents($target);
      if ($data !== false && strpos($data, 'installed-by: html2pdf') !== false) {
         @unlink($target);
      }
   }
   return true;
}

function plugin_html2pdf_check_prerequisites() { return true; }
function plugin_html2pdf_check_config()        { return true; }
