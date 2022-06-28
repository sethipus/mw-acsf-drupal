import React from 'react';
import mediaCarousel from './media-carousel.twig';
import mediaCarouselData from './media-carousel.yml';
import { useEffect } from '@storybook/client-api';
import './media-carousel';

/**
 * Storybook Definition.
 */
export default {
  title: 'Components / [ML 19] Media Carousel',
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
    Title: {
      name: 'Title',
      description: 'Title text for the media carousel.<b> maximum CC : 55</b>',
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
        'Description text for the media carousel.<b> maximum CC : 255</b>',
      table: {
        category: 'Text',
      },
      control: {
        type: 'object',
      },
    },
  },
}; 

export const mediaCarouselModule = ({ theme, Title, Description }) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return (
    <div
      dangerouslySetInnerHTML={{  
        __html: mediaCarousel({
          ...mediaCarouselData,
          theme_styles: theme,
          storybook_media_carousel_heading: Title,
          storybook_media_carousel_items: Description,
        }),
      }}
    />
  );
};

mediaCarouselModule.args = {
  theme: mediaCarouselData.theme_styles,
  Title: mediaCarouselData.storybook_media_carousel_heading,
  Description: mediaCarouselData.storybook_media_carousel_items,
};
