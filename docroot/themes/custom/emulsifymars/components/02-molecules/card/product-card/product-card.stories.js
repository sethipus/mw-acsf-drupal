import React from 'react';

import card from './product-card.twig';

import cardData from './product-card.yml';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/Cards/ProductCard' };

export const productCard = () => (
  <div dangerouslySetInnerHTML={{ __html: card(cardData) }} />
);
