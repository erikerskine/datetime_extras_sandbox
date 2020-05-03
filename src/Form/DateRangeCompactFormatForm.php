<?php

namespace Drupal\datetime_extras\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for editing compact date range formats.
 *
 * @package Drupal\datetime_extras\Form
 */
class DateRangeCompactFormatForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\datetime_extras\Entity\DateRangeCompactFormatInterface $format */
    $format = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $format->label(),
      '#description' => $this->t("Name of the format."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $format->id(),
      '#machine_name' => [
        'exists' => '\Drupal\datetime_extras\Entity\DateRangeCompactFormat::load',
      ],
      '#disabled' => !$format->isNew(),
    ];

    $form['formats'] = [
      '#type' => 'vertical_tabs',
      '#title' => $this->t('Formats'),
      '#tree' => FALSE,
    ];

    $form['formats']['basic'] = [
      '#type' => 'details',
      '#title' => $this->t('Basic'),
      '#open' => TRUE,
      '#weight' => 1,
      '#group' => 'formats',
      '#description' => $this->t('Basic format used for ranges that cannot be shown in a compact form.'),
    ];

    $form['formats']['basic']['default_pattern'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Pattern'),
      '#default_value' => $format->get('default_pattern') ?: '',
      '#maxlength' => 100,
      '#description' => $this->t('A user-defined date format. See the <a href="http://php.net/manual/function.date.php">PHP manual</a> for available options.'),
      '#required' => TRUE,
    ];

    $form['formats']['basic']['default_separator'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Separator'),
      '#default_value' => $format->get('default_separator') ?: '',
      '#maxlength' => 100,
      '#size' => 10,
      '#description' => $this->t('Text between start and end dates.'),
      '#required' => FALSE,
    ];

    $form['formats']['same_day'] = [
      '#type' => 'details',
      '#title' => $this->t('Same day'),
      '#open' => TRUE,
      '#weight' => 2,
      '#group' => 'formats',
      '#description' => $this->t('Optional formatting of time ranges within a single day. Do not use this for date-only formats.'),
    ];

    $form['formats']['same_day']['same_day_start_pattern'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Start date/time pattern'),
      '#default_value' => $format->get('same_day_start_pattern') ?: '',
      '#maxlength' => 100,
      '#description' => $this->t('A user-defined date format. See the <a href="http://php.net/manual/function.date.php">PHP manual</a> for available options.'),
    ];

    $form['formats']['same_day']['same_day_separator'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Separator'),
      '#default_value' => $format->get('same_day_separator') ?: '',
      '#maxlength' => 100,
      '#size' => 10,
      '#description' => $this->t('Text between start and end dates. If left blank, the basic separator is used.'),
      '#required' => FALSE,
    ];

    $form['formats']['same_day']['same_day_end_pattern'] = [
      '#type' => 'textfield',
      '#title' => $this->t('End date/time pattern'),
      '#default_value' => $format->get('same_day_end_pattern') ?: '',
      '#maxlength' => 100,
      '#description' => $this->t('A user-defined date format. See the <a href="http://php.net/manual/function.date.php">PHP manual</a> for available options.'),
    ];

    $form['formats']['same_month'] = [
      '#type' => 'details',
      '#title' => $this->t('Same month'),
      '#open' => TRUE,
      '#weight' => 3,
      '#group' => 'formats',
      '#description' => $this->t('Optional formatting of date ranges that span multiple days within the same month.'),
    ];

    $form['formats']['same_month']['same_month_start_pattern'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Start date pattern'),
      '#default_value' => $format->get('same_month_start_pattern') ?: '',
      '#maxlength' => 100,
      '#description' => $this->t('A user-defined date format. See the <a href="http://php.net/manual/function.date.php">PHP manual</a> for available options.'),
    ];

    $form['formats']['same_month']['same_month_separator'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Separator'),
      '#default_value' => $format->get('same_month_separator') ?: '',
      '#maxlength' => 100,
      '#size' => 10,
      '#description' => $this->t('Text between start and end dates. If left blank, the basic separator is used.'),
      '#required' => FALSE,
    ];

    $form['formats']['same_month']['same_month_end_pattern'] = [
      '#type' => 'textfield',
      '#title' => $this->t('End date pattern'),
      '#default_value' => $format->get('same_month_end_pattern') ?: '',
      '#maxlength' => 100,
      '#description' => $this->t('A user-defined date format. See the <a href="http://php.net/manual/function.date.php">PHP manual</a> for available options.'),
    ];

    $form['formats']['same_year'] = [
      '#type' => 'details',
      '#title' => $this->t('Same year'),
      '#open' => TRUE,
      '#weight' => 4,
      '#group' => 'formats',
      '#description' => $this->t('Optional formatting of date ranges that span multiple months within the same year.'),
    ];

    $form['formats']['same_year']['same_year_start_pattern'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Start date pattern'),
      '#default_value' => $format->get('same_year_start_pattern') ?: '',
      '#maxlength' => 100,
      '#description' => $this->t('A user-defined date format. See the <a href="http://php.net/manual/function.date.php">PHP manual</a> for available options.'),
    ];

    $form['formats']['same_year']['same_year_separator'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Separator'),
      '#default_value' => $format->get('same_year_separator') ?: '',
      '#maxlength' => 100,
      '#size' => 10,
      '#description' => $this->t('Text between start and end dates. If left blank, the basic separator is used.'),
      '#required' => FALSE,
    ];

    $form['formats']['same_year']['same_year_end_pattern'] = [
      '#type' => 'textfield',
      '#title' => $this->t('End date pattern'),
      '#default_value' => $format->get('same_year_end_pattern') ?: '',
      '#maxlength' => 100,
      '#description' => $this->t('A user-defined date format. See the <a href="http://php.net/manual/function.date.php">PHP manual</a> for available options.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\datetime_extras\Entity\DateRangeCompactFormatInterface $format */
    $format = $this->entity;
    $status = $format->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label format.', [
          '%label' => $format->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Updated the %label format.', [
          '%label' => $format->label(),
        ]));
    }
    $form_state->setRedirectUrl($format->toUrl('collection'));
  }

}
