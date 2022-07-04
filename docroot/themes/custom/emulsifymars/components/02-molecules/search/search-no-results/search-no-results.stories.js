import React from 'react';
import searchResultsNoResults from './search-no-results.twig';
import searchResultsNoResultsData from './search-no-results.yml';

/**
 * Storybook Definition.
 */
export default {
  title: 'Components/ [GE 08] Search Overlay / Search with No results',
  argTypes: {
    theme: {
      name: 'Theme',
      description: 'Theme for the card',
      table: {
        category: 'Theme',
      },
      control: {
        type: 'select',
        options: ['twix', 'dove', 'mars', 'galaxy'],
      },
    },
    heading: {
      name: 'Error message',
      table: {
        category: 'Text',
      },
      control: {
        type: 'text',
      },
    },
    re_search: {
      name: 'Suggested Search Re-entry',
      table: {
        category: 'Text',
      },
      control: {
        type: 'text',
      },
    },
    links: {
      name: 'Other links',
      table: {
        category: 'Text',
      },
      control: {
        type: 'object',
      },
    },
  },
};

export const searchResultsNoResultsLayout = ({
  theme,
  heading,
  links,
  re_search,
}) => (
  <div
    dangerouslySetInnerHTML={{
      __html: searchResultsNoResults({
        ...searchResultsNoResultsData,
        theme_styles: theme,
        no_results_heading: heading,
        no_results_text: re_search,
        no_results_links: links,
      }),
    }}
  />
);
searchResultsNoResultsLayout.args = {
  theme: searchResultsNoResultsData.theme_styles,
  heading: searchResultsNoResultsData.no_results_heading,
  re_search: searchResultsNoResultsData.no_results_text,
  links: searchResultsNoResultsData.no_results_links,
};
