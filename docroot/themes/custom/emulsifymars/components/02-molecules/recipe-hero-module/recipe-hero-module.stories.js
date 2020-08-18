import React from 'react';

import recipeHeroModuleTwig from './recipe-hero-module.twig';
import recipeHeroModuleData from './recipe-hero-module.yml';
import recipeSocial from '../menus/social/social-menu.yml';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/Recipe Hero Module' };

export const recipeHeroModule = () => (
  <div dangerouslySetInnerHTML={{ __html: recipeHeroModuleTwig({
      ...recipeHeroModuleData,
      ...recipeSocial
    }) }} />
);


