import React from 'react';
import ReactDOMServer from "react-dom/server";

import productContentPairUp from './product-content-pair-up.twig';
import productContentPairUpData from './product-content-pair-up.yml';
import {
  recipeCardContentPairUp,
} from "../../02-molecules/card/recipe-card/recipe-card.stories";

import {
  productCardContentPairUp,
} from "../../02-molecules/card/product-card/product-card.stories";

import {
  articleCardContentPairUp,
} from "../../02-molecules/card/article-card/article-card.stories";

import { useEffect } from '@storybook/client-api';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/Product Content Pair Up' };

export const productContentPairUpModuleArticleCard = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  productContentPairUpData.supporting_card_content = [
    ReactDOMServer.renderToStaticMarkup(articleCardContentPairUp()),
  ];
  return <div dangerouslySetInnerHTML={{ __html: productContentPairUp(productContentPairUpData) }} />
};

export const productContentPairUpModuleProductCard = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  productContentPairUpData.supporting_card_content = [
    ReactDOMServer.renderToStaticMarkup(productCardContentPairUp()),
  ];
  return <div dangerouslySetInnerHTML={{ __html: productContentPairUp(productContentPairUpData) }} />
};

export const productContentPairUpModuleRecipeCard = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  productContentPairUpData.supporting_card_content = [
    ReactDOMServer.renderToStaticMarkup(recipeCardContentPairUp()),
  ];
  return <div dangerouslySetInnerHTML={{ __html: productContentPairUp(productContentPairUpData) }} />
};
