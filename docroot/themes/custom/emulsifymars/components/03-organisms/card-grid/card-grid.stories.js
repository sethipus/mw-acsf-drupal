import React from 'react';
import {useEffect} from '@storybook/client-api';

import cardGrid from './card-grid.twig';
import cardGridData from './card-grid.yml';
import ajaxCardGridData from "../grid/ajax-card-grid.yml";
import searchFilterData from "./../../02-molecules/product-hub-search-filter/product-hub-search-filter.yml";
import "./../../02-molecules/product-hub-search-filter/product-hub-search-filter"

import productCard from "../../02-molecules/card/product-card/product-card.twig";
import productCardData from "../../02-molecules/card/product-card/product-card.yml";
import recipeCard from "../../02-molecules/card/recipe-card/recipe-card.twig";
import recipeCardData from "../../02-molecules/card/recipe-card/recipe-card.yml";
import articleCard from "../../02-molecules/card/article-card/article-card.twig";
import articleCardData from "../../02-molecules/card/article-card/article-card.yml";
/**
 * Storybook Definition.
 */
export default {title: 'Organisms/Card grid'};

export const cardGridModuleWithResults = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  ajaxCardGridData.items = [
    productCard(productCardData),
    productCard(productCardData),
    recipeCard(recipeCardData),
    articleCard(articleCardData),
    productCard(productCardData),
    recipeCard(recipeCardData),
    articleCard(articleCardData),
    productCard(productCardData)
  ];
  return <div dangerouslySetInnerHTML={{
    __html: "<div style='height: 300px; background-color: grey'></div>" + cardGrid({
      ...ajaxCardGridData,
      ...cardGridData,
      ...searchFilterData
    })
  }}/>;
};
