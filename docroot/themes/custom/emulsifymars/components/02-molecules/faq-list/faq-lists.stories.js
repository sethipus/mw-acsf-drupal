import React from 'react';

import faqList from './faq-list.twig';

import faqListData from './faq-list.yml';

/**
 * Storybook Definition.
 */
export default {
  title: 'Components/ [ML 28] FAQs',
  parameters: {
    componentSubtitle: `A list of popular questions and answers
   created and curated by editors and organized by date added 
   with search and common topic filters. Anytime the user selects
   a topic filter, the URL should update to reflect that selection
   (so that URL can be shared and drive to a prefiltered version of the FAQ page).
   It can be added to the following page - Contact & Help and Campaign Page.`,
  },
  argTypes: {
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
    title: {
      name: 'FAQ Heading',
      description: '<b> Maximum character limit is 55 </b>',
      table: {
        category: 'Text',
      },
      control: {
        type: 'text',
      },
    },
    searchLinks: {
      name: 'Common Topic Filters',
      description:
        'Editors have the ability to create, add, reorder and delete common topic filters. Max filters, <b>10. Max CC for each filter</b>',
      table: {
        category: 'Text',
      },
      control: {
        type: 'object',
      },
    },
    faqLists: {
      name: 'QA Blurbs',
      description: `Editors author can add or remove the Q/A.`,
      table: {
        category: 'Text',
      },
      control: {
        type: 'object',
      },
    },
  },
};

export const faqLayout = ({ theme, title, searchLinks, faqLists }) => (
  <div
    dangerouslySetInnerHTML={{
      __html: faqList({
        ...faqListData,
        theme_styles: theme,
        title: title,
        facetLinks: searchLinks,
        faq_items: faqLists,
      }),
    }}
  />
);
faqLayout.args = {
  theme: faqListData.theme_styles,
  title: faqListData.title,
  searchLinks: faqListData.facetLinks,
  faqLists: faqListData.faq_items,
};
