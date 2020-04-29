<?php

namespace Drupal\datetime_extras;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides a service to handle compact formatting of date ranges.
 */
class DateRangeCompactFormatter implements DateRangeCompactFormatterInterface {

  /**
   * The config entity storage for compact date range formats.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $formatStorage;

  /**
   * The core date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $coreDateFormatter;

  /**
   * Constructs the compact date range formatter service.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $core_date_formatter
   *   The core date formatter.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, DateFormatterInterface $date_formatter) {
    $this->formatStorage = $entity_type_manager->getStorage('daterange_compact_format');
    $this->coreDateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public function formatDateTimeRange($start_timestamp, $end_timestamp, $type = 'medium', $timezone = NULL, $langcode = NULL) {
    $start_date_time = DrupalDateTime::createFromTimestamp($start_timestamp, $timezone);
    $end_date_time = DrupalDateTime::createFromTimestamp($end_timestamp, $timezone);

    /** @var \Drupal\datetime_extras\Entity\DateRangeCompactFormatInterface $format */
    $format = $this->formatStorage->load($type);
    $default_pattern = $format->get('default_pattern');
    $separator = $format->get('separator') ?: '';

    // Strings containing the ISO-8601 representations of the start and end
    // datetime can be used to determine if the date and/or time are the same.
    $start_iso_8601 = $start_date_time->format('Y-m-d\TH:i:s');
    $end_iso_8601 = $end_date_time->format('Y-m-d\TH:i:s');

    if ($start_iso_8601 === $end_iso_8601) {
      // The start and end values are the same.
      return $this->coreDateFormatter->format($start_timestamp, 'custom',
        $default_pattern, $timezone, $langcode);
    }
    elseif (substr($start_iso_8601, 0, 10) == substr($end_iso_8601, 0, 10)) {
      // The range is contained within a single day.
      $start_pattern = $format->get('same_day_start_pattern') ?: '';
      $end_pattern = $format->get('same_day_end_pattern') ?: '';
      if ($start_pattern && $end_pattern) {
        $start_text = $this->coreDateFormatter->format($start_timestamp, 'custom', $start_pattern, $timezone, $langcode);
        $end_text = $this->coreDateFormatter->format($end_timestamp, 'custom', $end_pattern, $timezone, $langcode);
        return $start_text . $separator . $end_text;
      }
    }
    elseif (substr($start_iso_8601, 0, 7) === substr($end_iso_8601, 0, 7)) {
      // The range spans several days within the same month.
      $start_pattern = $format->get('same_month_start_pattern') ?: '';
      $end_pattern = $format->get('same_month_end_pattern') ?: '';
      if ($start_pattern && $end_pattern) {
        $start_text = $this->coreDateFormatter->format($start_timestamp, 'custom', $start_pattern, $timezone, $langcode);
        $end_text = $this->coreDateFormatter->format($end_timestamp, 'custom', $end_pattern, $timezone, $langcode);
        return $start_text . $separator . $end_text;
      }
    }
    elseif (substr($start_iso_8601, 0, 4) === substr($end_iso_8601, 0, 4)) {
      // The range spans several months within the same year.
      $start_pattern = $format->get('same_year_start_pattern') ?: '';
      $end_pattern = $format->get('same_year_end_pattern') ?: '';
      if ($start_pattern && $end_pattern) {
        $start_text = $this->coreDateFormatter->format($start_timestamp, 'custom', $start_pattern, $timezone, $langcode);
        $end_text = $this->coreDateFormatter->format($end_timestamp, 'custom', $end_pattern, $timezone, $langcode);
        return $start_text . $separator . $end_text;
      }
    }

    // Fallback: show the start and end dates in full using the default
    // pattern. This is the case if the range spans different years,
    // or if the other patterns are not specified.
    $start_text = $this->coreDateFormatter->format($start_timestamp, 'custom', $default_pattern, $timezone, $langcode);
    $end_text = $this->coreDateFormatter->format($end_timestamp, 'custom', $default_pattern, $timezone, $langcode);
    if ($start_text === $end_text) {
      return $start_text;
    } else {
      return $start_text . $separator . $end_text;
    }
  }

}
