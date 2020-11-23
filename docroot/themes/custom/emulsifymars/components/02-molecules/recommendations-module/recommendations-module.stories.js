import React from 'react';

import recommendations from './recommendations-module.twig';
import recommendationsData from './recommendations-module.yml';
import { useEffect } from '@storybook/client-api';
import productCard from './../../02-molecules/card/product-card/product-card.twig';
import productCardData from './../../02-molecules/card/product-card/product-card.yml';
import recipeCard from './../../02-molecules/card/recipe-card/recipe-card.twig';
import recipeCardData from './../../02-molecules/card/recipe-card/recipe-card.yml';
import articleCard from './../../02-molecules/card/article-card/article-card.twig';
import articleCardData from './../../02-molecules/card/article-card/article-card.yml';

import './recommendations-module';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/Recommendations Module' };

export const recommendationsModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  recommendationsData.recommended_items = [
    productCard(productCardData),
    articleCard(articleCardData),
    recipeCard(recipeCardData),
    productCard(productCardData),
    articleCard(articleCardData),
    recipeCard(recipeCardData),
    productCard(productCardData),
  ];
  return <div dangerouslySetInnerHTML={{ __html: "<div style='height: 300px; background-color: grey'></div>" + recommendations(recommendationsData) }} />
};
