import React from 'react';

import articleWysiwygTwig from './article-wysiwyg.twig';
import articleWysiwygData from './article-wysiwyg.yml';

export default {
  title: 'Components/[ML 30] WSYWIG',
  parameters: {
    componentSubtitle: `Can be added on articles and select other pages 
                       to create moments of storytelling with body copy
                       and various text styles. Can be centered or left-right
                       aligned via html tags. Block position on Article and
                       Recipe detail pages on body sections is left aligned.
                       It can be added to the following pages - homepage,landing
                       page, about page,product detail,recipe detail,article and 
                       campaign page.`,
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
    Header: {
      name: 'Header',
      description: 'Header text',
      defaultValue: {
        summary: 'Lorem Ipsum...',
      },
      table: { category: 'Text' },
      control: { type: 'text' },
    },
    body: {
      name: 'Body Text',
      description: 'Header text',
      defaultValue: {
        summary: 'Lorem Ipsum...',
      },
      table: { category: 'Text' },
      control: { type: 'text' },
    },
  },
};
export const WYSIWYGLayout = ({ theme, Header, body }) => {
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: articleWysiwygTwig({
          ...articleWysiwygData,
          theme_styles: theme,
          heading: Header,
          content: body,
        }),
      }}
    />
  );
};
WYSIWYGLayout.args = {
  theme: articleWysiwygData.theme_styles,
  Header: articleWysiwygData.heading,
  body: articleWysiwygData.content,
};
