import React from 'react';
import searchPageHeader from './search-page-header.twig';
import searchPageHeaderData from './search-page-header.yml';
import { useEffect } from '@storybook/client-api';
import './search-page-header.js';
import '../../01-atoms/search-results-item/search-results-item';

export default { title: 'Molecules/Serach page header' };

export const searchPageHeaderModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{__html: searchPageHeader(searchPageHeaderData)}}/>
};
