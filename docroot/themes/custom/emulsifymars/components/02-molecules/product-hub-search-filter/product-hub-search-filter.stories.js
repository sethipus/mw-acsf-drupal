import React from 'react';

import productHubSearchFilter from '../product-hub-search-filter/product-hub-search-filter.twig';
import productHubSearchFilterData from '../product-hub-search-filter/product-hub-search-filter.yml';
import { useEffect } from '@storybook/client-api';
import '../product-hub-search-filter/product-hub-search-filter.js';

/**
 * Storybook Definition.
 */
// export default { title: 'Molecules/Product Hub Search Filter' };

export const filters = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: productHubSearchFilter(productHubSearchFilterData) }} />;
};
