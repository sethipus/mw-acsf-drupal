import React from 'react';
import searchInput from './search-input.twig';
import searchInputData from './search-input.yml';
import { useEffect } from '@storybook/client-api';
import './search-input.js';

// export default { title: 'Molecules/Search input' };

export const searchInputeModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{__html: searchInput(searchInputData)}}/>
};
