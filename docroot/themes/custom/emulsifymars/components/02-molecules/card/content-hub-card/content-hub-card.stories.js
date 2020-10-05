import React from 'react';
import card from './content-hub-card.twig';
import cardData from './content-hub-card.yml';

export default { title: 'Molecules/Cards/Content Hub Card' };

export const contentHubCard = () => (
  <div dangerouslySetInnerHTML={{ __html: card(cardData) }} />
);
