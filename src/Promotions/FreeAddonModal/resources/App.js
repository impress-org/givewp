import {render} from '@wordpress/element';

import Modal from './Modal';

const modalNode = document.createElement('div');
document.body.append(modalNode);

render(<Modal />, modalNode);
