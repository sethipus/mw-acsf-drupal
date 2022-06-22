import React from 'react';

import productUsed from './product-used-module.twig';
import productCard from './../card/product-card/product-card.twig';
import productCardData from './../card/product-card/product-card.yml';

import { useEffect } from '@storybook/client-api';

import './product-used-module';

/**
 * Storybook Definition.
 */
// export default { title: 'Molecules/Product Used Module' };

export const productUsedModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  const productUsedData = {
    theme_styles: 'twix',
    product_used_items: [
      productCard(productCardData),
      productCard(productCardData),
      productCard(productCardData)
    ]
  };
  return <div dangerouslySetInnerHTML={{__html: productUsed(productUsedData)}}/>
};
