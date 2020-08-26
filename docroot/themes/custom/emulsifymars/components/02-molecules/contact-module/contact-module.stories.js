import React from 'react';
import contactModule from './contact-module.twig';
import contactModuleData from './contact-module.yml';

export default { title: 'Molecules/Contact Module' };

export const contactModuleExample = () => {
  return <div dangerouslySetInnerHTML={{ __html: contactModule(contactModuleData) }} />;
};
