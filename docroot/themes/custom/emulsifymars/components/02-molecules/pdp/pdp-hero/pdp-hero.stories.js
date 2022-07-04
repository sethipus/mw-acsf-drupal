import React from 'react';
import { useEffect } from '@storybook/client-api';

import pdpHero from './pdp-hero.twig';
import pdpHeroData from './pdp-hero.yml';

export default {
  title: 'Components/[ML 23] Product Detail Hero/ PDP Hero',
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
    Content: {
      name: 'Content',
      description:
        'Eyebrow of the PDP page -<b> maximum character limit is 15.</b>.Product name - <b> maximum character limit is 60.</b> Product description- <b> maximum character limit is 300 . </b>',
      defaultValue: { summary: 'LOREM' },
      table: {
        category: 'Text',
      },
      control: { type: 'object' },
    },
    images: {
      name: 'Images',
      description:
        'Up to 5 Images can be added. Images should include: 1. Key Product Pack Image, 2. Product Open Pack Image, 3. Product Outside of Pack/No Pack Image, 4. & 5. Additional Product images if available. For images Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul>',
      table: {
        category: 'Text',
      },
      control: {
        type: 'object',
      },
    },
    sizes: {
      name: 'Available sizes of product',
      description: 'List down all the sizes of the product - <b>CC: 20 Max</b>',
      table: {
        category: 'Text',
      },
      control: {
        type: 'object',
      },
    },
  },
};

export const pdpHeroModuleLayout = ({ theme, Content, images, sizes }) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: pdpHero({
          ...pdpHeroData,
          theme_styles: theme,
          pdp_common_hero_data: Content,
          pdp_hero_data: images,
          pdp_size_items_data: sizes,
        }),
      }}
    />
  );
};
pdpHeroModuleLayout.args = {
  theme: pdpHeroData.theme_styles,
  Content: pdpHeroData.pdp_common_hero_data,
  images: pdpHeroData.pdp_hero_data,
  sizes: pdpHeroData.pdp_size_items_data,
};
