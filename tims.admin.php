<?php


function tims_template_form($form, &$form_state, Entity $entity, $op, $entity_type) {

  $form['hook'] = array(
    '#type' => 'textfield',
    '#title' => 'Theme Hook',
    '#default_value' => $entity->hook,
    '#description' => t('The theme hook for this template. See <a href="@link">Working with template suggestions</a>.', array('@link' => url('https://drupal.org/node/223440'))),
    '#required' => TRUE,
  );

  $form['template'] = array(
    '#type' => 'textarea',
    '#title' => 'Template',
    '#default_value' => $entity->template,
    '#rows' => 20,
    '#description' => t('A template using Twig syntax. Refer to the <a href="@link">Theming Drupal 8</a> guide and <a href="@help">this module\'s help page</a>.', array('@link' => url('https://drupal.org/node/1906384'), '@help' => url('admin/help/tims'))),
  );

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => 'Save',
  );


  // Checks for http://codemirror.net/ installed at sites/all/libraries for syntax highlighting.
  if (file_exists(DRUPAL_ROOT . '/sites/all/libraries/codemirror/lib/codemirror.js')) {
    // Base library
    $form['template']['#attached']['js'][] = 'sites/all/libraries/codemirror/lib/codemirror.js';
    $form['template']['#attached']['css'][] = 'sites/all/libraries/codemirror/lib/codemirror.css';

    // Modes
    $form['template']['#attached']['js'][] = 'sites/all/libraries/codemirror/mode/xml/xml.js';
    $form['template']['#attached']['js'][] = 'sites/all/libraries/codemirror/mode/css/css.js';
    $form['template']['#attached']['js'][] = 'sites/all/libraries/codemirror/mode/javascript/javascript.js';
    $form['template']['#attached']['js'][] = 'sites/all/libraries/codemirror/mode/htmlmixed/htmlmixed.js';
    if (file_exists(DRUPAL_ROOT . '/sites/all/libraries/codemirror/addon/mode/overlay.js')) {
      $form['template']['#attached']['js'][] = 'sites/all/libraries/codemirror/addon/mode/overlay.js';
      $form['template']['#attached']['js'][] = drupal_get_path('module', 'tims') . '/codemirror/mode/twig.js';
    }
    else {
      drupal_set_message(t('Version of CodeMirror is less than 3.01. Update sites/all/libraries/codemirror to enable syntax highlighting.'), 'warning');
    }

    // This module's implementation
    $form['template']['#attached']['js'][] = drupal_get_path('module', 'tims') . '/codemirror/tims.js';
    $form['template']['#attached']['css'][] = drupal_get_path('module', 'tims') . '/codemirror/tims.css';
  }


  return $form;
}

function tims_template_form_validate($form, &$form_state) {
  $new = entity_ui_form_submit_build_entity($form, $form_state);
  $existingEntities = entity_load('tims_template', FALSE, array('hook' => $new->hook));
  foreach ($existingEntities as $existingEntity)
  if ($new !== $existingEntity) {
    form_set_error('hook', 'This theme hook is already in use.');
  }
}

function tims_template_form_submit($form, &$form_state) {
  $e = entity_ui_form_submit_build_entity($form, $form_state);
  $e->save();
  $form_state['redirect'] = 'admin/structure/tims/list';

  $opVerbs = array(
    'add'    => 'created',
    'edit'   => 'saved',
    'delete' => 'deleted',
  );

  drupal_set_message('Template for theme hook "' . $e->hook . '" ' . $opVerbs[$form_state['op']] . '.');
}
