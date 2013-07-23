<?php

function tims_edit($form, &$form_state, $hook) {
  $templates = variable_get('tims_templates', array());
  $template = '';
  if (array_key_exists($hook, $templates)) {
    $template = $templates[$hook];
  }

  $form['hook'] = array(
    '#type' => 'textfield',
    '#title' => 'Theme Hook',
    '#default_value' => ($hook != '_new_' ? $hook : NULL),
    '#description' => t('The theme hook for this template. See <a href="@link">Working with template suggestions</a>.', array('@link' => url('https://drupal.org/node/223440'))),
    '#required' => TRUE,
  );

  $form['template'] = array(
    '#type' => 'textarea',
    '#title' => 'Template',
    '#default_value' => $template,
    '#rows' => 20,
    '#description' => t('A template using Twig syntax. Refer to the <a href="@link">Theming Drupal 8</a> guide and <a href="@help">this module\'s help page</a>.', array('@link' => url('https://drupal.org/node/1906384'), '@help' => url('admin/help/tims'))),
  );

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => 'Save',
  );

  return $form;
}

function tims_edit_validate($form, &$form_state) {
  $templates = variable_get('tims_templates', array());
  $originalHook = $form_state['complete form']['hook']['#default_value'];
  $newHook = strtr($form_state['values']['hook'], array('-' => '_'));
  if ($originalHook != $newHook && isset($templates[$newHook])) {
    form_set_error('hook', 'This theme hook is already in use.');
  }
}

function tims_edit_submit($form, &$form_state) {
  $templates = variable_get('tims_templates', array());
  $originalHook = $form_state['complete form']['hook']['#default_value'];
  $newHook = strtr($form_state['values']['hook'], array('-' => '_'));
  unset($templates[$originalHook]);
  $templates[$newHook] = $form_state['values']['template'];
  variable_set('tims_templates', $templates);
  $form_state['redirect'] = 'admin/structure/tims';

  if ($originalHook) {
    drupal_set_message('Template for theme hook "' . $newHook . '" saved.');
  }
  else {
    drupal_set_message('Template for theme hook "' . $newHook . '" created.');
  }
}

function tims_delete($form, &$form_state, $hook) {
  $form['hook'] = array(
    '#type' => 'hidden',
    '#value' => $hook,
  );

  return confirm_form(
    $form,
    t('Are you sure you want to delete the template for "%tn"?', array('%tn' => $hook)),
    'admin/structure/tims',
    t('This action cannot be undone.'),
    t('Delete Template')
  );
}

function tims_delete_submit($form, &$form_state) {
  $hook = $form_state['values']['hook'];
  $templates = variable_get('tims_templates', array());
  unset($templates[$hook]);
  variable_set('tims_templates', $templates);
  $form_state['redirect'] = 'admin/structure/tims';

  drupal_set_message('Template for theme hook "' . $hook . '" deleted.');
}

function tims_list($form, &$form_state) {
  $header = array(
    'hook' => array(
      'data' => t('Hook'),
      'field' => 'hook',
    ),
    'operations' => t('Operations'),
  );

  $templates = variable_get('tims_templates', array());

  $rows = array();
  foreach ($templates as $hook => $template) {
    $rows[$hook] = array(
      'hook' => $hook,
      'operations' => array(
        'data' => array(
          '#theme' => 'links__node_operations',
          '#links' => array(
            'edit' => array(
              'title' => t('edit'),
              'href' => 'admin/structure/tims/' . $hook . '/edit',
            ),
            'delete' => array(
              'title' => t('delete'),
              'href' => 'admin/structure/tims/' . $hook . '/delete',
            ),
          ),
          '#attributes' => array('class' => array('links', 'inline')),
        ),
      ),
    );
  }

  $form['template_list'] = array(
    '#caption' => t('Existing Templates: ') . count($templates),
    '#theme' => 'table',
    '#header' => $header,
    '#rows' => $rows,
    '#empty' => t('No templates have been created.'),
  );

  return $form;
}
