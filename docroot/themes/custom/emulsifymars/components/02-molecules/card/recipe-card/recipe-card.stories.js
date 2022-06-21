import React from 'react';

import recipeCard from './recipe-card.twig';
import recipeCardData from './recipe-card.yml';
import { useEffect } from '@storybook/client-api';
import recipeCardMaxLengthData from './recipe-card-max-length.yml';
import recipeCardPairUpData from './recipe-card-content-pair-up.yml';

import './recipe-card';

export default {
  title: 'Components/[GE 02] Card Library/Recipe Card',
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
    Heading: {
      name: 'Title',
      description: 'Heading of the card.',
      defaultValue: {
        summary: 'Lorem...',
      },
      table: {
        category: 'Text',
      },
      control: { type: 'text' },
    },
    CookingTime: {
      name: 'Cooking Time',
      description: 'Cooking time of the recipe.',
      defaultValue: {
        summary: '35mins',
      },
      table: {
        category: 'Text',
      },
      control: { type: 'number' },
    },
    IngridentsItems: {
      name: 'Ingredients amount',
      description: 'Ingridents recquired for the recipe.',
      defaultValue: {
        summary: '10 items',
      },
      table: {
        category: 'Text',
      },
      control: { type: 'number' },
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
      control: { type: 'text' },
    },
    Bagde: {
      name: 'New Badge',
      description: 'True or False for badge',
      defaultValue: {
        summary: 'True',
      },
      table: {
        category: 'Theme',
      },
      control: { type: 'boolean' },
    },
    BadgeText: {
      name: 'Badge CTA',
      description: 'Badge cta of the recipe card.',
      defaultValue: {
        summary: 'NEW',
      },
      table: {
        category: 'Text',
      },
      control: { type: 'text' },
    },
    BackgroundImage: {
      name: 'Background Image',
      description:
        'Change the bgImage of the recipe card.Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul>',
      table: {
        category: 'Theme',
      },
      control: { type: 'object' },
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
  parameters: {
    componentSubtitle: ` Drive to Recipe Detail Pages and can
                        display star ratings. It can be displayed
                        in the following pages which includes Homepages,
                        Landing pages, Hub pages, Recipe Detail Pages and
                        Search Results.`,
  },
};

export const recipeCardLayout = ({
  theme,
  Heading,
  CookingTime,
  IngridentsItems,
  ButtonText,
  Bagde,
  BadgeText,
  BackgroundImage,
  BackgroundColor,
}) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: recipeCard({
          ...recipeCardData,
          theme_styles: theme,
          recipe_card_heading: Heading,
          recipe_card_minutes_number: CookingTime,
          recipe_card_items_number: IngridentsItems,
          recipe_card_button_text: ButtonText,
          recipe_is_new: Bagde,
          badge_text: BadgeText,
          recipe_card_image_src: BackgroundImage,
          select_background_color: BackgroundColor,
        }),
      }}
    />
  );
};

recipeCardLayout.args = {
  theme: recipeCardData.theme_styles,
  Heading: recipeCardData.recipe_card_heading,
  ButtonText: recipeCardData.recipe_card_button_text,
  Bagde: recipeCardData.recipe_is_new,
  BadgeText: recipeCardData.badge_text,
  BackgroundImage: recipeCardData.recipe_card_image_src,
  CookingTime: recipeCardData.recipe_card_minutes_number,
  IngridentsItems: recipeCardData.recipe_card_items_number,
  BackgroundColor: recipeCardData.select_background_color,
};
