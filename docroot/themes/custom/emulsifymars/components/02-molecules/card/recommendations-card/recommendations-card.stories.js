import React from 'react';

import card from './recommendations-card.twig';
import cardData from './recommendations-card.yml';

import hubCard from './content-hub-card.twig';
import hubCardData from './recommendations-card.yml';

import landingCard from './landing-card.twig';
import landingCardData from './recommendations-card.yml';

import campaignCard from './campaign-card.twig';
import campaignCardData from './recommendations-card.yml';
import campaignCardMaxLengthData from './recommendations-card-max-length.yml';

export default {
  title: 'Molecules/Cards/Recommendations card',
  decorators: [(Story) => <div style={{padding: '5rem'}}><Story/></div>]
};

export const recomendationsCard = () => {
  return <div dangerouslySetInnerHTML={{ __html: card(cardData) }} />
};

export const contentHubCard = () => {
  return <div dangerouslySetInnerHTML={{ __html: hubCard(hubCardData) }} />
};

export const landingPageCard = () => {
  return <div dangerouslySetInnerHTML={{ __html: landingCard(landingCardData) }} />
}

export const campaignPageCard = () => {
  return <div dangerouslySetInnerHTML={{ __html: campaignCard(campaignCardData) }} />
}

export const contentHubCardMaxLength = () => {
  return <div dangerouslySetInnerHTML={{ __html: campaignCard({...campaignCardData, ...campaignCardMaxLengthData}) }} />
}
