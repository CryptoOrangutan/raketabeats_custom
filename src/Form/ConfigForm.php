<?php

/**
 * @file
 * Raketabeats settings.
 */

namespace Drupal\raketabeats_custom\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class ConfigForm extends ConfigFormBase {
  
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'raketabeats_custom';
  }
  
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'raketabeats_custom.settings',
    ];
  }
  
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->configFactory->get('raketabeats_custom.settings');
    $form['field_product_roles'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Role field'),
      '#default_value' => $config->get('field_product_roles'),
      '#description' => $this->t('Hidden role field for purchasing content access.'),
    ];
    
    return parent::buildForm($form, $form_state);
  }
  
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::configFactory()
      ->getEditable('raketabeats_custom.settings')
      ->set('field_product_roles', $form_state->getValue('field_product_roles'))
      ->save();
    parent::submitForm($form, $form_state);
  }
}