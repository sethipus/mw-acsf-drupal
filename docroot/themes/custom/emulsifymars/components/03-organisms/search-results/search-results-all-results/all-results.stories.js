import React from 'react';

import allResultsTwig from './all-results.twig';
import allResultsData from './all-results.yml';
import ajaxCardGridData from '../../grid/ajax-card-grid.yml';
import searchResultsData from '../../../02-molecules/search/search-results/search-results.yml';
import productCard from './../../../02-molecules/card/product-card/product-card.twig';
import productCardData from './../../../02-molecules/card/product-card/product-card.yml';
import recipeCard from './../../../02-molecules/card/recipe-card/recipe-card.twig';
import recipeCardData from './../../../02-molecules/card/recipe-card/recipe-card.yml';
import articleCard from './../../../02-molecules/card/article-card/article-card.twig';
import articleCardData from './../../../02-molecules/card/article-card/article-card.yml';

import { useEffect } from '@storybook/client-api';
import '../../../01-atoms/search-results-item/search-results-item';

/**
 * Storybook Definition.
 */
export default { title: 'Organisms/Search Results/View all results' };

export const allResultsTemplate = () => {
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
  return <div dangerouslySetInnerHTML={{ __html: allResultsTwig({
      ...allResultsData,
      ...ajaxCardGridData,
      ...searchResultsData
    }) 
  }} />
};

