import React from 'react';
import entryGateForm from './entry-gate-form.twig';
import entryGateFormData from './entry-gate-form.yml';
import './entry-gate-form';

// export default { title: 'Molecules/Entry Gate Form' };

export const entryGateFormExample = () => (
  <div dangerouslySetInnerHTML={{ __html: entryGateForm(entryGateFormData) }} style={{padding: '10rem', backgroundColor: '#ccc'}}/>
);
