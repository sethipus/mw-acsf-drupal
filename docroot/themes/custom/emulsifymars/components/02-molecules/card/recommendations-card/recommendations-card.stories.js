import React from 'react';

import card from './recommendations-card.twig';
import cardData from './recommendations-card.yml';

export default {
  title: 'Components/[GE 02] Card Library/Recommendations card',
  decorators: [(Story) => <div style={{padding: '5rem'}}><Story/></div>],
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
    Heading: {
      name: 'Title',
      description: 'Heading of the recipe card.',
      defaultValue: {
        summary: 'Lorem Ipsum..',
      },
      table: {
        category: 'Text',
      },
      control: {
        type: 'text',
      },
    },
    ButtonText: {
      name: 'Button CTA',
      description: 'Button cta of the recipe card.',
      defaultValue: {
        summary: 'Explore',
      },
      table: {
        category: 'Text',
      },
      control: {
        type: 'text',
      },
    },
    BackgroundImage: {
      name: 'Background Image',
      description: 'BgImage of the recipe card.Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul>',
      defaultValue: {
        summary: '',
      },
      table: {
        category: 'Theme',
      },
      control: {
        type: 'object',
      },
    },
    badge: {
      name: 'Badge',
      description: 'True or False for the badge of the recipe card.',
      defaultValue: {
        summary: 'NEW',
      },
      table: {
        category: 'Text',
      },
      control: {
        type: 'boolean',
      },
    },
    BackgroundColor: {
      name: 'Background Color',
      description: 'Background color for the recipe card',
      table: {
        category: 'Theme',
      },
      control: {
        type: 'select',
        options: ['color_c', 'color_d', 'color_e'],
      },
    },
  },
  parameters:{
    componentSubtitle:`Drive to Landing and Campaign Pages. It can be 
                      displayed in the following pages which includes Homepages,
                      Landing pages, Hub pages, Recipe Detail Pages and Search Results.`
  }
};

export const recomendationsCardLayout = ({
  theme,
  Heading,
  ButtonText,
  BackgroundImage,
  badge,
  BackgroundColor
}) => {
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: card({
          ...cardData,
          theme_styles:theme,
          heading: Heading,
          button_text: ButtonText,
          card_image_src: BackgroundImage,
          is_new: badge,
          select_background_color:BackgroundColor
        }),
      }}
    />
  );
};

recomendationsCardLayout.args = {
  theme: cardData.theme_styles,
  Heading: cardData.heading,
  ButtonText: cardData.button_text,
  BackgroundImage: cardData.card_image_src,
  badge: cardData.is_new,
  BackgroundColor:'color_c'
};
