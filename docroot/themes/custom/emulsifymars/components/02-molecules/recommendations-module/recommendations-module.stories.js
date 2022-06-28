import React from 'react';
import ReactDOMServer from 'react-dom/server';

import recommendations from './recommendations-module.twig';
import recommendationsData from './recommendations-module.yml';
import { useEffect } from '@storybook/client-api';

import { recipeCardLayout } from '../../02-molecules/card/recipe-card/recipe-card.stories';
export default { title: 'Components/[ML 21] Recommendations Module' };

export const recommendationsModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  recommendationsData.recommended_items = [
    ReactDOMServer.renderToStaticMarkup(
      recipeCardLayout({
        theme: 'twix',
        Heading: 'Dove caramel soft bread pudding',
        CookingTime: '35',
        IngridentsItems: '10',
        ButtonText: 'BAKE IT',
        Bagde: false,
        BadgeText: 'NEW',
        BackgroundImage: 'Recipe_Image.png',
      }),
    ),
    ReactDOMServer.renderToStaticMarkup(
      recipeCardLayout({
        theme: 'twix',
        Heading: 'Dove caramel soft bread pudding',
        CookingTime: '35',
        IngridentsItems: '10',
        ButtonText: 'BAKE IT',
        Bagde: false,
        BadgeText: 'NEW',
        BackgroundImage: 'Recipe_Image.png',
      }),
    ),
    ReactDOMServer.renderToStaticMarkup(
      recipeCardLayout({
        theme: 'twix',
        Heading: 'Dove caramel soft bread pudding',
        CookingTime: '35',
        IngridentsItems: '10',
        ButtonText: 'BAKE IT',
        Bagde: false,
        BadgeText: 'NEW',
        BackgroundImage: 'Recipe_Image.png',
      }),
    ),
    ReactDOMServer.renderToStaticMarkup(
      recipeCardLayout({
        theme: 'twix',
        Heading: 'Dove caramel soft bread pudding',
        CookingTime: '35',
        IngridentsItems: '10',
        ButtonText: 'BAKE IT',
        Bagde: false,
        BadgeText: 'NEW',
        BackgroundImage: 'Recipe_Image.png',
      }),
    ),
    ReactDOMServer.renderToStaticMarkup(
      recipeCardLayout({
        theme: 'twix',
        Heading: 'Dove caramel soft bread pudding',
        CookingTime: '35',
        IngridentsItems: '10',
        ButtonText: 'BAKE IT',
        Bagde: false,
        BadgeText: 'NEW',
        BackgroundImage: 'Recipe_Image.png',
      }),
    ),
    ReactDOMServer.renderToStaticMarkup(
      recipeCardLayout({
        theme: 'twix',
        Heading: 'Dove caramel soft bread pudding',
        CookingTime: '35',
        IngridentsItems: '10',
        ButtonText: 'BAKE IT',
        Bagde: false,
        BadgeText: 'NEW',
        BackgroundImage: 'Recipe_Image.png',
      }),
    ),
  ];
  return (
    <div
      dangerouslySetInnerHTML={{
        __html:
          recommendations(recommendationsData),
      }}
    />
  );
};
