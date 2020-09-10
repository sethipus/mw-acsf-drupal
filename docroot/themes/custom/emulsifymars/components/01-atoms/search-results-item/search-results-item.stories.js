import React from 'react';
import searchResultsItem from './search-results-item.twig';
import searchResultsItemData from './search-results-item.yml';

export default { title: 'Atoms/Search results item' };

export const searchResultsItemModule = () => (
  <div dangerouslySetInnerHTML={{ __html: searchResultsItem(searchResultsItemData) }} />
);
