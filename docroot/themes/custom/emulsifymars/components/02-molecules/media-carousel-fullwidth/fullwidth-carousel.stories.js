import React from 'react';
import mediaCarouselFullWidth from './media-carousel-fullwidth.twig';
import mediaCarouselFullWidthData from './media-carousel-fullwidth.yml';
import { useEffect } from '@storybook/client-api';

export default {
  title: 'Components /[ML 34] Fullwidth Media Carousel',
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
    Title: {
      name: 'Title',
      description: 'Title text for the fullwidth media carousel.<b> maximum CC : 55</b>',
      table: {
        category: 'Text',
      },
      control: {
        type: 'text',
      },
    },
    Description: {
      name: 'Image/Video Description',
      description:
        'Description text for the fullwidth media carousel.<b> maximum CC : 255</b>',
      table: {
        category: 'Text',
      },
      control: {
        type: 'object',
      },
    },
  },
};

export const mediaCarouselFullWidthModule = ({ theme, Title, Description }) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: mediaCarouselFullWidth({
          ...mediaCarouselFullWidthData,
          theme_styles: theme,
          storybook_fullwidth_media_carousel_heading: Title,
          storybook_fullwidth_media_carousel_items: Description,
        }),
      }}
    />
  );
};

mediaCarouselFullWidthModule.args = {
  theme: mediaCarouselFullWidthData.theme_styles,
  Title: mediaCarouselFullWidthData.storybook_fullwidth_media_carousel_heading,
  Description: mediaCarouselFullWidthData.storybook_fullwidth_media_carousel_items,
};
