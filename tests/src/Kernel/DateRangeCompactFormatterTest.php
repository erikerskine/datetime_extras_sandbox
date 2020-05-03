<?php

namespace Drupal\Tests\datetime_extras\Kernel;

use Drupal\datetime_extras\Entity\DateRangeCompactFormat;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests compact date range formatter functionality.
 *
 * These tests cover the datetime_extras.daterange_compact.formatter service
 * only, not the field formatter (see DateRangeCompactFieldFormatterTest for
 * that).
 */
class DateRangeCompactFormatterTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'datetime',
    'datetime_extras',
    'datetime_range',
    'user',
  ];

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  protected function setUp() {
    parent::setUp();

    $this->installConfig(['system']);
    $this->installConfig(['datetime_extras']);

    // Create a typical date format for USA.
    DateRangeCompactFormat::create([
      'id' => 'usa_date',
      'label' => 'USA (date only)',
      'default_pattern' => 'F jS, Y',
      'default_separator' => ' - ',
      'same_month_start_pattern' => 'F jS',
      'same_month_end_pattern' => 'jS, Y',
      'same_year_start_pattern' => 'F jS',
      'same_year_end_pattern' => 'F jS, Y',
    ])->save();

    // Create a typical datetime format for USA.
    // This format also contains varying separators to address the use case
    // in #2959070.
    DateRangeCompactFormat::create([
      'id' => 'usa_datetime',
      'label' => 'USA (date & time)',
      'default_pattern' => 'g:ia \o\n F jS, Y',
      'default_separator' => ' - ',
      'same_day_start_pattern' => 'g:ia',
      'same_day_end_pattern' => 'g:ia \o\n F jS, Y',
      'same_day_separator' => '-',
    ])->save();

    // Create a ISO-8601 date format without any compact variations.
    DateRangeCompactFormat::create([
      'id' => 'iso_8601_date',
      'label' => 'ISO-8601 (date only)',
      'default_pattern' => 'Y-m-d',
      'default_separator' => ' - ',
    ])->save();

    // Create a ISO-8601 datetime format without any compact variations.
    DateRangeCompactFormat::create([
      'id' => 'iso_8601_datetime',
      'label' => 'ISO-8601 (date & time)',
      'default_pattern' => 'Y-m-d\TH:i:s',
      'default_separator' => ' - ',
    ])->save();

    // Create a "year only" format to addresses the use case in #2890621,
    // where smaller units of time are omitted from the output.
    DateRangeCompactFormat::create([
      'id' => 'year_only',
      'label' => 'Year only',
      'default_pattern' => 'Y',
      'default_separator' => '-',
    ])->save();

    // Create a "month & year only" format to addresses the use case in
    // #2890621, where smaller units of time are omitted from the output.
    DateRangeCompactFormat::create([
      'id' => 'month_and_year_only',
      'label' => 'Month & year only',
      'default_pattern' => 'F Y',
      'default_separator' => '-',
      'same_year_start_pattern' => 'F',
      'same_year_end_pattern' => 'F Y',
    ])->save();
  }

  /**
   * Tests the display of date-only range fields.
   */
  public function testDateRanges() {
    $all_data = [];

    // Same day.
    $all_data[] = [
      'start' => '2017-01-01',
      'end' => '2017-01-01',
      'expected' => [
        'medium_date' => '1 January 2017',
        'usa_date' => 'January 1st, 2017',
        'iso_8601_date' => '2017-01-01',
        'year_only' => '2017',
        'month_and_year_only' => 'January 2017',
      ],
    ];

    // Different days, same month.
    $all_data[] = [
      'start' => '2017-01-02',
      'end' => '2017-01-03',
      'expected' => [
        'medium_date' => '2–3 January 2017',
        'usa_date' => 'January 2nd - 3rd, 2017',
        'iso_8601_date' => '2017-01-02 - 2017-01-03',
        'year_only' => '2017',
        'month_and_year_only' => 'January 2017',
      ],
    ];

    // Different months, same year.
    $all_data[] = [
      'start' => '2017-01-04',
      'end' => '2017-02-05',
      'expected' => [
        'medium_date' => '4 January – 5 February 2017',
        'usa_date' => 'January 4th - February 5th, 2017',
        'iso_8601_date' => '2017-01-04 - 2017-02-05',
        'year_only' => '2017',
        'month_and_year_only' => 'January-February 2017',
      ],
    ];

    // Different years.
    $all_data[] = [
      'start' => '2017-01-06',
      'end' => '2018-02-07',
      'expected' => [
        'medium_date' => '6 January 2017 – 7 February 2018',
        'usa_date' => 'January 6th, 2017 - February 7th, 2018',
        'iso_8601_date' => '2017-01-06 - 2018-02-07',
        'year_only' => '2017-2018',
        'month_and_year_only' => 'January 2017-February 2018',
      ],
    ];

    /** @var \Drupal\datetime_extras\DateRangeCompactFormatterInterface $formatter */
    $formatter = $this->container->get('datetime_extras.daterange_compact.formatter');

    foreach ($all_data as $data) {
      foreach ($data['expected'] as $format => $expected) {
        $start = \DateTime::createFromFormat('Y-m-d', $data['start'])->getTimestamp();
        $end = \DateTime::createFromFormat('Y-m-d', $data['end'])->getTimestamp();

        $actual = $formatter->formatDateTimeRange($start, $end, $format);
        $message = "Using the $format format for " . $data['start'] . ' to ' . $data['end'];
        $this->assertEqual($actual, $expected, $message);
      }
    }
  }

  /**
   * Tests the display of date and time range fields.
   */
  public function testDateTimeRanges() {
    $all_data = [];

    // Note: the default timezone for unit tests is Australia/Sydney
    // see https://www.drupal.org/node/2498619 for why
    // Australia/Sydney is UTC +10:00 (normal) or UTC +11:00 (DST)
    // DST starts first Sunday in October
    // DST ends first Sunday in April.

    // Same day.
    $all_data[] = [
      'start' => '2017-01-01T20:00:00',
      'end' => '2017-01-01T23:00:00',
      'expected' => [
        'medium_datetime' => '1 January 2017 20:00–23:00',
        'usa_datetime' => '8:00pm-11:00pm on January 1st, 2017',
        'iso_8601_datetime' => '2017-01-01T20:00:00 - 2017-01-01T23:00:00',
        'year_only' => '2017',
        'month_and_year_only' => 'January 2017',
      ],
    ];

    // Different day in UTC, same day in Australia.
    $all_data[] = [
      'start' => '2017-01-02T10:00:00',
      'end' => '2017-01-02T12:00:00',
      'expected' => [
        'medium_datetime' => '2 January 2017 10:00–12:00',
        'usa_datetime' => '10:00am-12:00pm on January 2nd, 2017',
        'iso_8601_datetime' => '2017-01-02T10:00:00 - 2017-01-02T12:00:00',
        'year_only' => '2017',
        'month_and_year_only' => 'January 2017',
      ],
    ];

    // Same day in UTC, different day in Australia.
    $all_data[] = [
      'start' => '2017-01-01T23:00:00',
      'end' => '2017-01-02T02:00:00',
      'expected' => [
        'medium_datetime' => '1 January 2017 23:00 – 2 January 2017 02:00',
        'usa_datetime' => '11:00pm on January 1st, 2017 - 2:00am on January 2nd, 2017',
        'iso_8601_datetime' => '2017-01-01T23:00:00 - 2017-01-02T02:00:00',
        'year_only' => '2017',
        'month_and_year_only' => 'January 2017',
      ],
    ];

    // Different days in UTC and Australia, also spans DST change.
    $all_data[] = [
      'start' => '2017-04-01T12:00:00',
      'end' => '2017-04-08T11:00:00',
      'expected' => [
        'medium_datetime' => '1 April 2017 12:00 – 8 April 2017 11:00',
        'usa_datetime' => '12:00pm on April 1st, 2017 - 11:00am on April 8th, 2017',
        'iso_8601_datetime' => '2017-04-01T12:00:00 - 2017-04-08T11:00:00',
        'year_only' => '2017',
        'month_and_year_only' => 'April 2017',
      ],
    ];

    /** @var \Drupal\datetime_extras\DateRangeCompactFormatterInterface $formatter */
    $formatter = $this->container->get('datetime_extras.daterange_compact.formatter');

    foreach ($all_data as $data) {
      foreach ($data['expected'] as $format => $expected) {
        $start = \DateTime::createFromFormat('Y-m-d\TH:i:s', $data['start'])->getTimestamp();
        $end = \DateTime::createFromFormat('Y-m-d\TH:i:s', $data['end'])->getTimestamp();

        $actual = $formatter->formatDateTimeRange($start, $end, $format);
        $message = "Using the $format format for " . $data['start'] . ' to ' . $data['end'];
        $this->assertEqual($actual, $expected, $message);
      }
    }
  }

}
