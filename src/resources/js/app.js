import './bootstrap';

import Alpine from 'alpinejs';

import.meta.glob([
    '../favicons/**',
  ]);

window.Alpine = Alpine;

Alpine.start();
