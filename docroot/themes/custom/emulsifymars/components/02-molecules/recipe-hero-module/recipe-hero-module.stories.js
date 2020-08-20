import React from 'react';
import { useEffect } from '@storybook/client-api';

import '../../01-atoms/video/fullscreen-video/video';

import recipeHeroModuleTwig from './recipe-hero-module.twig';
import recipeHeroModuleData from './recipe-hero-module.yml';
import recipeSocial from '../menus/social/social-menu.yml';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/Recipe Hero Module' };

export const recipeHeroModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: recipeHeroModuleTwig({
      ...recipeHeroModuleData,
      ...recipeSocial
    }) }} />
};


