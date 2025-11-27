import './bootstrap';

// import Alpine from 'alpinejs';
// window.Alpine = Alpine;
// Alpine.start();



import Alpine from 'alpinejs'
import focus from '@alpinejs/focus'
window.Alpine = Alpine
Alpine.plugin(focus)
Alpine.start()



import 'chart.js/auto';
import 'chartjs-adapter-date-fns';
import { WordCloudController, WordElement } from 'chartjs-chart-wordcloud';
import annotationPlugin from 'chartjs-plugin-annotation';
import { Chart } from 'chart.js';
Chart.register(annotationPlugin);
Chart.register(WordCloudController, WordElement);
window.Chart = Chart;





// import {
//   Chart, LinearScale, CategoryScale, PointElement, LineElement, Tooltip, Legend
// } from 'chart.js'
// Chart.register(LinearScale, CategoryScale, PointElement, LineElement, Tooltip, Legend)
// window.Chart = Chart
