import React from 'react';
import pdpHero from './pdp-hero/pdp-hero.twig';
import pdpHeroData from './pdp-hero/pdp-hero.yml';
import pdpNutrition from './pdp-nutrition/pdp-nutrition.twig';
import pdpNutritionData from './pdp-nutrition/pdp-nutrition.yml';
import pdpAllergen from './pdp-allergen/pdp-allergen.twig';
import pdpAllergenData from './pdp-allergen/pdp-allergen.yml';
import pdpCooking from './pdp-cooking/pdp-cooking.twig';
import pdpCookingData from './pdp-cooking/pdp-cooking.yml';
import pdpMoreInformation from './pdp-more-information/pdp-more-information.twig';
import pdpMoreInformationData from './pdp-more-information/pdp-more-information.yml';
import pdpBenefits from './pdp-benefits/pdp-benefits.twig';
import pdpBenefitsData from './pdp-benefits/pdp-benefits.yml';
import { useEffect } from '@storybook/client-api';

import '../../03-organisms/pdp-body/pdp-body';

import pdpMultipackDetails from './pdp-multipack-details/pdp-multipack-details.twig';
import pdpMultipackDetailsData from './pdp-multipack-details/pdp-multipack-details.yml';

/**
 * Storybook Definition.
 */
// export default { title: 'Components/ Product Detail Hero' };

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

export const pdpCookingModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: pdpCooking(pdpCookingData) }} />
};

export const pdpBenefitsModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: pdpBenefits(pdpBenefitsData) }} />
};

export const pdpMoreInformationModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{__html: pdpMoreInformation(pdpMoreInformationData)}}/>
};

export const pdpMultipackDetailsModule = () => {
  return <div dangerouslySetInnerHTML={{ __html: pdpMultipackDetails(pdpMultipackDetailsData) }} />
};
