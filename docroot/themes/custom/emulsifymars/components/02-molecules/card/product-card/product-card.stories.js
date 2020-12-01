import React from 'react';
import card from './product-card.twig';
import { useEffect } from '@storybook/client-api';
import cardData from './product-card.yml';
import contentPairUpData from './product-in-pair-up.yml'

import './product-card';

/**
 * Storybook Definition.
 */
export default {
  title: 'Molecules/Cards/ProductCard',
  decorators:  [(Story) => <div style={{ padding: '5rem' }}><Story/></div>]
};

export const productCardDefault = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: card(cardData) }}/>
};

export const productCardNewProduct = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: card(Object.assign({}, cardData, {is_new: true})) }}/>
}

export const productCardContentPairUp = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: card(Object.assign({}, cardData, contentPairUpData)) }}/>
}

export const productCardImageOverride = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  cardData.item.card__image__src = 'image9@3x.png';
  cardData.item.card__image__hover__src = 'image-4@3x.png';
  return <div dangerouslySetInnerHTML={{ __html: card(cardData) }}/>;
};
