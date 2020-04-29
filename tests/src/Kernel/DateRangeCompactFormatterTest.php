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

    // Create a typical format for USA.
    DateRangeCompactFormat::create([
      'id' => 'usa',
      'label' => 'USA',
      'date_settings' => [
        'default_pattern' => 'F jS, Y',
        'separator' => ' - ',
        'same_month_start_pattern' => 'F jS',
        'same_month_end_pattern' => 'jS, Y',
        'same_year_start_pattern' => 'F jS',
        'same_year_end_pattern' => 'F jS, Y',
      ],
      'datetime_settings' => [
        'default_pattern' => 'g:ia \o\n F jS, Y',
        'separator' => ' - ',
        'same_day_start_pattern' => 'g:ia',
        'same_day_end_pattern' => 'g:ia \o\n F jS, Y',
      ],
    ])->save();

    // Create a ISO-8601 format without any compact variations.
    DateRangeCompactFormat::create([
      'id' => 'iso_8601',
      'label' => 'ISO-8601',
      'date_settings' => [
        'default_pattern' => 'Y-m-d',
        'separator' => ' - ',
      ],
      'datetime_settings' => [
        'default_pattern' => 'Y-m-d\TH:i:s',
        'separator' => ' - ',
      ],
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
        'medium' => '1 January 2017',
        'usa' => 'January 1st, 2017',
        'iso_8601' => '2017-01-01',
      ],
    ];

    // Different days, same month.
    $all_data[] = [
      'start' => '2017-01-02',
      'end' => '2017-01-03',
      'expected' => [
        'medium' => '2–3 January 2017',
        'usa' => 'January 2nd - 3rd, 2017',
        'iso_8601' => '2017-01-02 - 2017-01-03',
      ],
    ];

    // Different months, same year.
    $all_data[] = [
      'start' => '2017-01-04',
      'end' => '2017-02-05',
      'expected' => [
        'medium' => '4 January–5 February 2017',
        'usa' => 'January 4th - February 5th, 2017',
        'iso_8601' => '2017-01-04 - 2017-02-05',
      ],
    ];

    // Different years.
    $all_data[] = [
      'start' => '2017-01-06',
      'end' => '2018-02-07',
      'expected' => [
        'medium' => '6 January 2017–7 February 2018',
        'usa' => 'January 6th, 2017 - February 7th, 2018',
        'iso_8601' => '2017-01-06 - 2018-02-07',
      ],
    ];

    /** @var \Drupal\datetime_extras\DateRangeCompactFormatterInterface $formatter */
    $formatter = $this->container->get('datetime_extras.daterange_compact.formatter');

    foreach ($all_data as $data) {
      foreach ($data['expected'] as $format => $expected) {
        $start = \DateTime::createFromFormat('Y-m-d', $data['start'])->getTimestamp();
        $end = \DateTime::createFromFormat('Y-m-d', $data['end'])->getTimestamp();

        $actual = $formatter->formatDateRange($start, $end, $format);
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
        'medium' => '1 January 2017 20:00–23:00',
        'usa' => '8:00pm - 11:00pm on January 1st, 2017',
        'iso_8601' => '2017-01-01T20:00:00 - 2017-01-01T23:00:00',
      ],
    ];

    // Different day in UTC, same day in Australia.
    $all_data[] = [
      'start' => '2017-01-02T10:00:00',
      'end' => '2017-01-02T12:00:00',
      'expected' => [
        'medium' => '2 January 2017 10:00–12:00',
        'usa' => '10:00am - 12:00pm on January 2nd, 2017',
        'iso_8601' => '2017-01-02T10:00:00 - 2017-01-02T12:00:00',
      ],
    ];

    // Same day in UTC, different day in Australia.
    $all_data[] = [
      'start' => '2017-01-01T23:00:00',
      'end' => '2017-01-02T02:00:00',
      'expected' => [
        'medium' => '1 January 2017 23:00–2 January 2017 02:00',
        'usa' => '11:00pm on January 1st, 2017 - 2:00am on January 2nd, 2017',
        'iso_8601' => '2017-01-01T23:00:00 - 2017-01-02T02:00:00',
      ],
    ];

    // Different days in UTC and Australia, also spans DST change.
    $all_data[] = [
      'start' => '2017-04-01T12:00:00',
      'end' => '2017-04-08T11:00:00',
      'expected' => [
        'medium' => '1 April 2017 12:00–8 April 2017 11:00',
        'usa' => '12:00pm on April 1st, 2017 - 11:00am on April 8th, 2017',
        'iso_8601' => '2017-04-01T12:00:00 - 2017-04-08T11:00:00',
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
