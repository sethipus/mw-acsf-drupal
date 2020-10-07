import React from 'react';

import card from './product-card.twig';

import cardData from './product-card.yml';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/Cards/ProductCard' };

export const productCardDefault = () => (
  <div dangerouslySetInnerHTML={{ __html: card(cardData) }} style={{padding: '5rem'}}/>
);

export const productCardNewProduct = () => (
  <div dangerouslySetInnerHTML={{ __html: card(Object.assign({}, cardData, {recipe_is_new: true})) }} style={{padding: '5rem'}}/>
);

export const productCardImageOverride = () => {
  return <div dangerouslySetInnerHTML={{ __html: card({
    theme_styles: 'twix',
    default_link_content: 'See details',
    link_content: 'BUY NOW2',
    item: {
      card_url: 'https://storybook.js.org/',
      card__image__src: 'image9@3x.png',
      card__image__override__src: 'image-4@3x.png',
      paragraph_content: 'TWIX® PEANUT BUTTER Minis',
      default_link_attributes: {
        target: '_self',
        href: 'https://storybook.js.org/'
      },
      link_url: ''
    }
  }) }} style={{padding: '5rem'}}/>;
};
