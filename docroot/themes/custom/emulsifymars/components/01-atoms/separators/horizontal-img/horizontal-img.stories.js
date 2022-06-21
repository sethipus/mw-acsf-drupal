import React from 'react';
import horizontalImg from './horizontal-img.twig';
import horizontalImgData from './horizontal-img.yml';

/**
 * Storybook Definition.
 */
// export default { title: 'Atoms/Separators' };

export const horizontalImageOBSOLETE = () => (
  <div dangerouslySetInnerHTML={{ __html: horizontalImg(horizontalImgData) }} />
);
