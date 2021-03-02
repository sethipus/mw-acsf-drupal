import React from 'react';
import ReactDOMServer from "react-dom/server";

import recipeBodyTwig from './recipe-body.twig';
import recipeBodyData from './recipe-body.yml';
import { useEffect } from '@storybook/client-api';
import './recipe-body';

import {
  productCardNewProduct,
  productCardMaxLength
} from "../../02-molecules/card/product-card/product-card.stories";


/**
 * Storybook Definition.
 */
export default { title: 'Organisms/Recipe Body' };

export const recipeBodyTemplate = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  const product_used_items = {
    theme_styles: 'twix',
    product_used_items: [
      ReactDOMServer.renderToStaticMarkup(productCardNewProduct()),
      ReactDOMServer.renderToStaticMarkup(productCardMaxLength()),
    ]
  };
  return <div dangerouslySetInnerHTML={{ __html: recipeBodyTwig({ ...recipeBodyData, ...product_used_items }) }} />;
};
