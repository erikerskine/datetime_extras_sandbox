<?php

namespace Drupal\datetime_extras\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining a compact date range format.
 */
interface DateRangeCompactFormatInterface extends ConfigEntityInterface {

  /**
   * The settings for use when displaying date only ranges.
   *
   * @return array
   *   An array whose keys are described below:
   *     - "default_pattern" - the default format pattern, used for ranges
   *          where the start/end dates are the same, ranges that span
   *          multiple years, or where no more specific pattern is available.
   *     - "separator" - the separator string to place in between the
   *          start and end values.
   *     - "same_month_start_pattern" - the pattern with which to format
   *          the start date, for ranges that span multiple days within
   *          the same calendar month.
   *     - "same_month_end_pattern" - as above, but for the end date.
   *     - "same_year_start_pattern" - the pattern with which to format
   *         the start date, for ranges that span multiple months within
   *         the same calendar year.
   *     - "same_year_end_pattern" - as above but for the end date.
   */
  public function getDateSettings();

  /**
   * The settings for use when displaying date & time ranges.
   *
   * @return array
   *   An array with the following keys:
   *     - "default_pattern" - the default format pattern, used for ranges
   *          where the start and end values are the same, ranges that span
   *          multiple days, or where no more specific pattern is available.
   *     - "separator" - the separator string to place in between the
   *          start and end values.
   *     - "same_day_start_pattern" - the pattern with which to format
   *          the start date & time, for ranges that are contained within
   *          a single day.
   *     - "same_day_end_pattern" - as above but for the end date & time.
   */
  public function getDateTimeSettings();

}
