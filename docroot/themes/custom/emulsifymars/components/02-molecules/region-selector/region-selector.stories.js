import React from 'react';

import regionSelector from './region-selector.twig';

import regionSelectorData from './region-selector.yml';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/RegionSelector' };

export const regionSelectorEx = () => (
  <div dangerouslySetInnerHTML={{ __html: regionSelector(regionSelectorData) }} />
);
