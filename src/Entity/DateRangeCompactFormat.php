<?php

namespace Drupal\datetime_extras\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the compact date range format entity.
 *
 * @ConfigEntityType(
 *   id = "daterange_compact_format",
 *   label = @Translation("Compact date range format"),
 *   handlers = {
 *     "list_builder" = "Drupal\datetime_extras\DateRangeCompactFormatListBuilder",
 *     "form" = {
 *       "add" = "Drupal\datetime_extras\Form\DateRangeCompactFormatForm",
 *       "edit" = "Drupal\datetime_extras\Form\DateRangeCompactFormatForm",
 *       "delete" = "Drupal\datetime_extras\Form\DateRangeCompactFormatDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "daterange_compact_format",
 *   admin_permission = "administer site configuration",
 *   list_cache_tags = { "rendered" },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/config/regional/daterange-compact-format/{daterange_compact_format}",
 *     "add-form" = "/admin/config/regional/daterange-compact-format/add",
 *     "edit-form" = "/admin/config/regional/daterange-compact-format/{daterange_compact_format}/edit",
 *     "delete-form" = "/admin/config/regional/daterange-compact-format/{daterange_compact_format}/delete",
 *     "collection" = "/admin/config/regional/daterange-compact-format"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "default_pattern",
 *     "separator",
 *     "same_day_start_pattern",
 *     "same_day_end_pattern",
 *     "same_month_start_pattern",
 *     "same_month_end_pattern",
 *     "same_year_start_pattern",
 *     "same_year_end_pattern",
 *   }
 * )
 */
class DateRangeCompactFormat extends ConfigEntityBase implements DateRangeCompactFormatInterface {

  /**
   * The ID of this format.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of this format.
   *
   * @var string
   */
  protected $label;

  /**
   * {@inheritdoc}
   */
  public function getCacheTagsToInvalidate() {
    return ['rendered'];
  }

}
