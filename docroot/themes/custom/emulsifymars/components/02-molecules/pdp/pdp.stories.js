import React from 'react';
import pdpHero from './pdp-hero/pdp-hero.twig';
import pdpHeroData from './pdp-hero/pdp-hero.yml';
import pdpNutrition from './pdp-nutrition/pdp-nutrition.twig';
import pdpNutritionData from './pdp-nutrition/pdp-nutrition.yml';
import pdpAllergen from './pdp-allergen/pdp-allergen.twig';
import pdpAllergenData from './pdp-allergen/pdp-allergen.yml';
import { useEffect } from '@storybook/client-api';
import './pdp-hero/pdp-hero';

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
