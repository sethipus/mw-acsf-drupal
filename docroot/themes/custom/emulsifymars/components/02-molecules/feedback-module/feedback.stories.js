import React from 'react';
import { useEffect } from '@storybook/client-api';

import feedback from './feedback.twig';
import feedbackPositive from './feedback-positive.twig'
import feedbackNegative from './feedback-negative.twig'
import feedbackData from './feedback.yml';

import './feedback';

export default { title: 'Molecules/Feedback module' };

export const feedbackInitialState = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: feedback(feedbackData) }} />
};

export const feedbackPositiveState = () => {
  return <div dangerouslySetInnerHTML={{ __html: feedbackPositive(feedbackData) }} />
};

export const feedbackNegativeState = () => {
  return <div dangerouslySetInnerHTML={{ __html: feedbackNegative(feedbackData) }} />
};
