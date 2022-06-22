import React from 'react';
import searchInline from './inline-search/inline-search.twig';
import searchInlineData from './inline-search/inline-search.yml';
import searchOverlay from './search-overlay/search-overlay.twig';
import searchOverlayData from './search-overlay/search-overlay.yml';
import { useEffect } from '@storybook/client-api';

import './search-overlay/search-overlay';

/**
 * Storybook Definition.
 */
// export default { title: 'Molecules/Search' };

export const inlineSearch = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: searchInline(searchInlineData) }} />
};
export const searchOverlayBlock = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: searchOverlay(searchOverlayData) }} />
};
