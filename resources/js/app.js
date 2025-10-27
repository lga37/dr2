import './bootstrap';
//import './charts';
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();





import 'chart.js/auto';
import 'chartjs-adapter-date-fns';

import { WordCloudController, WordElement } from 'chartjs-chart-wordcloud';


import annotationPlugin from 'chartjs-plugin-annotation';

import { Chart } from 'chart.js';


Chart.register(annotationPlugin);

Chart.register(WordCloudController, WordElement);


window.Chart = Chart;
