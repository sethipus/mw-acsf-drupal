import React from 'react';
import fonts from './fonts.twig';

// export default { title: 'Atoms/Fonts' };

export const fontDisplayTest = () => {
  return <div dangerouslySetInnerHTML={{ __html: fonts() }} />
};
