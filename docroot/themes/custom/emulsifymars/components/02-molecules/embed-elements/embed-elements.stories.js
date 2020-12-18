import React from 'react';
import { useEffect } from '@storybook/client-api';

import keepInTouchForm from './keep-in-touch/keep-in-touch.twig';
import newsletterSignupForm from './newsletter-signup-form/newsletter-signup-form.twig';
import newsletterSignupFormSuccess from './newsletter-signup-form-success/newsletter-signup-form-success.twig';

import './keep-in-touch/keep-in-touch';
import './newsletter-signup-form/newsletter-signup-form';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/Embed Elements' };

export const keepInTouchFormModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: keepInTouchForm() }} />
};

export const newsletterSignupFormModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: newsletterSignupForm() }} />
};

export const newsletterSignupFormSuccessModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: newsletterSignupFormSuccess() }} />
};

