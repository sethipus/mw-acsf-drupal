import React from 'react';
import { useEffect } from '@storybook/client-api';

import recipeCard from './recipe-card.twig';
import recipeCardData from './recipe-card.yml';

export default { title: 'Molecules/Cards/Recipe Card' };

import '../cards';

export const recipeCardExample = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: recipeCard(recipeCardData) }} />
};
