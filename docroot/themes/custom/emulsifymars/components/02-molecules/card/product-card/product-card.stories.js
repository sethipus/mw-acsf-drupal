import React from 'react';
import card from './product-card.twig';
import { useEffect } from '@storybook/client-api';
import cardData from './product-card.yml';
import contentPairUpData from './product-in-pair-up.yml'

import './product-card';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/Cards/ProductCard' };

export const productCardDefault = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: card(cardData) }} style={{padding: '5rem'}}/>
};

export const productCardNewProduct = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: card(Object.assign({}, cardData, {is_new: true})) }} style={{padding: '5rem'}}/>
}

export const productCardContentPairUp = () => {
  return <div dangerouslySetInnerHTML={{ __html: card(Object.assign({}, cardData, contentPairUpData)) }} style={{padding: '5rem'}}/>
}

export const productCardImageOverride = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: card({
    theme_styles: 'twix',
    default_link_content: 'See details',
    link_content: 'BUY NOW2',
    item: {
      card_url: 'https://storybook.js.org/',
      card__image__src: 'image9@3x.png',
      card__image__hover__src: 'image-4@3x.png',
      paragraph_content: 'TWIX® PEANUT BUTTER Minis',
      default_link_attributes: {
        target: '_self',
        href: 'https://storybook.js.org/'
      },
      link_url: ''
    }
  }) }} style={{padding: '5rem'}}/>;
};
