import React from 'react';
import { useEffect } from '@storybook/client-api';

import cardData from './product-card.yml';
import card from './product-card.twig';

import './product-card';

/**
 * Storybook Definition.
 */
export default {
  title: 'Components/[GE 02] Card Library/ProductCard',
  decorators: [
    Story => (
      <div style={{ padding: '5rem' }}>
        <Story />
      </div>
    ),
  ],
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
    item: {
      name: 'Content',
      description:
        'Content of the card.Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul>',
      table: {
        category: 'Text',
      },
      control: { type: 'object' },
    },
    buttonText: {
      name: 'Button CTA',
      description: 'Button text of the card.',
      defaultValue: {
        summary: 'Explore',
      },
      table: {
        category: 'Text',
      },
      control: { type: 'text' },
    },
    badgeText: {
      name: 'Badge CTA',
      description: 'Badge text of the card.',
      defaultValue: {
        summary: 'NEW',
      },
      table: {
        category: 'Text',
      },
      control: { type: 'text' },
    },
    badge: {
      name: 'Badge Toogle',
      description: 'True or False for the badge.',
      defaultValue: {
        summary: 'True',
      },
      table: {
        category: 'Theme',
      },
      control: { type: 'boolean' },
    },
    eyebrow: {
      name: 'Eyebrow',
      description: 'Eyebrow text for the card',
      defaultValue: {
        summary: 'lorem',
      },
      table: {
        category: 'Text',
      },
      control: { type: 'text' },
    },
  },
  parameters: {
    componentSubtitle: `Product Detail cards drive to both product detail pages and
      launch the Where To Buy Overlay experience, as well as display
      star ratings. It can be displayed in the following pages which
      includes Homepages, Landing pages, Hub pages, Recipe Detail Pages
      and Search Results.`,
  },
};

export const productCardLayout = ({
  theme,
  item,
  buttonText,
  badgeText,
  badge,
  rating,
  eyebrow,
}) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: card({
          ...cardData,
          theme_styles: theme,
          item,
          default_link_content: buttonText,
          badge_text: badgeText,
          is_new: badge,
          card__eyebrow: eyebrow,
        }),
      }}
    />
  );
};

productCardLayout.args = {
  theme: cardData.theme_styles,
  item: cardData.item,
  buttonText: cardData.default_link_content,
  badgeText: cardData.badge_text,
  badge: cardData.recipe_is_new,
  eyebrow: cardData.card__eyebrow,
};
