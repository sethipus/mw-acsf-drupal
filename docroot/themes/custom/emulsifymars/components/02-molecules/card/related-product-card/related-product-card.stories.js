import React from 'react';

import card from './related-product-card.twig';

import cardData from './related-product-card.yml';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/Cards/RelatedProductCard' };

export const relatedProductCard = () => (
  <div dangerouslySetInnerHTML={{ __html: card(cardData) }} />
);
