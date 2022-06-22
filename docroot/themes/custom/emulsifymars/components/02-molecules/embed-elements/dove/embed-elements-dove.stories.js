import React from 'react';
import { useEffect } from '@storybook/client-api';

import doveKeepInTouchForm from './keep-in-touch/keep-in-touch.twig';
import doveNewsletterSignupForm from './newsletter-signup-form/newsletter-signup-form.twig';
import doveNewsletterSignupFormSuccess from './newsletter-signup-form-success/newsletter-signup-form-success.twig';

import './keep-in-touch/keep-in-touch';
import './newsletter-signup-form/newsletter-signup-form';

/**
 * Storybook Definition.
 */
// export default { title: 'Molecules/Embed Elements/Dove' };

export const doveKeepInTouchFormModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: doveKeepInTouchForm() }} />
};

export const doveNewsletterSignupFormModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: doveNewsletterSignupForm() }} />
};

export const doveNewsletterSignupFormSuccessModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: doveNewsletterSignupFormSuccess() }} />
};
