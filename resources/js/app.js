import './bootstrap';
import Alpine from 'alpinejs';
import { installUploadGuard } from './upload-guard';

installUploadGuard();

// Make Alpine available globally
window.Alpine = Alpine;

Alpine.start();
