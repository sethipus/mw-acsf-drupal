import React from 'react';
import ReactDOMServer from 'react-dom/server';
import { useEffect } from '@storybook/client-api';

import './recommendations-module';
import recommendations from './recommendations-module.twig';
import recommendationsData from './recommendations-module.yml';

import { recipeCardLayout } from '../../02-molecules/card/recipe-card/recipe-card.stories';

export default {
  title: 'Components/[ML 21] Recommendations Module',
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
      table: {
        category: 'Text',
      },
      description:
        'Title for the recommendation module. <b>Maximum character limit is 55.</b>',
      control: { type: 'text' },
    },
  },
};

export const recommendationsModule = ({ theme, title }) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  recommendationsData.recommended_items = [
    ReactDOMServer.renderToStaticMarkup(
      recipeCardLayout({
        theme: 'twix',
        Heading: 'TWIX® Banana Split Cake',
        CookingTime: '2 hours',
        IngridentsItems: '15',
        ButtonText: 'SEE DETAILS',
        Bagde: false,
        BadgeText: 'NEW',
        BackgroundImage: 'https://www.twix.com/cdn-cgi/image/width=318,height=239,fit=cover,g=0.5x0.5,f=auto,quality=90/sites/g/files/fnmzdf236/files/2021-02/Twix-Recipe-BananaSplitCake-21x9.jpg',
      }),
    ),
    ReactDOMServer.renderToStaticMarkup(
      recipeCardLayout({
        theme: 'twix',
        Heading: 'TWIX Touchdown Trifle Cups ',
        CookingTime: '50 mins',
        IngridentsItems: '7',
        ButtonText: 'SEE DETAILS',
        Bagde: false,
        BadgeText: 'NEW',
        BackgroundImage: 'https://lhcdn-src.mars.com/cdn-cgi/image/width=318,height=239,fit=cover,g=0.5x0.5,f=auto,quality=90/adaptivemedia/rendition/id_5cbf0d5245ff39e7fb6d5a11d30034cbc9648aad/name_5cbf0d5245ff39e7fb6d5a11d30034cbc9648aad.jpg',
      }),
    ),
    ReactDOMServer.renderToStaticMarkup(
      recipeCardLayout({
        theme: 'twix',
        Heading: 'TWIX® Coffee Frappé',
        CookingTime: '10min',
        IngridentsItems: '5',
        ButtonText: 'SEE DETAILS',
        Bagde: false,
        BadgeText: 'NEW',
        BackgroundImage: 'https://www.twix.com/cdn-cgi/image/width=318,height=239,fit=cover,g=0.5x0.5,f=auto,quality=90/sites/g/files/fnmzdf236/files/2021-02/Twix-Recipe-CoffeeFrappe-21x9.jpg',
      }),
    ),
    ReactDOMServer.renderToStaticMarkup(
      recipeCardLayout({
        theme: 'twix',
        Heading: 'TWIX® Cookies & Creme Mug Cakes',
        CookingTime: '1 hours',
        IngridentsItems: '11',
        ButtonText: 'SEE DETAILS',
        Bagde: false,
        BadgeText: 'NEW',
        BackgroundImage: 'https://www.twix.com/cdn-cgi/image/width=318,height=239,fit=cover,g=0.5x0.5,f=auto,quality=90/sites/g/files/fnmzdf236/files/2021-02/Twix-Recipe-MadeWithTwix-21x9.jpg',
      }),
    ),
    ReactDOMServer.renderToStaticMarkup(
      recipeCardLayout({
        theme: 'twix',
        Heading: 'TWIX® Granola Bars',
        CookingTime: '2 hours',
        IngridentsItems: '13',
        ButtonText: 'SEE DETAILS',
        Bagde: false,
        BadgeText: 'NEW',
        BackgroundImage: 'https://www.twix.com/cdn-cgi/image/width=318,height=239,fit=cover,g=0.5x0.5,f=auto,quality=90/sites/g/files/fnmzdf236/files/2021-02/Twix-Recipe-GranolaBars-21x9.jpg',
      }),
    ),
    ReactDOMServer.renderToStaticMarkup(
      recipeCardLayout({
        theme: 'twix',
        Heading: 'Dove caramel soft bread pudding',
        CookingTime: '35',
        IngridentsItems: '10',
        ButtonText: 'SEE DETAILS',
        Bagde: false,
        BadgeText: 'NEW',
        BackgroundImage: 'Recipe_Image.png',
      }),
    ),
  ];
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: recommendations({
          ...recommendationsData,
          theme_styles: theme,
          recommendation_title: title,
        }),
      }}
    />
  );
};
recommendationsModule.args = {
  theme: recommendationsData.theme_styles,
  title: recommendationsData.recommendation_title,
};
