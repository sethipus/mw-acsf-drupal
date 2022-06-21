import React from 'react';

import articleListTwig from './article-list.twig';
import articleListData from './article-list.yml';

export default {
  title: 'Components/ [ML 20] List',
  parameters: {
    componentSubtitle: `Must support -  1. Text only items 2. Text and image items
    There is no max to the number of items the editor can add to a list. It can be added
    to the following pages - About page, product detail, recipe Detail, article and campaign page.`,
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
      name: 'Title',
      description: 'Title',
      defaultValue: {
        summary: 'Lorem Ipsum...',
      },
      table: { category: 'Text' },
      control: { type: 'text' },
    },
    Content: {
      name: 'Content',
      description: 'Maximum number of point that can be added is <b> 9 </b> . List image should be of ratio <b> 16X9 </b>',
      defaultValue: {
        summary: 'Lorem Ipsum...',
      },
      table: { category: 'Text' },
      control: { type: 'object' },
    },
  },
};
export const ListLayout = ({ theme, title, Content }) => {
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: 
        "<div style='height: 300px; background-color: grey'></div>" +
        articleListTwig({
          ...articleListData,
          theme_styles: theme,
          title: title,
          takeaways_list: Content,
        }),
      }}
    />
  );
};
ListLayout.args = {
  theme: articleListData.theme_styles,
  title: articleListData.title,
  Content: articleListData.takeaways_list,
};
