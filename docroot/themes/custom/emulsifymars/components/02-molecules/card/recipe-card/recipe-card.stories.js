import React from 'react';

import recipeCard from './recipe-card.twig';
import recipeCardData from './recipe-card.yml';
import recipeCardMaxLengthData from './recipe-card-max-length.yml';
import recipeCardPairUpData from './recipe-card-content-pair-up.yml';

export default {
  title: 'Molecules/Cards/Recipe Card',
  decorators:  [(Story) => <div style={{ padding: '5rem' }}><Story/></div>]
};

export const recipeCardContentPairUp = () => {
  return <div dangerouslySetInnerHTML={{ __html: recipeCard({...recipeCardData, ...recipeCardPairUpData}) }} />
};

export const recipeCardExample = () => {
  return <div dangerouslySetInnerHTML={{ __html: recipeCard(recipeCardData) }} />
};

export const recipeCardMaxLength = () => {
  return <div dangerouslySetInnerHTML={{ __html: recipeCard({...recipeCardData, ...recipeCardMaxLengthData}) }} />
};
