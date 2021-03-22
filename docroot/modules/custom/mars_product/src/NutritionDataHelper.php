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
    return $this->configFactory->getEditable('nutrition_table.settings');
  }

  /**
   * Get nutrition data fields mapping.
   *
   * @return array
   *   Mapping.
   */
  public function getMapping() {
    $config = $this->getNutritionConfig();
    if ($config->isNew()) {
      $nutrtition_config = $this->classResolver
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
        $nutrtition_config[$group] = $config->get($group);
      }
    }

    $mapping = [];
    foreach ($nutrtition_config as $group => $fields) {
      foreach ($fields as $field) {
        $mapping[$group][$field['field']] = $field;
      }
    }

    return $mapping;
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
