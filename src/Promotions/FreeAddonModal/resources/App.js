import React from 'react';
import ReactDOM from 'react-dom';

import Modal from './Modal';

const modalNode = document.createElement('div');
document.body.append(modalNode);

ReactDOM.render(<Modal />, modalNode);
