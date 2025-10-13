<?php
use Glpi\Plugin\Hooks;

function plugin_init_html2pdf() {
   global $PLUGIN_HOOKS;

   // Űrlap hookok
   $PLUGIN_HOOKS[Hooks::PRE_ITEM_FORM]['html2pdf']  = 'plugin_html2pdf_pre_item_form';
   $PLUGIN_HOOKS[Hooks::POST_ITEM_FORM]['html2pdf'] = 'plugin_html2pdf_post_item_form';

   // Massive actions a Felhasználók listához
   $PLUGIN_HOOKS['use_massive_action']['html2pdf'] = 1;
   $PLUGIN_HOOKS[Hooks::MASSIVE_ACTIONS]['html2pdf']         = 'plugin_html2pdf_MassiveActions';
   $PLUGIN_HOOKS[Hooks::MASSIVE_ACTIONS_DISPLAY]['html2pdf'] = 'plugin_html2pdf_MassiveActionsDisplay';
   $PLUGIN_HOOKS[Hooks::MASSIVE_ACTIONS_PROCESS]['html2pdf'] = 'plugin_html2pdf_MassiveActionsProcess';
}

function plugin_version_html2pdf() {
   return [
      'name'         => 'HTML → PDF bridge',
      'version'      => '1.2.0',
      'author'       => 'kerecsenlaci',
      'requirements' => ['glpi' => ['min' => '11.0', 'max' => '11.99']],
   ];
}

function plugin_html2pdf_check_prerequisites() { return true; }
function plugin_html2pdf_check_config()        { return true; }
