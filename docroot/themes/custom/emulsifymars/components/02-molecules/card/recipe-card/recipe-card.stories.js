import React from 'react';

import recipeCard from './recipe-card.twig';
import recipeCardData from './recipe-card.yml';
import recipeCardMaxLengthData from './recipe-card-max-length.yml';

export default {
  title: 'Molecules/Cards/Recipe Card',
  decorators:  [(Story) => <div style={{ padding: '5rem' }}><Story/></div>]
};

export const recipeCardExample = () => {
  return <div dangerouslySetInnerHTML={{ __html: recipeCard(recipeCardData) }} />
};

export const recipeCardMaxLength = () => {
  return <div dangerouslySetInnerHTML={{ __html: recipeCard({...recipeCardData, ...recipeCardMaxLengthData}) }} />
};
