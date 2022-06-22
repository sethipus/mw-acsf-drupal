import React from 'react';
import { useEffect } from '@storybook/client-api';

import grid from './grid.twig';
import gridData from './grid.yml';
import gridff from './grid-ff.twig';
import gridffData from './grid-ff.yml';
import gridCardData from './grid-cards.yml';
import gridCtaData from './grid-ctas.yml';
import ajaxGrid from './ajax-card-grid.twig';
import ajaxGridData from './ajax-card-grid.yml';
import ajaxCardGrid from './ajaxcardgrid';
import productCard from './../../02-molecules/card/product-card/product-card.twig';
import productCardData from './../../02-molecules/card/product-card/product-card.yml';
import recipeCard from './../../02-molecules/card/recipe-card/recipe-card.twig';
import recipeCardData from './../../02-molecules/card/recipe-card/recipe-card.yml';
import articleCard from './../../02-molecules/card/article-card/article-card.twig';
import articleCardData from './../../02-molecules/card/article-card/article-card.yml';

/**
 * Storybook Definition.
 */
// export default { title: 'Organisms/Grids' };

export const defaultGrid = () => (
  <div dangerouslySetInnerHTML={{ __html: grid(gridData) }} />
);
export const FlexibleFramerGrid = () => (
  <div
    dangerouslySetInnerHTML={{ __html: gridff({...gridffData }) }}
  />
);
export const cardGrid = () => (
  <div
    dangerouslySetInnerHTML={{ __html: grid({ ...gridData, ...gridCardData }) }}
  />
);
export const ctaGrid = () => (
  <div
    dangerouslySetInnerHTML={{ __html: grid({ ...gridData, ...gridCtaData }) }}
  />
);

export const ajaxCardGridExample = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  ajaxGridData.items = [
    productCard(productCardData),
    productCard(productCardData),
    recipeCard(recipeCardData),
    articleCard(articleCardData),
    productCard(productCardData),
    recipeCard(recipeCardData),
    articleCard(articleCardData),
    productCard(productCardData)
  ];
  return <div dangerouslySetInnerHTML={{ __html:  "<div style='height: 300px; background-color: grey'></div>" + ajaxGrid(ajaxGridData) }} />
};
