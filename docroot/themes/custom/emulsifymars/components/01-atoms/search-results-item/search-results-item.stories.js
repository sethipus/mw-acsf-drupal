import React from 'react';
import { useEffect } from '@storybook/client-api';
import searchResultsItem from './search-results-item.twig';
import searchResultsItemData from './search-results-item.yml';
import './search-results-item';

// export default { title: 'Atoms/Search results item' };

export const searchResultsItemModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{__html: searchResultsItem(searchResultsItemData)}}/>
}
