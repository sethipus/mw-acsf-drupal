import React from 'react';
import searchPageHeader from './search-page-header.twig';
import searchPageHeaderData from './search-page-header.yml';
import { useEffect } from '@storybook/client-api';
import '../../01-atoms/search-results-item/search-results-item';

export default {
  title: 'Components/ [ML 32] Search Header',
  parameters: {
    componentSubtitle: `The Search Header is an editable search field at the top of the Search Results Page that includes predictive search terms.
  It is added in the Search page.`,
  },
  argTypes:{
    theme: { 
      name: 'Theme',
      description: 'Theme for the card',
      defaultValue: {
        summary: 'Twix',
      },
      table: {
        category: 'Theme',
      },
      control: {
        type: 'select',
        options: ['twix', 'dove', 'mars', 'galaxy'],
      },
    },
    Title: {
      name: 'Title',
      description: 'Title of the story. <b>Maximum character limit is 55.</b>',
      table: { category: 'Text' },
      control: { type: 'text' },
    },
    searchResults: {
      name: 'Searched Term Title',
      description: 'Title of the story. <b>Maximum character limit is 55.</b>',
      table: { category: 'Text' },
      control: { type: 'object' },
    },
  }
};

export const searchPageHeaderModuleLayout = ({
  theme,
  Title,
  searchResults
}) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: searchPageHeader({
          ...searchPageHeaderData,
          theme_styles:theme,
          search_page_header_heading:Title,
          search_results:searchResults
        }),
      }}
    />
  );
};
searchPageHeaderModuleLayout.args = {
  theme:searchPageHeaderData.theme_styles,
  Title:searchPageHeaderData.search_page_header_heading,
  searchResults:searchPageHeaderData.search_results
}