import React from 'react';

import recipeCard from './recipe-card.twig';
import recipeCardData from './recipe-card.yml';

export default { title: 'Molecules/Cards/Recipe Card' };

export const recipeCardExample = () => {
  return <div dangerouslySetInnerHTML={{ __html: recipeCard(recipeCardData) }} />
};
