import React from 'react';
import errorComponent from './error-component.twig';
import errorComponentData from './error-component.yml';

// export default { title: 'Molecules/Error component' };

export const errorPageComponent = () => {
  return <div dangerouslySetInnerHTML={{ __html: errorComponent(errorComponentData) }} />
};
