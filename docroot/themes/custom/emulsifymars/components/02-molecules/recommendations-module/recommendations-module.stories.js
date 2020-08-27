import React from 'react';

import recommendations from './recommendations-module.twig';
import recommendationsData from './recommendations-module.yml';
import { useEffect } from '@storybook/client-api';

import './recommendations-module';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/Recommendations Module' };

export const recommendationsModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: recommendations(recommendationsData) }} />
};
