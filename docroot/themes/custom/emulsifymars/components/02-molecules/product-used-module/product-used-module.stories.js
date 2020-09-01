import React from 'react';

import productUsed from './product-used-module.twig';
import productUsedData from './product-used-module.yml';
import { useEffect } from '@storybook/client-api';

import './product-used-module';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/Product Used Module' };

export const productUsedModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: productUsed(productUsedData) }} />
};
