<?php

namespace Drupal\mars_product;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ClassResolver;
use Drupal\mars_product\Form\NutritionConfigForm;
use Drupal\mars_product\Plugin\Block\PdpHeroBlock;

/**
 * Nutrition data helper class.
 */
class NutritionDataHelper {

  /**
   * The config factory interface.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The class resolver service.
   *
   * @var \Drupal\Core\DependencyInjection\ClassResolver
   */
  protected $classResolver;

  /**
   * NutritionDataHelper constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\Core\DependencyInjection\ClassResolver $class_resolver
   *   The class resolver service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    ClassResolver $class_resolver
  ) {
    $this->configFactory = $config_factory;
    $this->classResolver = $class_resolver;
  }

  /**
   * Get nutrition data config.
   */
  public function getNutritionConfig() {
    return $this->configFactory->getEditable('mars_product.nutrition_table_settings');
  }

  /**
   * Get nutrition data fields mapping.
   *
   * @param string $field_prefix
   *   Prefix for the field name. Dual or product.
   *
   * @return array
   *   Mapping.
   */
  public function getMapping(string $field_prefix = 'product') {
    $config = $this->getNutritionConfig();
    if ($config->isNew()) {
      $nutrition_config = $this->classResolver
        ->getInstanceFromDefinition(NutritionConfigForm::class)
        ->getDefaultConfiguration();
    }
    else {
      $groups = [
        PdpHeroBlock::NUTRITION_SUBGROUP_1,
        PdpHeroBlock::NUTRITION_SUBGROUP_2,
        PdpHeroBlock::NUTRITION_SUBGROUP_3,
        PdpHeroBlock::NUTRITION_SUBGROUP_VITAMINS,
      ];
      foreach ($groups as $group) {
        $nutrition_config[$group] = $config->get($group);
      }
    }

    $mapping = [];
    foreach ($nutrition_config as $group => $fields) {
      foreach ($fields as $field) {
        $this->replaceFieldNamePrefix($field, $field_prefix);
        $mapping[$group][$field['field']] = $field;
      }
    }

    return $mapping;
  }

  /**
   * Replace field name prefix in order to support ordering for dual fields.
   *
   * @param array $field
   *   Field data array.
   * @param string $field_prefix
   *   Field prefix.
   */
  private function replaceFieldNamePrefix(array &$field, string $field_prefix) {
    $field['field'] = str_replace('product', $field_prefix, $field['field']);
    if (isset($field['daily_field']) && $field['daily_field'] != 'none') {
      $field['daily_field'] = str_replace('product', $field_prefix, $field['daily_field']);
    }
  }

  /**
   * Sort mapping according to weight.
   *
   * @param array $mapping
   *   Mapping.
   *
   * @return array
   *   Sortered mapping.
   */
  public function sortFields(array &$mapping) {
    usort($mapping, function ($a, $b) {
      if ($a['weight'] == $b['weight']) {
        return 0;
      }
      return $a['weight'] < $b['weight']
        ? -1
        : 1;
    });
    return $mapping;
  }

}
