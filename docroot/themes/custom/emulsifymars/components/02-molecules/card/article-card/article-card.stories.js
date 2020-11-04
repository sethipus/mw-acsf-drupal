import React from 'react';
import { useEffect } from '@storybook/client-api';

import articleCard from './article-card.twig';
import articleCardData from './article-card.yml';

import '../cards';

export default { title: 'Molecules/Cards/Article Card' };

export const articleCardNewNoBg = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: articleCard(Object.assign({}, articleCardData, {article_is_new: true, article_card_background_img_url: ''})) }} style={{padding: '5rem'}}/>
};

export const articleCardNewWithBg = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: articleCard(Object.assign({}, articleCardData, {article_is_new: true, article_card_background_img_url: '/content-feature-bg.png'})) }} style={{padding: '5rem'}}/>
};

export const articleCardOldNoBg = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: articleCard(Object.assign({}, articleCardData, {article_is_new: false, article_card_background_img_url: ''})) }} style={{padding: '5rem'}}/>
};

export const articleCardOldWithBg = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: articleCard(Object.assign({}, articleCardData, {article_is_new: false, article_card_background_img_url: '/content-feature-bg.png'})) }} style={{padding: '5rem'}}/>
};
