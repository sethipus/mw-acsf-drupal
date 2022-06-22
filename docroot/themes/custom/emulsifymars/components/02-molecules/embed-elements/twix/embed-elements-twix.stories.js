import React from 'react';
import { useEffect } from '@storybook/client-api';

import twixKeepInTouchForm from './keep-in-touch/keep-in-touch.twig';
import twixNewsletterSignupForm from './newsletter-signup-form/newsletter-signup-form.twig';
import twixNewsletterSignupFormSuccess from './newsletter-signup-form-success/newsletter-signup-form-success.twig';

import './keep-in-touch/keep-in-touch';
import './newsletter-signup-form/newsletter-signup-form';

/**
 * Storybook Definition.
 */
// export default { title: 'Molecules/Embed Elements/Twix' };

export const twixKeepInTouchFormModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: twixKeepInTouchForm() }} />
};

export const twixNewsletterSignupFormModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: twixNewsletterSignupForm() }} />
};

export const twixNewsletterSignupFormSuccessModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: twixNewsletterSignupFormSuccess() }} />
};
