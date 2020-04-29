<?php

namespace Drupal\datetime_extras\Form;

use Drupal\Core\Entity\EntityDeleteForm;

/**
 * Provides a form for deleting a compact date range format.
 *
 * @package Drupal\datetime_extras\Form
 */
class DateRangeCompactFormatDeleteForm extends EntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the %name format?', ['%name' => $this->entity->label()]);
  }

}
