import React from 'react';

import newsLetterForm from './newsletter-form/newsletter-form.twig';
import newsLetterFormData from './newsletter-form/newsletter-form.yml';

import newsLetterEmailForm from './newsletter-email-form/newsletter-email-form.twig';
import newsLetterEmailFormData from './newsletter-email-form/newsletter-email-form.yml';
import './newsletter-form/newsletter-form';
/**
 * Storybook Definition.
 */
 export default { title: 'Components/[ML 33] Newsletter form' };

export const newsletterSignupFormModule = () => (
  <div dangerouslySetInnerHTML={{ __html: newsLetterForm(newsLetterFormData) }} />
);

export const newsletterSignupEmailFormModule = () => (
  <div dangerouslySetInnerHTML={{ __html: newsLetterEmailForm(newsLetterEmailFormData) }} />
);
