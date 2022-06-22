import React from 'react';
import searchResultsCards from './search-results-cards/search-results-cards.twig';
import searchResultsCardsData from './search-results-cards/search-results-cards.yml';
import searchResultsNoResults from './search-no-results/search-no-results.twig';
import searchResultsNoResultsData from './search-no-results/search-no-results.yml';

/**
 * Storybook Definition.
 */

// export default { title: 'Molecules/Search' };

export const searchResultsCardsExample = () => (
  <div dangerouslySetInnerHTML={{ __html: searchResultsCards(searchResultsCardsData) }} />
);

export const searchResultsNoResultsExample = () => (
  <div dangerouslySetInnerHTML={{ __html: searchResultsNoResults(searchResultsNoResultsData) }} />
);
