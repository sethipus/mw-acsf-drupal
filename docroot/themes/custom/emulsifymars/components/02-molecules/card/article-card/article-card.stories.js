import React from 'react';

import articleCard from './article-card.twig';
import articleCardData from './article-card.yml';

export default {
  title: 'Components/[GE 02] Card Library/Article Card',
  decorators: [
    Story => (
      <div style={{ padding: '5rem' }}>
        <Story />
      </div>
    ),
  ],
  argTypes: {
    theme:{
      name:'Theme',
      description:'Theme of the card',
      table:{
        category:'Theme'
      },
      control:{
        type:'select', options:['twix','mars','galaxy','dove']
      }
    },
    articleCardHeading: {
      name: 'Title',
      description: 'Heading of the card',
      defaultValue: {
        summary: 'Delicious Chocolates',
      },
      table: {
        category: 'Text',
      },
      control: {
        type: 'text',
      },
    },
    articleCardText: {
      name: 'Description',
      description: 'Paragraph text of the card',
      defaultValue: {
        summary: 'Delicious Chocolates....',
      },
      table: {
        category: 'Text',
      },
      control: {
        type: 'text',
      },
    },
    buttonText: {
      name: 'Button CTA',
      description: 'Button text of the card button',
      defaultValue: {
        summary: 'EXPLORE',
      },
      table: {
        category: 'Text',
      },
      control: { type: 'text' },
    },
    badgeText: {
      name: 'Badge Text',
      description: 'Content of the badge',
      defaultValue: {
        summary: 'NEW',
      },
      table: {
        category: 'Text',
      },
      control: { type: 'text' },
    },
    badge: {
      name: 'Badge Toggle',
      description: 'True or false for the badge',
      defaultValue: {
        summary: 'true',
      },
      table: {
        category: 'Theme',
      },
      control: {
        type: 'boolean',
      },
    },
    backgroundImage: {
      name: 'Background Image',
      description: 'Change the background image of the card',
      table: {
        category: 'Theme',
      },
      defaultValue: {
        summary:
          'http://dove.mars.acsitefactory.com/sites/g/files/fnmzdf186/files/2020-12/Dove%20Home%20Banner%2021-9.PNG',
      },
      control: {
        type: 'object',
      },
    },
    backgroundColor: {
      name: 'Theme Background Color',
      description: 'Background Color for the specific theme',
      defaultValue: {
        summary: 'color_c',
      },
      table: {
        category: 'Theme',
      },
      control: {
        type: 'select',
        options: ['color_c', 'color_d', 'color_e'],
      },
    },
    eyebrow:{
      name: 'Eyebrow',
      description: 'Article card eyebrow',
      defaultValue: {
        summary: 'lorem..',
      },
      table: {
        category: 'Text',
      },
      control: {
        type: 'text',
      },
    }
  },
  parameters:{
    componentSubtitle:`Cards used throughout the site that drive to products, recipes, articles, landing and campaign pages (and sometimes even hubs in very specific instances).
    Powered dynamically by the page(content type) that it drives to (eg. Product Cards are powered by Product Detail Pages)
    Card Grids and Content Modules live on: Homepages, Landing pages, Hub pages, Recipe Detail Pages
    and Search Results`
  }
};

export const ArticleCardLayout = ({
  theme,
  articleCardHeading,
  articleCardText,
  buttonText,
  badgeText,
  badge,
  backgroundImage,
  backgroundColor,
  eyebrow
}) => {
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: articleCard(
          Object.assign({}, articleCardData, {
            theme_styles:theme,
            article_is_new: badge,
            article_card_background_img_url: backgroundImage,
            article_card_heading: articleCardHeading,
            article_card_text: articleCardText,
            article_card_button_text: buttonText,
            badge_text: badgeText,
            select_background_color: backgroundColor,
            article_card_eyebrow:eyebrow
          }),
        ),
      }}
    />
  );
};

ArticleCardLayout.args = {
  theme:articleCardData.theme_styles,
  articleCardHeading: articleCardData.article_card_heading,
  articleCardText: articleCardData.article_card_text,
  buttonText: articleCardData.article_card_button_text,
  badgeText: articleCardData.badge_text,
  badge: articleCardData.article_is_new,
  backgroundColor: articleCardData.select_background_color,
  backgroundImage: articleCardData.article_card_background_img_url,
  eyebrow:articleCardData.article_card_eyebrow
};
