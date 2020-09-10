import React from 'react';
import contactForm from './contact-form.twig';

export default { title: 'Molecules/Contact Form' };

export const contactFormModule = () => {
  return <div dangerouslySetInnerHTML={{ __html: contactForm() }} />
};
