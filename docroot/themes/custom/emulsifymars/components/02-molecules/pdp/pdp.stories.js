import React from 'react';
import pdpHero from './pdp-hero/pdp-hero.twig';
import pdpHeroData from './pdp-hero/pdp-hero.yml';
import pdpNutrition from './pdp-nutrition/pdp-nutrition.twig';
import pdpNutritionData from './pdp-nutrition/pdp-nutrition.yml';
import pdpAllergen from './pdp-allergen/pdp-allergen.twig';
import pdpAllergenData from './pdp-allergen/pdp-allergen.yml';
import pdpMultipack from './pdp-multipack/pdp-multipack.twig';
import pdpMultipackData from './pdp-multipack/pdp-multipack.yml';
import { useEffect } from '@storybook/client-api';

import '../../03-organisms/pdp-body/pdp-body';
import './pdp-multipack/pdp-multipack';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/PDP' };

export const pdpHeroModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: pdpHero(pdpHeroData) }} />
};

export const pdpNutritionModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: pdpNutrition(pdpNutritionData) }} />
};

export const pdpAllergenModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: pdpAllergen(pdpAllergenData) }} />
};

export const pdpMultipackModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: pdpMultipack(pdpMultipackData) }} />
};
