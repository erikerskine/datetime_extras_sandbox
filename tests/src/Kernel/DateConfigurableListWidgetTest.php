<?php

namespace Drupal\Tests\datetime_extras\Kernel;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\Traits\ExpectDeprecationTrait;

/**
 * Test the DateConfigurableListWidget for datetime fields.
 *
 * @coversDefaultClass \Drupal\datetime_extras\Plugin\Field\FieldWidget\DateConfigurableListWidget
 * @group datetime_extras
 * @group legacy
 */
class DateConfigurableListWidgetTest extends KernelTestBase {
  use ExpectDeprecationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'datetime',
    'datetime_extras',
  ];

  /**
   * @covers ::__construct
   */
  public function testConstruction() {
    $base_field_definition = BaseFieldDefinition::create('datetime')
      ->setName('Configurable List');

    $widget_options = [
      'field_definition' => $base_field_definition,
      'form_mode' => 'default',
      'configuration' => [
        'type' => 'datatime_extras_configurable_list',
      ],
    ];

    $this->expectDeprecation('The Drupal\datetime_extras\Plugin\Field\FieldWidget\DateConfigurableListWidget is deprecated in datetime_extras:8.x-1.0 and is removed from datetime_extras:8.x-2.0. Use Drupal\datetime_extras\Plugin\Field\FieldWidget\DateTimeDatelistNoTimeWidget instead. See https://www.drupal.org/node/2973035');
    $this->container->get('plugin.manager.field.widget')->getInstance($widget_options);
  }

}
