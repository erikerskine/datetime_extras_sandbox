<?php

namespace Drupal\datetime_extras;

/**
 * Provides an interface defining a compact date range formatter.
 *
 * @package Drupal\datetime_extras
 */
interface DateRangeCompactFormatterInterface {

  /**
   * Formats a date and time range, using a datetime range format type.
   *
   * @param int $start_timestamp
   *   A UNIX timestamp representing the start time.
   * @param int $end_timestamp
   *   A UNIX timestamp representing the end time.
   * @param string $type
   *   (optional) The format to use, one of:
   *   - One of the built-in formats: 'short', 'medium', 'long'.
   *   - The machine name of an administrator-defined datetime range format.
   *   Defaults to 'medium'.
   * @param string|null $timezone
   *   (optional) Time zone identifier, as described at
   *   http://php.net/manual/timezones.php Defaults to the time zone used to
   *   display the page.
   * @param string|null $langcode
   *   (optional) Language code to translate to. NULL (default) means to use
   *   the user interface language for the page.
   *
   * @return string
   *   A translated date & time range string in the requested format.
   *   Since the format may contain user input, this value should be escaped
   *   when output.
   */
  public function formatDateTimeRange($start_timestamp, $end_timestamp, $type = 'medium', $timezone = NULL, $langcode = NULL);

}