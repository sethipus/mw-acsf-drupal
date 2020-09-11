import React from 'react';

import recipeBodyTwig from './recipe-body.twig';
import recipeBodyData from './recipe-body.yml';
import { useEffect } from '@storybook/client-api';
import './recipe-body';

import '../../02-molecules/recommendations-module/recommendations-module';

/**
 * Storybook Definition.
 */
export default { title: 'Organisms/Recipe Body' };

export const recipeBodyTemplate = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: recipeBodyTwig({ ...recipeBodyData }) }} />;
};
