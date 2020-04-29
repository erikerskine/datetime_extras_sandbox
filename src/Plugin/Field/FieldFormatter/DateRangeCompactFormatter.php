<?php

namespace Drupal\datetime_extras\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\datetime_extras\DateRangeCompactFormatterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'Compact' formatter for 'daterange' fields.
 *
 * This formatter renders the data range using <time> elements, with
 * configurable date formats (from the list of configured formats) and a
 * separator.
 *
 * @FieldFormatter(
 *   id = "daterange_compact",
 *   label = @Translation("Compact"),
 *   field_types = {
 *     "daterange"
 *   }
 * )
 */
class DateRangeCompactFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The compact date range formatter service.
   *
   * @var \Drupal\datetime_extras\DateRangeCompactFormatterInterface
   */
  protected $formatter;

  /**
   * The config entity storage for compact date range formats.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $formatStorage;

  /**
   * Constructs a new DateRangeCompactFormatter.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Third party settings.
   * @param \Drupal\datetime_extras\DateRangeCompactFormatterInterface $formatter
   *   The compact date range formatter service.
   * @param \Drupal\Core\Entity\EntityStorageInterface $format_storage
   *   The compact date range format entity storage.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, DateRangeCompactFormatterInterface $formatter, EntityStorageInterface $format_storage) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->formatter = $formatter;
    $this->formatStorage = $format_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('datetime_extras.daterange_compact.formatter'),
      $container->get('entity_type.manager')->getStorage('daterange_compact_format')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'format_type' => 'medium',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $format_types = $this->formatStorage->loadMultiple();
    $options = [];
    foreach ($format_types as $type => $type_info) {
      $options[$type] = $type_info->label();
    }

    $form['format_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Format'),
      '#description' => $this->t('Choose a compact format for displaying the date range.'),
      '#options' => $options,
      '#default_value' => $this->getSetting('format_type'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $summary[] = $this->t('Format: @format', ['@format' => $this->getSetting('format_type')]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      if (!empty($item->start_date) && !empty($item->end_date)) {
        $start_timestamp = $item->start_date->getTimestamp();
        $end_timestamp = $item->end_date->getTimestamp();
        $format = $this->getSetting('format_type');

        if ($this->getFieldSetting('datetime_type') == DateTimeItem::DATETIME_TYPE_DATE) {
          $timezone = DateTimeItemInterface::STORAGE_TIMEZONE;
          $text = $this->formatter->formatDateRange($start_timestamp, $end_timestamp, $format, $timezone);
        }
        else {
          $timezone = date_default_timezone_get();
          $text = $this->formatter->formatDateTimeRange($start_timestamp, $end_timestamp, $format, $timezone);
        }

        $elements[$delta] = [
          '#plain_text' => $text,
          '#cache' => [
            'contexts' => [
              'timezone',
            ],
          ],
        ];
      }
    }

    return $elements;
  }

}
