import React from 'react';
import { useEffect } from '@storybook/client-api';
import contentFeature from './content-feature.twig';
import contentFeatureData from './content-feature.yml';

import './content-feature';

export default { title: 'Molecules/Content Feature' };

export const contentFeatureModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: contentFeature(contentFeatureData) }} />;
    
};