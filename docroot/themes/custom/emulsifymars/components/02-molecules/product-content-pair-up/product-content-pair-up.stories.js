import React from 'react';

import productContentPairUp from './product-content-pair-up.twig';
import productContentPairUpData from './product-content-pair-up.yml';
import { useEffect } from '@storybook/client-api';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/Product Content Pair Up' };

export const productContentPairUpModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: productContentPairUp(productContentPairUpData) }} />
};
