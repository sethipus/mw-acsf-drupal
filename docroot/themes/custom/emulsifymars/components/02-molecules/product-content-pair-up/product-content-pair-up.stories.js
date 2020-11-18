import React from 'react';

import productContentPairUp from './product-content-pair-up.twig';
import productContentPairUpData from './product-content-pair-up.yml';
import productCard from './../../02-molecules/card/product-card/product-card.twig';
import productCardData from './../../02-molecules/card/product-card/product-card.yml';
import recipeCard from './../../02-molecules/card/recipe-card/recipe-card.twig';
import recipeCardData from './../../02-molecules/card/recipe-card/recipe-card.yml';
import articleCard from './../../02-molecules/card/article-card/article-card.twig';
import articleCardData from './../../02-molecules/card/article-card/article-card.yml';
import { useEffect } from '@storybook/client-api';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/Product Content Pair Up' };

export const productContentPairUpModuleArticleCard = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  productContentPairUpData.supporting_card_content = [
    articleCard(articleCardData)
  ];
  return <div dangerouslySetInnerHTML={{ __html: productContentPairUp(productContentPairUpData) }} />
};

export const productContentPairUpModuleProductCard = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  productContentPairUpData.supporting_card_content = [
    productCard(productCardData)
  ];
  return <div dangerouslySetInnerHTML={{ __html: productContentPairUp(productContentPairUpData) }} />
};

export const productContentPairUpModuleRecipeCard = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  productContentPairUpData.supporting_card_content = [
    recipeCard(recipeCardData)
  ];
  return <div dangerouslySetInnerHTML={{ __html: productContentPairUp(productContentPairUpData) }} />
};
