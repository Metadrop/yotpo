<?php

/**
 * @file
 * Variable integration for the yotpo module.
 */

function yotpo_variable_group_info() {
  $groups['yotpo'] = array(
    'title' => t('Yotpo settings'),
    'description' => t('Yotpo settings (API credentials)'),
    'access' => 'administer site configuration',
    'path' => array('admin/config/services/yotpo'),
  );
  return $groups;
}

/**
 * Implements hook_variable_info().
 */
function yotpo_variable_info($options) {

  $variable['commerce_yotpo_api_id'] = array(
    'title' => t('Yotpo API ID', array(), $options),
    'description' => t('Your Yotpo API ID.', array(), $options),
    'type' => 'string',
    'default' => t('', array(), $options),
    'group' => 'yotpo',
  );

  $variable['commerce_yotpo_api_secret'] = array(
    'title' => t('Yotpo API Secret', array(), $options),
    'description' => t('Your Yotpo API Secret.', array(), $options),
    'type' => 'string',
    'default' => t('', array(), $options),
    'group' => 'yotpo',
  );

  return $variable;
}
