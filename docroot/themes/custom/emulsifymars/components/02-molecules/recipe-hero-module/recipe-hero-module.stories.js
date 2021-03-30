import React from 'react';
import { useEffect } from '@storybook/client-api';

import '../../01-atoms/video/fullscreen-video/video';

import recipeHeroModuleTwig from './recipe-hero-module.twig';
import recipeHeroModuleVideoData from './recipe-hero-module-video.yml';
import recipeHeroModuleImageData from './recipe-hero-module-image.yml';
import recipeSocial from '../menus/social/social-menu.yml';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/Recipe Hero Module' };

export const recipeHeroModuleVideo = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: recipeHeroModuleTwig({
      ...recipeHeroModuleVideoData,
      ...recipeSocial
    }) }} />
};
export const recipeHeroModuleImage = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: recipeHeroModuleTwig({
      ...recipeHeroModuleImageData,
      ...recipeSocial
    }) }} />
};


