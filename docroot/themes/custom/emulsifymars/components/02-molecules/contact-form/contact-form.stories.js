import React from 'react';
import contactForm from './contact-form.twig';
import { useEffect } from '@storybook/client-api';

import './contact-form';

export default { title: 'Molecules/Contact Form' };

export const contactFormModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: contactForm() }} />
};
