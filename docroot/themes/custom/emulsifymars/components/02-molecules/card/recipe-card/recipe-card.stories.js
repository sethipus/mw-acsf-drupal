import React from 'react';

import recipeCard from './recipe-card.twig';
import recipeCardData from './recipe-card.yml';
import { useEffect } from '@storybook/client-api';
import recipeCardMaxLengthData from './recipe-card-max-length.yml';
import recipeCardPairUpData from './recipe-card-content-pair-up.yml';

import './recipe-card';

export default {
  title: 'Molecules/Cards/Recipe Card',
  decorators:  [(Story) => <div style={{ padding: '5rem' }}><Story/></div>]
};

export const recipeCardContentPairUp = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: recipeCard({...recipeCardData, ...recipeCardPairUpData}) }} />
};

export const recipeCardExample = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: recipeCard(recipeCardData) }} />
};

export const recipeCardMaxLength = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: recipeCard({...recipeCardData, ...recipeCardMaxLengthData}) }} />
};
