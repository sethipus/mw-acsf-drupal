import React from 'react';

import noResultsTwig from './no-results-page.twig';
import noResultsData from './no-results.yml';

/**
 * Storybook Definition.
 */
// export default { title: 'Organisms/Search' };

export const noResultsTemplate = () => (
  <div dangerouslySetInnerHTML={{ __html: noResultsTwig({ ...noResultsData }) }} />
);

