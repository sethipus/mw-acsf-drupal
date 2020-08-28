import React from 'react';
import pdpHero from './pdp-hero.twig';
import pdpHeroData from './pdp-hero.yml';
import { useEffect } from '@storybook/client-api';
import './pdp-hero';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/PDP' };

export const pdpHeroModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: pdpHero(pdpHeroData) }} />
};
