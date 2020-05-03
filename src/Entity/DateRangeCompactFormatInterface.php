<?php

namespace Drupal\datetime_extras\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining a compact date range format.
 *
 * Configuration entities of this type contain the following properties:
 *     - "default_pattern" - the default format pattern, used for ranges
 *          where the start/end values are the same, ranges that span
 *          multiple years, or where no more specific pattern is available.
 *     - "default_separator" - the separator string to place in between the
 *          start and end values.
 *     - "same_day_start_pattern" - the pattern with which to format
 *          the start date & time, for ranges that are contained within
 *          a single day. Not used for date-only range types.
 *     - "same_day_end_pattern" - as above but for the end date & time.
 *     - "same_day_separator" - alternative separator for use with the
 *          above patterns.
 *     - "same_month_start_pattern" - the pattern with which to format
 *          the start date, for ranges that span multiple days within
 *          the same calendar month.
 *     - "same_month_end_pattern" - as above, but for the end date.
 *     - "same_month_separator" - alternative separator for use with the
 *          above patterns.
 *     - "same_year_start_pattern" - the pattern with which to format
 *         the start date, for ranges that span multiple months within
 *         the same calendar year.
 *     - "same_year_end_pattern" - as above but for the end date.
 *     - "same_year_separator" - alternative separator for use with the
 *          above patterns.
 */
interface DateRangeCompactFormatInterface extends ConfigEntityInterface {}
