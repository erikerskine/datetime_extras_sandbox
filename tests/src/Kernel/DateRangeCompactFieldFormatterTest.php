<?php

namespace Drupal\Tests\datetime_extras\Kernel;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests compact date range field formatter functionality.
 *
 * These tests work by creating a couple of fields on an entity and rendering
 * those fields using the 'daterange_compact' formatter. They test the
 * behaviour of that field formatter, and the default configuration.
 *
 * More comprehensive testing of formatting logic can be found in
 * DateRangeCompactFormatterTest.
 *
 * @group field
 */
class DateRangeCompactFieldFormatterTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'field',
    'datetime',
    'datetime_extras',
    'datetime_range',
    'entity_test',
    'user',
  ];

  /**
   * The name of the entity type used in testing.
   *
   * @var string
   */
  protected $entityType;

  /**
   * The name of the bundle used for testing.
   *
   * @var string
   */
  protected $bundle;

  /**
   * The made up name of the date-only range field.
   *
   * @var string
   */
  protected $dateFieldName;

  /**
   * The made up name of the date & time range field.
   *
   * @var string
   */
  protected $dateTimeFieldName;

  /**
   * The default display for this entity.
   *
   * @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface
   */
  protected $defaultDisplay;

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  protected function setUp() {
    parent::setUp();

    $this->installConfig(['system']);
    $this->installConfig(['field']);
    $this->installConfig(['datetime_extras']);
    $this->installEntitySchema('entity_test');

    $this->entityType = 'entity_test';
    $this->bundle = $this->entityType;
    $this->dateFieldName = mb_strtolower($this->randomMachineName());
    $this->dateTimeFieldName = mb_strtolower($this->randomMachineName());

    $date_field_storage = FieldStorageConfig::create([
      'field_name' => $this->dateFieldName,
      'entity_type' => $this->entityType,
      'type' => 'daterange',
      'settings' => [
        'datetime_type' => DateTimeItem::DATETIME_TYPE_DATE,
      ],
    ]);
    $date_field_storage->save();

    $date_field_instance = FieldConfig::create([
      'field_storage' => $date_field_storage,
      'bundle' => $this->bundle,
      'label' => $this->randomMachineName(),
    ]);
    $date_field_instance->save();

    $date_time_field_storage = FieldStorageConfig::create([
      'field_name' => $this->dateTimeFieldName,
      'entity_type' => $this->entityType,
      'type' => 'daterange',
      'settings' => [
        'datetime_type' => DateTimeItem::DATETIME_TYPE_DATETIME,
      ],
    ]);
    $date_time_field_storage->save();

    $date_time_field_instance = FieldConfig::create([
      'field_storage' => $date_time_field_storage,
      'bundle' => $this->bundle,
      'label' => $this->randomMachineName(),
    ]);
    $date_time_field_instance->save();

    $this->defaultDisplay = EntityViewDisplay::create([
      'targetEntityType' => $this->entityType,
      'bundle' => $this->bundle,
      'mode' => 'default',
      'status' => TRUE,
    ]);
    $this->defaultDisplay->setComponent($this->dateFieldName, [
      'type' => 'daterange_compact',
      'settings' => [
        'format_type' => 'medium_date',
      ],
    ]);
    $this->defaultDisplay->setComponent($this->dateTimeFieldName, [
      'type' => 'daterange_compact',
      'settings' => [
        'format_type' => 'medium_datetime',
      ],
    ]);
    $this->defaultDisplay->save();
  }

  /**
   * Renders fields of a given entity with a given display.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The entity object with attached fields to render.
   * @param \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display
   *   The display to render the fields in.
   *
   * @return string
   *   The rendered entity fields.
   *
   * @throws \Exception
   */
  protected function renderEntityFields(FieldableEntityInterface $entity, EntityViewDisplayInterface $display) {
    $content = $display->build($entity);
    $content = $this->render($content);
    return $content;
  }

  /**
   * Tests the display of an entity containing a date-only range field.
   *
   * @throws \Exception
   */
  public function testDateRangeField() {
    $entity = EntityTest::create([]);
    $entity->{$this->dateFieldName}->value = '2020-01-01';
    $entity->{$this->dateFieldName}->end_value = '2020-12-31';
    $this->renderEntityFields($entity, $this->defaultDisplay);

    $expected = '1 January–31 December 2020';
    $message = 'Expecting the rendered entity to show "' . $expected . '"';
    $this->assertRaw($expected, $message);
  }

  /**
   * Tests the display of an entity containing a date and time range field.
   *
   * @throws \Exception
   */
  public function testDateTimeRangeField() {

    // Note: data is stored in UTC, but the default timezone when running tests
    // is Australia/Sydney (see https://www.drupal.org/node/2498619 for why).
    // Hence the discrepancy between 'value'/'end_value' and the expected
    // output.
    //
    // Australia/Sydney is UTC +10:00 (normal) or UTC +11:00 (DST)
    // DST starts first Sunday in October
    // DST ends first Sunday in April.

    $entity = EntityTest::create([]);
    $entity->{$this->dateTimeFieldName}->value = '2020-05-01T00:00:00';
    $entity->{$this->dateTimeFieldName}->end_value = '2020-05-01T01:00:00';
    $this->renderEntityFields($entity, $this->defaultDisplay);

    $expected = '1 May 2020 10:00–11:00';
    $message = 'Expecting the rendered entity to show "' . $expected . '"';
    $this->assertRaw($expected, $message);
  }

}
