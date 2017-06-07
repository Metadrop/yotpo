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

  $variable['commerce_yotpo_api_key'] = array(
    'title' => t('Yotpo API Key', array(), $options),
    'description' => t("Your Yotpo API Key. Visit <a href=\"@yotpo-support\">Yotpo Support</a> to learn more.", array('@yotpo-support' => 'https://support.yotpo.com/en/article/finding-your-app-key-and-your-secret-key'), $options),
    'type' => 'string',
    'default' => t('', array(), $options),
    'group' => 'yotpo',
  );

  $variable['commerce_yotpo_api_secret'] = array(
    'title' => t('Yotpo API Secret', array(), $options),
    'description' => t("Your Yotpo API Secret. Visit <a href=\"@yotpo-support\">Yotpo Support</a> to learn more.", array('@yotpo-support' => 'https://support.yotpo.com/en/article/finding-your-app-key-and-your-secret-key'), $options),
    'type' => 'string',
    'default' => t('', array(), $options),
    'group' => 'yotpo',
  );

  return $variable;
}
