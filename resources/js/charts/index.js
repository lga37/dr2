import { Chart, registerables } from 'chart.js';
import zoomPlugin from 'chartjs-plugin-zoom';
import 'chartjs-adapter-date-fns';

Chart.register(...registerables, zoomPlugin);

// expõe algo simples pra testar que o bundle carregou
window.DRCharts = {
  ping() { console.log('Chart.js OK', Chart.version); }
};




// // resources/js/charts/index.js
// import { Chart, registerables } from 'chart.js';
// import zoomPlugin from 'chartjs-plugin-zoom';
// import 'chartjs-adapter-date-fns';

// Chart.register(...registerables, zoomPlugin);

// const nf = new Intl.NumberFormat('pt-BR');

// function fontePointStyle(fonte){
//   const f = (fonte||'').toLowerCase();
//   if (f === 'api') return 'circle';
//   if (f === 'cdx') return 'rectRot';
//   if (f === 'calc') return 'triangle';
//   return 'rect';
// }

// function initSubs(elId, dadosSubs = [], bandSubs = []) {
//   const el = document.getElementById(elId);
//   if (!el) return;

//   // datasets
//   const dsSubs = {
//     label: 'Inscritos',
//     data: dadosSubs.map(d => ({ x: new Date(d.date), y: d.value, url: d.url, fonte: d.fonte })),
//     parsing: false,
//     borderWidth: 2,
//     tension: 0.25,
//     pointRadius(ctx){ return (ctx.raw?.url ? 5 : 4); },
//     pointHoverRadius: 7,
//     pointStyle(ctx){ return fontePointStyle(ctx.raw?.fonte); },
//   };

//   const ds = [dsSubs];
//   if (bandSubs && bandSubs.length) {
//     const grad = (ctx) => {
//       const g = ctx.createLinearGradient(0,0,0,400);
//       g.addColorStop(0, 'rgba(0,0,0,0.10)');
//       g.addColorStop(1, 'rgba(0,0,0,0.00)');
//       return g;
//     };
//     ds.unshift(
//       { label:'Faixa (max)', type:'line', data: bandSubs.map(b=>({x:new Date(b.date), y:b.max})),
//         borderWidth:0, pointRadius:0, fill:'+1', backgroundColor:(c)=>grad(c.chart.ctx) },
//       { label:'Faixa (min)', type:'line', data: bandSubs.map(b=>({x:new Date(b.date), y:b.min})),
//         borderWidth:0, pointRadius:0, fill:false, backgroundColor:'rgba(0,0,0,0)' },
//     );
//   }

//   const chart = new Chart(el, {
//     type: 'line',
//     data: { datasets: ds },
//     options: {
//       responsive: true,
//       interaction: { mode: 'nearest', intersect: false },
//       plugins: {
//         legend: { position:'top' },
//         tooltip: {
//           callbacks: {
//             title: (items)=> items.length ? new Date(items[0].parsed.x).toISOString().slice(0,10) : '',
//             label: (item)=> `Inscritos: ${nf.format(item.parsed.y)}`,
//             afterBody: (items)=>{
//               const d = items[0].raw || {};
//               const a = [];
//               if (d.fonte) a.push(`Fonte: ${String(d.fonte).toUpperCase()}`);
//               if (d.url)   a.push(`Snapshot: clique na linha`);
//               return a;
//             }
//           }
//         },
//         zoom: {
//           pan:  { enabled:true, mode:'x' },
//           zoom: { wheel:{enabled:true}, pinch:{enabled:true}, mode:'x' }
//         }
//       },
//       scales: {
//         x: { type:'time', time:{ unit:'month' } },
//         y: { ticks:{ callback:v=>nf.format(v) } }
//       },
//       onClick(evt, els){
//         if (!els?.length) return;
//         const i = els[0].index;
//         const d = dadosSubs[i];
//         if (d?.url) window.open(d.url, '_blank', 'noopener');
//       }
//     }
//   });

//   // reset de zoom no duplo clique
//   el.addEventListener('dblclick', ()=> chart.resetZoom?.());
// }

// function initEng(elId, dadosEng = []) {
//   const el = document.getElementById(elId);
//   if (!el) return;

//   const labels = dadosEng.map(d => d.month);
//   new Chart(el, {
//     data: {
//       labels,
//       datasets: [
//         { type:'bar', label:'Uploads/mês', yAxisID:'y1',
//           data: dadosEng.map(d=>d.uploads), borderWidth:0, borderRadius:6 },
//         { type:'line', label:'Views/mês', yAxisID:'y2',
//           data: dadosEng.map(d=>d.views), borderWidth:2, tension:.25, pointRadius:3 }
//       ]
//     },
//     options: {
//       parsing:false,
//       plugins:{ legend:{position:'top'} },
//       scales:{
//         x: { type:'time', time:{ unit:'month' } },
//         y1:{ position:'left', beginAtZero:true, title:{display:true,text:'Uploads'}, grid:{drawOnChartArea:false} },
//         y2:{ position:'right', title:{display:true,text:'Views'}, ticks:{ callback:v=>nf.format(v) } }
//       }
//     }
//   });
// }

// function initPolar(elId, dadosPolar = []) {
//   const el = document.getElementById(elId);
//   if (!el) return;

//   const coresPeriodo = {'2024 Q1':'#4e79a7','2024 Q2':'#59a14f','2024 Q3':'#e15759','2024 Q4':'#f28e2b'};
//   const pts = dadosPolar.map(d => ({
//     x:d.views, y:d.comments_ratio, r:Math.max(4, Math.min(14, d.uploads_week*4)),
//     periodo:d.periodo, title:d.title, date:d.date
//   }));

//   new Chart(el, {
//     type:'scatter',
//     data:{ datasets:[{
//       label:'Vídeos',
//       data: pts,
//       parsing:false,
//       pointRadius: ctx => ctx.raw?.r ?? 6,
//       pointHoverRadius: ctx => (ctx.raw?.r ?? 6) + 2,
//       backgroundColor: ctx => coresPeriodo[ctx.raw?.periodo] || '#7b7b7b'
//     }]},
//     options:{
//       plugins:{
//         legend:{display:false},
//         tooltip:{
//           callbacks:{
//             title: (items)=> items.length ? (items[0].raw?.date || '') : '',
//             label: (ctx)=>{
//               const r = ctx.raw || {};
//               return `${r.title||'Vídeo'} • ${r.periodo}\nViews: ${nf.format(r.x)} • 
// Comments ratio: ${(r.y*100).toFixed(1)}% • Uploads/semana: ${((r.r||6)/4).toFixed(0)}`;
//             }
//           }
//         },
//         zoom:{ pan:{enabled:true,mode:'xy'}, zoom:{wheel:{enabled:true}, pinch:{enabled:true}, mode:'xy'} }
//       },
//       scales:{
//         x:{ type:'logarithmic', title:{display:true,text:'Views (log)'}, ticks:{ callback:v=>nf.format(v) } },
//         y:{ title:{display:true,text:'comments_ratio'}, ticks:{ callback:v=>(v*100).toFixed(1)+'%' } }
//       }
//     }
//   });
// }

// window.DRCharts = { initSubs, initEng, initPolar };
