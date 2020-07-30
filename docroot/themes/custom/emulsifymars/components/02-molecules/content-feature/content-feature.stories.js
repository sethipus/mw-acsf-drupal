import React from 'react';
import contentFeature from './content-feature.twig';
import contentFeatureData from './content-feature.yml';

export default { title: 'Molecules/Content Feature' };

export const contentFeatureModule = () => (
  <div dangerouslySetInnerHTML={{ __html: contentFeature(contentFeatureData) }} />
);