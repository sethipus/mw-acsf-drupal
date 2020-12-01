import React from 'react';

import articleCard from './article-card.twig';
import articleCardData from './article-card.yml';


export default {
  title: 'Molecules/Cards/Article Card',
  decorators:  [(Story) => <div style={{ padding: '5rem' }}><Story/></div>]
};

export const articleCardNewNoBg = () => {
  return <div dangerouslySetInnerHTML={{ __html: articleCard(Object.assign({}, articleCardData, {article_is_new: true, article_card_background_img_url: ''})) }}/>
};

export const articleCardNewWithBg = () => {
  return <div dangerouslySetInnerHTML={{ __html: articleCard(Object.assign({}, articleCardData, {article_is_new: true, article_card_background_img_url: '/content-feature-bg.png'})) }}/>
};

export const articleCardOldNoBg = () => {
  return <div dangerouslySetInnerHTML={{ __html: articleCard(Object.assign({}, articleCardData, {article_is_new: false, article_card_background_img_url: ''})) }}/>
};

export const articleCardOldWithBg = () => {
  return <div dangerouslySetInnerHTML={{ __html: articleCard(Object.assign({}, articleCardData, {article_is_new: false, article_card_background_img_url: '/content-feature-bg.png'})) }}/>
};
