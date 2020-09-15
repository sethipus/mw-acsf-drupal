import React from 'react';

import allResultsTwig from './all-results.twig';
import allResultsData from './all-results.yml';
import ajaxCardGridData from '../../grid/ajax-card-grid.yml';
import searchResultsData from '../../../02-molecules/search/search-results/search-results.yml';

import { useEffect } from '@storybook/client-api';
import '../../../01-atoms/search-results-item/search-results-item';

/**
 * Storybook Definition.
 */
export default { title: 'Organisms/Search Results/View all results' };

export const allResultsTemplate = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: allResultsTwig({
      ...allResultsData,
      ...ajaxCardGridData,
      ...searchResultsData
  }) }} />
};
