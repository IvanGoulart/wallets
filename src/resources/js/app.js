import './bootstrap';
// resources/js/app.js
import '../css/app.css'; // Importa o CSS do Tailwind (opcional, mas recomendado para garantir a ordem de carregamento)
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
