import React from 'react';
import badge from './badge.twig';
import badgeData from './badge.yml';

// export default { title: 'Atoms/Badge' };

export const badgeExample = () => (
  <div dangerouslySetInnerHTML={{ __html: badge(badgeData) }} />
);
