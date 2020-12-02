import React from 'react';

import recipeBodyTwig from './recipe-body.twig';
import recipeBodyData from './recipe-body.yml';
import { useEffect } from '@storybook/client-api';
import './recipe-body';

import productCard from "../../02-molecules/card/product-card/product-card.twig";
import productCardData from "../../02-molecules/card/product-card/product-card.yml";


/**
 * Storybook Definition.
 */
export default { title: 'Organisms/Recipe Body' };

export const recipeBodyTemplate = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  const product_used_items = {
    theme_styles: 'twix',
    product_used_items: [
      productCard(productCardData),
      productCard(productCardData)
    ]
  };
  return <div dangerouslySetInnerHTML={{ __html: recipeBodyTwig({ ...recipeBodyData, ...product_used_items }) }} />;
};
