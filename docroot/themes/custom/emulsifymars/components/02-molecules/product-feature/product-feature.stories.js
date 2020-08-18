import React from 'react';
import { useEffect } from '@storybook/client-api';
import productFeature from './product-feature.twig';
import productFeatureData from './product-feature.yml';

import './product-feature';

export default { title: 'Molecules/Product Feature' };

export const prodcutFeatureModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: productFeature(productFeatureData) }} />;

};
