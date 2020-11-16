import React from 'react';

import recipeTemplateTwig from './recipe-template.twig';
import recipeTemplateData from './recipe-template.yml';
import recipeHeroModuleData from '../../02-molecules/recipe-hero-module/recipe-hero-module-video.yml';
import recipeSocial from '../../02-molecules/menus/social/social-menu.yml';
import recipeBodyData from '../../03-organisms/recipe-body/recipe-body.yml';

import { useEffect } from '@storybook/client-api';
import '../../03-organisms/recipe-body/recipe-body';

/**
 * Storybook Definition.
 */
export default { title: 'Templates/Recipe Template' };

export const recipeTemplate = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{
    __html: recipeTemplateTwig({
      ...recipeTemplateData,
      ...recipeHeroModuleData,
      ...recipeSocial,
      ...recipeBodyData
    })
  }}/>;
};
