import React from 'react';
import articleHeaderImage from './article-header-image.twig';
import articleHeaderImageData from './article-header-image.yml';
import articleHeaderNoImage from './article-header-noimage.twig';
import articleHeaderNoImageData from './article-header-noimage.yml';
import iconsSocial from '../../02-molecules/menus/social/social-menu.yml';

export default {
  title: 'Components/ [ML 25] Article Header',
  parameters: {
    componentSubtitle: `A header component placed at the top of an 
                        article that provides the title, heading, share 
                        actions, and an optional background image. It can 
                        be added in the following pages - article and campaign page.`,
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
    backgroundImage: {
      name: 'Background Image',
      description: 'Background Image for the article header',
      table: {
        category: 'Theme',
      },
      control: {
        type: 'object',
      },
    },
    eyebrow: {
      name: 'Manual Eyebrow',
      description:
        'Eyebrow text for the article header.<b> maximum CC : 15</b>',
      table: {
        category: 'Text',
      },
      control: {
        type: 'text',
      },
    },
    Title: {
      name: 'Title',
      description: 'Title text for the article header.<b> maximum CC : 60</b>',
      table: {
        category: 'Text',
      },
      control: {
        type: 'text',
      },
    },
    PublishDate: {
      name: 'Publish Date',
      description:
        'Dynamic follows format: "Published [Shortened month eg. Jun][date of month][year]',
      table: {
        category: 'Text',
      },
      control: {
        type: 'text',
      },
    },
  },
};

export const articleHeaderWithImageLayout = ({
  theme,
  backgroundImage,
  eyebrow,
  Title,
  PublishDate
}) => (
  <div
    dangerouslySetInnerHTML={{
      __html: articleHeaderImage({
        ...articleHeaderImageData,
        ...iconsSocial,
        theme_styles:theme,
        images:backgroundImage,
        eyebrow:eyebrow,
        heading:Title,
        date_published:PublishDate
      }),
    }}
  />
);
articleHeaderWithImageLayout.args = {
  theme:articleHeaderImageData.theme_styles,
  backgroundImage:articleHeaderImageData.images,
  eyebrow:articleHeaderImageData.eyebrow,
  Title:articleHeaderImageData.heading,
  PublishDate:articleHeaderImageData.date_published
}
export const articleHeaderWithNoImageLayout = ({
  theme,
  eyebrow,
  Title,
  PublishDate
}) => (
  <div
    dangerouslySetInnerHTML={{
      __html: articleHeaderNoImage({
        ...articleHeaderNoImageData,
        ...iconsSocial,
        theme_styles:theme,
        eyebrow:eyebrow,
        heading:Title,
        date_published:PublishDate
      }),
    }}
  />
);
articleHeaderWithNoImageLayout.args = {
  theme:articleHeaderNoImageData.theme_styles,
  eyebrow:articleHeaderNoImageData.eyebrow,
  Title:articleHeaderNoImageData.heading,
  PublishDate:articleHeaderNoImageData.date_published,
  backgroundImage:'Not applicable'
}