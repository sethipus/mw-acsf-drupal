import React from 'react';
import { useEffect } from '@storybook/client-api';

import productSelector from './product-selector.twig';
import productSelectorData from './product-selector.yml';

import './product-selector';

/**
 * Storybook Definition.
 */
// export default { title: 'Molecules/Product Selector' };

export const productSelectorExample = () => {

useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: productSelector({ ...productSelectorData }) }} />;
};
