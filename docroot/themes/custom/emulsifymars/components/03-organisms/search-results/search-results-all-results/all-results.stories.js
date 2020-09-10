import React from 'react';

import allResultsTwig from './all-results.twig';
import allResultsData from './all-results.yml';
import ajaxCardGridData from '../../grid/ajax-card-grid.yml';
import searchResultsData from '../../../02-molecules/search/search-results/search-results.yml';

/**
 * Storybook Definition.
 */
export default { title: 'Organisms/Search Results/View all results' };

export const allResultsTemplate = () => (
  <div dangerouslySetInnerHTML={{ __html: allResultsTwig({
      ...allResultsData,
      ...ajaxCardGridData,
      ...searchResultsData
  }) }} />
);

