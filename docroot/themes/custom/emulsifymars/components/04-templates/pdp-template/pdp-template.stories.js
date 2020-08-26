import React from 'react';

import pdpTemplateTwig from './pdp-template.twig';
import pdpTemplateData from './pdp-template.yml';
import pdpHeroModuleData from '../../02-molecules/pdp/pdp-hero/pdp-hero.yml';
import pdpNutritionModuleData from '../../02-molecules/pdp/pdp-nutrition/pdp-nutrition.yml';
import { useEffect } from '@storybook/client-api';
// import '../../02-molecules/pdp/pdp-hero/';

/**
 * Storybook Definition.
 */
export default { title: 'Templates/PDP Template' };

export const pdpTemplate = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: pdpTemplateTwig({
      ...pdpTemplateData,
      ...pdpHeroModuleData,
      ...pdpNutritionModuleData
    }) }} />
  };


