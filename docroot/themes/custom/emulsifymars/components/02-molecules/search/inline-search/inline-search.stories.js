import React from 'react';
import searchInline from './inline-search.twig';
import searchInlineData from './inline-search.yml';

import { useEffect } from '@storybook/client-api';

import '../search-overlay/search-overlay';

/**
 * Storybook Definition.
 */
export default {
  title: 'Components/ [GE 08] Search Overlay / Inline Search ',
  parameters:{
    componentSubtitle:`The global search overlay is accessed from
     the navigation, and is triggered once the user clicks into the
      search bar and starts typing.`
  },
  argTypes: {
    search_text: {
      name: 'Search Field',
      control: {
        type: 'text',
      },
    },
  },
};

export const inlineSearch = ({ search_text }) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: searchInline({
          ...searchInlineData,
          inline_search_title: search_text,
        }),
      }}
    />
  );
};
inlineSearch.args = {
    search_text:searchInlineData.inline_search_title
}