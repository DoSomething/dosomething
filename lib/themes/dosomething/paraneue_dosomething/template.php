<?php

include 'template_hooks/preprocess_page/html5shiv.php';
include 'template_hooks/form_alter/login.php';
include 'template_hooks/form_alter/register.php';

/**
 * Implements hook_preprocess_page().
 */
function paraneue_dosomething_preprocess_page(&$vars, $hook) {
  paraneue_dosomething_preprocess_page_html5shiv($vars, $hook);
}

/**
 * Implements hook_form_alter();
 */
function paraneue_dosomething_form_alter(&$form, &$form_state, $form_id) {
  paraneue_dosomething_form_alter_login($form, $form_state, $form_id);
  paraneue_dosomething_form_alter_register($form, $form_state, $form_id);
}
