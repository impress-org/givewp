import React from 'react';
import ReactDOM from 'react-dom';

import './index.scss';
import App from './App';

import './blocks/fields/index'

import './blocks/nameFieldGroup'
import './blocks/nameFieldGroup/style.scss'

import './blocks/donation-amount-levels/index'

import './blocks/section/index'

ReactDOM.render(
  <React.StrictMode>
    <App />
  </React.StrictMode>,
  document.getElementById('root')
);
