import React from 'react';
import { useEffect } from '@storybook/client-api';

import '../../01-atoms/video/fullscreen-video/video';

import recipeFeatureModuleTwig from './recipe-feature-module.twig';
import recipeFeatureModuleData from './recipe-feature-module.yml';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/Recipe Feature Module' };

export const recipeFeatureModule = () => {
  return <div dangerouslySetInnerHTML={{ __html: recipeFeatureModuleTwig({
      ...recipeFeatureModuleData
    }) }} />
};
