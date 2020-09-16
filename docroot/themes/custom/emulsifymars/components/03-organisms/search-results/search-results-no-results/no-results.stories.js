import React from 'react';

import noResultsTwig from './no-results.twig';
import noResultsData from './no-results.yml';

/**
 * Storybook Definition.
 */
export default { title: 'Organisms/Search Results/No results' };

export const noResultsTemplate = () => (
  <div dangerouslySetInnerHTML={{ __html: noResultsTwig({ ...noResultsData }) }} />
);

