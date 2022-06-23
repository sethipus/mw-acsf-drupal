import React from 'react';
import ReactDOMServer from 'react-dom/server';

import productContentPairUp from './product-content-pair-up.twig';
import productContentPairUpData from './product-content-pair-up.yml';

import productCard from '../card/product-card/product-card.twig';
import productCardData from '../card/product-card/product-card.yml';
import productRating from '../card/product-card/product-rating.yml'

/**
 * Storybook Definition.
 */
export default {
  title: 'Components/[ML 03] Product Content Pair Up',
  parameters: {
    componentSubtitle:
      `A storytelling component that allows for products
      and content such as recipes or articles to be pared
      up side by side. It can be displayed in the following
      pages- Homepage, Landing page, About page, Product page,
      Content hub and Campaign page.`,
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
      name: 'Title text',
      description: 'Title of the layout.<b> Maximum character limit is 100.</b>',
      table: {
        category: 'Text',
      },
      defaultValue: { summary: 'Lorem Ipsum..' },
      control: { type: 'text' },
    },
    EyebrowText: {
      name: 'Eyebrow',
      description: 'Eyebrow of the layout. <b>Maximum character limit is 100.</b>',
      table: {
        category: 'Text',
      },
      defaultValue: { summary: 'Lorem' },
      control: { type: 'text' },
    },
    Background: {
      name: 'Background Image',
      description: 'Background Image of the layout.Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul>',
      table: {
        category: 'Theme',
      },
      defaultValue: {
        summary:
          'http://dove.mars.acsitefactory.com/sites/g/files/fnmzdf186/files/2020-12/Dove%20Home%20Banner%2021-9.PNG',
      },
      control: { type: 'object' },
    },
    card_eyebrow:{
      name:'Eyebrow',
      table: {
        category: 'Product Card Component',
      },
      control:{
        type:'text'
      }
    },
    item:{
      name:'Contents',
      table: {
        category: 'Product Card Component',
      },
      control:{
        type:'object'
      }
    }
  },
};

export const ProductContentWithAritcleCardPairUpLayout = ({
  theme,
  Title,
  EyebrowText,
  Background,
  card_eyebrow,
  item
}) => {
  productContentPairUpData.supporting_card_content = [
    ReactDOMServer.renderToStaticMarkup(
      <div
        dangerouslySetInnerHTML={{
          __html: productCard({ ...productCardData,...productRating,card__eyebrow: card_eyebrow,item:item }),
        }}
      />,
    ),
  ];
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: productContentPairUp({
          ...productContentPairUpData,
          theme_styles: theme,
          lead_card_title: Title,
          lead_card_eyebrow: EyebrowText,
          background: Background,
          card__eyebrow: card_eyebrow,
          item:item
        }),
      }}
    />
  );
};
ProductContentWithAritcleCardPairUpLayout.args = {
  theme: productContentPairUpData.theme_styles,
  Title: productContentPairUpData.lead_card_title,
  EyebrowText: productContentPairUpData.lead_card_eyebrow,
  Background: productContentPairUpData.background,
  card_eyebrow: 'MADE WITH',
  item:productCardData.item
};
